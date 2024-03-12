<?php
include "config.php";
global $_CONFIG;
define("MONO_ON", 1);
require "class/class_db_mysql.php";
$db = new database;
$db->configure($_CONFIG['hostname'], $_CONFIG['username'], $_CONFIG['password'], $_CONFIG['database'], $_CONFIG['persistent']);
$db->connect();
require 'global_func.php';
$set = array();
$settq = $db->query("SELECT * FROM settings");
while ($r = mysqli_fetch_assoc($settq)) {
    $set[$r['conf_name']] = $r['conf_value'];
}
// logEvent(1, "DP {$pack} top of page.");

// Validate with Paypal
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);
// logEvent(1, "DP {$pack} passed PayPal validation.");

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];

if ($fp) {
    fputs($fp, $header . $req);
    while (!feof($fp)) {
        $res = fgets($fp, 1024);
        if (strcmp($res, "VERIFIED") == 0) {
            if ($payment_status != "Completed") {
                fclose($fp);
                die("");
            }
            if (mysqli_num_rows($db->query("SELECT * FROM logsDonations WHERE ldTransaction='{$txn_id}'")) > 0) {
                fclose($fp);
                die("");
            }
            if ($payment_currency != "USD") {
                fclose($fp);
                die("");
            }
            $packr = explode('|', $item_name);
            if (str_replace("www.", "", $packr[0]) != str_replace("www.", "", $_SERVER['HTTP_HOST'])) {
                fclose($fp);
                die("");
            }
            if ($packr[1] != "DP") {
                fclose($fp);
                die("");
            }
            $pack = $packr[2];
            if ($pack < 300 or $pack > 400) {
                fclose($fp);
                die("");
            }
            if ($pack == 301 && $payment_amount != "3.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 302 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 303 && $payment_amount != "7.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 304 && $payment_amount != "9.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 305 && $payment_amount != "3.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 306 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 310 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 311 && $payment_amount != "11.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 312 && $payment_amount != "16.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 313 && $payment_amount != "28.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 314 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 315 && $payment_amount != "11.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 316 && $payment_amount != "16.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 317 && $payment_amount != "28.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 318 && $payment_amount != "6.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 319 && $payment_amount != "16.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 320 && $payment_amount != "24.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 321 && $payment_amount != "42.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 322 && $payment_amount != "5.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 323 && $payment_amount != "13.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 324 && $payment_amount != "20.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 325 && $payment_amount != "35.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 326 && $payment_amount != "17.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 330 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 331 && $payment_amount != "11.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 332 && $payment_amount != "16.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 333 && $payment_amount != "28.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 334 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 335 && $payment_amount != "11.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 336 && $payment_amount != "16.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 337 && $payment_amount != "28.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 338 && $payment_amount != "6.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 339 && $payment_amount != "16.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 340 && $payment_amount != "24.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 341 && $payment_amount != "42.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 342 && $payment_amount != "5.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 343 && $payment_amount != "13.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 344 && $payment_amount != "20.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 345 && $payment_amount != "35.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 346 && $payment_amount != "15.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 350 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 354 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 358 && $payment_amount != "5.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 366 && $payment_amount != "10.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 370 && $payment_amount != "4.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 371 && $payment_amount != "60.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 372 && $payment_amount != "100.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 373 && $payment_amount != "60.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 374 && $payment_amount != "100.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 375 && $payment_amount != "90.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 376 && $payment_amount != "150.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 377 && $payment_amount != "75.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 378 && $payment_amount != "125.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 379 && $payment_amount != "60.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 380 && $payment_amount != "100.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 381 && $payment_amount != "60.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 382 && $payment_amount != "100.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 383 && $payment_amount != "90.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 384 && $payment_amount != "150.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 385 && $payment_amount != "75.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 386 && $payment_amount != "125.00") {
                fclose($fp);
                die("");
            }
            if ($pack == 399 && $payment_amount != "1000.00") {
                fclose($fp);
                die("");
            }

            $buyer = $packr[3];
            itemAdd($pack, 1, 0, $buyer, 0);
            logEvent($buyer, "For donating, you gain a " . iteminfo($pack) . ". Thank you!");
            $user['userid'] = 1;
            logEvent(1, "~ " . iteminfo($pack) . " donated to " . mafiosoLight($buyer) . ".");
            $db->query("INSERT INTO logsDonations VALUES('',$pack,'','$payment_amount',$buyer,'$payer_email',unix_timestamp(),'$txn_id')");

//   $amnt=round(($payment_amount-2)/10);
//   if ($amnt>0) {
//    itemAdd(301,0,$buyer,0,$amnt);
//    logEvent($buyer, "As a special gift you get {$amnt} x ".iteminfo(301).".");
//   }
            $r = mysqli_fetch_assoc($db->query("SELECT donatedM FROM users WHERE userid={$buyer}"));
            if ($r['donatedM'] < 25 and ($r['donatedM'] + $payment_amount >= 25)) {
                itemAdd(310, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$25 this month, you gain a " . iteminfo(310) . ". Thank you!");
                //DP6 stats
            }
            if ($r['donatedM'] < 50 and ($r['donatedM'] + $payment_amount >= 50)) {
                itemAdd(318, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$50 this month, you gain a " . iteminfo(318) . ". Thank you!");
                //DP8 respect
            }
            if ($r['donatedM'] < 75 and ($r['donatedM'] + $payment_amount >= 75)) {
                itemAdd(338, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$75 this month, you gain a " . iteminfo(338) . ". Thank you!");
                //DP13 wine
            }
            if ($r['donatedM'] < 100 and ($r['donatedM'] + $payment_amount >= 100)) {
                itemAdd(366, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$100 this month, you gain a " . iteminfo(366) . ". Thank you!");
                //DP19 convention
            }
            if ($r['donatedM'] < 150 and ($r['donatedM'] + $payment_amount >= 150)) {
                itemAdd(346, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$150 this month, you gain a " . iteminfo(346) . ". Thank you!");
                //DP15 family party
            }
            if ($r['donatedM'] < 200 and ($r['donatedM'] + $payment_amount >= 200)) {
                itemAdd(326, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$200 this month, you gain a " . iteminfo(326) . ". Thank you!");
                //DP10 self improvement
            }
            if ($r['donatedM'] < 250 and ($r['donatedM'] + $payment_amount >= 250)) {
                itemAdd(311, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$250 this month, you gain a " . iteminfo(311) . ". Thank you!");
                //DP6x3 gym is for wimps
            }
            if ($r['donatedM'] < 300 and ($r['donatedM'] + $payment_amount >= 300)) {
                itemAdd(319, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$300 this month, you gain a " . iteminfo(319) . ". Thank you!");
                //DP8x3 Respect
            }
            if ($r['donatedM'] < 350 and ($r['donatedM'] + $payment_amount >= 350)) {
                itemAdd(339, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$350 this month, you gain a " . iteminfo(339) . ". Thank you!");
                //DP13x3 basket of wine
            }
            if ($r['donatedM'] < 400 and ($r['donatedM'] + $payment_amount >= 400)) {
                itemAdd(312, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$400 this month, you gain a " . iteminfo(312) . ". Thank you!");
                //DP6x5 gym is for wimps
            }
            if ($r['donatedM'] < 500 and ($r['donatedM'] + $payment_amount >= 500)) {
                itemAdd(320, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$500 this month, you gain a " . iteminfo(320) . ". Thank you!");
                //DP8x5 Respect
            }
            if ($r['donatedM'] < 600 and ($r['donatedM'] + $payment_amount >= 600)) {
                itemAdd(340, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$600 this month, you gain a " . iteminfo(340) . ". Thank you!");
                //DP13x5 basket of wine
            }
            if ($r['donatedM'] < 700 and ($r['donatedM'] + $payment_amount >= 700)) {
                itemAdd(312, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$700 this month, you gain a " . iteminfo(312) . ". Thank you!");
                //DP6x5 gym is for wimps
            }
            if ($r['donatedM'] < 800 and ($r['donatedM'] + $payment_amount >= 800)) {
                itemAdd(320, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$800 this month, you gain a " . iteminfo(320) . ". Thank you!");
                //DP8x5 Respect
            }
            if ($r['donatedM'] < 900 and ($r['donatedM'] + $payment_amount >= 900)) {
                itemAdd(340, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$900 this month, you gain a " . iteminfo(340) . ". Thank you!");
                //DP13x5 basket of wine
            }
            if ($r['donatedM'] < 1000 and ($r['donatedM'] + $payment_amount >= 1000)) {
                itemAdd(312, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$1,000 this month, you gain a " . iteminfo(312) . ". Thank you!");
                //DP6x5 gym is for wimps
            }
            if ($r['donatedM'] < 1100 and ($r['donatedM'] + $payment_amount >= 1100)) {
                itemAdd(320, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$1,100 this month, you gain a " . iteminfo(320) . ". Thank you!");
                //DP8x5 Respect
            }
            if ($r['donatedM'] < 1200 and ($r['donatedM'] + $payment_amount >= 1200)) {
                itemAdd(340, 1, 0, $buyer, 0);
                logEvent($buyer, "For donating \$1,200 this month, you gain a " . iteminfo(340) . ". Thank you!");
                //DP13x5 basket of wine
            }
            $db->query("UPDATE users SET donatedM=donatedM+{$payment_amount} WHERE userid={$buyer}");
        } else if (strcmp($res, "INVALID") == 0) {
            logEvent(1, "Invalid donation attempt by " . mafiosoLight($buyer) . ".");
        }
    }
    fclose($fp);
}
