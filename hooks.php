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
// DO NOT EDIT ANYTHING BELOW THIS LINE

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

require_once('includes/functions.inc.php');

function smsmanager_settings()
{
    $SETING = array();
    $result = Capsule::table('mod_smsmanager_config')->get();
    foreach ($result as $data) {
        $setting = $data->setting;
        $value = $data->value;
        $SETING["{$setting}"] = "{$value}";
    }
    return $SETING;
}

function smsmanager_getclientnumber($userid)
{
    $fieldid = smsmanager_fieldid();
    if ($fieldid != '') {
        $dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
    } else {
        $command = 'GetClientsDetails';
        $postData = array(
            'clientid' => $userid,
            'stats' => true,
        );
        $admindat = Capsule::table('tbladmins')->where('roleid', '1')->where('disabled', '0')->select('id')->first();
        $admindata = $admindat->username;
        $results = localAPI($command, $postData, $admindata);
        $phone = $results['phonenumber'];
        if ($results['phonenumberformatted']) {
            $phone = $results['phonenumberformatted'];
        }
        $phone = str_replace('.', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $dataa = $phone;
    }
    return $dataa;
}

function smsmanager_receiveAlerts($userid)
{
    $result = Capsule::table('mod_smsmanager_preferences')->where('userid', $userid)->value('value');
    $value = unserialize($result);
    if ($value['smsalerts'] == "on")
        return true;
    else {
        if (!isset($value['smsalerts']) || @$value['smsalerts'] == '') {
            $result = Capsule::table('mod_smsmanager_config')->where('setting', 'tplsms_all_alerts')->first();
            if (strtolower($result->value) == "on")
                return true;
        }
        return false;
    }
}

function smsmanager_nav()
{
    global $CONFIG;
    if (isset($_SESSION['uid'])) {
        if (file_exists(ROOTDIR . "/modules/addons/smsmanager/lang/" . $CONFIG['Language'] . ".php")) {
            include(ROOTDIR . "/modules/addons/smsmanager/lang/" . $CONFIG['Language'] . ".php");
        } elseif (file_exists(ROOTDIR . "/modules/addons/smsmanager/lang/english.php")) {
            include(ROOTDIR . "/modules/addons/smsmanager/lang/english.php");
        }
        $check = Capsule::table('mod_smsmanager_config')->where('setting', 'tplsms_alerts')->value('value');
        if ($check == '')
            return '';
        $secondaryNavbar = Menu::secondaryNavbar();
        $smsmanager_nav = $_ADDONLANG['nav_title'];
        $secondaryNavbar['Account']->addChild('smsmanager', array('label' => $smsmanager_nav, 'uri' => 'index.php?m=smsmanager', 'order' => 1));
    }
}

function smsmanager_toTrim($to)
{
    $to = mysql_real_escape_string($to);
    $to = str_replace("+", "", $to);
    $to = str_replace(" ", "", $to);
    $to = trim($to);
    return floatval($to);
}

function smsmanager_formatMessage($message, $vars = '')
{
    global $CONFIG;
    $message = trim(mysql_real_escape_string($message));
    $companyname = $CONFIG['CompanyName'];
    $message = str_replace('{CompanyName}', $companyname, $message);
    if (!empty($vars)) {
        if (isset($vars['ticketid']) && !empty($vars['ticketid']) && is_numeric($vars['ticketid'])) {
            $ticketid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('tid');
            $ticketStatus = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('status');
        }

        if (isset($vars['params']['userid']) && !empty($vars['params']['userid']) && is_numeric($vars['params']['userid'])) {
            $clientid = $vars['params']['userid'];
        } elseif (isset($vars['userid']) && !empty($vars['userid']) && is_numeric($vars['userid'])) {
            $clientid = $vars['userid'];
        } elseif (isset($vars['user']) && !empty($vars['user']) && is_numeric($vars['user'])) {
            if (isset($_REQUEST['userid']) && !empty($_REQUEST['userid']) && is_numeric($_REQUEST['userid'])) {
                $clientid = $_REQUEST['userid'];
            } elseif (isset($_SESSION['uid']) && !empty($_SESSION['uid']) && is_numeric($_SESSION['uid'])) {
                $clientid = $_SESSION['uid'];
            } else
                $clientid = $vars['user'];
        } else {
            $clientid = $vars['clientid'];
        }
        if (isset($vars['serviceid']) && !empty($vars['serviceid']) && is_numeric($vars['serviceid'])) {
            $serviceid = $vars['serviceid'];
        } else {
            $serviceid = $vars['relid'];
        }

        if (isset($vars['invoiceid'])) {
            $invoiceid = $vars['invoiceid'];
        }

        if ($invoiceid != "" && $invoiceid) {
            $invdata = Capsule::table('tblinvoices')->where('id', $invoiceid)->first();
            $message = str_replace('{InvoiceID}', "#" . $invoiceid, $message);
            $currencyData = getCurrency($invdata->userid);
            $total = $currencyData['prefix'] . $invdata->total . ' ' . $currencyData['suffix'];
            $message = str_replace('{InvoiceTotal}', $total, $message);
            $duedate = fromMySQLDate($invdata->duedate);
            $message = str_replace('{InvoiceDueDate}', $duedate, $message);
            $dnitem = Capsule::table('tblorders')->where('tblorders.invoiceid', $invoiceid)->join('tblhosting', 'tblhosting.orderid', '=', 'tblorders.id')->select('tblhosting.domain as domain')->first();
            $idnma = '';
            if (count($dnitem) > 0) {
                $idnma = $dnitem->domain;
            } else {
                $dnitem = Capsule::table('tblorders')->where('tblorders.invoiceid', $invoiceid)->join('tbldomains', 'tbldomains.orderid', '=', 'tblorders.id')->select('tbldomains.domain as domain')->first();
                if (count($dnitem) > 0) {
                    $idnma = $dnitem->domain;
                }
            }
            $message = str_replace('{InvoiceDomain}', $idnma, $message);
        }
        if (isset($vars['rdomains'])) {
            $message = str_replace('{domain}', $vars['rdomains'], $message);
        }
        if ($clientid) {
            $cdata = Capsule::table('tblclients')->where('id', $clientid)->first();
            $firstname = ucwords($cdata->firstname);
            $lastname = ucwords($cdata->lastname);
            $message = str_replace('{ClientFullName}', $firstname . ' ' . $lastname, $message);
            $message = str_replace('{ClientFirstName}', $firstname, $message);
            $message = str_replace('{ClientLastName}', $lastname, $message);
        }
        if (isset($vars['iadmin'])) {
            $cdata = Capsule::table('tbladmins')->where('id', $vars['iadmin'])->first();
            $firstname = ucwords($cdata->firstname);
            $lastname = ucwords($cdata->lastname);
            $message = str_replace('{AdminFullName}', $firstname . ' ' . $lastname, $message);
            $message = str_replace('{AdminFirstName}', $firstname, $message);
            $message = str_replace('{AdminLastName}', $lastname, $message);
            $message = str_replace('{AdminDomain}', $vars['iadmindomain'], $message);
        }
        $message = str_replace('{packagename}', $vars['packagename'], $message);
        $message = str_replace('{ServiceID}', $serviceid, $message);
        $message = str_replace('{ClientID}', $clientid, $message);
        $message = str_replace('{OrderID}', $vars['OrderID'], $message);
        $message = str_replace('{TicketID}', $ticketid, $message);
        $message = str_replace('{TicketStatus}', $ticketStatus, $message);
    }
    //logactivity("Variables: ".print_r($vars,true)); // Debugging
    return $message;
}

function smsmanager_fieldid()
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "customfieldid")->value('value');
    return $data;
}

/// ADMIN NOTIFICATIONS ///

function smsmanager_admin_UnsuspendProduct($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminUnsuspendProductOn")->value('value');
    if (is_array($vars['params']))
        $vars = $vars['params'];
    if (strtolower($data) == "on") {

        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblhosting')->where('id', $vars['serviceid'])->value('userid');
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();

        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();


        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['productunsuspend']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminUnsuspendProduct')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminUnsuspendProduct")->value('value');

            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminUnsuspendProduct")->value('value');
            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

function smsmanager_admin_CreateProduct($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminCreateProductOn")->value('value');
    if (strtolower($data) == "on") {
        if (is_array($vars['params']))
            $vars = $vars['params'];
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblhosting')->where('id', $vars['serviceid'])->value('userid');
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();

        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;
        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['productcreate']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminCreateProduct')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminCreateProduct")->value('value');

            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminCreateProduct")->value('value');

            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

function smsmanager_admin_Checkout($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminCheckoutOn")->value('value');
    if (strtolower($data) == "on") {

        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblinvoices')->where('id', $vars['InvoiceID'])->value('userid');
        if ($userid == '')
            $userid = Capsule::table('tblorders')->where('id', $vars['OrderID'])->value('userid');
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        if ($vars['InvoiceID'] > 0) {
            if ($SETING['notfreeinvoice']) {
                $itotal = Capsule::table('tblinvoices')->where('id', $vars['InvoiceID'])->value('total');
                if ($itotal <= 0) {
                    return;
                }
            }
        }
        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();

        global $CONFIG;

        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['checkout']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminCheckout')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminCheckout")->value('value');
            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminCheckout")->value('value');
            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

function smsmanager_admin_TicketOpen($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminTicketOpenOn")->value('value');
    if (strtolower($data) == "on") {

        $userid = $vars['userid'];

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        $deptid = $vars['deptid'];

        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;

        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['ticketopen'][$deptid]) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminTicketOpen')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminTicketOpen")->value('value');
            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminTicketOpen")->value('value');
            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

function smsmanager_admin_TicketUserReply($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminTicketUserReplyOn")->value('value');
    if (strtolower($data) == "on") {

        $userid = $vars['userid'];

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        $deptid = $vars['deptid'];

        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();

        global $CONFIG;

        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['ticketuserreply'][$deptid]) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminTicketClose')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminTicketUserReply")->value('value');

            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminTicketUserReply")->value('value');
            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

function smsmanager_admin_TicketClose($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminTicketCloseOn")->value('value');
    if (strtolower($data) == "on") {

        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('userid');

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        $deptid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('did');
        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;


        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['ticketclose'][$deptid]) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminTicketClose')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminTicketClose")->value('value');

            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminTicketClose")->value('value');
            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

function smsmanager_admin_CancellationRequest($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminCancellationRequestOn")->value('value');
    if (strtolower($data) == "on") {

        $userid = $vars['userid'];

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();

        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;

        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;

            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['cancellationrequest']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminCancellationRequest')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminCancellationRequest")->value('value');

            //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminCancellationRequest")->value('value');
            $message = smsmanager_formatMessage($datac, $vars);
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
}

//add_hook("AdminAreaHeaderOutput", 0, "smsmanager_admin_UnsuspendProduct");
/// CLIENT NOTIFICATIONS ///

function smsmanager_ClientLogin($vars)
{
    if (isset($_SESSION['adminid']))
        return '';
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplClientLoginOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$datab = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $datab = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($datab);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ClientLogin')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {

            $userlang = strtolower($CONFIG['Language']);
        }
        $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "ClientLogin")->value('value');
        //$datac = Capsule::table('mod_smsmanager_config')->where('setting', "tplClientLogin")->value('value');
        $message = smsmanager_formatMessage($datac, $vars);
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_ClientAreaRegister($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplClientRegistrationOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ClientRegistration')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "ClientRegistration")->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplClientRegistration")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_ClientChangePassword($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplClientPasswordChangeOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $datab = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($datab);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ClientPasswordChange')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "ClientPasswordChange")->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplClientPasswordChange")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_AffiliateActivation($vars)
{

    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAffiliateActivationOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'AffiliateActivation')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AffiliateActivation")->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplAffiliateActivation")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_InvoiceCreated($vars)
{
    $invoiceid = $vars['invoiceid'];
    $userid = Capsule::table('tblinvoices')->where('id', $invoiceid)->value('userid');
    if (isset($vars['user']))
        $vars['userid'] = $userid;
    $receivealerts = smsmanager_receiveAlerts($userid);
    if (!$receivealerts)
        return;

    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplInvoiceCreatedOn")->value('value');
    if (strtolower($data) == "on") {
        $SETING = smsmanager_settings();
        if ($SETING['notfreeinvoice']) {
            $itotal = Capsule::table('tblinvoices')->where('id', $invoiceid)->value('total');
            if ($itotal <= 0) {
                return;
            }
        }
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'InvoiceCreated')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "InvoiceCreated")->value('value');
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplInvoiceCreated")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_InvoicePaid($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplInvoicePaidOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        if ($SETING['notfreeinvoice']) {
            $itotal = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('total');
            if ($itotal <= 0) {
                return;
            }
        }
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplInvoicePaid")->value('value');
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'InvoicePaid')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "InvoicePaid")->value('value');

        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_InvoiceReminder($vars)
{
    if ($vars['messagename'] == 'First Invoice Overdue Notice') {
        $vars['type'] = 'firstoverdue';
    } elseif ($vars['messagename'] == 'Second Invoice Overdue Notice') {
        $vars['type'] = 'secondoverdue';
    } elseif ($vars['messagename'] == 'Third Invoice Overdue Notice') {
        $vars['type'] = 'thirdoverdue';
    } elseif ($vars['messagename'] == 'Invoice Payment Reminder') {
        $vars['type'] = 'reminder';
    } else {
        return '';
    }
    $vars['invoiceid'] = $vars['relid'];
    $type = $vars['type'];
    $dataOn = Capsule::table('mod_smsmanager_config')->where('setting', "tplInvoiceReminderOn")->value('value');
    if (strtolower($dataOn) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;
        $SETING = smsmanager_settings();
        if ($SETING['notfreeinvoice']) {
            $itotal = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('total');
            if ($itotal <= 0) {
                return;
            }
        }
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        if ($type == "reminder") {
            $resultb = "InvoiceReminder";
        } elseif ($type == "firstoverdue") {
            $resultb = "InvoiceFirstOverdue";
        } elseif ($type == "secondoverdue") {
            $resultb = "InvoiceSecondOverdue";
        } elseif ($type == "thirdoverdue") {
            $resultb = "InvoiceThirdOverdue";
        } else {
            return false;
        }
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', $resultb)->value('value');
        $vars['userid'] = $userid;

        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', $resultb)->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', $resultb)->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_DomainReminder($vars)
{
    if ($vars['messagename'] == 'First Invoice Overdue Notice') {
        $vars['type'] = 'firstoverdue';
    } elseif ($vars['messagename'] == 'Second Invoice Overdue Notice') {
        $vars['type'] = 'secondoverdue';
    } elseif ($vars['messagename'] == 'Third Invoice Overdue Notice') {
        $vars['type'] = 'thirdoverdue';
    } elseif ($vars['messagename'] == 'Invoice Payment Reminder') {
        $vars['type'] = 'reminder';
    } else {
        return '';
    }
    $vars['invoiceid'] = $vars['relid'];
    $type = $vars['type'];
    $dataOn = Capsule::table('mod_smsmanager_config')->where('setting', "tplInvoiceReminderOn")->value('value');
    if (strtolower($dataOn) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        if ($SETING['notfreeinvoice']) {
            $itotal = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->value('total');
            if ($itotal <= 0) {
                return;
            }
        }
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        if ($type == "reminder") {
            $resultb = "DomainReminder";
        } elseif ($type == "firstoverdue") {
            $resultb = "DomainFirstOverdue";
        } elseif ($type == "secondoverdue") {
            $resultb = "DomainSecondOverdue";
        } elseif ($type == "thirdoverdue") {
            $resultb = "DomainThirdOverdue";
        } else {
            return false;
        }
        $domains = Capsule::table('tblorders')->where('invoiceid', $vars['invoiceid'])->join('tbldomains', 'tbldomains.orderid', '=', 'tblorders.id')->select('domain')->get();
        $dlist = '';
        if (count($domains) == 0)
            return '';
        if (count($domains) == 1)
            $dlist = $domains[0]->domain;
        if (count($domains) > 1) {
            foreach ($domains as $domain) {
                $dlist = $domain->domain . ',';
            }
        }
        $vars['rdomains'] = $dlist;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', $resultb)->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', $resultb)->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', $resultb)->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_ModuleCreate($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplModuleCreateOn")->value('value');
    if (is_array($vars['params']))
        $vars = $vars['params'];
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblhosting')->where('id', $vars['serviceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ModuleCreate')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'ModuleCreate')->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplModuleCreate")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_ModuleSuspend($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplModuleSuspendOn")->value('value');
    if (is_array($vars['params']))
        $vars = $vars['params'];
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblhosting')->where('id', $vars['serviceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ModuleSuspend')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'ModuleSuspend')->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplModuleSuspend")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_ModuleUnsuspend($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplModuleUnsuspendOn")->value('value');
    if (is_array($vars['params']))
        $vars = $vars['params'];
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblhosting')->where('id', $vars['serviceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ModuleUnsuspend')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'ModuleUnsuspend')->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplModuleUnsuspend")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_ModuleChangePassword($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplModulePasswordChangeOn")->value('value');
    if (is_array($vars['params']))
        $vars = $vars['params'];
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tblhosting')->where('id', $vars['serviceid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'ModulePasswordChange')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'ModulePasswordChange')->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplModulePasswordChange")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_CancellationRequest($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplCancellationRequestOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'CancellationRequest')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'CancellationRequest')->value('value');
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplCancellationRequest")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_DomainRegistration($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplDomainRegistrationOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplDomainRegistration")->value('value');
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'DomainRegistration')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'DomainRegistration')->value('value');

        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_DomainTransfer($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplDomainTransferOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'DomainTransfer')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'DomainTransfer')->value('value');

        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplDomainTransfer")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_DomainRenewal($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplDomainRenewalOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplDomainRenewal")->value('value');
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'DomainRenewal')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'DomainRenewal')->value('value');

        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_SupportTicketOpen($vars)
{

    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketOpenOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];

        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketOpen")->value('value');
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'SupportTicketOpen')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'SupportTicketOpen')->value('value');

        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_SupportTicketResponse($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketResponseOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'SupportTicketResponse')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'SupportTicketResponse')->value('value');
        //$datab = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketResponse")->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}

function smsmanager_SupportTicketClose($vars)
{
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketCloseOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['userid'];
        if (!isset($vars['userid']) || $userid == '')
            $userid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;

        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'SupportTicketClose')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'SupportTicketClose')->value('value');

        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
}
add_hook('TicketClose', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketChangeStatusOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'SupportTicketChangeStatus')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'SupportTicketChangeStatus')->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
});
add_hook('TicketStatusChange', 1, function($vars) {
    $allowed_status = array('open', 'closed', 'on hold');
    if (!in_array(strtolower($vars['status']), $allowed_status))
        return '';
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplSupportTicketChangeStatusOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'SupportTicketChangeStatus')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'SupportTicketChangeStatus')->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
});
add_hook('AfterModuleChangePackage', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplModulechangepackageOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = $vars['params']['userid'];
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $parray = array();
        $parray['userid'] = $userid;
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'Modulechangepackage')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $pitem = Capsule::table('tblproducts')->where('id', $vars['params']['packageid'])->select('name')->first();
        if (count($pitem) > 0) {
            $parray['packagename'] = $pitem->name;
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'Modulechangepackage')->value('value');
        $message = smsmanager_formatMessage($datab, $parray);
        $parray['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $parray, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
});
add_hook('AcceptOrder', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplOrderAcceptedOn")->value('value');
    if (strtolower($data) == "on") {
        $userid = Capsule::table('tblorders')->where('id', $vars['orderid'])->value('userid');
        $receivealerts = smsmanager_receiveAlerts($userid);
        if (!$receivealerts)
            return;
        $SETING = smsmanager_settings();
        $fieldid = smsmanager_fieldid();
        //$dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $userid)->value('value');
        $dataa = smsmanager_getclientnumber($userid);
        $to = smsmanager_toTrim($dataa);
        if (empty($to))
            return false;
        $vars['userid'] = $userid;
        $vars['OrderID'] = $vars['orderid'];
        global $CONFIG;
        if ($userid != '') {
            $userlang = Capsule::table('tblclients')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tblclients.language')->where('mod_smsmanager_multilanguages.name', 'OrderAccepted')->where('tblclients.id', $userid)->value('tblclients.language');
            if ($userlang == '') {
                $userlang = strtolower($CONFIG['Language']);
            }
        } else {
            $userlang = strtolower($CONFIG['Language']);
        }
        $datab = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', 'OrderAccepted')->value('value');
        $message = smsmanager_formatMessage($datab, $vars);
        $vars['sendlater'] = 'on';
        $response = smsmanager_sendSMS($to, $message, $vars, $userid);
        smsmanager_logsms($userid, $to, $message, $response);
    }
    return;
});
add_hook('AdminLogin', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminLoginOn")->value('value');
    if (strtolower($data) == "on") {
        $SETING = smsmanager_settings();
        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;
        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;
            if ($id != $vars['adminid'])
                continue;
            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['adminLogin']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminLogin')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminLogin")->value('value');
            $vars['iadmin'] = $vars['adminid'];
            $message = smsmanager_formatMessage($datac, $vars);
            smsmanager_sendSMS($to, $message, $vars);
            logActivity('SMS manager -- ' . $message);
            //smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
});
add_hook('AfterRegistrarRegistration', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminDomainRegisterOn")->value('value');
    if (strtolower($data) == "on") {
        $SETING = smsmanager_settings();
        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;
        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;
            if ($id == '')
                continue;
            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['adminDomainRegister']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminDomainRegister')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminDomainRegister")->value('value');
            $varsr = array();
            $varsr['iadmin'] = $id;
            $varsr['iadmindomain'] = $vars['params']['sld'] . "." . $vars['params']['tld'];
            $message = smsmanager_formatMessage($datac, $varsr);
            smsmanager_sendSMS($to, $message, $vars);
            logActivity('SMS manager -- ' . $message);
            //smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
});
add_hook('AfterRegistrarRegistrationFailed', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminDomainRegisterFailedOn")->value('value');
    if (strtolower($data) == "on") {
        $SETING = smsmanager_settings();
        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;
        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;
            if ($id == '')
                continue;
            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['adminDomainRegisterFailed']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminDomainRegisterFailed')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminDomainRegisterFailed")->value('value');
            $varsr = array();
            $varsr['iadmin'] = $id;
            $varsr['iadmindomain'] = $vars['params']['sld'] . "." . $vars['params']['tld'];
            $message = smsmanager_formatMessage($datac, $varsr);
            smsmanager_sendSMS($to, $message, $vars);
            logActivity('SMS manager -- ' . $message);
            //smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
});
add_hook('AfterRegistrarRenewal', 1, function($vars) {
    $data = Capsule::table('mod_smsmanager_config')->where('setting', "tplAdminDomainRenewalOn")->value('value');
    if (strtolower($data) == "on") {
        $SETING = smsmanager_settings();
        $admins = unserialize($SETING['adminMobileNumbers']);
        if (!is_array($admins))
            $admins = array();
        global $CONFIG;
        foreach ($admins AS $id => $to) {
            if (empty($to))
                continue;
            if ($id == '')
                continue;
            $fullArr = unserialize($SETING['adminNotifications']);
            $data = unserialize(base64_decode($fullArr[$id]));
            if (strtolower($data['adminDomainRenewal']) != "on")
                continue;
            if ($id != '') {
                $userlang = Capsule::table('tbladmins')->join('mod_smsmanager_multilanguages', 'mod_smsmanager_multilanguages.lang', '=', 'tbladmins.language')->where('mod_smsmanager_multilanguages.name', 'AdminDomainRenewal')->where('tbladmins.id', $id)->value('tbladmins.language');
                if ($userlang == '') {
                    $userlang = strtolower($CONFIG['Language']);
                }
            } else {
                $userlang = strtolower($CONFIG['Language']);
            }
            $datac = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $userlang)->where('name', "AdminDomainRenewal")->value('value');
            $varsr = array();
            $varsr['iadmin'] = $id;
            $varsr['iadmindomain'] = $vars['params']['sld'] . "." . $vars['params']['tld'];
            $message = smsmanager_formatMessage($datac, $varsr);
            smsmanager_sendSMS($to, $message, $vars);
            logActivity('SMS manager -- ' . $message);
            //smsmanager_logsms($userid, $to, $message, $response);
        }
        return true;
    }
    return;
});
// client hooks
add_hook("ClientAreaPrimaryNavbar", 0, "smsmanager_nav");
add_hook("ClientLogin", 0, "smsmanager_ClientLogin");
add_hook("ClientAreaRegister", 0, "smsmanager_ClientAreaRegister");
add_hook("ClientChangePassword", 0, "smsmanager_ClientChangePassword");
add_hook("AffiliateActivation", 0, "smsmanager_AffiliateActivation");
add_hook("InvoiceCreationPreEmail", 0, "smsmanager_InvoiceCreated");
add_hook("InvoiceCreationAdminArea", 0, "smsmanager_InvoiceCreated");
add_hook("InvoicePaid", 0, "smsmanager_InvoicePaid");
add_hook("EmailPreSend", 0, "smsmanager_InvoiceReminder");
add_hook("EmailPreSend", 1, "smsmanager_DomainReminder");
add_hook("AfterModuleCreate", 0, "smsmanager_ModuleCreate");
add_hook("AfterModuleSuspend", 0, "smsmanager_ModuleSuspend");
add_hook("AfterModuleUnsuspend", 0, "smsmanager_ModuleUnsuspend");
add_hook("AfterModuleChangePassword", 0, "smsmanager_ModuleChangePassword");
add_hook("CancellationRequest", 0, "smsmanager_CancellationRequest");
add_hook("AfterRegistrarRegistration", 0, "smsmanager_DomainRegistration");
add_hook("AfterRegistrarTransfer", 0, "smsmanager_DomainTransfer");
add_hook("AfterRegistrarRenew", 0, "smsmanager_DomainRenewal");
add_hook("TicketOpen", 0, "smsmanager_SupportTicketOpen");
add_hook("TicketAdminReply", 0, "smsmanager_SupportTicketResponse");
add_hook("TicketClose", 0, "smsmanager_SupportTicketClose");

// admin hooks
add_hook("CancellationRequest", 0, "smsmanager_admin_CancellationRequest");
add_hook("TicketClose", 0, "smsmanager_admin_TicketClose");
add_hook("TicketUserReply", 0, "smsmanager_admin_TicketUserReply");
add_hook("TicketOpen", 0, "smsmanager_admin_TicketOpen");
add_hook("AfterShoppingCartCheckout", 0, "smsmanager_admin_Checkout");
add_hook("AfterModuleCreate", 0, "smsmanager_admin_CreateProduct");
add_hook("AfterModuleUnsuspend", 0, "smsmanager_admin_UnsuspendProduct");

add_hook('ClientAreaFooterOutput', 0, function($vars) {
    global $smarty, $CONFIG, $_LANG;
    $return = '';
    $filename = App::getCurrentFilename();
    $samech = array('modern', 'moderntkiLIVE', 'dash-modern', 'dash_modern');
    if (in_array($vars['carttpl'], $samech) && ($filename == 'cart' && isset($_REQUEST['a']) && $_REQUEST['a'] == 'view')) {
        if (isset($_SESSION['cart'])) {
            redir('a=checkout', 'cart.php');
            exit;
        }
    }
    if (($filename == 'cart' && isset($_REQUEST['a']) && $_REQUEST['a'] == 'checkout') || ($filename == 'clientarea' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'details')) {
        $data = Capsule::table('mod_smsmanager_config')->where('setting', 'customfieldid')->first();
        if ($data->value != "") {
            global $CONFIG;
            if (file_exists(ROOTDIR . "/modules/addons/smsmanager/lang/" . $CONFIG['Language'] . ".php")) {
                include(ROOTDIR . "/modules/addons/smsmanager/lang/" . $CONFIG['Language'] . ".php");
            } elseif (file_exists(ROOTDIR . "/modules/addons/smsmanager/lang/english.php")) {
                include(ROOTDIR . "/modules/addons/smsmanager/lang/english.php");
            }
            $return .= '
		<link rel="stylesheet" href="modules/addons/smsmanager/assets/intlTelInput.css">
		<script src="modules/addons/smsmanager/assets/intlTelInput.min.js"></script>';
            $return .= '<script>
				$("#customfield' . $data->value . '").intlTelInput({
                    separateDialCode: true,
                                    utilsScript: "modules/addons/smsmanager/assets/utils.js"
				});
                $("#customfield' . $data->value . '").intlTelInput("setCountry", "' . strtolower($CONFIG['DefaultCountry']) . '");
                $("#inputCountry").on("keyup change", function () {
                    $("#customfield' . $data->value . '").intlTelInput("setCountry",$(this).val());
                });
                                $("form").submit(function(e) {
                                    if ($("[name=custtype]").length ) {
                                        if($("[name=custtype]").val() == "existing"){
                                            return "";
                                        }
                                    }';
            $rdata = Capsule::table('mod_smsmanager_config')->where('setting', 'requiredmobile')->first();
            if ($rdata->value == '' || $rdata->value != 'no') {
                $return .= 'var isValid = $("#customfield' . $data->value . '").intlTelInput("isValidNumber");
                    var trsb = $("#customfield' . $data->value . '").val();
                                    if(!isValid && !trsb.contains("+")){
                                        e.preventDefault();
                                        alert("' . $_ADDONLANG['fillright'] . '");
                                        return "";
                                    }
                                    ';
            }
            $return .= 'var valorOculto = $("#customfield' . $data->value . '").intlTelInput("getNumber", intlTelInputUtils.numberFormat.E164);
                                    $("#customfield' . $data->value . '").val(valorOculto);
                                });
			  </script>';
        }
    }
    return $return;
});
add_hook('ClientAreaFooterOutput', 0, function($vars) {
    global $smarty, $CONFIG, $_LANG;
    $return = '';
    $filename = App::getCurrentFilename();
    if ($filename == 'register') {
        $data = Capsule::table('mod_smsmanager_config')->where('setting', 'customfieldid')->first();
        if ($data->value != "") {
            $return .= '
		<link rel="stylesheet" href="modules/addons/smsmanager/assets/intlTelInput.css">
		<script src="modules/addons/smsmanager/assets/intlTelInput.min.js"></script>';
            $return .= '<script>
                $("#customfield' . $data->value . '").addClass("form-control input-200");
				$("#customfield' . $data->value . '").intlTelInput({
                    separateDialCode: true,
                    utilsScript: "modules/addons/smsmanager/assets/utils.js"
				});
                 $("form").submit(function(e) {
                    var valorOculto = $("#customfield' . $data->value . '").intlTelInput("getNumber", intlTelInputUtils.numberFormat.E164);
                    $("#customfield' . $data->value . '").val(valorOculto);
                });
                $("#customfield' . $data->value . '").intlTelInput("setCountry", "' . strtolower($CONFIG['DefaultCountry']) . '");
                $("#inputCountry").on("keyup change", function () {
                    $("#customfield' . $data->value . '").intlTelInput("setCountry",$(this).val());
                });
			  </script>';
        }
    }
    return $return;
});
add_hook('AdminAreaFooterOutput', 0, function($vars) {
    global $smarty, $CONFIG, $_LANG;
    $return = '';
    $filename = App::getCurrentFilename();
    if ($filename == 'clientsprofile' || $filename == 'clientsadd') {
        $data = Capsule::table('mod_smsmanager_config')->where('setting', 'customfieldid')->first();
        if ($data->value != "") {
            $return .= '
		<link rel="stylesheet" href="../modules/addons/smsmanager/assets/intlTelInput.css">
		<script src="../modules/addons/smsmanager/assets/intlTelInput.min.js"></script>';
            $return .= '<script>
                $("#customfield' . $data->value . '").addClass("form-control input-200");
				$("#customfield' . $data->value . '").intlTelInput({
                    separateDialCode: true,
                                    utilsScript: "../modules/addons/smsmanager/assets/utils.js"
				});
                                $("form").submit(function(e) {
                                    var valorOculto = $("#customfield' . $data->value . '").intlTelInput("getNumber", intlTelInputUtils.numberFormat.E164);
                                    $("#customfield' . $data->value . '").val(valorOculto);
                                });
			  </script>';
        }
    }
    return $return;
});
add_hook('AdminAreaClientSummaryPage', 0, function($vars) {
    global $smarty, $CONFIG, $_LANG;
    if (isset($_REQUEST['wssmsmessage'])) {
        $message = $_REQUEST['wssmsmessage'];
        $dataa = smsmanager_getclientnumber($_REQUEST['userid']);
        $to = smsmanager_toTrim($dataa);
        smsmanager_sendSMS($to, $message, array());
        echo '<div class="alert alert-success"><b>SMS manager - SMS is sent to client!</b></div>';
    }
    $trs = '';
    $trs .= '<tr><td align="center"><textarea name="wssmsmessage" class="form-control"></textarea></td></tr>';
    $trs .= '<tr><td align="center"><button class="btn btn-sm btn-success" type="submit" value="Send now">Send now</button></td></tr>';
    global $aInt;
    if ($aInt->adminTemplate == 'v4') {
        echo '<script>
    jQuery(document).ready(function () {
    jQuery("#clientsummarycontainer").find(".clientssummarybox:eq(5)").after(`<div class="clientssummarybox"><div class="title">Send SMS to client</div><form method="post"><table class="clientssummarystats" cellspacing="0" cellpadding="2"><tbody>' . $trs . '</tbody></table></form></div>`);
    });
</script>';
    } else {
        echo '<script>
    jQuery(document).ready(function () {
    jQuery(".client-summary-panels").find(".col-sm-6:eq(0)").find(".clientssummarybox:eq(1)").after(`<div class="clientssummarybox"><div class="title">Send SMS to client</div><form method="post"><table class="clientssummarystats" cellspacing="0" cellpadding="2"><tbody>' . $trs . '</tbody></table></form></div>`);
    });
</script>';
    }
});
