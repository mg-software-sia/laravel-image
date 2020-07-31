<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use MgSoftware\Image\components\ImageType;
use MgSoftware\Image\components\ResizeComponent;
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
        $thumbs = DB::table('image_thumbs')->get();
        foreach ($thumbs as $thumb){
            $params = ImageType::$params[$thumb->type];
            DB::table('image_thumbs')
                ->where('id', $thumb->id)
                ->update(ImageThumb::buildAttributes($params));
        }
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
