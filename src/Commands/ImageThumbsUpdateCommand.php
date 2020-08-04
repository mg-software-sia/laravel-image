<?php


namespace MgSoftware\Image\Commands;

use DB;
use Illuminate\Console\Command;
use MgSoftware\Image\components\ImageComponent;
use MgSoftware\Image\models\Image;
use MgSoftware\Image\models\ImageThumb;
use Symfony\Component\Console\Helper\ProgressBar;

class ImageThumbsUpdateCommand extends Command
{
    protected $signature = "image-thumbs:update";

    protected $description = "Checks if all images have thumbnails with correct parameters";

    private $imageContent = null;

    private $_progressBar;

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

    private function createNewThumb(ImageComponent $imageComponent, $image, int $type)
    {
        if (!$this->imageContent) {
            $this->imageContent = $imageComponent->originalStorage->get($image->path);
        }
        /** @var Image $image */
        $image = Image::where('id', $image->id)->first();
        $imageComponent->createThumb($image, $this->imageContent, $type);
    }

    private function checkParams($thumb, $imageComponent, $image)
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

    private function createProgressBar()
    {
        ProgressBar::setFormatDefinition(
            'image',
            '<info>Memory usage:</info> %memory% <fg=cyan>%bar%</> %percent%% (%current%/%max%) <info>Elapsed/ETA:</info> %elapsed%/%estimated%' . PHP_EOL
        );
        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat('image');
        return $progressBar;
    }

    private function getProgressBar()
    {
        if (!$this->_progressBar) {
            $this->_progressBar = $this->createProgressBar();
        }
        return $this->_progressBar;
    }
}
