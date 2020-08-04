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
        $imageCount = DB::table('images')->count();
        $this->getProgressBar()->start($imageCount);
        $imageComponent = new ImageComponent();
        foreach (DB::table('images')->cursor() as $image) {
            $thumbs = DB::table('image_thumbs')->where('image_id', $image->id)->get();
            $types = $thumbs->map->type;
            foreach (array_keys($imageComponent->types) as $type) {
                if (!$types->contains($type)) {
                    $this->createNewThumb($imageComponent, $image->id, $type);
                }
            }
            foreach ($thumbs as $thumb) {
                $this->checkParams($thumb, $imageComponent);
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

    private function checkParams($thumb, $imageComponent)
    {
        $type = $thumb->type;
        $imageId = $thumb->image_id;
        $params = ImageThumb::buildAttributes(ImageType::$params[$type]);
        foreach ($params as $param => $value) {
            if ($thumb->{$param} !== $value) {
                DB::transaction(function () use ($thumb, $imageComponent, $imageId, $type) {
                    $path = $thumb->path;
                    DB::table('image_thumbs')->delete($thumb->id);
                    $this->createNewThumb($imageComponent, $imageId, $type);
                    $imageComponent->thumbStorage->delete($path);
                });
                return;
            }
        }
    }
}
