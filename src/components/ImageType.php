<?php

namespace MgSoftware\Image\components;

use MgSoftware\Image\components\ResizeComponent;

class ImageType
{
    const TYPE_LARGE = 100;
    const TYPE_MEDIUM = 200;
    const TYPE_SMALL = 300;
    const TYPE_SMALL_BLURRED = 400;

    public static $params = [
        self::TYPE_LARGE => [
            ResizeComponent::PARAM_WIDTH => 1200,
            ResizeComponent::PARAM_HEIGHT => 800,
            ResizeComponent::PARAM_RATIO => ResizeComponent::RATIO_MIN,
            ResizeComponent::PARAM_NORMALIZE => 1,
            ResizeComponent::PARAM_JPEG_QUALITY => 90,
        ],
        self::TYPE_MEDIUM => [
            ResizeComponent::PARAM_WIDTH => 600,
            ResizeComponent::PARAM_HEIGHT => 400,
            ResizeComponent::PARAM_RATIO => ResizeComponent::RATIO_MAX,
            ResizeComponent::PARAM_NORMALIZE => 1,
            ResizeComponent::PARAM_JPEG_QUALITY => 90,
        ],
        self::TYPE_SMALL => [
            ResizeComponent::PARAM_WIDTH => 300,
            ResizeComponent::PARAM_HEIGHT => 200,
            ResizeComponent::PARAM_RATIO => ResizeComponent::RATIO_MAX,
            ResizeComponent::PARAM_NORMALIZE => 1,
            ResizeComponent::PARAM_JPEG_QUALITY => 90,
        ],
        self::TYPE_SMALL_BLURRED => [
            ResizeComponent::PARAM_WIDTH => 60,
            ResizeComponent::PARAM_HEIGHT => 40,
            ResizeComponent::PARAM_RATIO => ResizeComponent::RATIO_MAX,
            ResizeComponent::PARAM_BLUR => 5,
        ],
    ];
}