<?php

namespace MgSoftware\Image\models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Image
 *
 * @property int $id
 * @property string $filename
 * @property string $path
 * @property string $mime_type
 * @property int $height
 * @property int $width
 * @property \Illuminate\Support\Carbon $created_at
 * @package MgSoftware\Image\models
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Image whereWidth($value)
 * @mixin \Eloquent
 */
class Image extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'id',
        'filename',
        'path',
        'mime_type',
        'height',
        'width',
        'created_at'
    ];

    /**
     * Returns public url of image
     * @return string
     */
    public function getUrl()
    {
        return '';
    }

}