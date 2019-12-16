<?php

namespace MgSoftware\Image\models;

use Illuminate\Database\Eloquent\Model;
use MgSoftware\Image\components\IconComponent;

/**
 * Class Icon
 *
 * @property int $id
 * @property string $filename
 * @property string $path
 * @property \Illuminate\Support\Carbon $created_at
 * @package MgSoftware\Image\models
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon query()
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\MgSoftware\Image\models\Icon wherePath($value)
 * @mixin \Eloquent
 */
class Icon extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'id',
        'filename',
        'path',
        'created_at'
    ];

    /**
     * Returns public url of svg icon
     * @return string
     */
    public function getUrl(): string
    {
        /** @var IconComponent $icon */
        $icon = app('icon');
        return $icon->getIconUrl($this->path);
    }

}