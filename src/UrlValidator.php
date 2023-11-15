<?php

namespace ImageProxyPHP;

use InvalidArgumentException;

class UrlValidator {
    public function validate($imageName, $width, $height, $type) {
        if (!$imageName) {
            throw new InvalidArgumentException("Image name is required.");
        }

        if ($width && !is_numeric($width)) {
            throw new InvalidArgumentException("Width must be a number.");
        }

        if ($height && !is_numeric($height)) {
            throw new InvalidArgumentException("Height must be a number.");
        }

        $valid_types = ['jpg', 'jpeg', 'png', 'webp'];
        if ($type && !in_array($type, $valid_types)) {
            throw new InvalidArgumentException("Invalid type. Valid types are: " . implode(', ', $valid_types));
        }
    }
}