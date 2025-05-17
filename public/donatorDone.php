<?php

require_once "globals.php";
global $application;

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';

if ($action == "cancel") {
    print '
        <h3>Donations</h3>
        <p>You have cancelled your donation and will not be charged. Please try again...</p>
    ';
} else if ($action == "done") {
    if(!isset($_GET['tx'])) {
        die ('There was an error with your purchase.  Please contact Paypal.');
    }

    print '
        <h3>Donation Successful</h3>
        <p>Thank you for your donation to First Mafia Wars! Your transaction has been completed, and a receipt for your purchase has been emailed to you.</p>
        <p>You may log into your Paypal&reg; account at <a href=\'http://www.paypal.com\'>www.paypal.com</a> to view details of this transaction. Your First Mafia Wars&trade; Mafioso should be credited within a few minutes. If not please contact a staff member for assistance.</p>
        <p>Enjoy your new found wealth!</p>
    ';
}

$application->header->endPage();
