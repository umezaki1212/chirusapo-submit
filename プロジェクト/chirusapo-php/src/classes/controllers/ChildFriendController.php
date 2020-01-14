<?php
namespace Application\Controllers;

use Application\App\ChildFriendManager;
use Application\App\ChildManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\GoogleCloudStorage as GCS;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

class ChildFriendController {
    public static function get_friend(Request $request, Response $response) {
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
                    $friend_list = ChildFriendManager::list_friend($inner_child_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'friend_list' => $friend_list
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_friend(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;
        $user_name = isset($param['user_name']) ? $param['user_name'] : null;
        $birthday = isset($param['birthday']) ? $param['birthday'] : null;
        $gender = isset($param['gender']) ? $param['gender'] : null;
        $memo = isset($param['memo']) ? $param['memo'] : null;
        $user_icon = isset($file['user_icon']) ? $file['user_icon'] : null;

        $error = [];

        if (is_nulls($token, $child_id, $user_name, $birthday, $gender, $memo)) {
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
                    $validation_user_name = Validation::fire($user_name, Validation::$USER_NAME);
                    $validation_birthday = Validation::fire($birthday, Validation::$BIRTHDAY);
                    $validation_gender = Validation::fire($gender, Validation::$GENDER);
                    $validation_memo = Validation::fire($memo, Validation::$FRIEND_MEMO);
                    $allow_extension = is_null($user_icon) ? true : GCS::allow_extension($user_icon);

                    if (
                        !$validation_user_name ||
                        !$validation_birthday ||
                        !$validation_gender ||
                        !$validation_memo ||
                        !$allow_extension
                    ) {
                        if (!$validation_user_name) $error[] = Error::$VALIDATION_USER_NAME;
                        if (!$validation_birthday) $error[] = Error::$VALIDATION_BIRTHDAY;
                        if (!$validation_gender) $error[] = Error::$VALIDATION_GENDER;
                        if (!$validation_memo) $error[] = Error::$VALIDATION_FRIEND_MEMO;
                        if (!$allow_extension) $error[] = Error::$ALLOW_EXTENSION;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        if (is_null($user_icon)) {
                            $friend_id = ChildFriendManager::add_friend($inner_child_id, $user_name, $birthday, $gender, $memo);
                            $friend_info = ChildFriendManager::get_friend($friend_id);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'friend_info' => $friend_info
                                ]
                            ];
                        } else {
                            $file_name = GCS::upload($user_icon, 'child-friend-icon');
                            if (!$file_name) {
                                $result = [
                                    'status' => 400,
                                    'message' => [
                                        Error::$UPLOAD_FAILED
                                    ],
                                    'data' => null
                                ];
                            } else {
                                $friend_id = ChildFriendManager::add_friend($inner_child_id, $user_name, $birthday, $gender, $memo, $file_name);
                                $friend_info = ChildFriendManager::get_friend($friend_id);

                                $result = [
                                    'status' => 200,
                                    'message' => null,
                                    'data' => [
                                        'friend_info' => $friend_info
                                    ]
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function edit_friend(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $friend_id = isset($param['friend_id']) ? $param['friend_id'] : null;
        $user_name = isset($param['user_name']) ? $param['user_name'] : null;
        $birthday = isset($param['birthday']) ? $param['birthday'] : null;
        $gender = isset($param['gender']) ? $param['gender'] : null;
        $memo = isset($param['memo']) ? $param['memo'] : null;
        $user_icon = isset($file['user_icon']) ? $file['user_icon'] : null;

        $error = [];

        if (is_nulls($token, $friend_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_child_id = ChildFriendManager::friend_id_to_inner_child_id($friend_id);

            if (!$user_id || !$inner_child_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_child_id) $error[] = Error::$UNKNOWN_FRIEND;

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
                    $validation_user_name = is_null($user_name) ? true : Validation::fire($user_name, Validation::$USER_NAME);
                    $validation_birthday = is_null($birthday) ? true : Validation::fire($birthday, Validation::$BIRTHDAY);
                    $validation_gender = is_null($gender) ? true : Validation::fire($gender, Validation::$GENDER);
                    $validation_memo = is_null($memo) ? true : Validation::fire($memo, Validation::$FRIEND_MEMO);
                    $allow_extension = is_null($user_icon) ? true : GCS::allow_extension($user_icon);

                    if (
                        !$validation_user_name ||
                        !$validation_birthday ||
                        !$validation_gender ||
                        !$validation_memo ||
                        !$allow_extension
                    ) {
                        if (!$validation_user_name) $error[] = Error::$VALIDATION_USER_NAME;
                        if (!$validation_birthday) $error[] = Error::$VALIDATION_BIRTHDAY;
                        if (!$validation_gender) $error[] = Error::$VALIDATION_GENDER;
                        if (!$validation_memo) $error[] = Error::$VALIDATION_FRIEND_MEMO;
                        if (!$allow_extension) $error[] = Error::$ALLOW_EXTENSION;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        $update_flg = true;
                        $file_name = false;

                        if (!is_null($user_icon)) {
                            $file_name = GCS::upload($user_icon, 'child-friend-icon');

                            if (!$file_name) {
                                $update_flg = false;
                            }
                        }

                        if ($update_flg) {
                            if (!is_null($user_name)) {
                                ChildFriendManager::edit_user_name($friend_id, $user_name);
                            }
                            if (!is_null($birthday)) {
                                ChildFriendManager::edit_birthday($friend_id, $birthday);
                            }
                            if (!is_null($gender)) {
                                ChildFriendManager::edit_gender($friend_id, $gender);
                            }
                            if (!is_null($memo)) {
                                ChildFriendManager::edit_memo($friend_id, $memo);
                            }
                            if (!is_null($user_icon)) {
                                ChildFriendManager::edit_icon($friend_id, $file_name);
                            }

                            $friend_info = ChildFriendManager::get_friend($friend_id);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'friend_info' => $friend_info
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

        return $response->withJson($result);
    }

    public static function delete_friend(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $friend_id = isset($param['friend_id']) ? $param['friend_id'] : null;

        $error = [];

        if (is_nulls($token, $friend_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_child_id = ChildFriendManager::friend_id_to_inner_child_id($friend_id);

            if (!$user_id || !$inner_child_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_child_id) $error[] = Error::$UNKNOWN_FRIEND;

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
                    ChildFriendManager::delete_friend($friend_id);

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

    public static function autofill_friend(Request $request, Response $response) {
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
                if (!$inner_child_id) $error[] = Error::$UNKNOWN_FRIEND;

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
                    $child_info = ChildFriendManager::autofill_child_info($inner_child_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'child_info' => $child_info
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }
}