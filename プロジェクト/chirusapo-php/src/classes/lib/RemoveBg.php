<?php
namespace Application\Lib;

use Application\lib\GoogleCloudStorage as GCS;
use Exception;
use GuzzleHttp\Client;
use Slim\Http\UploadedFile;

class RemoveBg {
    public static function file(UploadedFile $file, $api_key) {
        try {
            $client = new Client();
            $res = $client->post('https://api.remove.bg/v1.0/removebg', [
                'multipart' => [
                    [
                        'name' => 'image_file',
                        'contents' => fopen($file->file, 'r')
                    ],
                    [
                        'name' => 'size',
                        'contents' => 'auto'
                    ]
                ],
                'headers' => [
                    'X-Api-Key' => $api_key
                ]
            ]);
            $file_name = random(30);
            if ($res->getStatusCode() == 200) {
                $fp = fopen(__DIR__.'/../../../tmp/'.$file_name.'.png', 'wb');
                fwrite($fp, $res->getBody());
                fclose($fp);

                $new_file_name = GCS::upload_tmp($file_name.'.png', 'cache');
                if ($new_file_name) {
                    return $new_file_name;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}