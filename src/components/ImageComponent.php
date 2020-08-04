<?php

namespace MgSoftware\Image\components;

use GuzzleHttp\Client;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use MgSoftware\Image\models\Image;
use MgSoftware\Image\models\ImageThumb;

/**
 * Class ImageComponent
 * @property string|null $scheme
 */
class ImageComponent
{

    /**
     * Storage adapter for original images
     * @var FilesystemAdapter
     */
    public $originalStorage;

    /**
     * Storage adapter for thumbnails
     * @var FilesystemAdapter
     */
    public $thumbStorage;


    /**
     * Component that is responsible for image resizing
     * @var ResizeComponent
     */
    public $resize = 'MgSoftware\Image\components\ResizeComponent';

    /**
     * @var array Pre resizes images
     */
    public $types = [];

    /**
     * Template for building urls.
     * To use hardcoded scheme (e.g. for console commands) set [[scheme]]
     * parameter in components configuration.
     * @var string
     */
    public $urlTemplate = '/{path}';

    /** @var string */
    public $thumbPathTemplate = '/{path}';

    /**
     * List of supported mime types
     * @var array
     */
    public $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'wbmp' => 'image/vnd.wap.wbmp',
        'xbm' => 'image/xbm',
    ];

    /**
     * Apply parameters for original image resize
     * @var array
     */
    public $originalImageParams = [
        ResizeComponent::PARAM_WIDTH => 3840,
        ResizeComponent::PARAM_HEIGHT => 2160,
        ResizeComponent::PARAM_RATIO => ResizeComponent::RATIO_MIN,
        ResizeComponent::PARAM_JPEG_QUALITY => 90,
    ];

    /** @inheritdoc */
    public function __construct()
    {
        $this->types = $this->getConfig('.types');
        $this->originalStorage = \Storage::drive($this->getConfig('.storage.original'));
        $this->thumbStorage = \Storage::drive($this->getConfig('.storage.thumb'));
        $this->resize = new ResizeComponent();

        if (!$this->originalStorage) {
            throw new InvalidArgumentException('`image` storage must be set.');
        }
        if (!$this->thumbStorage) {
            throw new InvalidArgumentException('`thumb` storage must be set.');
        }
    }

    public function getConfig(string $suffix = '')
    {
        return config('image'.$suffix);
    }

    /**
     * Saves image from UploadedFile instance
     * @param UploadedFile $instance
     * @return Image
     * @throws \Exception
     */
    public function saveFromInstance(UploadedFile $instance)
    {

        // Validate mime
        $mimeType = $instance->getMimeType();
        if (!$mimeType || !$this->isMimeTypeValid($mimeType)) {
            throw new \Exception('Wrong mime type. Given: '.$mimeType);
        }
        return $this->saveImage($instance->tempName, $mimeType);
    }

    /**
     * Saves image from local file
     * @param $fileName
     * @return Image
     * @throws \Exception|\Throwable
     */
    public function saveFromFile($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \Exception('file not exists: '.$fileName);
        }
        $data = file_get_contents($fileName);
        $base64 = base64_encode($data);
        return $this->saveFromBase64($base64);
    }

    /**
     * Saves image from url
     * @param $url
     * @return Image
     * @throws \Exception|\Throwable
     */
    public function saveFromUrl($url)
    {
        $client = new Client();
        $response = $client->get( $url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            ],
            'curl' => [
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to load image contents. Code: '.$response->getStatusCode().'. Url: '.$url);
        }
        $base64 = base64_encode($response->getBody());
        return $this->saveFromBase64($base64);
    }

    /**
     * Saves image from base64 string
     * @param $data
     * @return Image
     * @throws \Exception|\Throwable
     */
    public function saveFromBase64($data)
    {
        $f = finfo_open();
        $mimeType = finfo_buffer($f, base64_decode($data), FILEINFO_MIME_TYPE);
        unset($f);
        if (!$mimeType || !$this->isMimeTypeValid($mimeType)) {
            throw new \Exception('Wrong mime type. Given: '.$mimeType);
        }

        // Save image to tmp file
        $fh = tmpfile();
        stream_filter_append($fh, 'convert.base64-decode', STREAM_FILTER_WRITE);
        fwrite($fh, $data);

        // Save image
        $location = stream_get_meta_data($fh)['uri'];
        $image = $this->saveImage($location, $mimeType);

        // Close handle and return image
        fclose($fh);
        unset($fh);
        return $image;
    }

    /**
     * Saves image from temp location
     * @param string $tmpLocation Image temporary location
     * @param $mimeType
     * @return Image Image object
     * @throws \Exception|\Throwable
     */
    protected function saveImage($tmpLocation, $mimeType)
    {
        return \DB::transaction(function () use ($tmpLocation, $mimeType) {
            // Save image with temporary state
            $image = new Image();
            $image->path = $image->filename = 'temp';
            $image->mime_type = $mimeType;
            $image->height = 0;
            $image->width = 0;
            $image->saveOrFail();

            // Generate upload path and filename
            $extension = $this->getExtensionByMimeType($mimeType);
            $image->filename = $image->id.'.'.$extension;
            $image->path = $this->generateUploadPath($image->id).'/'.$image->filename;

            // Save content
            $content = $this->resize->thumbFromFile($tmpLocation, $extension, $this->originalImageParams);
            $this->originalStorage->put($image->path, $content, [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]);

            // Set image size and save image
            list($width, $height) = getimagesizefromstring($content);
            $image->height = $height;
            $image->width = $width;
            $image->saveOrFail();

            foreach (array_keys($this->types) as $type) {
                $this->createThumb($image, $content, $type);
            }

            return $image;
        });
    }

    public function createThumb(Image $image, $originalContent, $type)
    {
        \DB::transaction(function () use ($image, $originalContent, $type) {
            $params = $this->types[$type];

            $extension = $this->getExtensionByMimeType($image->mime_type);
            $content = $this->resize->thumbFromContent($originalContent, $extension, $params);
            $hash = md5($image->id.$type.microtime());

            $model = new ImageThumb(ImageThumb::buildAttributes($params));
            $model->image_id = $image->id;
            $model->type = $type;
            $model->path = $this->generateUploadPath($image->id).'/'.$hash.'/'.$image->filename;
            $model->saveOrFail();

            $this->thumbStorage->put($model->path, $content, [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC
            ]);
        });
    }

    /**
     * Returns public url of image
     * @param string $path
     * @return string
     */
    public function getImageUrl($path)
    {
        $directory = dirname($path);
        $filename = basename($path);
        $url = env('IMAGE_THUMB_URL').strtr($this->urlTemplate, [
            '{path}' => $path,
            '{directory}' => $directory,
            '{filename}' => $filename,
        ]);
        return $url;
    }

    /**
     * Returns extension by mime type
     * @param $mimeType
     * @return string|boolean
     */
    public function getExtensionByMimeType($mimeType)
    {
        return array_search($mimeType, $this->mimeTypes);
    }

    /**
     * Checks if mime type is valid
     * @param $mimeType
     * @return bool
     */
    public function isMimeTypeValid($mimeType)
    {
        return in_array($mimeType, $this->mimeTypes);
    }

    /**
     * This is native php function wrapper, native function raises error.
     * @param $fileName
     * @param null $imageInfo
     * @return array
     * @throws \Exception
     */
    public function getImageSize($fileName, &$imageInfo = null)
    {
        $data = @getimagesize($fileName, $imageInfo);
        if ($data === false) {
            throw new \Exception('Failed to get image size.');
        }
        return [
            'width' => (int) $data[0],
            'height' => (int) $data[1],
            'mimeType' => $data['mime'],
        ];
    }

    /**
     * Returns base path for image
     * @param $id
     * @return string
     */
    protected function generateUploadPath($id)
    {
        $parentFolder = date('Ym', time());

        $leadingZeros = str_pad($id, 6, '0', STR_PAD_LEFT);
        $folder1 = substr($leadingZeros, 0, 3);
        $folder2 = substr($leadingZeros, 3);
        $path = implode('/', [$parentFolder, $folder1, $folder2]);
        $path = trim($path, '/');

        return $path;
    }
}
