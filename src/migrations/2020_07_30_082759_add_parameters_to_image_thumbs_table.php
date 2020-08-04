<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParametersToImageThumbsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `image_thumbs`
            ADD COLUMN `param_jpeg_quality` SMALLINT(3) NULL AFTER `param_height`,
            ADD COLUMN `param_ratio` CHAR(3) NULL AFTER `param_jpeg_quality`,
            ADD COLUMN `param_blur` SMALLINT(3) NULL AFTER `param_ratio`,
            ADD COLUMN `param_no_zoom_in` TINYINT(1) NULL AFTER `param_blur`,
            ADD COLUMN `param_crop` TINYINT(1) NULL AFTER `param_no_zoom_in`,
            ADD COLUMN `param_background` CHAR(7) NULL AFTER `param_crop`,
            ADD COLUMN `param_normalize` TINYINT(1) NULL AFTER `param_background`,
            ADD COLUMN `param_auto_gamma` TINYINT(1) NULL AFTER `param_normalize`;
            ");
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
