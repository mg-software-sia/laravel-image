<?php

return [
    'types' => \MgSoftware\Image\components\ImageType::$params,
    'storage' => [
        'original' => env('IMAGE_ORIGINAL_STORAGE', 'image'),
        'thumb' => env('IMAGE_THUMB_STORAGE', 'thumb'),
        'icon' => env('ICON_STORAGE', 'icon')
    ]
];