<?php

namespace MgSoftware\Image\models;

use Illuminate\Database\Eloquent\Model;
use MgSoftware\Image\components\ImageComponent;

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
 * @property ImageThumb[] $thumbs
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

    private $_thumbPaths;

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

    public function getThumbs()
    {
        $this->hasMany(ImageThumb::class);
    }

    /**
     * Returns public url of image
     * @param int $type
     * @return string
     */
    public function getUrl(int $type): string
    {
        if (!$this->_thumbPaths) {
            $this->_thumbPaths = [];
            foreach ($this->thumbs as $thumb) {
                $this->_thumbPaths[$thumb->type] = $thumb->path;
            }
        }
        $path = $this->_thumbPaths[$type];
        /** @var ImageComponent $image */
        $image = app('image');
        return $image->getImageUrl($path);
    }

}