<?php
namespace Application\Lib;

use Google_Client;
use Google_Exception;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

require_once __DIR__.'/../../../vendor/autoload.php';

class BotDataSheet {
    public static function insert($calendar_id, $date, $user_id, $calendar_summary, $calendar_subject) {
        $keyFile = __DIR__ . '/../../auth/chirusapo-bot-e861064f3820.json';

        $client = new Google_Client();
        try {
            $client->setAuthConfig($keyFile);
        } catch (Google_Exception $e) {
            return false;
        }

        $client->setApplicationName("Sheet API");
        $scopes = [Google_Service_Sheets::SPREADSHEETS];
        $client->setScopes($scopes);

        $sheet = new Google_Service_Sheets($client);

        $values = [
            [$calendar_id, $date, $user_id, $calendar_summary, $calendar_subject]
        ];

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values,
        ]);
        $sheet->spreadsheets_values->append(
            "1dAEjXD7_5wbsS8TX4aVdIemk3zhWT1d4FY6yDUSDaAs",
            'data',
            $body,
            ["valueInputOption" => 'USER_ENTERED']
        );

        return true;
    }
}