<?php


namespace MgSoftware\Image\Commands;

use gotrip\base\Console\Commands\AbstractCommand;
use Illuminate\Console\Command;
use DB;
use League\Flysystem\AdapterInterface;
use MgSoftware\Image\components\ImageComponent;
use MgSoftware\Image\components\ImageType;
use MgSoftware\Image\models\Image;
use MgSoftware\Image\models\ImageThumb;
use function Couchbase\defaultDecoder;

class ImageThumbsUpdateCommand extends AbstractCommand
{
    protected $signature = "image-thumbs:update";

    protected $description = "Checks if all images have thumbnails with correct parameters";

    public function handle()
    {
        $imageCount = DB::table('image_thumbs')->max('image_id');
        $this->getProgressBar()->start($imageCount);
        $imageComponent = new ImageComponent();
        for ($imageId = 1; $imageId <= $imageCount; $imageId++) {
            $thumbs = DB::table('image_thumbs')->where('image_id', $imageId)->get();
            $types = $thumbs->map->type;
            foreach(array_keys($imageComponent->types) as $type){
                if(!$types->contains($type)){
                    $this->createNewThumb($imageComponent, $imageId, $type);
                }
            }
            foreach ($thumbs as $thumb) {
                $params = ImageThumb::buildAttributes(ImageType::$params[$thumb->type]);
                foreach ($params as $param => $value) {
                    if ($thumb->{$param} !== $value) {
                        $type = $thumb->type;
                        $imageComponent->thumbStorage->delete($thumb->path);
                        DB::table('image_thumbs')->delete($thumb->id);
                        $this->createNewThumb($imageComponent, $imageId, $type);
                    }
                }
            }
            $this->getProgressBar()->advance();
        }
        $this->getProgressBar()->finish();
    }

    private function createNewThumb(ImageComponent $imageComponent, int $imageId, int $type)
    {
        /** @var Image $image */
        $image = Image::where('id', $imageId)->first();
        $content = $imageComponent->originalStorage->get($image->path);
        $imageComponent->createThumb($image, $content, $type);
    }
}
