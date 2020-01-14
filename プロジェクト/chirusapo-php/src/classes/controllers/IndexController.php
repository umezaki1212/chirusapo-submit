<?php
namespace Application\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class IndexController {
    public function index(Request $request, Response $response) {
        $result = [
            'status' => 200,
            'message' => null,
            'data' => [
                'message' => 'Hello! Slim Framework.'
            ]
        ];

        return $response->withJson($result);
    }
}