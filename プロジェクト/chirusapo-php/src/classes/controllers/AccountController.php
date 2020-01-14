<?php
namespace Application\Controllers;

use Application\App\ChildManager;
use Application\lib\GoogleCloudStorage as GCS;
use Slim\Http\Request;
use Slim\Http\Response;
use Application\lib\Validation;
use Application\lib\Error;
use Application\app\AccountManager;
use Application\app\TokenManager;
use Application\app\GroupManager;

require_once __DIR__.'/../lib/Validation.php';
require_once __DIR__.'/../lib/Error.php';
require_once __DIR__.'/../lib/GoogleCloudStorage.php';
require_once __DIR__.'/../app/functions.php';
require_once __DIR__.'/../app/AccountManager.php';
require_once __DIR__.'/../app/TokenManager.php';
require_once __DIR__.'/../app/GroupManager.php';

class AccountController {
    public function sign_up(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $user_id = isset($param['user_id']) ? $param['user_id'] : null;
        $user_name = isset($param['user_name']) ? $param['user_name'] : null;
        $email = isset($param['email']) ? $param['email'] : null;
        $password = isset($param['password']) ? $param['password'] : null;
        $gender = isset($param['gender']) ? $param['gender'] : null;
        $birthday = isset($param['birthday']) ? $param['birthday'] : null;

        $error = [];

        if (is_null($user_id) || is_null($user_name) || is_null($email) || is_null($password) || is_null($gender) || is_null($birthday)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $valid_user_id = Validation::fire($user_id, Validation::$USER_ID);
            $valid_user_name = Validation::fire($user_name, Validation::$USER_NAME);
            $valid_email = Validation::fire($email, Validation::$EMAIL);
            $valid_password = Validation::fire($password, Validation::$PASSWORD);
            $valid_gender = Validation::fire($gender, Validation::$GENDER);
            $valid_birthday = Validation::fire($birthday, Validation::$BIRTHDAY);

            if (
                !$valid_user_id ||
                !$valid_user_name ||
                !$valid_email ||
                !$valid_password ||
                !$valid_gender ||
                !$valid_birthday
            ) {
                if (!$valid_user_id) $error[] = Error::$VALIDATION_USER_ID;
                if (!$valid_user_name) $error[] = Error::$VALIDATION_USER_NAME;
                if (!$valid_email) $error[] = Error::$VALIDATION_EMAIL;
                if (!$valid_password) $error[] = Error::$VALIDATION_PASSWORD;
                if (!$valid_gender) $error[] = Error::$VALIDATION_GENDER;
                if (!$valid_birthday) $error[] = Error::$VALIDATION_BIRTHDAY;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $check_user_id = AccountManager::already_user_id($_POST['user_id']);
                $check_email = AccountManager::already_email($_POST['email']);

                if (!$check_user_id || !$check_email) {
                    if (!$check_user_id) $error[] = Error::$ALREADY_USER_ID;
                    if (!$check_email) $error[] = Error::$ALREADY_EMAIL;

                    $result = [
                        'status' => 400,
                        'message' => $error,
                        'data' => null
                    ];
                } else {
                    $id = AccountManager::sign_up($user_id, $user_name, $email, $password, $gender, $birthday);
                    $token = TokenManager::add_token($id);
                    $user_info = AccountManager::user_info($id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'token' => $token,
                            'user_info' => $user_info
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public function sign_in(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $user_id = isset($param['user_id']) ? $param['user_id'] : null;
        $password = isset($param['password']) ? $param['password'] : null;

        $error = [];

        if (is_null($user_id) || is_null($password)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $valid_user_id = Validation::fire($user_id, Validation::$USER_ID_OR_EMAIL);
            $valid_password = Validation::fire($password, Validation::$PASSWORD);

            if (!$valid_user_id || !$valid_password) {
                if (!$valid_user_id) $error[] = Error::$VALIDATION_USER_ID;
                if (!$valid_password) $error[] = Error::$VALIDATION_PASSWORD;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $id = AccountManager::sign_in($user_id, $password);

                if (!$id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_USER
                        ],
                        'data' => null
                    ];
                } else {
                    $token = TokenManager::add_token($id);
                    $user_info = AccountManager::user_info($id);
                    $belong_group = GroupManager::belong_my_group($id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'token' => $token,
                            'user_info' => $user_info,
                            'belong_group' => $belong_group
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public function password_reset(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $user_id = isset($param['user_id']) ? $param['user_id'] : null;

        if (is_null($user_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $valid_user_id = Validation::fire($user_id, Validation::$USER_ID_OR_EMAIL);

            if (!$valid_user_id) {
                if (!$valid_user_id) $error[] = Error::$VALIDATION_USER_ID;

                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$VALIDATION_USER_ID
                    ],
                    'data' => null
                ];
            } else {
                $id = AccountManager::already_user_id_or_email($user_id);

                if (!$id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_USER
                        ],
                        'data' => null
                    ];
                } else {
                    $send_flg = AccountManager::password_reset($id);

                    if ($send_flg) {
                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => null
                        ];
                    } else {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$MAIL_SEND
                            ],
                            'data' => null
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function password_change(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $old_password = isset($param['old_password']) ? $param['old_password'] : null;
        $new_password = isset($param['new_password']) ? $param['new_password'] : null;

        $error = [];

        if (is_null($token) || is_null($old_password) || is_null($new_password)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $validation_old_password = Validation::fire($old_password, Validation::$PASSWORD);
            $validation_new_password = Validation::fire($new_password, Validation::$PASSWORD);

            if (!$validation_old_password || !$validation_new_password) {
                if (!$validation_old_password) $error[] = Error::$VALIDATION_OLD_PASSWORD;
                if (!$validation_new_password) $error[] = Error::$VALIDATION_NEW_PASSWORD;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $user_id = TokenManager::get_user_id($token);

                if (!$user_id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_TOKEN
                        ],
                        'data' => null
                    ];
                } else {
                    $change_password = AccountManager::password_change($user_id, $new_password, $old_password);

                    if ($change_password) {
                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => null
                        ];
                    } else {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$VERIFY_PASSWORD_FAILED
                            ],
                            'data' => null
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function account_edit(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $user_icon = isset($file['user_icon']) ? $file['user_icon'] : null;
        $user_name = isset($param['user_name']) ? $param['user_name'] : null;
        $line_id = isset($param['line_id']) ? $param['line_id'] : null;
        $introduction = isset($param['introduction']) ? $param['introduction'] : null;

        $error = [];

        if (is_null($token)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                $update_flg = true;
                $icon_file_name = false;

                if (!is_null($user_name)) {
                    $validation_user_name = Validation::fire($user_name, Validation::$USER_NAME);

                    if (!$validation_user_name) {
                        $error[] = Error::$VALIDATION_USER_NAME;
                        $update_flg = false;
                    }
                }

                if (!is_null($line_id)) {
                    $validation_line_id = empty($line_id) ? true : Validation::fire($line_id, Validation::$LINE_ID);

                    if (!$validation_line_id) {
                        $error[] = Error::$VALIDATION_LINE_ID;
                        $update_flg = false;
                    }
                }

                if (!is_null($introduction)) {
                    $validation_introduction = empty($introduction) ? true : Validation::fire($introduction, Validation::$INTRODUCTION);

                    if (!$validation_introduction) {
                        $error[] = Error::$VALIDATION_INTRODUCTION;
                        $update_flg = false;
                    }
                }

                if (!is_null($user_icon)) {
                    if (GCS::allow_extension($user_icon)) {
                        $icon_file_name = GCS::upload($user_icon, 'user-icon');

                        if (!$icon_file_name) {
                            $error[] = Error::$UPLOAD_FAILED;
                            $update_flg = false;
                        }
                    } else {
                        $error[] = Error::$ALLOW_EXTENSION;
                        $update_flg = false;
                    }
                }

                if ($update_flg) {
                    if (!is_null($user_name)) AccountManager::update_user_name($user_id, $user_name);
                    if (!is_null($line_id)) AccountManager::update_line_id($user_id, $line_id);
                    if (!is_null($introduction)) AccountManager::update_introduction($user_id, $introduction);
                    if (!is_null($user_icon) && $icon_file_name) AccountManager::update_user_icon($user_id, $icon_file_name);

                    $user_info = AccountManager::user_info($user_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'user_info' => $user_info
                        ]
                    ];
                } else {
                    $result = [
                        'status' => 400,
                        'message' => $error,
                        'data' => null
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function sign_out(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;

        if (is_null($token)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $already_token = TokenManager::already_token($token);

            if ($already_token) {
                TokenManager::delete_token($token);

                $result = [
                    'status' => 200,
                    'message' => null,
                    'data' => null
                ];
            } else {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            }
        }

        return $response->withJson($result);
    }

    public static function resign(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;

        if (is_null($token)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);

            if ($user_id) {
                AccountManager::resign($user_id);

                $result = [
                    'status' => 200,
                    'message' => null,
                    'data' => null
                ];
            } else {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            }
        }

        return $response->withJson($result);
    }

    public static function member_user_info(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $target_user_id = isset($param['target_user_id']) ? $param['target_user_id'] : null;

        $error = [];

        if (is_nulls($token, $target_user_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $inner_user_id = TokenManager::get_user_id($token);
            $target_inner_user_id = AccountManager::get_user_id($target_user_id);

            if (!$inner_user_id || !$target_inner_user_id) {
                if (!$inner_user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$target_inner_user_id) $error[] = Error::$UNKNOWN_TARGET_USER;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $group_member = GroupManager::family_user_id($inner_user_id, $target_inner_user_id);

                if (!$group_member) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    $user_info = AccountManager::member_user_info($target_inner_user_id);
                    $child_info = ChildManager::have_child_list($target_inner_user_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'user_info' => $user_info,
                            'child_info' => $child_info
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function line_cooperation(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $line_id = isset($param['line_id']) ? $param['line_id'] : null;

        if (is_nulls($token, $line_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);

            if ($user_id) {
                AccountManager::line_cooperation($user_id, $line_id);

                $result = [
                    'status' => 200,
                    'message' => null,
                    'data' => null
                ];
            } else {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            }
        }

        return $response->withJson($result);
    }
}