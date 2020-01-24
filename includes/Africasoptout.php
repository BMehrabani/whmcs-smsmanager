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

use Illuminate\Database\Capsule\Manager as Capsule;

require('../../../../init.php');

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
if (isset($_POST['senderId']) && $_POST['senderId'] != '' && isset($_POST['phoneNumber']) && $_POST['phoneNumber'] != '') {
    if (Capsule::schema()->hasTable('mod_smsmanager_africasoptout')) {
        $phonnum = trim(strip_tags($_POST['phoneNumber']));
        $senderid = trim(strip_tags($_POST['senderId']));
        $item = Capsule::table('mod_smsmanager_africasoptout')->where('phonenumber', $phonnum)->first();
        if (count($item) <= 0) {
            Capsule::table('mod_smsmanager_africasoptout')->insert([
                'phonenumber' => $phonnum,
                'senderid' => $senderid,
            ]);
            echo 'Added';
        }
    }
}
exit;
