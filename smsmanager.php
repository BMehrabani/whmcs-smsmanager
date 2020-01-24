<?php
/* * ********************************************************************
 * Jisort for WHMCS by Jisort
 * Copyright  Jisort,  All Rights Reserved
 *
 * Created By WHMCSServices      http://www.jisort.com
 * Contact:                      admin@jisort.com
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 * ******************************************************************** */
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Utility\Country as Country;

require_once('includes/functions.inc.php');

function smsmanager_config()
{
    $configarray = array(
        "name" => "Jisort",
        "description" => "Jisort for WHMCS is an advanced SMS System for WHMCS.",
        "version" => "1.0.0",
        "author" => "Jisort",
        "language" => "english",
        "fields" => array(
            "smsgateway" => array("FriendlyName" => "SMS Service Provider", "Type" => "dropdown", "Options" => "Jisort"),
            "nodeletedb" => array("FriendlyName" => "Database Table", "Type" => "yesno", "Size" => "25", "Description" => "Tick this box to delete the tables from the database when deactivating the module(with client mobiles too).",),
        ),
    );
    return $configarray;
}

function smsmanager_activate()
{
    try {
        Capsule::schema()->create(
            'mod_smsmanager_config', function ($table) {
            $table->text('setting');
            $table->text('value');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_smsmanager_config : {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
            'mod_smsmanager_multiconfig', function ($table) {
            $table->increments('id');
            $table->string('country');
            $table->text('setting');
            $table->text('value');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_smsmanager_multiconfig : {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
            'mod_smsmanager_confirmations', function ($table) {
            $table->increments('id');
            $table->integer('userid');
            $table->text('recipent');
            $table->integer('confirmed');
            $table->integer('timestamp');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_smsmanager_confirmations : {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
            'mod_smsmanager_preferences', function ($table) {
            $table->increments('id');
            $table->integer('userid');
            $table->text('value');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_smsmanager_preferences : {$e->getMessage()}");
    }
    try {
        Capsule::schema()->create(
            'mod_smsmanager', function ($table) {
            $table->increments('id');
            $table->integer('userid');
            $table->text('recipent');
            $table->text('message');
            $table->text('response');
            $table->integer('timestamp');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_smsmanager : {$e->getMessage()}");
    }
    $customfieldid = Capsule::table('tblcustomfields')->insertGetId([
        'type' => 'client',
        'relid' => '0',
        'fieldname' => 'Mobile Number',
        'fieldtype' => 'text',
        'description' => 'Enter your mobile phone number to receive SMS Alerts',
        'fieldoptions' => '',
        'regexpr' => '',
        'adminonly' => '',
        'required' => '',
        'sortorder' => '',
        'showorder' => 'on',
        'showinvoice' => '',
    ]);
    try {
        Capsule::table('mod_smsmanager_config')->insert(array(
            array(
                'setting' => 'tplsms_alerts',
                'value' => 'on',
            ),
            array(
                'setting' => 'requiredmobile',
                'value' => 'yes',
            ),
            array(
                'setting' => 'api_id',
                'value' => '',
            ),
            array(
                'setting' => 'api_domain',
                'value' => 'bulksms.vsms.net',
            ),
            array(
                'setting' => 'api_username',
                'value' => '',
            ),
            array(
                'setting' => 'api_password',
                'value' => '',
            ),
            array(
                'setting' => 'api_sender',
                'value' => '',
            ),
            array(
                'setting' => 'adminMobileNumbers',
                'value' => '',
            ),
            array(
                'setting' => 'adminNotifications',
                'value' => '',
            ),
            array(
                'setting' => 'customfieldid',
                'value' => $customfieldid,
            ),
            array(
                'setting' => 'tplClientLogin',
                'value' => 'Notification: You have just logged into your account at {CompanyName}',
            ),
            array(
                'setting' => 'tplClientLoginOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplClientRegistration',
                'value' => 'Notification: Thank you for registering with {CompanyName}, you will now receive SMS Alerts from us.',
            ),
            array(
                'setting' => 'tplClientRegistrationOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAffiliateActivation',
                'value' => 'Notification: Your Affiliate Account has been activated at {CompanyName}',
            ),
            array(
                'setting' => 'tplAffiliateActivationOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplClientPasswordChange',
                'value' => 'Notification: You have changed your password for the {CompanyName} Client Area. If you did not make this change then please contact us.',
            ),
            array(
                'setting' => 'tplClientPasswordChangeOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplClientTwoFactor',
                'value' => 'To contine with your login please enter the following code: {TwoFactor}',
            ),
            array(
                'setting' => 'tplClientTwoFactorOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplInvoiceCreated',
                'value' => 'Notification: A new invoice has been generated for your service with {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoiceCreatedOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplInvoicePaid',
                'value' => 'Thank you for your payment! Your invoice is now marked as paid at {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoicePaidOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplOrderAcceptedOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplOrderAccepted',
                'value' => 'Notification: Your order #{OrderID} accepted at {CompanyName}',
            ),
            array(
                'setting' => 'tplModulechangepackageOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplModulechangepackage',
                'value' => 'Notification: Your package is change to {packagename} at {CompanyName}',
            ),
            array(
                'setting' => 'tplAdminLoginOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminLogin',
                'value' => 'Notification: {AdminFullName} you have just logged into your account at {CompanyName}',
            ),
            array(
                'setting' => 'tplAdminDomainRegisterOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminDomainRegister',
                'value' => 'Notification: new domain is now registered succesfully with {CompanyName}',
            ),
            array(
                'setting' => 'tplAdminDomainRegisterFailedOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminDomainRegisterFailed',
                'value' => 'Notification: Their is an problem with registering {domain} at {CompanyName}',
            ),
            array(
                'setting' => 'tplAdminDomainRenewalOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminDomainRenewal',
                'value' => 'Notification: {domain} successfully renewed at {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoiceReminder',
                'value' => 'Just a quick reminder. Your invoice is due at {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoiceReminderOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplInvoiceFirstOverdue',
                'value' => 'Oops, your invoice remains unpaid and is now overdue, please login to your account and make a payment to prevent any disruption in your service at {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoiceFirstOverdueOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplInvoiceSecondOverdue',
                'value' => 'Oops, your invoice remains unpaid and is now overdue, please login to your account and make a payment to prevent any disruption in your service at {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoiceSecondOverdueOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplInvoiceThirdOverdue',
                'value' => 'Oops, your invoice remains unpaid and is now overdue, please login to your account and make a payment to prevent any disruption in your service at {CompanyName}',
            ),
            array(
                'setting' => 'tplInvoiceThirdOverdueOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainReminder',
                'value' => 'Your domain : {domain} need to register/renew and will expire very soon  {CompanyName}',
            ),
            array(
                'setting' => 'tplDomainReminderOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainFirstOverdue',
                'value' => 'Your domain : {domain} need to register/renew and will expire very soon {CompanyName}',
            ),
            array(
                'setting' => 'tplDomainFirstOverdueOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainSecondOverdue',
                'value' => 'Your domain : {domain} need to register/renew and will expire very soon {CompanyName}',
            ),
            array(
                'setting' => 'tplDomainSecondOverdueOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainThirdOverdue',
                'value' => 'Your domain : {domain} need to register/renew and will expire very soon {CompanyName}',
            ),
            array(
                'setting' => 'tplDomainThirdOverdueOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplModuleCreate',
                'value' => 'Great News. Your service is now active at {CompanyName}',
            ),
            array(
                'setting' => 'tplModuleCreateOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplModuleSuspend',
                'value' => 'Notification: Your service has been suspended at {CompanyName}. Please login to your account for more information.',
            ),
            array(
                'setting' => 'tplModuleSuspendOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplModuleUnsuspend',
                'value' => 'Great News. Your service at {CompanyName} has now been un-suspended.',
            ),
            array(
                'setting' => 'tplModuleUnsuspendOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplModulePasswordChange',
                'value' => 'Notification: Your password has been changed for your service at {CompanyName}',
            ),
            array(
                'setting' => 'tplModulePasswordChangeOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplCancellationRequest',
                'value' => 'We are sorry to hear that you are leaving {CompanyName], and we sure hope that you come back in the near future. In the meantime we have received your cancellation request. Thank you for using {CompanyName}',
            ),
            array(
                'setting' => 'tplCancellationRequestOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainRegistration',
                'value' => 'Your domain is now registered succesfully with {CompanyName}',
            ),
            array(
                'setting' => 'tplDomainRegistrationOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainTransfer',
                'value' => 'Your domain transfer has been initiated, and should be transferred to {CompanyName} within 7 working days.',
            ),
            array(
                'setting' => 'tplDomainTransferOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainRenewal',
                'value' => 'Your domain has been successfully renewed. Thank you for using {CompanyName}',
            ),
            array(
                'setting' => 'tplDomainRenewalOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainFirstRenewalReminder',
                'value' => 'Your domain name at {CompanyName} is ready to be renewed, please login to your account and renew this as soon as possible to prevent it from expiring. Thank you for choosing {CompanyName}!',
            ),
            array(
                'setting' => 'tplDomainFirstRenewalReminderOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainSecondRenewalReminder',
                'value' => 'Your domain name at {CompanyName} is about to expire, please login to your account and renew this as soon as possible to prevent it from expiring. Thank you for choosing {CompanyName}!',
            ),
            array(
                'setting' => 'tplDomainSecondRenewalReminderOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplDomainThirdRenewalReminder',
                'value' => 'Your domain name at {CompanyName} is about to expire, please login to your account and renew this as soon as possible to prevent it from expiring. Thank you for choosing {CompanyName}!',
            ),
            array(
                'setting' => 'tplDomainThirdRenewalReminderOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplSupportTicketOpen',
                'value' => 'Notification: Your support ticket has been opened at {CompanyName}, and we will get back to you shortly with a response.',
            ),
            array(
                'setting' => 'tplSupportTicketOpenOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplSupportTicketResponse',
                'value' => 'Notification: We have made a response to your support ticket at {CompanyName}. Please login to your account to view our response.',
            ),
            array(
                'setting' => 'tplSupportTicketResponseOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplSupportTicketClose',
                'value' => 'Notification: Your support ticket has now been closed. Thank you for contacting {CompanyName}',
            ),
            array(
                'setting' => 'tplSupportTicketCloseOn',
                'value' => '',
            ),
        ));
    } catch (\Exception $e) {
        logActivity("Unable to insert data to mod_smsmanager_config : {$e->getMessage()}");
    };

    //// Admin Templates ///
    try {
        Capsule::table('mod_smsmanager_config')->insert(array(
            array(
                'setting' => 'tplAdminUnsuspendProduct',
                'value' => 'Notification: Service #{ServiceID} has been unsuspended.',
            ),
            array(
                'setting' => 'tplAdminUnsuspendProductOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminCreateProduct',
                'value' => 'Notification: Service #{ServiceID} has been created successfully.',
            ),
            array(
                'setting' => 'tplAdminCreateProductOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminCheckout',
                'value' => 'Notification: Order #{OrderID} has just been placed.',
            ),
            array(
                'setting' => 'tplAdminCheckoutOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminTicketOpen',
                'value' => 'Notification: Ticket #{TicketID} has just been opened.',
            ),
            array(
                'setting' => 'tplAdminTicketOpenOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminTicketUserReply',
                'value' => 'Notification: Client #{ClientID} has responded to ticket #{TicketID}.',
            ),
            array(
                'setting' => 'tplAdminTicketUserReplyOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminTicketClose',
                'value' => 'Notification: Ticket #{TicketID} has been closed.',
            ),
            array(
                'setting' => 'tplAdminTicketCloseOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplAdminCancellationRequest',
                'value' => 'Notification: Client #{ClientID} has just cancelled service #{ServiceID}.',
            ),
            array(
                'setting' => 'tplAdminCancellationRequestOn',
                'value' => '',
            ),
            array(
                'setting' => 'tplsms_all_alerts',
                'value' => '',
            ),
            array(
                'setting' => 'tplSupportTicketChangeStatus',
                'value' => 'Notification: Ticket #{TicketID} status change to {TicketStatus}',
            ),
            array(
                'setting' => 'tplSupportTicketChangeStatusOn',
                'value' => '',
            ),
        ));
    } catch (\Exception $e) {
        logActivity("Unable to insert admin data to mod_smsmanager_config  : {$e->getMessage()}");
    };
    /// Admin Templates ///
    try {
        Capsule::schema()->create(
            'mod_smsmanager_multilanguages', function ($table) {
            $table->increments('id');
            $table->string('lang');
            $table->string('name');
            $table->text('value');
        }
        );
    } catch (\Exception $e) {
        logActivity("Unable to create mod_smsmanager_multilanguages : {$e->getMessage()}");
    }
    try {
        Capsule::table('mod_smsmanager_config')->insert(array(
            array(
                'setting' => 'active_languages',
                'value' => '',
            ),
        ));
    } catch (\Exception $e) {
        logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
    }
    return array("status" => "success", "description" => "SMS Manager has been activated.");
}

function smsmanager_upgrade($vars)
{
    if (isset($vars['smsmanager']))
        $vars = $vars['smsmanager'];
    $version = $vars['version'];
    $version = str_replace('.', '', $version);
    if ($version < 522) {
        try {
            Capsule::table('mod_smsmanager_config')->insert(array(
                array(
                    'setting' => 'tplOrderAcceptedOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplOrderAccepted',
                    'value' => 'Notification: Your order #{OrderID} accepted at {CompanyName}',
                ),
                array(
                    'setting' => 'tplModulechangepackageOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplModulechangepackage',
                    'value' => 'Notification: Your package is change to {packagename} at {CompanyName}',
                ),
                array(
                    'setting' => 'tplAdminLoginOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminLogin',
                    'value' => 'Notification: {AdminFullName} you have just logged into your account at {CompanyName}',
                ),
                array(
                    'setting' => 'tplAdminDomainRegisterOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminDomainRegister',
                    'value' => 'Notification: new domain is now registered succesfully with {CompanyName}',
                ),
                array(
                    'setting' => 'tplAdminDomainRegisterFailedOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminDomainRegisterFailed',
                    'value' => 'Notification: Their is an problem with registering {domain} at {CompanyName}',
                ),
                array(
                    'setting' => 'tplAdminDomainRenewalOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminDomainRenewal',
                    'value' => 'Notification: {domain} successfully renewed at {CompanyName}',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
        }
    }
    if ($version < 510) {
        try {
            Capsule::table('mod_smsmanager_config')->insert(array(
                array(
                    'setting' => 'requiredmobile',
                    'value' => 'yes',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
        }
    }
    if ($version < 500) {
        try {
            Capsule::table('mod_smsmanager_config')->insert(array(
                array(
                    'setting' => 'tplSupportTicketChangeStatus',
                    'value' => 'Notification: Ticket #{TicketID} status change to {TicketStatus}',
                ),
                array(
                    'setting' => 'tplSupportTicketChangeStatusOn',
                    'value' => '',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
        }
        try {
            Capsule::schema()->create(
                'mod_smsmanager_multilanguages', function ($table) {
                $table->increments('id');
                $table->string('lang');
                $table->string('name');
                $table->text('value');
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_smsmanager_multilanguages : {$e->getMessage()}");
        }
        try {
            Capsule::table('mod_smsmanager_config')->insert(array(
                array(
                    'setting' => 'active_languages',
                    'value' => '',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
        }
    }
    if ($version < 440) {
        try {
            Capsule::schema()->create(
                'mod_smsmanager_multiconfig', function ($table) {
                $table->increments('id');
                $table->string('country');
                $table->text('setting');
                $table->text('value');
            }
            );
        } catch (\Exception $e) {
            logActivity("Unable to create mod_smsmanager_multiconfig : {$e->getMessage()}");
        }
    }
    if ($version < 43) {
        try {
            Capsule::table('mod_smsmanager_config')->insert(array(
                array(
                    'setting' => 'tplsms_all_alerts',
                    'value' => '',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
        }
    }
    if ($version < 30) {
        try {
            Capsule::table('mod_smsmanager_config')->insert(array(
                array(
                    'setting' => 'api_domain',
                    'value' => 'bulksms.vsms.net',
                ),
                array(
                    'setting' => 'adminNotifications',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminUnsuspendProduct',
                    'value' => 'Notification: Service #{ServiceID} has been unsuspended.',
                ),
                array(
                    'setting' => 'tplAdminUnsuspendProductOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminCreateProduct',
                    'value' => 'Notification: Service #{ServiceID} has been created successfully.',
                ),
                array(
                    'setting' => 'tplAdminCreateProductOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminCheckout',
                    'value' => 'Notification: Order #{OrderID} has just been placed.',
                ),
                array(
                    'setting' => 'tplAdminCheckoutOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminTicketOpen',
                    'value' => 'Notification: Ticket #{TicketID} has just been opened.',
                ),
                array(
                    'setting' => 'tplAdminTicketOpenOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminTicketUserReply',
                    'value' => 'Notification: Client #{ClientID} has responded to ticket #{TicketID}.',
                ),
                array(
                    'setting' => 'tplAdminTicketUserReplyOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminTicketClose',
                    'value' => 'Notification: Ticket #{TicketID} has been closed.',
                ),
                array(
                    'setting' => 'tplAdminTicketCloseOn',
                    'value' => '',
                ),
                array(
                    'setting' => 'tplAdminCancellationRequest',
                    'value' => 'Notification: Client #{ClientID} has just cancelled service #{ServiceID}.',
                ),
                array(
                    'setting' => 'tplAdminCancellationRequestOn',
                    'value' => '',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_config  : {$e->getMessage()}");
        }
    }
    if ($version < 520) {
        try {
            Capsule::table('mod_smsmanager_multilanguages')->insert(array(
                array(
                    'lang' => 'english',
                    'name' => 'ClientTwoFactor',
                    'value' => '{FullName}, to contine with your login please enter the following code: {TwoFactor}',
                ),
            ));
        } catch (\Exception $e) {
            logActivity("Unable upgrade mod_smsmanager_multilanguages  : {$e->getMessage()}");
        }
    }
}

function smsmanager_deactivate()
{
    $delete = Capsule::table('tbladdonmodules')->where('module', 'smsmanager')->where('setting', 'nodeletedb')->first();
    if ($delete->value) {
        $fieldid = smsmanager_fieldid();
        if ($fieldid != '') {
            Capsule::table('tblcustomfields')->where('id', $fieldid)->delete();
            Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->delete();
        }
        Capsule::schema()->dropIfExists('mod_smsmanager');
        Capsule::schema()->dropIfExists('mod_smsmanager_multiconfig');
        Capsule::schema()->dropIfExists('mod_smsmanager_config');
        Capsule::schema()->dropIfExists('mod_smsmanager_preferences');
        Capsule::schema()->dropIfExists('mod_smsmanager_confirmations');
        Capsule::schema()->dropIfExists('mod_smsmanager_multilanguages');
        Capsule::schema()->dropIfExists('mod_smsmanager_africasoptout');
    }
    return array("status" => "success", "description" => "SMS Manager has been deactivated.");
}

function smsmanager_output($vars)
{
    global $CONFIG;
    global $aInt;
    $LANG = $vars["_lang"];
    $allow = true;
    $version = $vars['version'];
    $modulename = explode("=", $vars['modulelink']);
    $modulename = $modulename['1'];
    $modulelink = $vars['modulelink'];
    /*
      echo '<ul class="nav nav-tabs admin-tabs" role="tablist">';
      echo '<li class="' . (($_REQUEST['a'] == "") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '" aria-expanded="' . (($_REQUEST['a'] == "") ? 'true' : 'false') . '">' . $LANG['home'] . '</a></li>';
      echo '<li class="' . (($_REQUEST['a'] == "mass") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=mass" aria-expanded="' . (($_REQUEST['a'] == "mass") ? 'true' : 'false' ) . '">' . $LANG['mass'] . '</a></li>';
      echo '<li class="' . (($_REQUEST['a'] == "logs") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=logs" aria-expanded="' . (($_REQUEST['a'] == "logs") ? 'true' : 'false' ) . '">' . $LANG['logs'] . '</a></li>';
      echo '<li class="' . (($_REQUEST['a'] == "management") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=management" aria-expanded="' . (($_REQUEST['a'] == "management") ? 'true' : 'false' ) . '">' . $LANG['management'] . '</a></li>';
      echo '<li class="' . (($_REQUEST['a'] == "config") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=config" aria-expanded="' . (($_REQUEST['a'] == "config") ? 'true' : 'false' ) . '">' . $LANG['config'] . '</a></li>';
      echo '<li class="' . (($_REQUEST['a'] == "admincontact" || $_REQUEST['a'] == "configureadmins") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=admincontact" aria-expanded="' . (($_REQUEST['a'] == "admincontact" || $_REQUEST['a'] == "configureadmins") ? 'true' : 'false') . '">' . $LANG['admincontact'] . '</a></li>';
      if (strtolower($vars['smsgateway']) == "africastalking") {
      echo '<li class="' . (($_REQUEST['a'] == "optout") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=optout" aria-expanded="' . (($_REQUEST['a'] == "optout") ? 'true' : 'false' ) . '">' . $LANG['suboptout'] . '</a></li>';
      if (!Capsule::schema()->hasTable('mod_smsmanager_africasoptout')) {
      try {
      Capsule::schema()->create(
      'mod_smsmanager_africasoptout', function ($table) {
      $table->increments('id');
      $table->string('phonenumber');
      $table->string('senderid');
      }
      );
      } catch (\Exception $e) {
      logActivity("Unable to create mod_smsmanager_africasoptout : {$e->getMessage()}");
      }
      }
      }
      echo '<li class="' . (($_REQUEST['a'] == "addon") ? 'active' : '') . '"><a class="tab-top" href="addonmodules.php?module=' . $modulename . '&a=addon" aria-expanded="' . (($_REQUEST['a'] == "addon") ? 'true' : 'false' ) . '">About Addon</a></li>';
      echo '</ul>';
      echo '<div class="tab-content admin-tabs">
      <div class="tab-pane active" id="tab1">';

     *
     */
    require_once __DIR__ . '/includes/menu.php';
    if (isset($_REQUEST['a']) && $_REQUEST['a'] == 'addon') {
        $vchk = '';
        // if (!isset($_SESSION['SMMcheckversion'])) {
        //     $sversion = curlCall('https://www.whmcsservices.com/members/modules/addons/modulesmanager/versioncheck.php?check=smsmanager', '');
        //     $lversion = smsmanager_config();
        //     $sversion = json_decode($sversion, true);
        //     $sversi = str_replace('.', '', $sversion['version']);
        //     $lversi = str_replace('.', '', $lversion['version']);
        //     if ($lversi >= $sversi) {
        //         $vchk = 'Your addon version is update with last version<br><span class="label label-success">Module Version : ' . $sversion['version'] . '</span>';
        //     } else {
        //         $vchk = 'Your addon version isn\'t up to date <span class="label inactive">Local Module version : ' . $lversion['version'] . '</span>, Please update addon to <br><span class="label label-success">Version : ' . $sversion['version'] . '</span>';
        //     }
        //     $_SESSION['SMMcheckversion'] = $vchk;
        // }
        $vchk = 'Your addon version is update with last version<br><span class="label label-success">Module Version : 1.0.0</span>';
        echo '<div style="background-color: whitesmoke;">
<style>.orange {
    color:#946780;
}
.subtitle {
    font-size: 16px;
    font-weight: 700;
}

.padding20 {
    padding:20px;
}
</style>
	<div class="row text-center">
    	<h2><strong>Jisort</strong> - Help Center</h2>
		    <div class="col-md-3 ">
		<span style="font-size: 55px" class="fa fa-cube orange"></span>
		<h2 class="subtitle">Module Version</h2>
		<p>' . ((isset($_SESSION['SMMcheckversion'])) ? $_SESSION['SMMcheckversion'] : $vchk) . '</p>
		</div>
    		<div class="col-md-3">
    		<span style="font-size: 55px" class="fa fa-refresh orange"></span>
    		<h2 class="subtitle">Update Jisort</h2>
    		<p>Coming Soon</p>
    		</div>
    		<div class="col-md-3">
    		<span style="font-size: 55px" class="fa fa-graduation-cap orange"></span>
    		<h2 class="subtitle">Knowledgebase</h2>
    		<p>Helpful article and guide on how to use this module<br><a href="https://www.jisort.com/" target="_blank">Knowledgebase Base</a></p>
    		</div>
    		<div class="col-md-3">
    		<span style="font-size: 55px" class="fa fa-shopping-bag orange"></span>
    		<h2 class="subtitle">WHMCS Markeplace</h2>
    		<p>Checkout out own Markerplace
                <a target="_blank" href="https://www.jisort.com/">Market place link</a>
                </p>
    		</div>

	</div>
	<div class="row text-center">

		    <div class="col-md-3">
		<span style="font-size: 55px" class="fa fa-plus-circle orange"></span>
		<h2 class="subtitle">Feature Request</h2>
		<p>Share ideas, discuss and vote on requests from other users in community<br>
                <a target="_blank" href="https://www.jisort.com/">Open Request</a>
</p>
		</div>
    		<div class="col-md-3">
    		<span style="font-size: 55px" class="fa fa-tasks orange"></span>
    		<h2 class="subtitle">Support / Bugs Report</h2>
    		<p>Conact us of support or bugs<br>
                <a target="_blank" href="https://www.jisort.com/">Open Ticket</a>
                </p>
    		</div>
    		<div class="col-md-3">
    		<span style="font-size: 55px" class="fa  fa-heart orange"></span>
    		<h2 class="subtitle">Vote My Addons</h2>
    		<p>Please vote the Addon at WHMCS Marketplace. Your feedback is essential for us!<br>
                  <a target="_blank" href="https://www.jisort.com/">Rate now!</a>
</p>
    		</div>
    		<div class="col-md-3">
    		<span style="font-size: 55px" class="fa fa-envelope-o orange"></span>
    		<h2 class="subtitle">Contact Us</h2>
    		<p>Need any custom developer fro your WHMCS?<br>
                <a target="_blank" href="https://www.jisort.com/">Open Ticket</a>
                </p>
    		</div>

	</div>
</div>';
        echo '<br><p align="center"><button type="button" class="btn btn-danger" onclick="location.href=\'' . $modulelink . '\'">' . $LANG['back'] . '</button></p>';
        /*
          echo '</div></div><script type="text/javascript" src="../assets/js/bootstrap-tabdrop.js"></script>
          <link rel="stylesheet" type="text/css" href="../assets/css/tabdrop.css" /><script>$(document).ready(function(){
          $(".admin-tabs").tabdrop();
          });</script>';
         *
         */
        return;
    }

    $SETING = array();
    $result = Capsule::table('mod_smsmanager_config')->get();
    foreach ($result as $data) {
        $setting = $data->setting;
        $value = $data->value;
        $SETING["{$setting}"] = "{$value}";
    }
    $smsUsername = $SETING['api_username'];
    $smsPassword = base64_decode($SETING['api_password']);
    $smsSender = $SETING['api_sender'];
    $smsGateway = $vars['smsgateway'];
    $apiID = $SETING['api_id'];
    $apiDomain = $SETING['api_domain'];
    $tplClientLogin = $SETING['tplClientLogin'];
    $tplClientPasswordChange = $SETING['tplClientPasswordChange'];
    $tplClientRegistration = $SETING['tplClientRegistration'];
    $tplAffiliateActivation = $SETING['tplAffiliateActivation'];
    $tplClientTwoFactor = $SETING['tplClientTwoFactor'];
    if (isset($_REQUEST['a']) && $_REQUEST['a'] == 'optout') {
        if (isset($_REQUEST['delid']) && $_REQUEST['delid'] != '') {
            Capsule::table('mod_smsmanager_africasoptout')->where('id', $_REQUEST['delid'])->delete();
            echo infoBox($aInt->lang('global', 'changesuccess'), $aInt->lang('global', 'changesuccessdeleted'));
        }
        $aInt->sortableTableInit("id");
        $numrows = Capsule::table('mod_smsmanager_africasoptout')->count();
        $limit = 50;
        $page = $_REQUEST['page'];
        if (empty($page) || !is_numeric($page))
            $page = 0;
        else
            $page = $page;
        $page = "" . $page . "";
        $records = $page * $limit;
        $result = Capsule::table('mod_smsmanager_africasoptout')->skip($records)->take($limit)->get();
        foreach ($result AS $data) {
            $tabledata[] = array(
                '<center>' . $data->id . '</center>',
                '<center>' . $data->senderid . '</center>',
                '<center>' . $data->phonenumber . '</center>',
                '<center><a href="addonmodules.php?module=smsmanager&a=optout&delid=' . $data->id . '" class="btn btn-danger" onclick="return confirm(\'' . $LANG['areyousure'] . '\')">' . $LANG['delete'] . '</a></center>',
            );
        }
        echo $aInt->sortableTable(array($aInt->lang("fields", "id"), $LANG['senderid'], $aInt->lang("fields", "phonenumber"), ''), $tabledata);
        /*
          echo '</div></div><script type="text/javascript" src="../assets/js/bootstrap-tabdrop.js"></script>
          <link rel="stylesheet" type="text/css" href="../assets/css/tabdrop.css" /><script>$(document).ready(function(){
          $(".admin-tabs").tabdrop();
          });</script>';
         *
         */
        return '';
    }
    if ($_REQUEST['a'] == "mass") {
        if (isset($_REQUEST['clientstype']) && isset($_REQUEST['send'])) {
            @set_time_limit(0);
            $tbldata = Capsule::table('tblclients');
            $fieldid = smsmanager_fieldid();
            if (isset($_REQUEST['clientstype']) && $_REQUEST['clientstype'] == 'selected') {
                $tbldata->whereIn('tblclients.id', $_REQUEST['userid']);
            }
            if (isset($_REQUEST['countrystatus']) && $_REQUEST['countrystatus'] == 'selected') {
                $tbldata->whereIn('tblclients.country', $_REQUEST['country']);
            }
            if (isset($_REQUEST['smsstatus']) && $_REQUEST['smsstatus'] == 'selected') {
                $excldq = Capsule::table('mod_smsmanager_preferences')->where('value', 'LIKE', '%off%')->select('userid')->get();
                if (count($excldq) > 0) {
                    $exarray = array();
                    foreach ($excldq as $value) {
                        $exarray[] = $value->userid;
                    }
                    $tbldata->whereNotIn('tblclients.id', $exarray);
                }
            }
            if ((isset($_REQUEST['prodstatus']) && $_REQUEST['prodstatus'] == 'selected') || (isset($_REQUEST['pid']) && count($_REQUEST['pid']) > 0)) {
                $tbldata->join('tblhosting', 'tblhosting.userid', '=', 'tblclients.id');
            }
            if (isset($_REQUEST['pid']) && count($_REQUEST['pid']) > 0) {
                $tbldata->whereIn('tblhosting.packageid', $_REQUEST['pid']);
            }
            if (isset($_REQUEST['prodstatus']) && $_REQUEST['prodstatus'] == 'selected') {
                $tbldata->whereIn('tblhosting.domainstatus', $_REQUEST['sprodstatus']);
            }
            $tbldata->select('tblclients.id as cuid', 'tblclients.phonenumber as phonenumber');
            $tblc = $tbldata->get();
            if (count($tblc) > 0) {
                $message = $_REQUEST['message'];
                foreach ($tblc as $value) {
                    $fieldid = smsmanager_fieldid();
                    if (!empty($fieldid)) {
                        $dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $value->cuid)->value('value');
                        $to = smsmanager_toTrim($dataa);
                    } else {
                        $to = smsmanager_toTrim($value->phonenumber); 
                    }
                    if (empty($to)) {
                        continue;
                    }
                    $response = smsmanager_sendSMS($to, $message, array());
                }
                $response = 'MassSent';
                redir('module=smsmanager&a=mass&success=true&response=' . urlencode($response));
                exit;
            }
        }
        $countires = new \WHMCS\Utility\Country;
        $countryoptions = '';
        foreach ($countires->getCountries() as $country => $data) {
            $countryoptions .= '<option value="' . $country . '">' . $data['name'] . '</option>';
        }
        echo '<script>function getClientSearchPostUrl() { return "ordersadd.php"; }</script>
              <script type="text/javascript" src="../modules/addons/smsmanager/assets/mass.js"></script>';
        echo '<center><div class="panel panel-success" style="width:80%"><div class="panel-heading">' . $LANG['manualsend'] . '</div><div class="panel-body">';
        if (isset($_REQUEST['success'])) {
            echo '<center><strong><font color="#FF0000">' . $LANG['smssent'] . '</font></strong><br>' . $LANG['sendmanuallymassd'] . '<br></center>';
        }
        $selectp = Capsule::table('tblproducts')->select('id', 'name')->get();
        $selectv = '<label style="margin-left: 10px;" class="checkbox-inline"><input id="checkall" type="checkbox" value=""><b>' . $LANG['checkallmass'] . '</b></label><br>';
        foreach ($selectp as $key) {
            $selectv .= '<label style="margin-left: 10px;" class="checkbox-inline"><input name="pid[]" class="check" type="checkbox" value="' . $key->id . '">' . $key->name . '</label>';
        }
        $aInt->requiredFiles(array("clientfunctions"));
        echo '<form method="POST" id="sform" action="addonmodules.php?module=smsmanager&a=mass&send=true">'
        . '<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
    <tbody>
        <tr>
            <td class="fieldlabel">' . $LANG['clientmass'] . '</td>
            <td class="fieldarea">
 <label class="radio-inline"><input type="radio" name="clientstype" value="all" checked="checked">' . $LANG['allmass'] . '</label>
<label class="radio-inline"><input type="radio" name="clientstype" value="selected">' . $LANG['selectedmass'] . '</label>
                ' . $aInt->clientsDropDown("") . '</td>
        </tr>
        <tr>
            <td class="fieldlabel">' . $LANG['smssmass'] . '</td>
            <td class="fieldarea">
 <label class="radio-inline"><input type="radio" name="smsstatus" value="all" checked="checked">' . $LANG['allmass'] . '</label>
<label class="radio-inline"><input type="radio" name="smsstatus" value="selected">' . $LANG['allowedmass'] . '</label>
</td>
        </tr>
        <tr>
            <td class="fieldlabel">' . $LANG['countrymass'] . '</td>
            <td class="fieldarea">
 <label class="radio-inline"><input type="radio" name="countrystatus" value="all" checked="checked">' . $LANG['allmass'] . '</label>
<label class="radio-inline"><input type="radio" name="countrystatus" value="selected">' . $LANG['scountrymass'] . '</label><br>
<select name="country[]" id="country" class="form-control" tabindex="13" multiple="multiple">' . $countryoptions . '</select>
</td>
        </tr>
        <tr>
            <td class="fieldlabel">' . $LANG['psmass'] . '</td>
            <td class="fieldarea">' . $selectv . '</td>
        </tr>
<tr>
            <td class="fieldlabel">' . $LANG['pssmass'] . '</td>
            <td class="fieldarea">
 <label class="radio-inline"><input type="radio" name="prodstatus" value="all" checked="checked">' . $LANG['allmass'] . '</label>
<label class="radio-inline"><input type="radio" name="prodstatus" value="selected">' . $LANG['selectedmass'] . '</label><br>
<select name="sprodstatus[]" multiple="multiple" class="form-control select-inline" id="sprodstatus"><option value="Pending">Pending</option><option value="Active">Active</option><option value="Completed">Completed</option><option value="Suspended">Suspended</option><option value="Terminated">Terminated</option><option value="Cancelled">Cancelled</option><option value="Fraud">Fraud</option></select>
</td>
        </tr>
<tr>
            <td class="fieldlabel">' . $LANG['message'] . '</td>
            <td class="fieldarea">
<textarea rows="6" id="textbox1" required="required" class="form-control" name="message" placeholder="Message" cols="25"></textarea><small><span id=\'remaining\'>160</span> ' . $LANG['charactersremaining'] . ' <span id=\'lmessages\'>1</span> ' . $LANG['mscount'] . '</small></td>
        </tr>
    </tbody>
</table><center>
		<script src="../modules/addons/smsmanager/assets/scripts.js"></script>
<script>
$("#selectUserid").attr("name","userid[]");
$("#checkall").click(function () {
    $(".check").prop("checked", $(this).prop("checked"));
});
</script><input type="submit" class="btn btn-success" value="' . $LANG['send'] . '" name="' . $LANG['send'] . '"></center></form>';
        echo '</div></div></center>';
        /*
          echo '</div></div><script type="text/javascript" src="../assets/js/bootstrap-tabdrop.js"></script>
          <link rel="stylesheet" type="text/css" href="../assets/css/tabdrop.css" /><script>$(document).ready(function(){
          $(".admin-tabs").tabdrop();
          });</script>';
         *
         */
        return;
    }
    if ($_REQUEST['a'] == "config") {
        $aInt->requiredFiles(array("clientfunctions"));

        if (isset($_REQUEST['multigateway'])) {
            if (isset($_REQUEST['addnew'])) {
                if (isset($_REQUEST['savemulti'])) {
                    $seti = array();
                    foreach ($_POST as $key => $value) {
                        if ($key == 'token' || $key == 'gateway' || $key == 'country')
                            continue;
                        $seti[$key] = $value;
                    }
                    Capsule::table('mod_smsmanager_multiconfig')->insert([
                        'setting' => 'multi_' . $_POST['gateway'],
                        'country' => $_POST['country'],
                        'value' => serialize($seti),
                    ]);
                    redir('module=smsmanager&a=config&multigateway=true', 'addonmodules.php');
                    exit;
                }

                $glist = smsmanager_config();
                $slist = $glist['fields']['smsgateway']['Options'];
                $slist = explode(',', $slist);
                $gatewayselect = '<select class="form-control select-inline" name="gateway">';
                foreach ($slist as $value) {
                    $gatewayselect .= '<option value="' . $value . '">' . $value . '</option>';
                }
                $gatewayselect .= '</select>';
                $additional .= '<tr><td class="fieldlabel">' . $LANG['apiid'] . '</td><td class="fieldarea"><input type="text" name="apiid" size="40" value=""><b>clickatell (communicator / central)</b> - ' . $LANG['apiiddesc'] . '</td></tr>';
                $additional .= '<tr><td class="fieldlabel">' . $LANG['apidomain'] . '</td><td class="fieldarea"><input type="text" name="apidomain" size="40" value=""><b>bulksms</b> -  ' . $LANG['apidomaindesc'] . '</td></tr>';
                $langusername = $LANG['username'];
                $langusernamedesc = $LANG['usernamedesc'];
                $langpassword = $LANG['password'];
                $langpassworddesc = $LANG['passworddesc'];

                echo '<form method="post" action="' . $modulelink . '&a=config&multigateway=true&addnew=true">
                    <input type="hidden" name="savemulti" value="1">
				<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td width="400" class="fieldlabel">' . $LANG['gateway'] . '</td><td class="fieldarea">' . $gatewayselect . '</td></tr>';
                echo '<tr><td class="fieldlabel">' . $LANG['country'] . ' : </td>
                                    <td class="fieldarea">
                                        ' . getCountriesDropDown('', "", 13) . '
                                    </td></tr>';
                echo '	<tr><td class="fieldlabel">' . $langusername . '</td><td class="fieldarea"><input type="text" name="apiusername" size="40" value=""> ' . $langusernamedesc . '</td></tr>';
                echo '	<tr><td class="fieldlabel">' . $langpassword . '</td><td class="fieldarea"><input type="password" name="apipassword" size="40" value=""> ' . $langpassworddesc . '</td></tr>';
                echo '	<tr><td class="fieldlabel">' . $LANG['senderid'] . '</td><td class="fieldarea"><input type="text" name="apisender" size="40" value=""> ' . $LANG['senderdesc'] . '</td></tr>' . $additional;
                echo '</table>
				<p align="center"><input type="submit" class="btn btn-success" value="' . $LANG['savechanges'] . '" class="button" > <a href="addonmodules.php?module=smsmanager&a=config&multigateway=true" class="btn btn-warning">' . $LANG['back'] . '</a></p>
			</form>';
                /*
                  echo '</div></div><script type="text/javascript" src="../assets/js/bootstrap-tabdrop.js"></script>
                  <link rel="stylesheet" type="text/css" href="../assets/css/tabdrop.css" /><script>$(document).ready(function(){
                  $(".admin-tabs").tabdrop();
                  });</script>';
                 *
                 */
                return '';
            }
            if (isset($_REQUEST['editid'])) {
                $item = Capsule::table('mod_smsmanager_multiconfig')->where('id', $_REQUEST['editid'])->first();
                if (count($item) <= 0) {
                    redir('module=smsmanager&a=config&multigateway=true', 'addonmodules.php');
                    exit;
                }
                if (isset($_REQUEST['savemulti'])) {
                    $seti = array();
                    foreach ($_POST as $key => $value) {
                        if ($key == 'token' || $key == 'gateway' || $key == 'country')
                            continue;
                        $seti[$key] = $value;
                    }
                    Capsule::table('mod_smsmanager_multiconfig')->where('id', $_REQUEST['editid'])->update([
                        'setting' => 'multi_' . $_POST['gateway'],
                        'country' => $_POST['country'],
                        'value' => serialize($seti),
                    ]);
                    redir('module=smsmanager&a=config&multigateway=true', 'addonmodules.php');
                    exit;
                }
                $item->setting = str_replace('multi_', '', $item->setting);
                $glist = smsmanager_config();
                $slist = $glist['fields']['smsgateway']['Options'];
                $slist = explode(',', $slist);
                $gatewayselect = '<select class="form-control select-inline" name="gateway">';
                foreach ($slist as $value) {
                    $gatewayselect .= '<option value="' . $value . '"  ' . (($item->setting == $value) ? 'selected="selected"' : '') . '>' . $value . '</option>';
                }
                $gatewayselect .= '</select>';
                $settings_item = unserialize($item->value);
                $additional .= '<tr><td class="fieldlabel">' . $LANG['apiid'] . '</td><td class="fieldarea"><input type="text" name="apiid" size="40" value="' . $settings_item['apiid'] . '"><b>clickatell (communicator / central)</b> - ' . $LANG['apiiddesc'] . '</td></tr>';
                $additional .= '<tr><td class="fieldlabel">' . $LANG['apidomain'] . '</td><td class="fieldarea"><input type="text" name="apidomain" size="40" value="' . $settings_item['apidomain'] . '"><b>bulksms</b> -  ' . $LANG['apidomaindesc'] . '</td></tr>';
                $langusername = $LANG['username'];
                $langusernamedesc = $LANG['usernamedesc'];
                $langpassword = $LANG['password'];
                $langpassworddesc = $LANG['passworddesc'];

                echo '<form method="post" action="' . $modulelink . '&a=config&multigateway=true&&editid=' . $item->id . '">
                    <input type="hidden" name="savemulti" value="1">
				<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td width="400" class="fieldlabel">' . $LANG['gateway'] . '</td><td class="fieldarea">' . $gatewayselect . '</td></tr>';
                echo '<tr><td class="fieldlabel">' . $LANG['country'] . ' : </td>
                                    <td class="fieldarea">
                                        ' . getCountriesDropDown($item->country, "", 13) . '
                                    </td></tr>';
                echo '	<tr><td class="fieldlabel">' . $langusername . '</td><td class="fieldarea"><input type="text" name="apiusername" size="40" value="' . $settings_item['apiusername'] . '"> ' . $langusernamedesc . '</td></tr>';
                echo '	<tr><td class="fieldlabel">' . $langpassword . '</td><td class="fieldarea"><input type="password" name="apipassword" size="40" value="' . $settings_item['apipassword'] . '"> ' . $langpassworddesc . '</td></tr>';
                echo '	<tr><td class="fieldlabel">' . $LANG['senderid'] . '</td><td class="fieldarea"><input type="text" name="apisender" size="40" value="' . $settings_item['apisender'] . '"> ' . $LANG['senderdesc'] . '</td></tr>' . $additional;
                echo '</table>
				<p align="center"><input type="submit" class="btn btn-success" value="' . $LANG['savechanges'] . '" class="button" > <a href="addonmodules.php?module=smsmanager&a=config&multigateway=true" class="btn btn-warning">' . $LANG['back'] . '</a></p>
			</form>';
                /*
                  echo '</div></div><script type="text/javascript" src="../assets/js/bootstrap-tabdrop.js"></script>
                  <link rel="stylesheet" type="text/css" href="../assets/css/tabdrop.css" /><script>$(document).ready(function(){
                  $(".admin-tabs").tabdrop();
                  });</script>';
                 *
                 */
                return '';
            }

            if (isset($_REQUEST['did'])) {
                Capsule::table('mod_smsmanager_multiconfig')->where('id', $_REQUEST['did'])->delete();
                echo infoBox($aInt->lang('global', 'changesuccess'), $aInt->lang('global', 'changesuccessdeleted'));
            }
            $aInt->sortableTableInit('nopagination', 'DESC');
            $countries = Capsule::table('mod_smsmanager_multiconfig')->orderBy('id', 'desc')->get();
            $numrows = count($countries);
            $cc = new Country;
            $aInt->deleteJSConfirm("doDelete", "global", "deleteconfirm", 'addonmodules.php?module=smsmanager&a=config&multigateway=true&did=');
            echo '<center><a href="addonmodules.php?module=smsmanager&a=config&multigateway=true&addnew=1" class="btn btn-primary">' . $LANG['addnew'] . '</a></center>';
            foreach ($countries as $value) {
                $gname = str_replace('multi_', '', $value->setting);
                $cname = $cc->getName($value->country);
                $tabledata[] = array(
                    '<center>' . $gname . '</center>',
                    '<center>' . $cname . '</center>',
                    '<center><a href="addonmodules.php?module=smsmanager&a=config&multigateway=true&editid=' . $value->id . '" class="btn btn-success">' . $LANG['edit'] . '</a></center>',
                    '<center><a onclick="doDelete(\'' . $value->id . '\'); return false;" class="btn btn-danger">' . $LANG['delete'] . '</a></center>'
                );
            }
            echo $aInt->sortableTable(array($LANG['gateway'], $LANG['country'], $LANG['edit'], $LANG['delete']), $tabledata);
            echo '<center><a href="addonmodules.php?module=smsmanager&a=config" class="btn btn-warning">' . $LANG['back'] . '</a></center>';
            /*
              echo '</div></div><script type="text/javascript" src="../assets/js/bootstrap-tabdrop.js"></script>
              <link rel="stylesheet" type="text/css" href="../assets/css/tabdrop.css" /><script>$(document).ready(function(){
              $(".admin-tabs").tabdrop();
              });</script>';
             *
             */
            return '';
        }
        if (isset($_REQUEST['save']) && $_REQUEST['save']) {
            if (strtolower($smsGateway) == "clickatell (communicator / central)") {
                Capsule::table('mod_smsmanager_config')->where('setting', 'api_id')->update([
                    'value' => $_REQUEST['apiid']
                ]);
            }
            if (strtolower($smsGateway) == "smsbao") {
                $citem = Capsule::table('mod_smsmanager_config')->where('setting', 'signature')->count();
                if ($citem > 0) {
                    Capsule::table('mod_smsmanager_config')->where('setting', 'signature')->update([
                        'value' => $_REQUEST['signature']
                    ]);
                } else {
                    Capsule::table('mod_smsmanager_config')->insert([
                        'setting' => 'signature',
                        'value' => $_REQUEST['signature']
                    ]);
                }
            }
            Capsule::table('mod_smsmanager_config')->where('setting', 'api_domain')->update([
                'value' => $_REQUEST['apidomain']
            ]);
            Capsule::table('mod_smsmanager_config')->where('setting', 'api_username')->update([
                'value' => $_REQUEST['apiusername']
            ]);
            Capsule::table('mod_smsmanager_config')->where('setting', 'api_password')->update([
                'value' => base64_encode($_REQUEST['apipassword'])
            ]);
            Capsule::table('mod_smsmanager_config')->where('setting', 'api_sender')->update([
                'value' => $_REQUEST['apisender']
            ]);
            Capsule::table('mod_smsmanager_config')->where('setting', 'customfieldid')->update([
                'value' => $_REQUEST['customfieldid']
            ]);
            if (isset($_REQUEST['active_languages'])) {
                $savedarray = $_REQUEST['active_languages'];
                if (count($_REQUEST['active_languages']) > 0) {
                    if (in_array('none', $_REQUEST['active_languages'])) {
                        $savedarray = array();
                    }
                }
                Capsule::table('mod_smsmanager_config')->where('setting', 'active_languages')->update([
                    'value' => implode(',', $savedarray)
                ]);
            }
            if (isset($_REQUEST['bulksmsroutes']) && $_REQUEST['bulksmsroutes'] != '') {
                Capsule::table('mod_smsmanager_config')->where('setting', 'bulksmsroutes')->update([
                    'value' => $_REQUEST['bulksmsroutes']
                ]);
            }
            if (isset($_REQUEST['requiredmobile'])) {
                Capsule::table('mod_smsmanager_config')->where('setting', 'requiredmobile')->update([
                    'value' => 'yes'
                ]);
            } else {
                Capsule::table('mod_smsmanager_config')->where('setting', 'requiredmobile')->update([
                    'value' => 'no'
                ]);
            }
            $notfrees = Capsule::table('mod_smsmanager_config')->where('setting', 'notfreeinvoice')->count();
            if ($notfrees <= 0) {
                Capsule::table('mod_smsmanager_config')->insert([
                    'setting' => 'notfreeinvoice',
                    'value' => ''
                ]);
            }
            if (isset($_REQUEST['specialtime'])) {
                Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->delete();
                Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->delete();
                Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->delete();
                Capsule::table('mod_smsmanager_config')->insert([
                    'setting' => 'specialtime',
                    'value' => 'on'
                ]);
                Capsule::table('mod_smsmanager_config')->insert([
                    'setting' => 'sendmin',
                    'value' => $_REQUEST['sendmin']
                ]);
                Capsule::table('mod_smsmanager_config')->insert([
                    'setting' => 'sendhr',
                    'value' => $_REQUEST['sendhr']
                ]);
            } else {
                Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->delete();
                Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->delete();
                Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->delete();
            }
            if (isset($_REQUEST['notfreeinvoice'])) {
                Capsule::table('mod_smsmanager_config')->where('setting', 'notfreeinvoice')->update([
                    'value' => 'yes'
                ]);
            } else {
                Capsule::table('mod_smsmanager_config')->where('setting', 'notfreeinvoice')->update([
                    'value' => ''
                ]);
            }
            redir("module=smsmanager&a=config&success");
            exit;
        }
        $chrq = Capsule::table('mod_smsmanager_config')->where('setting', 'requiredmobile')->count();
        if ($chrq <= 0) {
            Capsule::table('mod_smsmanager_config')->insert([
                'setting' => 'requiredmobile',
                'value' => 'yes',
            ]);
        }
        $data = Capsule::table('mod_smsmanager_config')->where('setting', 'customfieldid')->first();
        $ropdown = '';
        $rdata = Capsule::table('mod_smsmanager_config')->where('setting', 'requiredmobile')->first();
        $idata = Capsule::table('mod_smsmanager_config')->where('setting', 'notfreeinvoice')->first();
        $resu = Capsule::table('tblcustomfields')->where('type', 'client')->select('fieldname', 'id')->get();
        $dropdown .= '<option value="" ' . (($data->value == '') ? 'selected="selected"' : '') . '>' . $LANG['usewhmcsphonenumber'] . '</option>';
        foreach ($resu as $result) {
            if ($data->value == $result->id)
                $select = " selected";
            else
                $select = "";
            $dropdown .= '<option value="' . $result->id . '"' . $select . '>' . $result->fieldname . '</option>';
        }

        if (strtolower($smsGateway) == "clickatell (communicator / central)")
            $additional = '<tr><td class="fieldlabel">' . $LANG['apiid'] . '</td><td class="fieldarea"><input type="text" name="apiid" size="40" value="' . $apiID . '"> ' . $LANG['apiiddesc'] . '</td></tr>';

        if (strtolower($smsGateway) == "bulksms")
            $additional = '<tr><td class="fieldlabel">' . $LANG['apidomain'] . '</td><td class="fieldarea"><input type="text" name="apidomain" size="40" value="' . $apiDomain . '"> ' . $LANG['apidomaindesc'] . '</td></tr>';

        if (strtolower($smsGateway) == "twilio") {
            $langusername = $LANG['accountsid'];
            $langusernamedesc = $LANG['accountsiddesc'];
            $langpassword = $LANG['authtoken'];
            $langpassworddesc = $LANG['authtokendesc'];
        } elseif (strtolower($smsGateway) == "clickatell (platform)") {
            $langpassworddesc = $LANG['apikeydesc'];
            $langpassword = $LANG['apikey'];
        } elseif (strtolower($smsGateway) == "plivo") {
            $langusername = $LANG['plivoAUTHID'];
            $langusernamedesc = $LANG['plivoAUTHIDdesc'];
            $langpassword = $LANG['plivoTOKEN'];
            $langpassworddesc = $LANG['authtokendesc'];
        } elseif (strtolower($smsGateway) == "directcybertech") {
            $langusername = $LANG['authkey'];
        } elseif (strtolower($smsGateway) == "messagebird") {
            $langusername = $LANG['messagebirdauthkey'];
        } else {
            $langusername = $LANG['username'];
            $langusernamedesc = $LANG['usernamedesc'];
            $langpassword = $LANG['password'];
            $langpassworddesc = $LANG['passworddesc'];
        }
        echo '<form method="post" action="' . $modulelink . '&a=config&save=true">
				<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td width="400" class="fieldlabel">' . $LANG['gateway'] . '</td><td class="fieldarea">' . $vars['smsgateway'] . '</td></tr>' . $additional;
        if (strtolower($smsGateway) != "clickatell (platform)") {
            echo '	<tr><td class="fieldlabel">' . $langusername . '</td><td class="fieldarea"><input type="text" name="apiusername" size="40" value="' . $smsUsername . '"> ' . $langusernamedesc . '</td></tr>';
        }
        if (strtolower($smsGateway) != "directcybertech" && $smsGateway != "MessageBird") {
            echo '	<tr><td class="fieldlabel">' . $langpassword . '</td><td class="fieldarea"><input type="password" name="apipassword" size="40" value="' . $smsPassword . '"> ' . $langpassworddesc . '</td></tr>';
        } else if ($smsGateway != "MessageBird") {
            echo '	<tr><td class="fieldlabel">' . $LANG['route'] . '</td><td class="fieldarea"><select name="apipassword"><option value="1" ' . (($smsPassword == '1') ? 'selected="selected"' : '') . '>promotional</option><option value="4" ' . (($smsPassword == '4') ? 'selected="selected"' : '') . '>transactional</option></select> ' . $LANG['routedesc'] . '</td></tr>';
        }
        if (strtolower($smsGateway) != "clickatell (platform)" && strtolower($smsGateway) != "smsbao") {
            echo '	<tr><td class="fieldlabel">' . $LANG['senderid'] . '</td><td class="fieldarea"><input type="text" name="apisender" size="40" value="' . $smsSender . '"> ' . $LANG['senderdesc'] . '</td></tr>';
        }
        if (strtolower($smsGateway) == "smsbao") {
            $signature = Capsule::table('mod_smsmanager_config')->where('setting', 'signature')->first();
            echo '	<tr><td class="fieldlabel">' . $aInt->lang('mergefields', 'signature') . '</td><td class="fieldarea"><input type="text" name="signature" size="40" value="' . $signature->value . '"> ' . $aInt->lang('mergefields', 'signature') . '</td></tr>';
        }
        if (strtolower($smsGateway) == "bulksms" || strtolower($smsGateway) == "bulksms unicode") {
            $chrsq = Capsule::table('mod_smsmanager_config')->where('setting', 'bulksmsroutes')->count();
            if ($chrsq <= 0) {
                Capsule::table('mod_smsmanager_config')->insert([
                    'setting' => 'bulksmsroutes',
                    'value' => '2',
                ]);
            }
            $bulkroutes = Capsule::table('mod_smsmanager_config')->where('setting', 'bulksmsroutes')->first();
            $pvalues = array(1, 2, 3);
            $buselect = '<select name="bulksmsroutes">';
            foreach ($pvalues as $ke => $pvl) {
                if ($pvl == $bulkroutes->value) {
                    $buselect .= '<option value="' . $pvl . '" selected="selected">' . $pvl . '</option>';
                } else {
                    $buselect .= '<option value="' . $pvl . '">' . $pvl . '</option>';
                }
            }
            $buselect .= '</select>';
            echo '	<tr><td class="fieldlabel">' . $LANG['routes'] . '</td><td class="fieldarea">' . $buselect . ' ' . $LANG['routesdesc'] . '</td></tr>';
        }

        if (in_array(strtolower($smsGateway), array('bulksms', 'bulksms unicode', 'messagebird', 'sveve.no'))) {
            $sendmin = Capsule::table('mod_smsmanager_config')->where('setting', 'sendmin')->first();
            $sendhr = Capsule::table('mod_smsmanager_config')->where('setting', 'sendhr')->first();
            $minselect = '<select name="sendmin">';
            for ($i = 0; $i <= 59; $i++) {
                $ci = $i;
                if ($i < 10) {
                    $ci = '0' . $i;
                }
                $minselect .= '<option value="' . $ci . '" ' . (($sendmin->value == $ci) ? 'selected="selected"' : '') . '>' . $ci . '</option>';
            }
            $minselect .= '</select>';
            $hoselect = '<select name="sendhr">';
            for ($i = 0; $i <= 23; $i++) {
                $ci = $i;
                if ($i < 10) {
                    $ci = '0' . $i;
                }
                $hoselect .= '<option value="' . $ci . '"  ' . (($sendhr->value == $ci) ? 'selected="selected"' : '') . '>' . $ci . '</option>';
            }
            $hoselect .= '</select>';
            $tspecific = Capsule::table('mod_smsmanager_config')->where('setting', 'specialtime')->first();
            echo '	<tr><td class="fieldlabel">Enable sent at specific time</td><td class="fieldarea"><input type="checkbox" name="specialtime" ' . (($tspecific->value != '') ? 'checked="checked"' : '') . ' value="on"></td></tr>';
            echo '	<tr><td class="fieldlabel">Specific time</td><td class="fieldarea">Hour : ' . $hoselect . ' - Min : ' . $minselect . '</td></tr>';
        }
        echo '	<tr><td class="fieldlabel">' . $LANG['mobilenumberfield'] . '</td><td class="fieldarea"><select size="1" name="customfieldid">' . $dropdown . '</select> ' . $LANG['mobilenumberfielddesc'] . '</td></tr>';
        echo "<tr><td class=\"fieldlabel\">";
        echo $LANG['multilanguages'];
        echo "</td><td class=\"fieldarea\">";
        echo "<select size='5' multiple=\"multiple\" name=\"active_languages[]\">";
        $active_languages = Capsule::table('mod_smsmanager_config')->where('setting', 'active_languages')->first();
        if (count($active_languages) > 0) {
            $languages = explode(",", $active_languages->value);
        } else {
            $languages = array();
        }
        echo '<option value="none">None</option>';
        foreach (glob(ROOTDIR . '/lang/*.php') as $langz) {
            $langz = basename(str_replace('.php', '', $langz));
            if ($langz == $CONFIG['Language'] || $langz == 'index')
                continue;
            echo "<option value=\"" . $langz . "\"";
            if (in_array($langz, $languages)) {
                echo " selected=\"selected\"";
            }
            echo ">" . ucfirst($langz) . "</option>";
        }
        echo " </select></td></tr>";
        echo '<tr><td class="fieldlabel">' . $LANG['requiredmobile'] . ' : </td>
                                    <td class="fieldarea">
                                        <input type="checkbox" name="requiredmobile" ' . (($rdata->value != 'no') ? 'checked="checked"' : '') . ' value="yes">
                                            ' . $LANG['requiredmobiledesc'] . '
                                    </td></tr>
                                <tr>';
        echo '<tr><td class="fieldlabel">' . $LANG['notfreeinvoice'] . ' : </td>
                                    <td class="fieldarea">
                                        <input type="checkbox" name="notfreeinvoice" ' . (($idata->value == 'yes') ? 'checked="checked"' : '') . ' value="yes">
                                            ' . $LANG['notfreeinvoicedesc'] . '
                                    </td></tr>
                                <tr>';
        echo '</table>
				<p align="center"><input type="submit" class="btn btn-success" value="' . $LANG['savechanges'] . '" class="button" ></p>
			</form>';

        if (isset($_REQUEST['savecountry'])) {
            $cx = new Country;
            $_REQUEST['length'] = (int) $_REQUEST['length'];
            $fdata = Capsule::table('mod_smsmanager_config')->where('setting', 'customfieldid')->first();
            $precode = $cx->getCallingCode($_REQUEST['country']);
            $clist = Capsule::connection()->select('SELECT tblcustomfieldsvalues.* FROM tblcustomfieldsvalues LEFT JOIN tblclients ON tblclients.id = tblcustomfieldsvalues.relid WHERE tblclients.country = \'' . $_REQUEST['country'] . '\' AND LENGTH(tblcustomfieldsvalues.value) = \'' . $_REQUEST['length'] . '\' AND tblcustomfieldsvalues.fieldid = \'' . $fdata->value . '\' ');
            if (count($clist) > 0) {
                foreach ($clist as $value) {
                    $phone = trim($value->value);
                    if (isset($_REQUEST['removezero'])) {
                        $phone = ltrim($value->value, '0');
                    }
                    $phone = '+' . $precode . $phone;
                    Capsule::table('tblcustomfieldsvalues')->where('id', $value->id)->update([
                        'value' => $phone
                    ]);
                }
                echo infoBox($aInt->lang('global', 'changesuccess'), $aInt->lang('global', 'changesuccess'));
            }
        }
        echo '<center><a href="' . $modulelink . '&a=config&multigateway=true" class="btn btn-primary">' . $LANG["multigateway"] . '</a></center><br>';
        echo '<div class="panel panel-success" style="width:100%">
                <div class="panel-heading">' . $LANG['Addprefix'] . '</div>
                <div class="panel-body">
                    <form method="POST" action="addonmodules.php?module=smsmanager&a=config">
                    <input type="hidden" name="savecountry" value="1">
                        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
                            <tbody>
                                <tr>
                                    <td class="fieldlabel">' . $LANG['LocalLength'] . '</td>
                                    <td class="fieldarea">
                                        <input type="number" name="length" value="">
                                        ' . $LANG['LocalLengthDesc'] . '
                                        </td>
                                </tr>
                                <tr>
                                    <td class="fieldlabel">' . $LANG['zeroltrim'] . ' : </td>
                                    <td class="fieldarea">
                                        <input type="checkbox" name="removezero" value="1">
                                             ' . $LANG['zeroltrimdesc'] . '
                                    </td></tr>
                                <tr>
                                    <td class="fieldlabel">' . $LANG['country'] . ' : </td>
                                    <td class="fieldarea">
                                        ' . getCountriesDropDown('', "", 13) . '
                                    </td></tr>
                            </tbody>
                        </table>
                        <center>
                            <input type="submit" class="btn btn-success" value="' . $LANG['savechanges'] . '" name="savecoun">
                        </center>
                    </form>
                </div>
            </div>';
    }

    if ($_REQUEST['a'] == "management") {
        if (isset($_REQUEST['save']) && $_REQUEST['save']) {
            $active_languages = Capsule::table('mod_smsmanager_config')->where('setting', 'active_languages')->first();
            if (count($active_languages) > 0) {
                $languages = explode(",", $active_languages->value);
                $languages[] = $CONFIG['Language'];
            }
            $arrays = array(
                'clientLogin',
                'sms_alerts',
                'sms_all_alerts',
                'clientLoginOn',
                'clientRegistration',
                'clientRegistrationOn',
                'clientPasswordChange',
                'clientPasswordChangeOn',
                'affiliateActivation',
                'affiliateActivationOn',
                'invoiceCreated',
                'invoiceCreatedOn',
                'invoicePaid',
                'invoicePaidOn',
                'invoiceReminder',
                'invoiceReminderOn',
                'invoiceFirstOverdue',
                'invoiceFirstOverdueOn',
                'invoiceSecondOverdue',
                'invoiceSecondOverdueOn',
                'invoiceThirdOverdue',
                'invoiceThirdOverdueOn',
                'domainReminder',
                'domainReminderOn',
                'domainFirstOverdue',
                'domainFirstOverdueOn',
                'domainSecondOverdue',
                'domainSecondOverdueOn',
                'domainThirdOverdue',
                'domainThirdOverdueOn',
                'moduleCreate',
                'moduleCreateOn',
                'moduleSuspend',
                'moduleSuspendOn',
                'moduleUnSuspend',
                'moduleUnSuspendOn',
                'modulePasswordChange',
                'modulePasswordChangeOn',
                'cancellationRequest',
                'cancellationRequestOn',
                'domainRegistration',
                'domainRegistrationOn',
                'domainTransfer',
                'domainTransferOn',
                'domainRenewal',
                'domainRenewalOn',
                'supportTicketChangeStatus',
                'supportTicketChangeStatusOn',
                'supportTicketOpen',
                'supportTicketOpenOn',
                'supportTicketResponse',
                'supportTicketResponseOn',
                'supportTicketClose',
                'supportTicketCloseOn',
                'adminUnsuspendProduct',
                'adminUnsuspendProductOn',
                'adminCreateProduct',
                'adminCreateProductOn',
                'adminCheckout',
                'clientTwoFactor',
                'clientTwoFactorOn',
                'adminCheckoutOn',
                'adminTicketOpen',
                'adminTicketOpenOn',
                'adminTicketUserReply',
                'adminTicketUserReplyOn',
                'adminTicketClose',
                'adminTicketCloseOn',
                'adminCancellationRequest',
                'adminCancellationRequestOn',
                'orderAcceptedOn',
                'orderAccepted',
                'modulechangepackageOn',
                'modulechangepackage',
                'adminLoginOn',
                'adminLogin',
                'adminDomainRegisterOn',
                'adminDomainRegister',
                'adminDomainRegisterFailedOn',
                'adminDomainRegisterFailed',
                'adminDomainRenewalOn',
                'adminDomainRenewal',
            );
            foreach ($arrays as $input => $v) {
                $v2 = 'tpl' . ucfirst($v);
                if (!isset($_REQUEST[$v]))
                    $_REQUEST[$v] = '';
                Capsule::table('mod_smsmanager_config')->where('setting', $v2)->update([
                    'value' => $_REQUEST[$v]
                ]);
                foreach ($languages as $langid => $lang) {
                    $fieldl = $v . '_' . strtolower($lang);
                    if (isset($_REQUEST[$fieldl])) {
                        if ($_REQUEST[$fieldl] == '') {
                            Capsule::table('mod_smsmanager_multilanguages')->where('lang', $lang)->where('name', ucfirst($v))->delete();
                        } else {
                            $dealang = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $lang)->where('name', ucfirst($v))->first();
                            if (count($dealang) > 0) {
                                Capsule::table('mod_smsmanager_multilanguages')->where('lang', $lang)->where('name', ucfirst($v))->update([
                                    'value' => $_REQUEST[$fieldl]
                                ]);
                            } else {
                                Capsule::table('mod_smsmanager_multilanguages')->insert([
                                    'lang' => $lang,
                                    'name' => ucfirst($v),
                                    'value' => $_REQUEST[$fieldl]
                                ]);
                            }
                        }
                    }
                }
            }
            //die(print_r($_REQUEST));
            redir("module=smsmanager&a=management&success");
            exit;
        }

        function toggleTick($name, $locked = false)
        {
            $result = Capsule::table('mod_smsmanager_config')->where('setting', 'tpl' . ucfirst($name))->first();
            if (strtolower($result->value) == "on")
                $checked = " checked";
            else
                $checked = "";
            if ($locked)
                $locked = " disabled";
            else
                $locked = "";
            return '<div><input type="checkbox" value="on" data-switch-set="size" data-switch-value="small" name="' . $name . '"' . $checked . $locked . '></div>';
        }
        echo '<div class="alert alert-warning"><strong>Available Merge Fields:</strong><br>{CompanyName} - Your Company Name<br>{ClientID} - Clients ID<br>{ServiceID} - Service ID (For product SMS)<br>{OrderID} - Order ID (For Order SMS)<br>{TicketID} - Ticket ID (For Ticket SMS)<br>{TicketStatus} - Ticket Status (close/open/hold)<br>{ClientFullName} - Clients Full Name<br>{ClientFirstName} - Clients First Name<br>{ClientLastName} - Clients Last Name<br>{InvoiceID} - Invoice ID (Format: #123)<br>{InvoiceDueDate} - Invoice Due Date<br>{InvoiceTotal} - Invoice Total (Format: $1.00 USD - In client currency)<br>{InvoiceDomain} - Invoice related domain<br>{FullName} - Full Name (Client or Admin in Two-Factor Auth Only)<br>{AdminFullName} - Admin Full Name (For Two-Factor Auth Only)<br>{AdminFirstName} - Admin First Name (For Two-Factor Auth Only)<br>{AdminLastName} - Admin Last Name (For Two-Factor Auth Only)<br>{AdminID} - Admin ID (For Two-Factor Auth Only)<br>{domain} - Domain name (For Domain Reminder)<br><br><small>* {ClientFullName},{ClientFirstName},{ClientLastName} will show admin credentials for admin login on Two Factor Authentication</div>';
        echo "<script type='text/javascript'>
        //<![CDATA[
        function hideOtherLanguage(id) {
            $('.translatable-field').hide();
            $('.lang-' + id).show();
        }
        //]]>
    </script>";
        echo '<form method="post" action="' . $modulelink . '&a=management&save=true">';
        echo '<p><strong>' . $LANG['clientnotifications'] . '</strong></p><table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td class="fieldlabel" width="250">' . $LANG['smsalerts'] . '</td><td class="fieldlabel" width="150">' . toggleTick('sms_alerts') . '</td><td class="fieldarea">' . $LANG['smsalertsdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['clientallalerts'] . '</td><td class="fieldlabel" width="150">' . toggleTick('sms_all_alerts') . '</td><td class="fieldarea">' . $LANG['clientallalertsdesc'] . '</td></tr>
                                <tr><td class="fieldlabel" width="250">' . $LANG['clientlogin'] . '</td><td class="fieldlabel" width="150">' . toggleTick('clientLoginOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('clientLogin') . $LANG['clientlogindesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['clientregistration'] . '</td><td class="fieldlabel">' . toggleTick('clientRegistrationOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('clientRegistration') . $LANG['clientregistrationdesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['clientpasswordchange'] . '</td><td class="fieldlabel">' . toggleTick('clientPasswordChangeOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('clientPasswordChange') . $LANG['clientpasswordchangedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['clientaffiliateactivation'] . '</td><td class="fieldlabel">' . toggleTick('affiliateActivationOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('affiliateActivation') . $LANG['clientaffiliateactivationdesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['clienttwofactor'] . '</td><td class="fieldlabel">' . toggleTick('clientTwoFactorOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('clientTwoFactor') . $LANG['clienttwofactordesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['supportticketresponse'] . '</td><td class="fieldlabel">' . toggleTick('supportTicketResponseOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('supportTicketResponse') . $LANG['supportticketresponsedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['supportticketchangestatus'] . '</td><td class="fieldlabel">' . toggleTick('supportTicketChangeStatusOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('supportTicketChangeStatus') . $LANG['supportticketchangestatusdesc'] . '</td></tr>
				</table>';
        echo '<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td class="fieldlabel" width="250">' . $LANG['acceptorder'] . '</td><td class="fieldlabel" width="150">' . toggleTick('orderAcceptedOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('orderAccepted') . $LANG['acceptorderdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['invoicecreated'] . '</td><td class="fieldlabel" width="150">' . toggleTick('invoiceCreatedOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('invoiceCreated') . $LANG['invoicecreateddesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['invoicepaid'] . '</td><td class="fieldlabel">' . toggleTick('invoicePaidOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('invoicePaid') . $LANG['invoicepaiddesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['invoicereminder'] . '</td><td class="fieldlabel">' . toggleTick('invoiceReminderOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('invoiceReminder') . $LANG['invoicereminderdesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['invoicefirstoverdue'] . '</td><td class="fieldlabel">' . toggleTick('invoiceFirstOverdueOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('invoiceFirstOverdue') . $LANG['invoicefirstoverduedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['invoicesecondoverdue'] . '</td><td class="fieldlabel">' . toggleTick('invoiceSecondOverdueOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('invoiceSecondOverdue') . $LANG['invoicesecondoverduedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['invoicethirdoverdue'] . '</td><td class="fieldlabel">' . toggleTick('invoiceThirdOverdueOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('invoiceThirdOverdue') . $LANG['invoicethirdoverduedesc'] . '</td></tr>
				</table>';
        echo '<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td class="fieldlabel" width="250">' . $LANG['modulecreate'] . '</td><td class="fieldlabel" width="150">' . toggleTick('moduleCreateOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('moduleCreate') . $LANG['modulecreatedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['modulesuspend'] . '</td><td class="fieldlabel">' . toggleTick('moduleSuspendOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('moduleSuspend') . $LANG['modulesuspenddesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['moduleunsuspend'] . '</td><td class="fieldlabel">' . toggleTick('moduleUnSuspendOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('moduleUnSuspend') . $LANG['moduleunsuspenddesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['modulepasswordchange'] . '</td><td class="fieldlabel">' . toggleTick('modulePasswordChangeOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('modulePasswordChange') . $LANG['modulepasswordchangedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['cancellationrequest'] . '</td><td class="fieldlabel">' . toggleTick('cancellationRequestOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('cancellationRequest') . $LANG['cancellationrequestdesc'] . '</td></tr>
                <tr><td class="fieldlabel">' . $LANG['modulechangepackage'] . '</td><td class="fieldlabel">' . toggleTick('modulechangepackageOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('modulechangepackage') . $LANG['modulechangepackagedesc'] . '</td></tr>
				</table>';
        echo '<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td class="fieldlabel" width="250">' . $LANG['domainregistration'] . '</td><td class="fieldlabel" width="150">' . toggleTick('domainRegistrationOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainRegistration') . $LANG['domainregistrationdesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['domaintransfer'] . '</td><td class="fieldlabel">' . toggleTick('domainTransferOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainTransfer') . $LANG['domaintransferdesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['domainrenewal'] . '</td><td class="fieldlabel">' . toggleTick('domainRenewalOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainRenewal') . $LANG['domainrenewaldesc'] . '</td></tr>
                                <tr><td class="fieldlabel">' . $LANG['domainreminder'] . '</td><td class="fieldlabel">' . toggleTick('domainReminderOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainReminder') . $LANG['domainreminderdesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['domainfirstoverdue'] . '</td><td class="fieldlabel">' . toggleTick('domainFirstOverdueOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainFirstOverdue') . $LANG['domainfirstoverduedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['domainsecondoverdue'] . '</td><td class="fieldlabel">' . toggleTick('domainSecondOverdueOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainSecondOverdue') . $LANG['domainsecondoverduedesc'] . '</td></tr>
				<tr><td class="fieldlabel">' . $LANG['domainthirdoverdue'] . '</td><td class="fieldlabel">' . toggleTick('domainThirdOverdueOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('domainThirdOverdue') . $LANG['domainthirdoverduedesc'] . '</td></tr>
				</table>';
        echo '<br><p><strong>' . $LANG['adminnotifications'] . '</strong></p><table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr><td class="fieldlabel" width="250">' . $LANG['adminunsuspendproduct'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminUnsuspendProductOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminUnsuspendProduct') . $LANG['adminunsuspendproductdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['admincreateproduct'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminCreateProductOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminCreateProduct') . $LANG['admincreateproductdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['admincheckout'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminCheckoutOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminCheckout') . $LANG['admincheckoutdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminticketopen'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminTicketOpenOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminTicketOpen') . $LANG['adminticketopendesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminticketuserreply'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminTicketUserReplyOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminTicketUserReply') . $LANG['adminticketuserreplydesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminticketclose'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminTicketCloseOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminTicketClose') . $LANG['adminticketclosedesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['admincancellationrequest'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminCancellationRequestOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminCancellationRequest') . $LANG['admincancellationrequestdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminLogin'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminLoginOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminLogin') . $LANG['adminLogindesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminDomainRegister'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminDomainRegisterOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminDomainRegister') . $LANG['adminDomainRegisterdesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminDomainRegisterFailed'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminDomainRegisterFailedOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminDomainRegisterFailed') . $LANG['adminDomainRegisterFaileddesc'] . '</td></tr>
				<tr><td class="fieldlabel" width="250">' . $LANG['adminDomainRenewal'] . '</td><td class="fieldlabel" width="150">' . toggleTick('adminDomainRenewalOn') . '</td><td class="fieldarea">' . smsmanager_generatemulti('adminDomainRenewal') . $LANG['adminDomainRenewaldesc'] . '</td></tr>
				</table>';

        echo '<p align="center"><input type="submit" class="btn btn-success" value="' . $LANG['savechanges'] . '" class="button" ></p>
			</form>';
        echo '<script>
    $(function(argument) {
    $(\'[type="checkbox"]\').bootstrapSwitch();
    })
    </script>';
    }
    if ($_REQUEST['a'] == "admincontact") {

        if (isset($_POST['mobileNumber']) && isset($_REQUEST['save']) && $_REQUEST['save']) {
            $numbers = serialize($_POST['mobileNumber']);
            Capsule::table('mod_smsmanager_config')->where('setting', 'adminMobileNumbers')->update([
                'value' => $numbers
            ]);
            redir('module=smsmanager&a=admincontact');
        }

        echo '<form action="addonmodules.php?module=smsmanager&a=admincontact&save=true" method="post"><table id="sortabletbl1" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">';
        echo '<tr><th>' . $LANG['name'] . '</th><th>' . $LANG['emailaddress'] . '</th><th>' . $LANG['username'] . '</th><th>' . $LANG['mobilenumber'] . '</th><th>' . $LANG['notifications'] . '</th></tr>';
        $query = Capsule::table('tbladmins')->get();
        foreach ($query as $result) {
            $sql = Capsule::table('mod_smsmanager_config')->where('setting', 'adminMobileNumbers')->first();
            $mobileNumber = unserialize($sql->value);
            $mobileNumber = $mobileNumber[$result->id];
            if (empty($mobileNumber))
                $mobileNumber = "";
            echo '<tr><td>' . ucfirst($result->firstname) . " " . ucfirst($result->lastname) . '</td><td><a href="mailto:' . $result->email . '">' . $result->email . '</a></td><td>' . $result->username . '</td><td><input type="text" name="mobileNumber[' . $result->id . ']" value="' . $mobileNumber . '" class="form-control"></td><td><center><small><a href="' . $modulelink . '&a=configureadmins&id=' . $result->id . '">' . $LANG['configure'] . '</a></small></center></td></tr>';
        }

        echo '</table><center><input type="submit" value="' . $LANG['savechanges'] . '" class="btn btn-success"></center></form>';
    }

    if ($_REQUEST['a'] == "configureadmins" && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
        if (isset($_REQUEST['update']) && $_REQUEST['update']) {
            $fullArr = unserialize($SETING['adminNotifications']);
            if (!is_array($fullArr))
                $fullArr = array();
            $arr = array(
                "productunsuspend" => "",
                "productcreate" => "",
                "checkout" => "",
                "ticketopen" => "",
                "ticketuserreply" => "",
                "ticketclose" => "",
                "cancellationrequest" => "",
                "adminLogin" => "",
                "adminDomainRegister" => "",
                "adminDomainRegisterFailed" => "",
                "adminDomainRenewal" => "",
            );
            foreach ($_POST AS $key => $value) {
                if (isset($arr[$key])) {
                    $arr[$key] = $value;
                }
            }

            $arr = base64_encode(serialize($arr));
            $fullArr[$_REQUEST['id']] = $arr;
            $fullArr = serialize($fullArr);
            Capsule::table('mod_smsmanager_config')->where('setting', 'adminNotifications')->update([
                'value' => $fullArr
            ]);
            redir("module=smsmanager&a=admincontact&success=update");
        }

        $fullArr = unserialize($SETING['adminNotifications']);
        $data = unserialize(base64_decode($fullArr[$_REQUEST['id']]));

        if (strtolower($data['productunsuspend']) == "on")
            $productunsuspend = " checked";
        if (strtolower($data['productcreate']) == "on")
            $productcreate = " checked";
        if (strtolower($data['checkout']) == "on")
            $checkout = " checked";
        if (strtolower($data['cancellationrequest']) == "on")
            $cancellationrequest = " checked";
        if (strtolower($data['adminLogin']) == "on")
            $adminLogin = " checked";
        if (strtolower($data['adminDomainRegister']) == "on")
            $adminDomainRegister = " checked";
        if (strtolower($data['adminDomainRegisterFailed']) == "on")
            $adminDomainRegisterFailed = " checked";
        if (strtolower($data['adminDomainRenewal']) == "on")
            $adminDomainRenewal = " checked";
        echo '<form action="' . $modulelink . '&a=configureadmins&id=' . $_REQUEST['id'] . '&update=true" method="post"><div class="row"><div class="col-md-3"><div class="panel panel-default"><div class="panel-heading">' . $LANG['configureadminnotifications'] . '</div><div class="panel-body">';

        echo '<p><input type="checkbox" name="productunsuspend" id="productunsuspend" value="on"' . $productunsuspend . '> <label for="productunsuspend">' . $LANG['adminunsuspendproduct'] . '</label></p>';
        echo '<p><input type="checkbox" name="productcreate" id="productcreate" value="on"' . $productcreate . '> <label for="productcreate">' . $LANG['admincreateproduct'] . '</label></p>';
        echo '<p><input type="checkbox" name="checkout" id="checkout" value="on"' . $checkout . '> <label for="checkout">' . $LANG['admincheckout'] . '</label></p>';
        echo '<p><input type="checkbox" name="cancellationrequest" id="cancellationrequest" value="on"' . $cancellationrequest . '> <label for="cancellationrequest">' . $LANG['admincancellationrequest'] . '</label></p>';
        echo '<p><input type="checkbox" name="adminLogin" id="adminLogin" value="on"' . $adminLogin . '> <label for="adminLogin">' . $LANG['adminLogin'] . '</label></p>';
        echo '<p><input type="checkbox" name="adminDomainRegister" id="adminDomainRegister" value="on"' . $adminDomainRegister . '> <label for="adminDomainRegister">' . $LANG['adminDomainRegister'] . '</label></p>';
        echo '<p><input type="checkbox" name="adminDomainRegisterFailed" id="adminDomainRegisterFailed" value="on"' . $adminDomainRegisterFailed . '> <label for="adminDomainRegisterFailed">' . $LANG['adminDomainRegisterFailed'] . '</label></p>';
        echo '<p><input type="checkbox" name="adminDomainRenewal" id="adminDomainRenewal" value="on"' . $adminDomainRenewal . '> <label for="adminDomainRenewal">' . $LANG['adminDomainRenewal'] . '</label></p>';

        echo '</div></div></div>';
        $sql = Capsule::table('tblticketdepartments')->get();
        foreach ($sql as $department) {
            if (strtolower($data['ticketopen'][$department->id]) == "on")
                $ticketopen = " checked";
            if (strtolower($data['ticketuserreply'][$department->id]) == "on")
                $ticketuserreply = " checked";
            if (strtolower($data['ticketclose'][$department->id]) == "on")
                $ticketclose = " checked";

            echo '<div class="col-md-3"><div class="panel panel-default"><div class="panel-heading">' . ucfirst($department->name) . ' ' . $LANG['department'] . '</div><div class="panel-body">';
            echo '<p><input type="checkbox" name="ticketopen[' . $department->id . ']" id="ticketopen' . $department->id . '" value="on"' . $ticketopen . '> <label for="ticketopen' . $department->id . '">' . $LANG['adminticketopen'] . '</label></p>';
            echo '<p><input type="checkbox" name="ticketuserreply[' . $department->id . ']" id="ticketuserreply' . $department->id . '" value="on"' . $ticketuserreply . '> <label for="ticketuserreply' . $department->id . '">' . $LANG['adminticketuserreply'] . '</label></p>';
            echo '<p><input type="checkbox" name="ticketclose[' . $department->id . ']" id="ticketclose' . $department->id . '" value="on"' . $ticketclose . '> <label for="ticketclose' . $department->id . '">' . $LANG['adminticketclose'] . '</label></p>';
            echo '<br><br>';

            $ticketopen = "";
            $ticketuserreply = "";
            $ticketclose = "";

            echo '</div></div></div>';
        }
        echo '</div>';
        echo '<center><div class="row"><p><button class="btn btn-success" type="submit">' . $LANG['savechanges'] . '</button></p></div></center>';
        echo '</form>';
    }

    if ($_REQUEST['a'] == "" && $allow) {
        $q = trim(mysql_real_escape_string($_REQUEST['q']));

        echo '<div class="panel panel-default"><div class="panel-heading">' . $LANG['search'] . '</div><div class="panel-body">';
        echo '<form action="addonmodules.php?module=smsmanager&search=true" method="post"><center><div style="width: 93%; float:left;"><input type="text" value="' . $q . '" placeholder="' . $LANG['searchplaceholder'] . '" class="form-control" name="q"></div><div style="width: 5%; float:left;"><input type="submit" style="margin-left:5px;" class="btn btn-success" name="search" value="' . $LANG['search'] . '"></div></center></form>';
        echo '</div></div>';

        if (isset($_POST['q']) && !empty($_POST['q'])) {
            $query = Capsule::table('tblclients')->where('email', 'LIKE', '%' . $q . '%')->select('id', 'firstname', 'lastname', 'email')->get();
            $output = "";
            $sarray = array();
            foreach ($query as $result) {
                $sarray[$result->id] = array("id" => $result->id, "firstname" => $result->firstname, "lastname" => $result->lastname, "email" => $result->email);
            }
            $querya = Capsule::table('mod_smsmanager')->where('recipent', 'LIKE', '%' . $q . '%')->select('userid')->get();
            foreach ($querya as $resulta) {
                $result = Capsule::table('tblclients')->where('id', $resulta->userid)->select('id', 'firstname', 'lastname', 'email')->first();
                $sarray[$resulta->userid] = array("id" => $result->id, "firstname" => $result->firstname, "lastname" => $result->lastname, "email" => $result->email);
            }
            $output = "";
            foreach ($sarray AS $key => $value) {
                $output .= '<a href="addonmodules.php?module=smsmanager&view=true&q=' . $q . '&id=' . $key . '">' . ucfirst($sarray[$key]['firstname']) . " " . ucfirst($sarray[$key]['lastname']) . "</a><br>";
            }
            echo $output;
        }
        if (!isset($_REQUEST['q'])) {
            if (isset($_REQUEST['manual']) && $_REQUEST['manual'] && $_REQUEST['manual'] != "success") {
                if (is_array($_REQUEST['to'])) {
                    $message = mysql_real_escape_String(trim($_REQUEST['message']));
                    foreach ($_REQUEST['to'] as $key => $value) {
                        $to = mysql_real_escape_string(trim($value));
                        smsmanager_sendSMS($to, $message);
                    }
                } else {
                    $to = mysql_real_escape_string(trim($_REQUEST['to']));
                    $message = mysql_real_escape_String(trim($_REQUEST['message']));
                    $response = smsmanager_sendSMS($to, $message, array('manual' => 'on'));
                    logActivity($response);
                }
                redir('module=smsmanager&manual=success&response='.$response);
                // redir('module=smsmanager&manual=success&response=1');
                exit;
            }
            if (isset($_REQUEST['manual']) && $_REQUEST['manual'] == "success") {
                if (urldecode($_REQUEST['response']) == $LANG['enterrecipient']) {
                    $msg = "<center><strong><font color='#FF0000'>" . $LANG['enterrecipient'] . "</font></strong></center><br>";
                } elseif (urldecode($_REQUEST['response']) == $LANG['entermessage']) {
                    $msg = "<center><strong><font color='#FF0000'>" . $LANG['entermessage'] . "</font></strong></center><br>";
                } elseif (urldecode($_REQUEST['response']) == $LANG['credentialerror']) {
                    $msg = "<center><strong><font color='#FF0000'>" . $LANG['credentialerror'] . "</font></strong></center><br>";
                } elseif (urldecode($_REQUEST['response']) != 'success' && $_REQUEST['response']) {
                    $msg = "<center><strong><font color='#FF0000'>" . urldecode($_REQUEST['response']) . "</font></strong></center><br>";
                } else {
                    if (isset($_REQUEST['response'])) {
                        $msg = "<center><strong><font color='#FF0000'>" . $LANG['smssent'] . "</font></strong> <br>" . $LANG['smssentmore'] . "</center><br>";
                    } else
                        $msg = "<center><strong><font color='#FF0000'>" . $LANG['smssent'] . "</font></strong></center><br>";
                }
            }
            $sendout = '';
            if (isset($_REQUEST['selectedclients']) && count($_REQUEST['selectedclients']) > 0) {
                $fieldid = smsmanager_fieldid();
                foreach ($_REQUEST['selectedclients'] as $key => $value) {
                    $dataa = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $fieldid)->where('relid', $value)->value('value');
                    $to = smsmanager_toTrim($dataa);
                    if ($to != '') {
                        $uitem = Capsule::table('tblclients')->where('id', $value)->select('firstname', 'lastname', 'companyname')->first();
                        $sendout .= '<input type="hidden" value="' . $to . '" name="to[]"> <b>' . $uitem->firstname . ' ' . $uitem->lastname . (($uitem->companyname != '') ? ' (' . $uitem->companyname . ')' : '') . '</b></br>';
                    }
                }
            }
            echo '<style>@media screen and (-webkit-min-device-pixel-ratio:0) { .intl-tel-input.separate-dial-code .selected-dial-code{padding-left: 9px !important;}}</style><center><div class="panel panel-success" style="width:50%"><div class="panel-heading">' . $LANG['manualsend'] . '</div><div class="panel-body">';
            echo $msg . '<form method="POST" id="sform" action="addonmodules.php?module=smsmanager&manual=true"><p>'
            . (($sendout != '') ? $sendout : '<input type="tel" id="customfieldto" class="form-control" name="to"></p>')
            . '<p><textarea id="textbox1" rows="6" class="form-control" name="message" placeholder="' . $LANG['message'] . '" cols="25"></textarea><small><span id=\'remaining\'>160</span> ' . $LANG['charactersremaining'] . ' <span id=\'lmessages\'>1</span> ' . $LANG['mscount'] . '</small></p><center><input type="submit" class="btn btn-success" value="' . $LANG['send'] . '" name="send"></center></form><link rel="stylesheet" href="../modules/addons/smsmanager/assets/intlTelInput.css">
		<script src="../modules/addons/smsmanager/assets/intlTelInput.min.js"></script>
		<script src="../modules/addons/smsmanager/assets/scripts.js"></script>
        <script>
        $("#sform").submit(function() {
        $("#customfieldto").val($("#customfieldto").intlTelInput("getNumber", intlTelInputUtils.numberFormat.E164));
        });
                $("#customfieldto").addClass("form-control");
				$("#customfieldto").intlTelInput({
                    separateDialCode: true,
                    utilsScript: "../modules/addons/smsmanager/assets/utils.js"
				});
                $("#customfieldto").intlTelInput("setCountry", "' . strtolower($CONFIG['DefaultCountry']) . '");</script>
                ';
            echo '</div></div></center>';
        }
    }

    if (isset($_REQUEST['view']) && $_REQUEST['view']) {
        if (isset($_REQUEST['resend']) && is_numeric($_REQUEST['resend']) && $_REQUEST['resend'] != "success") {
            $result = Capsule::table('mod_smsmanager')->where('id', trim(mysql_real_escape_string($_REQUEST['resend'])))->first();
            $to = $result->recipent;
            $message = $result->message;
            $userid = $result->userid;
            $response = smsmanager_sendSMS($to, $message, $vars);
            smsmanager_logsms($userid, $to, $message, $response);
            redir('module=smsmanager&a=' . $_REQUEST['a'] . '&view=true&q=' . $_REQUEST['q'] . '&id=' . $_REQUEST['id'] . '&resend=success');
            exit;
        }
        if (isset($_REQUEST['resend']) && $_REQUEST['resend'] == "success") {
            echo "<center><strong>" . $LANG['messageresent'] . "</strong></center>";
        }
        $resultsshown = false;
        $querya = Capsule::table('mod_smsmanager')->where('userid', trim(mysql_real_escape_string($_REQUEST['id'])))->orderBy('timestamp', 'DESC')->get();
        $total = count($querya);
        foreach ($querya as $resulta) {
            $result = Capsule::table('tblclients')->where('id', $resulta->userid)->select('id', 'firstname', 'lastname', 'email')->first();
            if (!$resultsshown)
                echo "<strong>" . $total . " " . $LANG['resultsfor'] . " " . ucfirst($result->firstname) . " " . ucfirst($result->lastname) . " (" . $_REQUEST['q'] . ")</strong><br>";
            $resultsshown = true;
            echo date("M d, Y H:i", $resulta->timestamp) . " - " . $resulta->recipent . " - " . $resulta->message . ' [<a href="addonmodules.php?module=smsmanager&view=true&q=' . $_REQUEST['q'] . '&id=' . $_REQUEST['id'] . '&resend=' . $resulta->id . '">' . $LANG['resend'] . '</a>]<br>';
        }
    }

    if (isset($_REQUEST['a']) && $_REQUEST['a'] == "logs") {
        if (isset($_REQUEST['clean']) && $_REQUEST['clean'] == "log") {
            Capsule::table('mod_smsmanager')->delete();
            echo "<center><strong>" . $LANG['cleanmessage'] . "</strong></center>";
        }
        $querya = Capsule::table('mod_smsmanager')->orderBy('timestamp', 'DESC')->get();
        $total = count($querya);
        echo "<strong>" . $total . " " . $LANG['logs'] . "</strong><br>";
        foreach ($querya as $resulta) {
            $result = Capsule::table('tblclients')->where('id', $resulta->userid)->select('id', 'firstname', 'lastname', 'email')->first();
            $resultsshown = true;
            echo date("M d, Y H:i", $resulta->timestamp) . " - " . $result->firstname . " " . $result->lastname . " - " . $resulta->recipent . " - " . $resulta->message . ' [<a href="addonmodules.php?module=smsmanager&a=logs&view=true&q=' . $_REQUEST['q'] . '&id=' . $_REQUEST['id'] . '&resend=' . $resulta->id . '">' . $LANG['resend'] . '</a>]<br>';
        }
        echo "<center><a href='addonmodules.php?module=smsmanager&a=logs&clean=log' class='btn btn-danger'>" . $LANG['cleanlog'] . "</a></center><br>";
    }
    echo '</div></div>';
}

function smsmanager_clientarea($vars)
{
    $check = Capsule::table('mod_smsmanager_config')->where('setting', 'tplsms_alerts')->value('value');
    if ($check == '')
        return '';
    $LANG = $vars['_lang'];
    $template = "templates/smsmanager";

    if (isset($_REQUEST['action']) && $_REQUEST['action']) {
        $tot = Capsule::table('mod_smsmanager_preferences')->where('userid', trim(mysql_real_escape_string($_SESSION['uid'])))->first();
        unset($_POST['token']);
        unset($_POST['update']);
        $value = serialize($_POST);
        if ($tot) {
            Capsule::table('mod_smsmanager_preferences')->where('userid', trim(mysql_real_escape_string($_SESSION['uid'])))->update([
                'value' => $value,
            ]);
        } else {
            Capsule::table('mod_smsmanager_preferences')->insert([
                'value' => $value,
                'userid' => trim(mysql_real_escape_string($_SESSION['uid'])),
            ]);
        }
        redir('m=smsmanager');
        exit;
    }

    $result = Capsule::table('mod_smsmanager_preferences')->where('userid', trim(mysql_real_escape_string($_SESSION['uid'])))->value('value');
    $value = unserialize($result);
    $resultc = Capsule::table('mod_smsmanager_config')->where('setting', 'tplsms_all_alerts')->first();
    if (strtolower($resultc->value) == "on") {
        if (!isset($value['smsalerts']) || @$value['smsalerts'] == '') {
            $value['smsalerts'] = "on";
        }
    }

    if ($value['smsalerts'] == "on")
        $checked = " checked";
    else
        $notchecked = " checked";

    $pagetitle = $LANG['module_title'];
    $message .= '<div class="panel panel-default"><div class="panel-heading"><center>' . $LANG['clientpreferences'] . '</center></div><div class="panel-body"><center><form method="POST" action="' . $vars["modulelink"] . '&action=confirm"><p>' . $LANG['receivesms'] . '</p><p><input type="radio" value="on" checked name="smsalerts"' . $checked . '> ' . $LANG['yes'] . '&nbsp;&nbsp;&nbsp;<input type="radio" name="smsalerts" value="off"' . $notchecked . '> ' . $LANG['no'] . '</p><p><button class="btn btn-success" type="submit" name="update">' . $LANG['updatesettings'] . '</button></p></center></form></div></div>';

    return array(
        'pagetitle' => $pagetitle,
        'breadcrumb' => array('index.php?m=smsmanager' => $pagetitle),
        'templatefile' => $template,
        'requirelogin' => true, # or false
        'vars' => array(
            'message' => $message . '<br>',
            'alert' => $alert,
        ),
    );
}

function smsmanager_getlanguages()
{
    global $CONFIG;
    $languages = array();
    if (isset($_SESSION['SMSManagerLangList'])) {
        //return $_SESSION['SMSManagerLangList'];
    }
    $active_languages = Capsule::table('mod_smsmanager_config')->where('setting', 'active_languages')->first();
    if (count($active_languages) > 0) {
        $languages = explode(",", $active_languages->value);
        $languages[] = $CONFIG['Language'];
    } else {
        $languages[] = $CONFIG['Language'];
    }
    $_SESSION['SMSManagerLangList'] = $languages;
    return $languages;
}

function smsmanager_generatemulti($name)
{
    global $CONFIG;
    $languages = smsmanager_getlanguages();
    foreach ($languages as $key => $value) {
        $lfields .= '<li><a href="javascript:hideOtherLanguage(\'' . $value . '\');" tabindex="-1">' . ucfirst($value) . '</a></li>';
    }
    $lfield = '';
    $defalang = Capsule::table('mod_smsmanager_config')->where('setting', 'tpl' . ucfirst($name))->first();
    foreach ($languages as $key => $value) {
        $fvalue = '';
        $dealang = Capsule::table('mod_smsmanager_multilanguages')->where('lang', $value)->where('name', ucfirst($name))->first();
        if (count($dealang) > 0) {
            $fvalue = $dealang->value;
        } else {
            if (strtolower($value) == strtolower($CONFIG['Language']) && $defalang->value) {
                $fvalue = $defalang->value;
            }
        }
        $display = ((strtolower($value) == strtolower($CONFIG['Language'])) ? 'block' : 'none');
        $lfield = $lfield . '<div class="translatable-field lang-' . $value . '" style="display: ' . $display . ';">
            <div class="col-lg-9" style="margin-right: 0px;padding-right: 0px;padding-left: 0px;">
                <textarea id="' . $name . $value . '" name="' . $name . '_' . $value . '" class="form-control">' . $fvalue . '</textarea>
            </div>
            <div class="col-lg-1" style="padding-left: 0px;">
                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown" aria-expanded="false">
                    ' . $value . '
                    <i class="icon-caret-down"></i>
                </button>
                <ul class="dropdown-menu">
                ' . $lfields . '
                </ul>
            </div>
        </div>';
    }
    $rtu = '<div class="col-lg-12" style="padding: 0px;">
    <div class="form-group">
    ' . $lfield . '
</div>
</div>';
    return $rtu;
}
