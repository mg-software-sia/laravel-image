<?php

namespace MgSoftware\Image\components;

use GuzzleHttp\Client;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\UploadedFile;
use League\Flysystem\AdapterInterface;
use MgSoftware\Image\models\Icon;

/**
 * Class IconComponent
 * @property string|null $scheme
 */
class IconComponent
{

    /**
     * Storage adapter for icons
     * @var FilesystemAdapter
     */
    public $iconStorage;

    /**
     * Template for building urls.
     * To use hardcoded scheme (e.g. for console commands) set [[scheme]]
     * parameter in components configuration.
     * @var string
     */
    public $urlTemplate = '/{path}';

    /**
     * Icon mime types
     * @var array
     */
    public $mimeTypes = [
        'image/svg-xml',
        'image/svg+xml',
        'image/svg'
    ];

    /**
     * Icon mime type extension
     * @var string
     */
    public $mimeTypeExtension = 'svg';

    /** @inheritdoc */
    public function __construct()
    {
        $this->iconStorage = \Storage::drive($this->getConfig('.storage.icon'));

        if (!$this->iconStorage) {
            throw new InvalidConfigException('`icon` storage must be set.');
        }
    }

    public function getConfig(string $suffix = '')
    {
        return config('image'.$suffix);
    }

    /**
     * Saves icon from UploadedFile instance
     * @param UploadedFile $instance
     * @return Icon
     * @throws \Exception
     */
    public function saveFromInstance(UploadedFile $instance)
    {

        // Validate mime
        $mimeType = $instance->getMimeType();
        if (!$mimeType || !$this->isMimeTypeValid($mimeType)) {
            throw new \Exception('Wrong mime type. Given: '.$mimeType);
        }
        return $this->saveIcon($instance->tempName);
    }

    /**
     * Saves icon from local file
     * @param $fileName
     * @return Icon
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
     * Saves icon from url
     * @param $url
     * @return Icon
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
            throw new \Exception('Failed to load icon contents. Code: '.$response->getStatusCode().'. Url: '.$url);
        }
        $base64 = base64_encode($response->getBody());
        return $this->saveFromBase64($base64);
    }

    /**
     * Saves icon from base64 string
     * @param $data
     * @return Icon
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

        // Save icon to tmp file
        $fh = tmpfile();
        stream_filter_append($fh, 'convert.base64-decode', STREAM_FILTER_WRITE);
        fwrite($fh, $data);

        // Save icon
        $location = stream_get_meta_data($fh)['uri'];
        $icon = $this->saveIcon($location);

        // Close handle and return icon
        fclose($fh);
        unset($fh);
        return $icon;
    }

    /**
     * Saves icon from temp location
     * @param string $tmpLocation Icon temporary location
     * @return Icon Icon object
     * @throws \Exception|\Throwable
     */
    protected function saveIcon($tmpLocation)
    {
        return \DB::transaction(function () use ($tmpLocation) {
            // Save icon with temporary state
            $icon = new Icon();
            $icon->path = $icon->filename = 'temp';
            $icon->saveOrFail();

            // Generate upload path and filename
            $hash = md5($icon->id.microtime());
            $icon->filename = $icon->id.'.'.$this->mimeTypeExtension;
            $icon->path = $this->generateUploadPath($icon->id).'/'.$hash.'/'.$icon->filename;

            // Save content
            $content = file_get_contents($tmpLocation);
            $this->iconStorage->put($icon->path, $content, [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC
            ]);

            $icon->saveOrFail();

            return $icon;
        });
    }

    /**
     * Returns public url of icon
     * @param string $path
     * @return string
     */
    public function getIconUrl($path)
    {
        $directory = dirname($path);
        $filename = basename($path);
        $url = env('ICON_URL').strtr($this->urlTemplate, [
            '{path}' => $path,
            '{directory}' => $directory,
            '{filename}' => $filename,
        ]);
        return $url;
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
     * Returns base path for icon
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