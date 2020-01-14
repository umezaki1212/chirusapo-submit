<?php
namespace Application\controllers;

use Classes\app\MasterManager;
use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__.'/../app/MasterManager.php';

class MasterController {
    public static function master_download(Request $request, Response $response) {
        $master_vaccination = MasterManager::get_vaccination();
        $master_allergy = MasterManager::get_allergy();

        $result = [
            'status' => 200,
            'message' => null,
            'data' => [
                'vaccination' => $master_vaccination,
                'allergy' => $master_allergy
            ]
        ];

        return $response->withJson($result);
    }
}