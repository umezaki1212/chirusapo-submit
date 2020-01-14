<?php
namespace Application\controllers;

use Application\app\AccountManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

/*
require_once __DIR__.'/../lib/Error.php';
require_once __DIR__.'/../app/GroupManager.php';
require_once __DIR__.'/../app/TokenManager.php';
*/

class GroupController {
    public static function group_join(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $pin_code = isset($param['pin_code']) ? $param['pin_code'] : null;

        $error = [];

        if (is_null($token) || is_null($group_id) || is_null($pin_code)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $validation_group_id = Validation::fire($group_id, Validation::$GROUP_ID);
            $validation_pin_code = Validation::fire($pin_code, Validation::$PIN_CODE);

            if (!$validation_group_id || !$validation_pin_code) {
                if (!$validation_group_id) $error[] = Error::$VALIDATION_GROUP_ID;
                if (!$validation_pin_code) $error[] = Error::$VALIDATION_PIN_CODE;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $user_id = TokenManager::get_user_id($token);
                $already_group = GroupManager::already_group_id($group_id);

                if (!$user_id || !$already_group) {
                    if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                    if (!$already_group) $error[] = Error::$UNKNOWN_GROUP;

                    $result = [
                        'status' => 400,
                        'message' => $error,
                        'data' => null
                    ];
                } else {
                    $inner_group_id = GroupManager::get_group_id($group_id);
                    $already_belong = GroupManager::already_belong_group($inner_group_id, $user_id);
                    $verify_pin_code = GroupManager::pin_code_verify($inner_group_id, $pin_code);

                    if (!$already_belong && $verify_pin_code) {
                        GroupManager::join_group($inner_group_id, $user_id);
                        $belong_group = GroupManager::belong_my_group($user_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'belong_group' => $belong_group
                            ]
                        ];
                    } else {
                        if ($already_belong) $error[] = Error::$ALREADY_BELONG_GROUP;
                        if (!$verify_pin_code) $error[] = Error::$VERIFY_PIN_CODE;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function group_create(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $group_name = isset($param['group_name']) ? $param['group_name'] : null;

        $error = [];

        if (is_null($token) || is_null($group_id) || is_null($group_name)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $validation_group_id = Validation::fire($group_id, Validation::$GROUP_ID);
            $validation_group_name = Validation::fire($group_name, Validation::$GROUP_NAME);

            if (!$validation_group_id || !$validation_group_name) {
                if (!$validation_group_id) $error[] = Error::$VALIDATION_GROUP_ID;
                if (!$validation_group_name) $error[] = Error::$VALIDATION_GROUP_NAME;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $user_id = TokenManager::get_user_id($token);
                $already_group = GroupManager::already_group_id($group_id);

                if (!$user_id || $already_group) {
                    if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                    if ($already_group) $error[] = Error::$ALREADY_CREATE_GROUP;

                    $result = [
                        'status' => 400,
                        'message' => $error,
                        'data' => null
                    ];
                } else {
                    $inner_group_id = GroupManager::create_group($group_id, $group_name);
                    GroupManager::join_group($inner_group_id, $user_id);
                    $belong_group = GroupManager::belong_my_group($user_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'belong_group' => $belong_group
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function belong_group(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

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

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                $belong_group = GroupManager::belong_my_group($user_id);

                $result = [
                    'status' => 200,
                    'message' => null,
                    'data' => [
                        'belong_group' => $belong_group
                    ]
                ];
            }
        }

        return $response->withJson($result);
    }
    
    public static function belong_member(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;

        $error = [];

        if (is_null($token) || is_null($group_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $validation_group_id = Validation::fire($group_id, Validation::$GROUP_ID);

            if (!$validation_group_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$VALIDATION_GROUP_ID
                    ],
                    'data' => null
                ];
            } else {
                $user_id = TokenManager::get_user_id($token);
                $already_group = GroupManager::already_group_id($group_id);

                if (!$user_id || !$already_group) {
                    if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                    if (!$already_group) $error[] = Error::$UNKNOWN_GROUP;

                    $result = [
                        'status' => 400,
                        'message' => $error,
                        'data' => null
                    ];
                } else {
                    $inner_group_id = GroupManager::get_group_id($group_id);
                    $already_belong = GroupManager::already_belong_group($inner_group_id, $user_id);

                    if ($already_belong) {
                        $belong_member = GroupManager::belong_member($inner_group_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'belong_member' => $belong_member
                            ]
                        ];
                    } else {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$UNREADY_BELONG_GROUP
                            ],
                            'data' => null
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function group_withdrawal(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;

        $error = [];

        if (is_null($token) || is_null($group_id)) {
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
                    GroupManager::withdrawal_group($inner_group_id, $user_id);
                    $belong_group = GroupManager::belong_my_group($user_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'belong_group' => $belong_group
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function group_withdrawal_force(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $target_user_id = isset($param['target_user_id']) ? $param['target_user_id'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $target_user_id)) {
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
                    foreach ($target_user_id as $value) {
                        $inner_user_id = AccountManager::get_user_id($value);

                        if (!$inner_user_id) {
                            $error[] = Error::$UNKNOWN_USER;
                        } else {
                            if (!GroupManager::already_belong_group($inner_group_id, $inner_user_id)) {
                                $error[] = Error::$UNREADY_BELONG_GROUP;
                            }
                        }
                    }

                    if (count($error) !== 0) {
                        $result = [
                            'status' => 400,
                            'message' => array_values(array_unique($error)),
                            'data' => null
                        ];
                    } else {
                        foreach ($target_user_id as $value) {
                            $inner_user_id = AccountManager::get_user_id($value);
                            GroupManager::withdrawal_group($inner_group_id, $inner_user_id);
                        }

                        $belong_member = GroupManager::belong_member($inner_group_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'belong_member' => $belong_member
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function group_delete(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

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
                    GroupManager::delete_group($inner_group_id);

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

    public static function group_edit(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $group_name = isset($param['group_name']) ? $param['group_name'] : null;
        $pin_code = isset($param['pin_code']) ? $param['pin_code'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $group_name, $pin_code)) {
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
                    $validation_group_name = Validation::fire($group_name, Validation::$GROUP_NAME);
                    $validation_pin_code = Validation::fire($pin_code, Validation::$PIN_CODE);

                    if (!$validation_group_name || !$validation_pin_code) {
                        if (!$validation_group_name) $error[] = Error::$VALIDATION_GROUP_NAME;
                        if (!$validation_pin_code) $error[] = Error::$VALIDATION_PIN_CODE;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        GroupManager::edit_group($inner_group_id, $group_name, $pin_code);
                        $group_info = GroupManager::get_group($group_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'group_info' => $group_info
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }
}