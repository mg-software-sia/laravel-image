<?php

use Illuminate\Database\Migrations\Migration;
use MgSoftware\Image\components\ImageComponent;
use MgSoftware\Image\models\ImageThumb;

class UpdateImageThumbsParameters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('image_thumbs')->chunkById(1000, function ($thumbs) {
            foreach ($thumbs as $thumb) {
                /** @var ImageComponent $image */
                $image = app('image');
                $params = $image->types[$thumb->type];
                DB::table('image_thumbs')
                    ->where('id', $thumb->id)
                    ->update(ImageThumb::buildAttributes($params));
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
