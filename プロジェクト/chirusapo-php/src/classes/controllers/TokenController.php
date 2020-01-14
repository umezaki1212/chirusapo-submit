<?php
namespace Application\controllers;

use Application\app\AccountManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__.'/../lib/Error.php';
require_once __DIR__.'/../app/AccountManager.php';
require_once __DIR__.'/../app/TokenManager.php';
require_once __DIR__.'/../app/GroupManager.php';

class TokenController {
    public static function verify_token(Request $request, Response $response) {
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
            $verify_token = TokenManager::verify_token($token);

            if ($verify_token) {
                $user_id = TokenManager::get_user_id($token);
                $user_info = AccountManager::user_info($user_id);
                $belong_group = GroupManager::belong_my_group($user_id);

                $result = [
                    'status' => 200,
                    'message' => null,
                    'data' => [
                        'user_info' => $user_info,
                        'belong_group' => $belong_group
                    ]
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