<?php

namespace ImageProxyPHP;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Exception;

class S3ClientWrapper {
    private static $s3Client = null;

    public function __construct() {
        if (self::$s3Client === null) {
            self::$s3Client = new S3Client([
                'version'     => 'latest',
                'region'      => 'us-west-2',
                'credentials' => [
                    'key'    => getenv('AWS_ACCESS_KEY_ID'),
                    'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
        }
    }

    public function getObject($bucket, $key) {
        try {
            return self::$s3Client->getObject([
                'Bucket' => $bucket,
                'Key'    => $key
            ]);
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                // Handle the case where the key doesn't exist
                throw new Exception("The specified key does not exist in the S3 bucket: " . $e->getMessage());
            } else {
                // Handle other S3 exceptions
                throw new Exception("Error fetching object from S3: " . $e->getMessage());
            }
        } catch (AwsException $e) {
            // Handle other AWS exceptions
            throw new Exception("Error fetching object from S3: " . $e->getMessage());
        }
    }

    // Here we would add more functions like to post an image or get the list of image keys
}

?>