<?php
namespace Application\Controllers;

use Application\App\ChildFaceManager;
use Application\App\ChildManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\GoogleCloudStorage as GCS;
use Slim\Http\Request;
use Slim\Http\Response;

class ChildFaceController {
    public static function get_face(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;

        $error = [];

        if (is_nulls($token, $child_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_child_id = ChildManager::child_id_to_inner_child_id($child_id);

            if (!$user_id || !$inner_child_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_child_id) $error[] = Error::$UNKNOWN_CHILD;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!ChildManager::have_child($user_id, $inner_child_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    $face_list = ChildFaceManager::get_face($inner_child_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'face_list' => $face_list
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_face(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;
        $face_image = isset($file['face_image']) ? $file['face_image'] : null;

        $error = [];

        if (is_nulls($token, $child_id, $face_image)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_child_id = ChildManager::child_id_to_inner_child_id($child_id);

            if (!$user_id || !$inner_child_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_child_id) $error[] = Error::$UNKNOWN_CHILD;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!ChildManager::have_child($user_id, $inner_child_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    if (!GCS::allow_extension($face_image)) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$ALLOW_EXTENSION
                            ],
                            'data' => null
                        ];
                    } else {
                        $file_name = GCS::upload($face_image, 'face-recognition/child');

                        if (!$file_name) {
                            $result = [
                                'status' => 400,
                                'message' => [
                                    Error::$UPLOAD_FAILED
                                ],
                                'data' => null
                            ];
                        } else {
                            $face_id = ChildFaceManager::add_face($inner_child_id, $file_name);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'face_info' => [
                                        'id' => $face_id,
                                        'file_name' => 'https://storage.googleapis.com/chirusapo/face-recognition/child/'.$file_name
                                    ]
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function delete_face(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $file_name = isset($param['file_name']) ? $param['file_name'] : null;

        $error = [];

        if (is_nulls($token, $file_name)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $child_id = ChildFaceManager::file_name_to_child_id($file_name);

            if (!$user_id || !$child_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$child_id) $error[] = Error::$UNKNOWN_FACE;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!ChildManager::have_child($user_id, $child_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    ChildFaceManager::delete_face($file_name);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => null
                    ];
                }
            }
        }

        return $response->withJson($result);
    }
}