<?php
namespace Application\lib;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer {
    private static $SERVER_DOMAIN = '';
    private static $SERVER_PORT = 465;
    private static $SERVER_PROTOCOL = 'ssl';
    private static $USERNAME = '';
    private static $PASSWORD = '';
    private static $FROM = '';

    public function __construct($to, $subject, $body) {
        require_once __DIR__.'/../../../vendor/autoload.php';

        $transport = new Swift_SmtpTransport(self::$SERVER_DOMAIN, self::$SERVER_PORT, self::$SERVER_PROTOCOL);
        $transport->setUsername(self::$USERNAME);
        $transport->setPassword(self::$PASSWORD);

        $mailer = new Swift_Mailer($transport);

        $message = new Swift_Message($subject);
        $message->setFrom([self::$FROM => self::$FROM]);
        $message->setTo([$to]);
        $message->setBody($body);

        return ($mailer->send($message)) ? true : false;
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @return bool
     */
    /*
    public static function send_mail($to, $subject, $body) {
        require_once __DIR__.'/../vendor/autoload.php';

        $transport = new Swift_SmtpTransport(self::$SERVER_DOMAIN, self::$SERVER_PORT, self::$SERVER_PROTOCOL);
        $transport->setUsername(self::$USERNAME);
        $transport->setPassword(self::$PASSWORD);

        $mailer = new Swift_Mailer($transport);

        $message = new Swift_Message($subject);
        $message->setFrom([self::$FROM => self::$FROM]);
        $message->setTo([$to]);
        $message->setBody($body);

        return ($mailer->send($message)) ? true : false;
    }
    */
}