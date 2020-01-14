<?php
namespace Application\Controllers;

use Application\app\GroupManager;
use Application\app\ModelManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\GoogleCloudStorage as GCS;
use Application\Lib\RemoveBg;
use Slim\Http\Request;
use Slim\Http\Response;

class ModelController {
    public static function get_model(Request $request, Response $response) {
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
                    $model = ModelManager::get_model($inner_group_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => $model
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_child(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $model_image = isset($file['model']) ? $file['model'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $model_image)) {
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
                    $model_file_name = false;

                    if (GCS::allow_extension($model_image)) {
                        $model_file_name = GCS::upload($model_image, 'model/child');

                        if (!$model_file_name) {
                            $error[] = Error::$UPLOAD_FAILED;
                            $update_flg = false;
                        }
                    } else {
                        $error[] = Error::$ALLOW_EXTENSION;
                        $update_flg = false;
                    }

                    if (!$update_flg || !$model_file_name) {
                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        ModelManager::add_child_model($inner_group_id, $model_file_name);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'model_file_name' => 'https://storage.googleapis.com/chirusapo/model/child/'.$model_file_name
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_clothes(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $model_image = isset($file['model']) ? $file['model'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $model_image)) {
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
                    $model_file_name = false;

                    if (GCS::allow_extension($model_image)) {
                        $model_file_name = GCS::upload($model_image, 'model/clothes');

                        if (!$model_file_name) {
                            $error[] = Error::$UPLOAD_FAILED;
                            $update_flg = false;
                        }
                    } else {
                        $error[] = Error::$ALLOW_EXTENSION;
                        $update_flg = false;
                    }

                    if (!$update_flg || !$model_file_name) {
                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        ModelManager::add_clothes_model($inner_group_id, $model_file_name);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'model_file_name' => 'https://storage.googleapis.com/chirusapo/model/clothes/'.$model_file_name
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_child_remove(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $api_key = isset($param['api_key']) ? $param['api_key'] : null;
        $model_image = isset($file['image']) ? $file['image'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $api_key, $model_image)) {
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
                    if (!GCS::allow_extension($model_image)) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$ALLOW_EXTENSION
                            ],
                            'data' => null
                        ];
                    } else {
                        $file_name = RemoveBg::file($model_image, $api_key);

                        if (!$file_name) {
                            $result = [
                                'status' => 400,
                                'message' => [
                                    Error::$GENERATE_MODEL_FAILED
                                ],
                                'data' => null
                            ];
                        } else {
                            if (GCS::copy($file_name, 'cache/', 'model/child/')) {
                                ModelManager::add_child_model($inner_group_id, $file_name);

                                $result = [
                                    'status' => 200,
                                    'message' => null,
                                    'data' => [
                                        'model_path' => 'https://storage.googleapis.com/chirusapo/model/child/'.$file_name
                                    ]
                                ];
                            } else {
                                $result = [
                                    'status' => 400,
                                    'message' => [
                                        Error::$UPLOAD_FAILED
                                    ],
                                    'data' => null
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_clothes_remove(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $api_key = isset($param['api_key']) ? $param['api_key'] : null;
        $model_image = isset($file['image']) ? $file['image'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $api_key, $model_image)) {
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
                    if (!GCS::allow_extension($model_image)) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$ALLOW_EXTENSION
                            ],
                            'data' => null
                        ];
                    } else {
                        $file_name = RemoveBg::file($model_image, $api_key);

                        if (!$file_name) {
                            $result = [
                                'status' => 400,
                                'message' => [
                                    Error::$GENERATE_MODEL_FAILED
                                ],
                                'data' => null
                            ];
                        } else {
                            if (GCS::copy($file_name, 'cache/', 'model/clothes/')) {
                                ModelManager::add_clothes_model($inner_group_id, $file_name);

                                $result = [
                                    'status' => 200,
                                    'message' => null,
                                    'data' => [
                                        'model_path' => 'https://storage.googleapis.com/chirusapo/model/clothes/'.$file_name
                                    ]
                                ];
                            } else {
                                $result = [
                                    'status' => 400,
                                    'message' => [
                                        Error::$UPLOAD_FAILED
                                    ],
                                    'data' => null
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }
}