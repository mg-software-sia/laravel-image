<?php

namespace MgSoftware\Image\models;

use Illuminate\Database\Eloquent\Model;

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
}