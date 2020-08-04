<?php


use Illuminate\Database\Migrations\Migration;

class UpdateImageThumbsTableColumns extends Migration
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
            CHANGE COLUMN `width` `param_width` SMALLINT(5) UNSIGNED NULL DEFAULT NULL ,
            CHANGE COLUMN `height` `param_height` SMALLINT(5) UNSIGNED NULL DEFAULT NULL ;");
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
