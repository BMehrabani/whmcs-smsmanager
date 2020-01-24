<?php
/* * ********************************************************************
 * SMS Manager by WHMCS Services
 * Copyright  WHMCS Services,  All Rights Reserved
 *
 * Created By WHMCSServices      http://www.whmcsservices.com
 * Contact:                      whmcs@whmcsservices.com
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 * ******************************************************************** */
if (!defined('WHMCS'))
    die('This file cannot be accessed directly');

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\User\Client as Client;

function wsis_utf8($str)
{
    return (bool) preg_match('//u', $str);
}

function jisort_jsonapi($data_array) {
    $data_string = '{
    "recipient": "' . $data_array['from'] . '",
    "encoding": "UNICODE",
    "to": "' . $data_array['to'] . '",
    "body": "' . $data_array['message'] . '"
}';
    $recipient = $data_array['to'];
    $message = $data_array['message'];
    $api_key = $data_array['username'];
    $password = $data_array['password'];
    $client_id = 'i7YlkIu4qdkLZJsnJubhIbeYWP0Qq2NH3D0vatNO';
    $client_secret = 'cx84im8OqngRMM3EftMAfKh1vwEFuSuAD9GH2gE7JxzjE7lJCTI55ZJND8MFPOGkHcFesA9Piy9CgKSzaz3L0bKyspdhq1w8wRouYwhrv3ba8rNwvZ4ppnsebR0rccdB';
    $token_valid = false;
    if ($_SESSION['jisort_access_token'] && $_SESSION['jisort_token_expiry_time']) {
        $now_date = new DateTime();
        $now_date = $now_date->getTimestamp();
        if ($_SESSION['jisort_token_expiry_time'] > $now_date) {
            $token_valid = true;
            $access_token = $_SESSION['jisort_access_token'];
        }
    }
    if (!$token_valid) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://my.jisort.com/registration/login/");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$api_key&password=$password&grant_type=password&client_id=$client_id&client_secret=$client_secret");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/x-www-form-urlencoded",)
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $data = json_decode($result);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http == 201 || $http == 200) {
            $expiry_date = new DateTime();
            $add_interval = 'PT' . $data->expires_in . 'S';
            $expiry_date->add(new DateInterval($add_interval));
            $_SESSION['jisort_token_expiry_time'] = $expiry_date->getTimestamp();
            $access_token = $data->access_token;
            $_SESSION['jisort_access_token'] = $access_token;
        }
    }
    if ($access_token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://my.jisort.com/messenger/outbox/");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "recipients=". urlencode($recipient) ."&message=$message");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));
        $result = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http == 201 || $http == 200) {
            if ($result != '') {
                return 'success';
            } else {
                return 'Error on connection to Jisort';
            }
        } else if ($http == 400) {
            $data = json_decode($result);
            return 'error : ' . $data[0];
        }
    } else {
        return 'error : ' . $result;
    }
}

function bulksms_jsonapi($data_array)
{
    $data_string = '{
    "from": "' . $data_array['from'] . '",
    "encoding": "UNICODE",
    "to": "' . $data_array['to'] . '",
    "body": "' . $data_array['message'] . '"
}';
    $api_key = $data_array['username'];
    $password = $data_array['password'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.bulksms.com/v1/messages");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $api_key . ':' . $password);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json')
    );
    $result = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http == 201) {
        if ($result != '') {
            $res = json_decode($result);
            if (isset($res[0]) && $res[0]->type == 'SENT') {
                return 'success';
            } else {
                return 'error : ' . $res->status;
            }
        } else {
            return 'Error on connection to bulkSMS';
        }
    } else {
        return 'Username or password is wrong';
    }
}

function smsmanager_sendSMS($to = '', $message = '', $vars = array(), $userid = '')
{
    if (file_exists(ROOTDIR . "/modules/addons/smsmanager/lang/" . $CONFIG['Language'] . ".php")) {
        include(ROOTDIR . "/modules/addons/smsmanager/lang/" . $CONFIG['Language'] . ".php");
    } elseif (file_exists(ROOTDIR . "/modules/addons/smsmanager/lang/english.php")) {
        include(ROOTDIR . "/modules/addons/smsmanager/lang/english.php");
    }
    $SETING = array();
    $result = Capsule::table('mod_smsmanager_config')->get();
    foreach ($result as $data) {
        $setting = $data->setting;
        $value = $data->value;
        $SETING["{$setting}"] = "{$value}";
    }
    $smsGateway = '';
    if (isset($userid) && $userid != '') {
        $cn = Client::find($userid);
        if ($cn['country'] != '') {
            $item = Capsule::table('mod_smsmanager_multiconfig')->where('country', $cn['country'])->first();
            if (count($item) > 0) {
                $uset = unserialize($item->value);
                $SETING['api_username'] = $uset['apiusername'];
                $SETING['api_password'] = base64_encode($uset['apipassword']);
                $SETING['api_sender'] = $uset['apisender'];
                $SETING['api_id'] = $uset['apiid'];
                $SETING['api_domain'] = $uset['apidomain'];
                $SETING['api_domain'] = $uset['apidomain'];
                $smsGateway = str_replace('multi_', '', strtolower($item->setting));
            }
        }
    }
    $smsUsername = $SETING['api_username'];
    $smsPassword = base64_decode($SETING['api_password']);
    $smsSender = $SETING['api_sender'];
    $apiID = $SETING['api_id'];
    $apiDomain = $SETING['api_domain'];
    $data = Capsule::table('tbladdonmodules')->where('module', 'smsmanager')->where('setting', 'smsgateway')->first();
    if ($smsGateway == '')
        $smsGateway = strtolower($data->value);
    if (empty($smsUsername) || empty($smsPassword)) {
        if (empty($smsUsername) && $smsGateway != "clickatell (platform)" && $smsGateway != "MessageBird")
            return $_ADDONLANG['credentialerror'];
    } elseif (empty($to) || strlen($to) < 3) {
        return $_ADDONLANG['enterrecipient'];
    } elseif (empty($message) || strlen($message) < 3) {
        return $_ADDONLANG['entermessage'];
    }
    if ($smsGateway == "jisort") {
        $data_array = array('to' => $to, 'from' => $smsSender, 'message' => $message, 'username' => $smsUsername, 'password' => $smsPassword);
        $res = jisort_jsonapi($data_array);
        logModuleCall('smsmanager', $smsGateway, $data_array, $res);
        // logactivity("DEBUG SMSMANAGER: gateway: " . $smsGateway . " - Result: " . $res); //debugging
        if ($vars['manual']) {
            return $res;
        }
        return true;

    }elseif ($smsGateway == "text marketer1") {
        $url = 'http://api.textmarketer.co.uk/gateway/' . '?username=' . $smsUsername . '&password=' . $smsPassword . '&option=xml';
        $url .= '&to=' . $to . '&message=' . urlencode($message) . '&orig=' . urlencode($smsSender);
    } elseif ($smsGateway == "bulksms") {
        if ($apiDomain != '' && $apiDomain == 'https://api.bulksms.com/v1/messages') {
            $to = str_replace('+', '', $to);
            $to = '+' . $to;
            $data_array = array('to' => $to, 'from' => $smsSender, 'message' => $message, 'username' => $smsUsername, 'password' => $smsPassword);
            if ($SETING['bulksmsroutes'] != '2' && $SETING['bulksmsroutes'] != '') {
                $data_array['routing_group'] = $SETING['bulksmsroutes'];
            }
            $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
            if ($tspecific->value && $vars['sendlater']) {
                $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
                $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
                $data_array['send_time'] = date('Y-m-d ' . $sendhr->value . ':' . $sendmin->value . ':01');
            }
            $res = bulksms_jsonapi($data_array);
            logModuleCall('smsmanager', $smsGateway, $data_array, $res);
            //logactivity("DEBUG SMSMANAGER: gateway: " . $smsGateway . " - Result: " . $res); //debugging
            if ($vars['manual']) {
                return "Gateway Result: " . $res;
            }
            return true;
        } else {
            if (empty($apiDomain))
                $apiDomain = "bulksms.vsms.net";
            $apiDomain = str_replace('/eapi', '', $apiDomain);
            if (strpos($apiDomain, 'https://') === false && strpos($apiDomain, 'http://') === false) {
                $apiDomain = str_replace("https://", "", $apiDomain);
                $apiDomain = str_replace("http://", "", $apiDomain);
                $apiDomain = "http://" . $apiDomain;
            }
            $apiDomain = explode("/eapi/submission/send_sms", $apiDomain);
            $apiDomain = $apiDomain[0];
            $encodeurl = '';
            $smessage = $message;
            if (strlen($message) != mb_strlen($message, 'utf-8')) {
                $smessage = bin2hex(mb_convert_encoding($message, 'utf-16', 'utf-8'));
                $encodeurl = '&dca=16bit';
            } else {
                $encodeurl = '&allow_concat_text_sms=1&concat_text_sms_max_parts=5';
            }
            $url = $apiDomain . '/eapi/submission/send_sms/2/2.0' . '?username=' . $smsUsername . '&password=' . urlencode($smsPassword);
            $url .= '&message=' . urlencode($smessage) . '&msisdn=' . urlencode($to) . '&sender=' . urlencode($smsSender) . $encodeurl;
            if ($SETING['bulksmsroutes'] != '2' && $SETING['bulksmsroutes'] != '') {
                $url .= '&routing_group=' . $SETING['bulksmsroutes'];
            }
            $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
            if ($tspecific->value && $vars['sendlater']) {
                $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
                $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
                $url .= '&send_time=' . urlencode(date('Y-m-d ' . $sendhr->value . ':' . $sendmin->value . ':01'));
            }
        }
    } elseif ($smsGateway == "bulksms uk") { // NO LONGER IN USE AND CAN BE REMOVED
        $url = 'https://www.bulksms.co.uk/eapi/submission/send_sms/2/2.0' . '?username=' . $smsUsername . '&password=' . $smsPassword;
        $url .= '&message=' . urlencode($message) . '&msisdn=' . urlencode($to) . '&sender=' . urlencode($smsSender) . '&allow_concat_text_sms=1&concat_text_sms_max_parts=5';
        if ($SETING['bulksmsroutes'] != '2' && $SETING['bulksmsroutes'] != '') {
            $url .= '&routing_group=' . $SETING['bulksmsroutes'];
        }
        $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
        if ($tspecific->value && $vars['sendlater']) {
            $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
            $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
            $url .= '&send_time=' . urlencode(date('Y-m-d ' . $sendhr->value . ':' . $sendmin->value . ':01'));
        }
    } elseif ($smsGateway == "bulksms usa") { // NO LONGER IN USE AND CAN BE REMOVED
        $url = 'https://bulksms.vsms.net/eapi/submission/send_sms/2/2.0' . '?username=' . $smsUsername . '&password=' . $smsPassword;
        $url .= '&message=' . urlencode($message) . '&msisdn=' . urlencode($to) . '&sender=' . urlencode($smsSender) . '&allow_concat_text_sms=1&concat_text_sms_max_parts=5';
        if ($SETING['bulksmsroutes'] != '2' && $SETING['bulksmsroutes'] != '') {
            $url .= '&routing_group=' . $SETING['bulksmsroutes'];
        }
        $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
        if ($tspecific->value && $vars['sendlater']) {
            $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
            $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
            $url .= '&send_time=' . urlencode(date('Y-m-d ' . $sendhr->value . ':' . $sendmin->value . ':01'));
        }
    } elseif ($smsGateway == "clickatell (communicator / central)") {
        $url = 'http://api.clickatell.com/http/sendmsg?user=' . $smsUsername . '&password=' . $smsPassword . '&api_id=' . $apiID . '';
        $url .= '&to=' . urlencode($to) . '&text=' . urlencode($message) . '&from=' . urlencode($smsSender);
    } elseif ($smsGateway == "clickatell (platform)") {
        $url = 'https://platform.clickatell.com/messages/http/send?apiKey=' . urlencode($smsPassword);
        $url .= '&to=' . urlencode($to) . '&content=' . urlencode($message) . '&from=' . urlencode($smsSender);
    } elseif ($smsGateway == "sendpk") {
        $url = 'http://sendpk.com/api/sms.php?username=' . $smsUsername . '&password=' . $smsPassword . '';
        $url .= '&sender=' . urlencode($smsSender) . '&mobile=' . urlencode($to) . '&message=' . urlencode($message) . '';
    } elseif ($smsGateway == "gateway sa") {
        $url = 'http://www.apps.gateway.sa/vendorsms/pushsms.aspx?user=' . $smsUsername . '&password=' . $smsPassword . '';
        $url .= '&sid=' . urlencode($smsSender) . '&msisdn=' . urlencode($to) . '&msg=' . urlencode($message) . '&fl=0';
    } elseif ($smsGateway == "smsbao") {
        $url = 'http://api.smsbao.com/sms?u=' . urlencode($smsUsername) . '&p=' . urlencode(md5($smsPassword)) . '';
        $url .= '&m=' . urlencode($to) . '&c=' . urlencode($message . ' ' . '[' . $SETING['signature'] . ']');
    } elseif ($smsGateway == "supersolution") {
        $to = str_replace("+", "", $to);
        $message = html_entity_decode($message, ENT_QUOTES);
        $message = str_replace('%5Cr%5Cn', '%0A', urlencode($message));
        $url = 'http://bsms.supersolutions.pk/vendorsms/pushsms.aspx?user=' . urlencode($smsUsername) . '&password=' . urlencode($smsPassword) . '';
        $url .= '&sid=' . urlencode($smsSender) . '&fl=0&msisdn=' . urlencode($to) . '&msg=' . $message . '';
    } elseif ($smsGateway == "sveve.no") {
        $to = str_replace("+", "", $to);
        $message = html_entity_decode($message, ENT_QUOTES);
        $message = str_replace('%5Cr%5Cn', '%0A', urlencode($message));
        $url = 'https://sveve.no/SMS/SendMessage?user=' . urlencode($smsUsername) . '&passwd=' . urlencode($smsPassword) . '';
        $url .= '&from=' . urlencode($smsSender) . '&to=' . urlencode($to) . '&msg=' . $message . '';
        $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
        if ($tspecific->value && $vars['sendlater']) {
            $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
            $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
            $url .= '&date_time=' . date('Ymd' . $sendhr->value . $sendmin->value);
        }
    } elseif ($smsGateway == "directcybertech") {
        $to = str_replace("+", "", $to);
        $url = 'http://smscp.directcybertech.com/api/sendhttp.php?authkey=' . urlencode($smsUsername) . '';
        $url .= '&sender=' . urlencode($smsSender) . '&country=0&mobiles=' . urlencode($to) . '&message=' . urlencode($message) . '&route=' . $smsPassword;
    } elseif ($smsGateway == "twilio") {
        require_once "TwilioApi.php";
        $to = "+" . str_replace("+", "", $to);
        $res = SMS_user_Twilio($smsUsername, $smsPassword, $to, $smsSender, $message);
        //logactivity("DEBUG SMSMANAGER: gateway: " . $smsGateway . " - Result: " . $res); //debugging
        logModuleCall('smsmanager', $smsGateway, array($smsUsername, $smsPassword, $to, $smsSender, $message), $res);
        if ($vars['manual']) {
            return "Gateway Result: " . $res;
        }
        return true;
    } elseif ($smsGateway == "bulksms unicode") {
        $data_array = array('to' => $to, 'from' => $smsSender, 'message' => $message, 'username' => $smsUsername, 'password' => $smsPassword);
        if ($SETING['bulksmsroutes'] != '2' && $SETING['bulksmsroutes'] != '') {
            $data_array['routing_group'] = $SETING['bulksmsroutes'];
        }
        $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
        if ($tspecific->value && $vars['sendlater']) {
            $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
            $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
            $data_array['schedule-date'] = date('Y-m-d ' . $sendhr->value . ':' . $sendmin->value . ':01');
        }
        $res = bulksms_jsonapi($data_array);
        logModuleCall('smsmanager', $smsGateway, $data_array, $res);
        //logactivity("DEBUG SMSMANAGER: gateway: " . $smsGateway . " - Result: " . $res); //debugging
        if ($vars['manual']) {
            return "Gateway Result: " . $res;
        }
        return true;
    } elseif ($smsGateway == "plivo") {
        $url = 'https://api.plivo.com/v1/Account/' . $smsUsername . '/Message/';
        $data = array("src" => "$smsSender", "dst" => "$to", "text" => "$message");
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_USERPWD, $smsUsername . ":" . $smsPassword);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        logModuleCall('smsmanager', $smsGateway, $data, $response);
        if ($vars['manual']) {
            return "Gateway Result: " . $response;
        }
        return true;
    } elseif ($smsGateway == "africastalking") {
        require_once __DIR__ . '/AfricasTalkingGateway.php';
        $to = "+" . str_replace("+", "", $to);
        $bitem = Capsule::table('mod_smsmanager_africasoptout')->where('phonenumber', $to)->first();
        if (count($bitem) > 0) {
            logactivity("DEBUG SMSMANAGER: gateway: " . "Gateway Result: Black Listed phone"); //debugging
            return "Gateway Result: Black Listed phone";
        }
        $data_array = array('to' => $to, 'from' => $smsSender, 'message' => $message, 'username' => $smsUsername, 'apiKey' => $smsPassword);
        if ($smsUsername == 'sandbox') {
            $Africasgateway = new AfricasTalkingGateway($smsUsername, $smsPassword, "sandbox");
        } else {
            $Africasgateway = new AfricasTalkingGateway($smsUsername, $smsPassword);
        }
        try {
            // Thats it, hit send and we'll take care of the rest.
            $results = $Africasgateway->sendMessage($to, $message, $smsSender);
        } catch (AfricasTalkingGatewayException $e) {
            if ($vars['manual']) {
                logModuleCall('smsmanager', $smsGateway, $data_array, $e->getMessage());
                return "Gateway Result: " . $e->getMessage(); //debugging
            }
        }
        logModuleCall('smsmanager', $smsGateway, $data_array, $results);
        return true;
    } elseif ($smsGateway == "messagebird") {
        $method = 'MessageBird';
    } elseif ($smsGateway == "dakiksms") {
        //$to = str_replace('%2B','',$to);
        //$to = str_replace('+','',$to);
        $url = 'http://www.dakiksms.com/api/xml_api_ileri.php';
        $smsParams = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<SMS>' .
            '<oturum>' .
            '<kullanici>' . $smsUsername . '</kullanici>' .
            '<sifre>' . $smsPassword . '</sifre>' .
            '</oturum>' .
            '<mesaj>' .
            '<baslik>' . $smsSender . '</baslik>' .
            '<metin>' . $message . '</metin>' .
            '<alicilar>' . $to . '</alicilar>' .
            '</mesaj>
                <karaliste>kendi</karaliste>
            </SMS>';
        $method = 'xmlpost';
    } else {
        return "Error, please select a valid SMS Gateway. " . $smsGateway . ' selected.';
    }
    if ($method == "post") {
        $res = smsmanager_post($url, $smsParams);
        logModuleCall('smsmanager', $smsGateway, $smsParams, $res);
        //logactivity("DEBUG SMSMANAGER: " . $url . " - Params: " . $smsParams . " - Result: " . $res); //debugging
        if ($vars['manual']) {
            return "Gateway Result: " . $res; //debugging
        }
    } elseif ($method == "xmlpost") {
        $res = smsmanager_xmlpost($url, $smsParams);
        //logactivity("DEBUG SMSMANAGER: " . $url . " - Params: " . $smsParams . " - Result: " . $res); //debugging
        logModuleCall('smsmanager', $smsGateway, $smsParams, $res);
        if ($vars['manual']) {
            return "Gateway Result: " . $res; //debugging
        }
    } elseif ($method == "jsonpost") {
        $res = smsmanager_jsonpost($url, $smsParams);
        logModuleCall('smsmanager', $smsGateway, $smsParams, $res);
    } elseif ($method == "MessageBird") {
        require __DIR__ . '/messagebird/autoload.php';
        $MessageBird = new \MessageBird\Client($smsUsername);
        $Message = new \MessageBird\Objects\Message();
        if (isset($smsSender) && $smsSender != '') {
            $Message->originator = $smsSender;
        } else {
            $Message->originator = 'MessageBird';
        }
        $to = str_replace('+', '', $to);
        $Message->recipients = array($to);
        $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
        if ($tspecific->value) {
            $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
            $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
            $Message->scheduledDatetime = date('Y-m-d ' . $sendhr->value . ':' . $sendmin->value . ':01');
        }
        $Message->body = $message;
        if (strlen($message) != mb_strlen($message, 'utf-8')) {
            $Message->datacoding = 'unicode';
        }
        $status = '';
        try {
            $MessageResult = $MessageBird->messages->create($Message);
            $status = 'Sent';
            //logactivity("DEBUG SMSMANAGER: MessageBird - send sms to : " . $to . " - Result: " . $status); //debugging
        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            //logactivity("DEBUG SMSMANAGER: MessageBird - Params: " . $smsParams . " - Result: Login Error"); //debugging
            $status = 'Login error';
        } catch (\MessageBird\Exceptions\BalanceException $e) {
            //logactivity("DEBUG SMSMANAGER: MessageBird - Params: " . $smsParams . " - Result: Balance is low"); //debugging
            $status = 'Balance error';
        } catch (\Exception $e) {
            //logactivity("DEBUG SMSMANAGER: MessageBird - Params: " . $smsParams . " - Result: " . $e->getMessage()); //debugging
            $status = 'Error : ' . $e->getMessage();
        }
        logModuleCall('smsmanager', $smsGateway, array('key' => $smsUsername, 'from' => $Message->originator), $status);
        if ($vars['manual']) {
            return "Gateway Result: " . $status; //debugging
        }
        return $status;
    } else {
        $fp = fopen($url, 'r');
        $res = fread($fp, 1024);
        if ($res == false) {
            $res = @file_get_contents($url);
            if ($res == false) {
                $res = smsmanager_post($url, '');
            }
        }
        logModuleCall('smsmanager', $smsGateway, $url, $res);
        //logactivity("DEBUG SMSMANAGER: gateway: " . $smsGateway . " - Result: " . $res); //debugging
        if ($vars['manual']) {
            return "Gateway Result: " . $res; //debugging
        }
    }
    return fread($fp, 1024);
}

function smsmanager_post($url, $vars)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function smsmanager_xmlpost($url, $vars)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_MUTE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function smsmanager_jsonpost($url, $vars)
{
    $data = array("to" => array($vars['to']), "content" => $vars['message']);
    $data_string = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_MUTE, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: ' . $vars['authorization'] . '',
        'Content-Length: ' . strlen($data_string) . '',
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function smsmanager_logsms($userid, $to, $message, $response)
{
    $timestamp = time();
    Capsule::table('mod_smsmanager')->insert([
        'timestamp' => $timestamp,
        'userid' => $userid,
        'recipent' => $to,
        'message' => $message,
        'response' => $response,
    ]);
    return true;
}