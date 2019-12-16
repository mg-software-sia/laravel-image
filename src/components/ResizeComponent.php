<?php

namespace MgSoftware\Image\components;

use Imagine\Image\ImageInterface as ImagineInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Imagine\Imagick\Image as ImagineImage;
use Imagine\Imagick\Imagine;

class ResizeComponent
{
    // Parameters
    const PARAM_WIDTH = 'w';
    const PARAM_HEIGHT = 'h';
    const PARAM_JPEG_QUALITY = 'q';
    const PARAM_RATIO = 'r';
    const PARAM_BLUR = 'b';
    const PARAM_NO_ZOOM_IN = 'nz';
    const PARAM_CROP = 'c';
    const PARAM_BACKGROUND = 'bg';
    const PARAM_NORMALIZE = 'n';
    const PARAM_AUTO_GAMMA = 'ag';

    const RATIO_MIN = 'min'; // any of sides is not larger than specified
    const RATIO_MAX = 'max'; // any of sides is equal or larger than specified (Images are not zoomed in, if they are smaller)

    /** @var array Default parameters */
    public $defaultParameters = [
        self::PARAM_JPEG_QUALITY => 75,
        self::PARAM_RATIO => self::RATIO_MAX,
        self::PARAM_CROP => 0,
        self::PARAM_NO_ZOOM_IN => 1,
    ];

    /**
     * Performs image resize and returns image content
     * @param $originalFile
     * @param $extension
     * @param $params
     * @return string
     */
    public function thumbFromFile($originalFile, $extension, $params)
    {
        $original = (new Imagine())->open($originalFile);

        $image = $this->createImage($original, $params, $options);
        $thumb = $image->get($extension, $options);

        $original->getImagick()->clear();
        $image->getImagick()->clear();

        return $thumb;
    }

    /**
     * Performs image resize and returns image content
     * @param $content
     * @param $extension
     * @param $params
     * @return string
     */
    public function thumbFromContent($content, $extension, $params)
    {
        $original = (new Imagine())->load($content);

        $image = $this->createImage($original, $params, $options);
        $thumb = $image->get($extension, $options);

        $original->getImagick()->clear();
        $image->getImagick()->clear();

        return $thumb;
    }

    /**
     * Calculates ratio based on parameters
     * @param int $originalWidth
     * @param int $originalHeight
     * @param array $params
     * @return float
     */
    public static function calculateRatio(int $originalWidth, int $originalHeight, array $params): float
    {
        // Calculate image ratios
        $ratios = [];
        if (isset($params[static::PARAM_WIDTH])) {
            $ratios[] = (int) $params[static::PARAM_WIDTH] / $originalWidth;
        }
        if (isset($params[static::PARAM_HEIGHT])) {
            $ratios[] = (int) $params[static::PARAM_HEIGHT] / $originalHeight;
        }
        if (!$ratios) {
            return 1;
        }

        // Choose ratio by ratio algorithm
        if (isset($params[static::PARAM_RATIO]) && $params[static::PARAM_RATIO] == static::RATIO_MIN) {
            $ratio = min($ratios);
        } else {
            $ratio = max($ratios);
        }

        // No zoom in image
        $noZoomIn = !isset($params[static::PARAM_NO_ZOOM_IN]) || $params[static::PARAM_NO_ZOOM_IN];
        if ($noZoomIn && $ratio >= 1) {
            return 1;
        }
        return $ratio;
    }

    public function mergeParamsWithDefault($params)
    {
        return array_merge($this->defaultParameters, $params);
    }

    /**
     * Creates image instance from given parameters
     * @param $originalFile
     * @param $params
     * @param $options
     * @return ImagineImage
     */
    protected function createImage(ImagineImage $original, $params, &$options)
    {
        $params = $this->mergeParamsWithDefault($params);
        if (isset($params[static::PARAM_BACKGROUND])) {
            $color = (new RGB())->color($params[static::PARAM_BACKGROUND]);
            $image = (new Imagine())->create($original->getSize(), $color);
            $image->paste($original, new Point(0, 0));
        } else {
            $image = $original;
        }

        if (isset($params[static::PARAM_NORMALIZE]) && $params[static::PARAM_NORMALIZE]) {
            $image->getImagick()->normalizeImage();
        }

        if (isset($params[static::PARAM_AUTO_GAMMA]) && $params[static::PARAM_AUTO_GAMMA]) {
            $image->getImagick()->autoGammaImage();
        }

        // Build options for image processing
        $options = [];
        if (isset($params[static::PARAM_JPEG_QUALITY])) {
            $options['jpeg_quality'] = $params[static::PARAM_JPEG_QUALITY];
        }
        if ($image->layers()->count() > 1) {
            $options['animated'] = true;
        }

        // Crop image
        if (isset($params[static::PARAM_CROP]) && $params[static::PARAM_CROP] && isset($params[static::PARAM_WIDTH], $params[static::PARAM_HEIGHT])) {
            $image->getImagick()->cropThumbnailImage((int) $params[static::PARAM_WIDTH], (int) $params[static::PARAM_HEIGHT]);
        } // Resize image
        else if (isset($params[static::PARAM_WIDTH]) || isset($params[static::PARAM_HEIGHT])) {
            $this->resize($image, $params);
        }

        // Blur image
        if (isset($params[static::PARAM_BLUR])) {
            $blur = (int) $params[static::PARAM_BLUR];
            $image->effects()->blur($blur);
        }

        return $image;
    }

    /**
     * Performs image resize
     * @param ImagineImage $image
     * @param $params
     */
    protected function resize(ImagineImage $image, $params)
    {
        // Animated gif
        if ($image->layers()->count() > 1) {
            $image->layers()->coalesce();
            foreach ($image->layers() as $frame) {
                $frame->interlace(ImagineInterface::INTERLACE_PLANE);
                $this->resizeLayer($frame, $params);
            }
        } // Standard image
        else {
            $image->interlace(ImagineInterface::INTERLACE_PLANE);
            $this->resizeLayer($image, $params);
        }
    }

    /**
     * Resize image layer
     * @param ImagineImage $image
     * @param $params
     * @return ImagineImage
     */
    protected function resizeLayer(ImagineImage $image, $params)
    {
        $imageSize = $image->getSize();
        $ratio = static::calculateRatio($imageSize->getWidth(), $imageSize->getHeight(), $params);
        if ($ratio === 1) {
            return $image;
        }

        // Resize image
        $box = $imageSize->scale($ratio);
        return $image->resize($box);
    }
}