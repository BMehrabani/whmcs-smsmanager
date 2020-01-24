<?php
require('Twilio/autoload.php');

use Twilio\Rest\Client;

function SMS_user_Twilio($AccountSid, $AuthToken, $to, $from, $text)
{
    try {
        $client = new Client($AccountSid, $AuthToken);
        $client->messages->create(
            $to, array(
            'from' => $from,
            'body' => $text,
            )
        );
        return 'success';
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
