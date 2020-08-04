<?php

namespace MgSoftware\Image\models;

use Illuminate\Database\Eloquent\Model;
use MgSoftware\Image\components\ResizeComponent;

/**
 * MgSoftware\Image\models\ImageThumb
 *
 * @property int $id
 * @property int $image_id
 * @property int $type
 * @property string $path
 * @property int $height
 * @property int $width
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb query()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\ImageThumb whereWidth($value)
 * @mixin \Eloquent
 */
class ImageThumb extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'image_id',
        'type',
        'path',
        'param_height',
        'param_width',
        'param_jpeg_quality',
        'param_ratio',
        'param_blur',
        'param_no_zoom_in',
        'param_crop',
        'param_background',
        'param_normalize',
        'param_auto_gamma',
        'created_at'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public static function buildAttributes($params)
    {
        $params = ResizeComponent::mergeParamsWithDefault($params);
        $attributes = [
            'param_width' => static::_nullIfNotExists(ResizeComponent::PARAM_WIDTH, $params),
            'param_height' => static::_nullIfNotExists(ResizeComponent::PARAM_HEIGHT, $params),
            'param_jpeg_quality' => static::_nullIfNotExists(ResizeComponent::PARAM_JPEG_QUALITY, $params),
            'param_ratio' => static::_nullIfNotExists(ResizeComponent::PARAM_RATIO, $params),
            'param_blur' => static::_nullIfNotExists(ResizeComponent::PARAM_BLUR, $params),
            'param_no_zoom_in' => static::_nullIfNotExists(ResizeComponent::PARAM_NO_ZOOM_IN, $params),
            'param_crop' => static::_nullIfNotExists(ResizeComponent::PARAM_CROP, $params),
            'param_background' => static::_nullIfNotExists(ResizeComponent::PARAM_BACKGROUND, $params),
            'param_normalize' => static::_nullIfNotExists(ResizeComponent::PARAM_NORMALIZE, $params),
            'param_auto_gamma' => static::_nullIfNotExists(ResizeComponent::PARAM_AUTO_GAMMA, $params),
        ];
        return $attributes;
    }

    private static function _nullIfNotExists($key, $params, $boolColumn = false)
    {
        $result = array_key_exists($key, $params) ? $params[$key] : null;
        if (!$boolColumn || $result !== null) {
            return $result;
        }
        return $boolColumn ? 1 : 0;
    }
}
