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
        'height',
        'width',
        'created_at'
    ];

    public function getImage()
    {
        return $this->belongsTo(Image::class);
    }

    public static function buildAttributes($params)
    {
        $attributes = [
            'width' => static::_nullIfNotExists(ResizeComponent::PARAM_WIDTH, $params),
            'height' => static::_nullIfNotExists(ResizeComponent::PARAM_HEIGHT, $params),
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