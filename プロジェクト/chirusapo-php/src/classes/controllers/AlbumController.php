<?php
namespace Application\Controllers;

use Application\App\AlbumManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\GoogleCloudStorage as GCS;
use Slim\Http\Request;
use Slim\Http\Response;

class AlbumController {
    public static function get_album(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;

        $error = [];

        if (is_nulls($token, $group_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_group_id = GroupManager::get_group_id($group_id);

            if (!$user_id || !$inner_group_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_group_id) $error[] = Error::$UNKNOWN_GROUP;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!GroupManager::already_belong_group($inner_group_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $album_data = AlbumManager::get_album($inner_group_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'album_data' => $album_data
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function upload_album(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $photo_image = isset($file['photo']) ? $file['photo'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $photo_image)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_group_id = GroupManager::get_group_id($group_id);

            if (!$user_id || !$inner_group_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_group_id) $error[] = Error::$UNKNOWN_GROUP;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!GroupManager::already_belong_group($inner_group_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $update_flg = true;
                    $photo_file_name = false;

                    if (GCS::allow_extension($photo_image)) {
                        $photo_file_name = GCS::upload($photo_image, 'album');

                        if (!$photo_file_name) {
                            $error[] = Error::$UPLOAD_FAILED;
                            $update_flg = false;
                        }
                    } else {
                        $error[] = Error::$ALLOW_EXTENSION;
                        $update_flg = false;
                    }

                    if (!$update_flg || !$photo_file_name) {
                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        AlbumManager::add_album($inner_group_id, $photo_file_name);

                        $album_data = AlbumManager::get_album($inner_group_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'album_data' => $album_data
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }
}