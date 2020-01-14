<?php
namespace Application\lib;

use Exception;
use Google\Cloud\Storage\StorageClient;
use Slim\Http\UploadedFile;

require_once __DIR__.'/../../../vendor/autoload.php';

class GoogleCloudStorage {
    private const projectId = 'jk3-late-dev-chirusapo';
    private const authFile = __DIR__ . '/../../auth/google-cloud-storage-key.json';
    private const bucketName = 'chirusapo';

    public static function upload(UploadedFile $file, string $file_path, $date = null) {
        try {
            if ($file->getError() == UPLOAD_ERR_OK) {
                if (is_null($date)) $date = date('Ymd-His');
                $client = new StorageClient([
                    'projectId' => self::projectId,
                    'keyFile' => json_decode(
                        file_get_contents(self::authFile, true), true
                    )
                ]);
                $bucket = $client->bucket(self::bucketName);

                $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
                $file_name = $date.'_'.random(10).'.'.$extension;

                $bucket->upload(fopen($file->file, 'r'), [
                    'name' => $file_path.'/'.$file_name
                ]);
                return $file_name;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public static function upload_tmp(string $file_name, string $file_path, $date = null) {
        try {
            if (is_null($date)) $date = date('Ymd-His');
            $client = new StorageClient([
                'projectId' => self::projectId,
                'keyFile' => json_decode(
                    file_get_contents(self::authFile, true), true
                )
            ]);
            $bucket = $client->bucket(self::bucketName);

            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = $date.'_'.random(10).'.'.$extension;

            $bucket->upload(fopen(__DIR__.'/../../../tmp/'.$file_name, 'r'), [
                'name' => $file_path.'/'.$new_file_name
            ]);
            return $new_file_name;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function delete($file_name) {
        try {
            $client = new StorageClient([
                'projectId' => self::projectId,
                'keyFile' => json_decode(
                    file_get_contents(self::authFile, true), true
                )
            ]);
            $bucket = $client->bucket(self::bucketName);
            $object = $bucket->object($file_name);
            $object->delete();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function allow_extension(UploadedFile $file) {
        switch (pathinfo($file->getClientFilename(), PATHINFO_EXTENSION)) {
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'mp4':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    public static function copy($object_name, $old_path, $new_path) {
        try {
            $client = new StorageClient([
                'projectId' => self::projectId,
                'keyFile' => json_decode(
                    file_get_contents(self::authFile, true), true
                )
            ]);
            $bucket = $client->bucket(self::bucketName);
            $object = $bucket->object($old_path.$object_name);
            $object->copy(self::bucketName, [
                'name' => $new_path.$object_name
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}