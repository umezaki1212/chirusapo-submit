<?php
namespace Application\Controllers;

use Application\lib\Error;
use Application\Lib\RemoveBg;
use Slim\Http\Request;
use Slim\Http\Response;

class RemoveBgController {
    public static function bg_remove(Request $request, Response $response) {

        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $api_key = isset($param['api_key']) ? $param['api_key'] : null;
        $image = isset($file['image']) ? $file['image'] : null;

        if (is_nulls($api_key, $image)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $file_name = RemoveBg::file($image, $api_key);

            if (!$file_name) {
                $result = [
                    'status' => 400,
                    'message' => null,
                    'data' => null
                ];
            } else {
                $result = [
                    'status' => 200,
                    'message' => null,
                    'data' => [
                        'remove_image' => 'https://storage.googleapis.com/chirusapo/cache/'.$file_name
                    ]
                ];
            }
        }

        return $response->withJson($result);
    }
}