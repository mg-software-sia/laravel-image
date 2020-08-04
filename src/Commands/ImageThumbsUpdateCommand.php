<?php


namespace MgSoftware\Image\Commands;

use gotrip\base\Console\Commands\AbstractCommand;
use DB;
use MgSoftware\Image\components\ImageComponent;
use MgSoftware\Image\models\Image;
use MgSoftware\Image\models\ImageThumb;

class ImageThumbsUpdateCommand extends AbstractCommand
{
    protected $signature = "image-thumbs:update";

    protected $description = "Checks if all images have thumbnails with correct parameters";

    private $imageContent = null;

    public function handle()
    {
        $imageCount = DB::table('images')->count();
        $this->getProgressBar()->start($imageCount);
        /** @var  ImageComponent $imageComponent */
        $imageComponent = app('image');
        foreach (DB::table('images')->cursor() as $image) {
            $this->imageContent = null;
            $thumbs = DB::table('image_thumbs')->where('image_id', $image->id)->get();
            $types = $thumbs->map->type;
            foreach (array_keys($imageComponent->types) as $type) {
                if (!$types->contains($type)) {
                    $this->createNewThumb($imageComponent, $image, $type);
                }
            }
            foreach ($thumbs as $thumb) {
                $this->checkParams($thumb, $imageComponent, $image);
            }
            $this->getProgressBar()->advance();
        }
        $this->getProgressBar()->finish();
    }
    private function createNewThumb(ImageComponent $imageComponent, Image $image, int $type)
    {
        if (! $this->imageContent) {
            $this->imageContent = $imageComponent->originalStorage->get($image->path);
        }
        $imageComponent->createThumb($image, $this->imageContent, $type);
    }

    private function checkParams($thumb, $imageComponent, Image $image)
    {
        $type = $thumb->type;
        $params = ImageThumb::buildAttributes($imageComponent->types[$type]);
        foreach ($params as $param => $value) {
            if ($thumb->{$param} !== $value) {
                DB::transaction(function () use ($thumb, $imageComponent, $image, $type) {
                    $path = $thumb->path;
                    DB::table('image_thumbs')->delete($thumb->id);
                    $this->createNewThumb($imageComponent, $image, $type);
                    $imageComponent->thumbStorage->delete($path);
                });
                return;
            }
        }
    }
}
