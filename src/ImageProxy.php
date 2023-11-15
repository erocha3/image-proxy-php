<?php

namespace ImageProxyPHP;

use Imagick;
use ImagickException;
use InvalidArgumentException;
use Exception;

class ImageProxy {
    private $s3ClientWrapper;
    private $urlValidator;

    const IMAGE_TYPES = [
        'jpg' => [
            'mime_type' => 'image/jpeg',
            'compression' => Imagick::COMPRESSION_JPEG,
            'format'    => 'jpeg',
        ],
        'jpeg' => [
            'mime_type' => 'image/jpeg',
            'compression' => Imagick::COMPRESSION_JPEG,
            'format'    => 'jpeg',
        ],
        'png' => [
            'mime_type' => 'image/png',
            'compression' => Imagick::COMPRESSION_ZIP,
            'format'    => 'png',
        ],
        'webp' => [
            'mime_type' => 'image/webp',
            'compression' => null, // WebP handles its own compression
            'format'    => 'webp',
        ],
    ];

    public function __construct(UrlValidator $urlValidator, S3ClientWrapper $s3ClientWrapper) {
        $this->urlValidator = $urlValidator;
        $this->s3ClientWrapper = $s3ClientWrapper;
    }

    public function serveImage($imageName, $width = null, $height = null, $type = null) {

        // Normalize the type
        $type = !$type ? 'jpg' : strtolower($type);

        try {
            $this->urlValidator->validate($imageName, $width, $height, $type);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo $e->getMessage();
            exit;
        }

        // Generate an ETag and check if an If-None-Match header was sent
        $etag = md5($imageName . $width . $height . $type);

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') == $etag) {
            http_response_code(304);
            exit;
        }

        // Fetch from S3 client
        try {
            $result = $this->s3ClientWrapper->getObject(getenv('S3_BUCKET'), getenv('S3_FOLDER') . '/' . $imageName . '.jpg');
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error fetching image from S3: " . $e->getMessage();
            exit;
        }

        // Load the image into Imagick
        $image = new Imagick();

        try {
            $image->readImageBlob($result['Body']);
        } catch (ImagickException $e) {
            http_response_code(500);
            echo "Error reading image: " . $e->getMessage();
            exit;
        }

        // Resize the image
        if ($width || $height) {
            $newWidth = $width ?: $height * ($image->getImageWidth() / $image->getImageHeight());
            $newHeight = $height ?: $width * ($image->getImageHeight() / $image->getImageWidth());

            try {
                $image->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);
            } catch (ImagickException $e) {
                http_response_code(500);
                echo "Error resizing image: " . $e->getMessage();
                exit;
            }
        }

         // Get the MIME type, compression algorithm and universal format for the desired image type
        $image_type = self::IMAGE_TYPES[$type];

        // Get the current image format
        $currentFormat = strtolower($image->getImageFormat());

        // Only change the format if the current format is different from the requested format
        if ($currentFormat != $image_type['format']) {
            try {
                $image->setImageFormat($type);
            } catch (ImagickException $e) {
                http_response_code(500);
                echo "Error changing image format: " . $e->getMessage();
                exit;
            }
        }

        // Set the image compression if needed
        if ($image_type['compression'] !== null) {
            try {
                $image->setImageCompression($image_type['compression']);
            } catch (ImagickException $e) {
                http_response_code(500);
                echo "Error setting image compression: " . $e->getMessage();
                exit;
            }
        }

        // Strip the image metadata
        try {
            $image->stripImage();
        } catch (ImagickException $e) {
            http_response_code(500);
            echo "Error stripping image metadata: " . $e->getMessage();
            exit;
        }

        // Get the MIME type from the map, or default to 'image/jpeg'
        $mime_type = $image_type['mime_type'];

        // Set cache headers
        $this->setCacheHeaders($etag);

        // Output the image
        header("Content-Type: {$mime_type}");
        header("Content-Disposition: inline; filename=\"{$imageName}.{$type}\"");
        echo $image;
    }

    private function setCacheHeaders($etag) {
        $cache_max_age = getenv('CACHE_MAX_AGE') ?? 86400;
        header("Cache-Control: public, max-age={$cache_max_age}");
        header('ETag: "' . $etag . '"');
        header('Vary: Accept');
    }
}