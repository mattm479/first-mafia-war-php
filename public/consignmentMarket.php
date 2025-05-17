<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$currency = isset($_POST['currency']) ? mysql_tex($_POST['currency']) : '';
$ID = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$AID = isset($_POST['AID']) ? mysql_num($_POST['AID']) : 0;
$price = isset($_POST['price']) ? mysql_num($_POST['price']) : 0;

print '
    <h3>Consignment Market</h3>
    <div class=floatright>
        <br>
        <img src=\'assets/images/photos/itemMarket.jpg\' width=200 height=267 alt=Shop>
    </div>
';

switch ($action) {
    case "add":
        add($application->db, $application->header, $application->user, $userId, $ID, $AID, $currency, $price);
        break;
    case "buy":
        buy($application->db, $application->header, $application->user, $userId, $ID);
        break;
    case "remove":
        remove($application->db, $application->header, $userId, $ID);
        break;
    default:
        index($application->db, $userId);
        break;
}

function index(Database $db, int $userId): void
{
    print '<table width=65% cellspacing=0 cellpadding=2 class=table style=\'font-size:smaller;\'>';

    $lt = '';
    $query = $db->query("SELECT cm.cmConsignor, cm.cmExpire, cm.cmID, cm.cmItem, cm.cmDaysLeft, cm.cmCurrency, cm.cmPrice, i.itmname, i.itmtype FROM conMarket cm LEFT JOIN items i ON cm.cmItem = i.itmid WHERE cmExpire > 0 ORDER BY i.itmtype, i.itmname, cm.cmPrice");
    while ($row = mysqli_fetch_assoc($query)) {
        if ($lt != itemType($row['itmtype'])) {
            $lt = itemType($row['itmtype']);
            print '
                <tr><td colspan=5>&nbsp;</td></tr>
                <tr>
                    <th>' . $lt . '</th>
                    <th>Item</th>
                    <th style=\'text-align:center;\'>Price</th>
                    <th style=\'text-align:center;\'>Action</th>
                </tr>
            ';
        }

        if ($userId == $row['cmConsignor']) {
            print '<tr><td><strong> &middot; Consignment expires in ' . $row['cmExpire'] . ' days</strong></td>';
            $buycode = '<a href=\'consignmentMarket.php?action=remove&ID=' . $row['cmID'] . '\'>Remove</a>';
        } else {
            print '<tr><td>' . mafioso($row['cmConsignor']) . '</td>';
            $buycode = '<a href=\'consignmentMarket.php?action=buy&ID=' . $row['cmID'] . '\'>Buy</a>';
        }

        print '<td>' . itemInfo($row['cmItem']);

        if ($row['cmDaysLeft'] > 0) {
            print '&nbsp;<span title=\'days left\'>(' . $row['cmDaysLeft'] . ')</span>';
        }

        print '</td><td style=\'text-align:right;\'>';

        if ($row['cmCurrency'] == "cash") {
            print moneyFormatter($row['cmPrice']);
        } else {
            print moneyFormatter($row['cmPrice'], "") . ' tokens';
        }

        print '</td><td class=center>' . $buycode . '</td></tr>';
    }

    print '</table>';
}

function add(Database $db, Header $headers, array $user, int $userId, int $ID, int $AID, string $currency, int $price): void
{
    // Set the price and confirm
    if ($ID > 0) {
        $query = $db->query("SELECT iv.inv_id, iv.inv_userid, i.itmname, i.itmBasePrice FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE inv_id = {$ID} and inv_userid = {$userId}");
        $row = mysqli_fetch_assoc($query);
        if (!mysqli_num_rows($query)) {
            print '
                <p>Please add a real item that you possess. Thanks.</p>
                <p><a href=\'home.php\'>Home</a></p>
            ';

            $headers->endpage();
            exit;
        }

        $value = moneyFormatter($row['itmBasePrice']);
        if ($row['itmBasePrice'] == 0) {
            $value = "priceless";
        }

        print '
            <p>You are adding one ' . $row['itmname'] . ' to the market. It is valued at ' . $value . '. This is a Consignment Market which means that you will pay 10% of the price you set to the Market owner (minnimum of $1 or 1 token).</p>
            <p>Also, after 30 days, the owner gets to keep the item and you get nothing. You may always remove the item before that time, but there are no Consignment Fee refunds.</p>
            <form action=\'consignmentMarket.php?action=add\' method=POST>
                <input type=hidden name=AID value=\'' . $ID . '\'>
                Price: <input type=text name=price value=\'0\'>
                <select name=currency type=dropdown>
                    <option value=cash>Cash</option>
                    <option value=tokens>Tokens of Respect</option>
                </select> &nbsp;
                <input type=submit value=Add><br>
            </form>
        ';
    }

    // Process the item and add to Market
    if ($price > 0) {
        $query = $db->query("SELECT iv.inv_itmexpire, iv.inv_id, iv.inv_itemid, i.itmname FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE inv_id = {$AID} and inv_userid = {$userId}");
        $row = mysqli_fetch_assoc($query);
        $curr = 'respect';
        if ($currency == 'cash') {
            $curr = 'money';
        }

        $fee = max(round($price / 10), 1);
        if ($fee > $user[$curr]) {
            print '
                <p>You do not have the funds to Consign this item.</p>
                <p><a href=\'consignmentMarket.php\'>return to the market</a></p>
            ';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE users SET {$curr} = {$curr} - {$fee} where userid = {$userId}");
        itemDelete($row['inv_id'], 1, $userId);
        $dura = 30;
        if ($row['inv_itmexpire'] > 0) {
            $dura = $row['inv_itmexpire'];
        }

        $db->query("INSERT INTO conMarket (cmItem, cmDaysLeft, cmQuantity, cmPrice, cmCurrency, cmExpire, cmConsignor, cmAddTime, cmBuyer, cmBuyTime) VALUES ({$row['inv_itemid']}, {$row['inv_itmexpire']}, 1, {$price}, '{$currency}', '{$dura}', {$userId}, unix_timestamp(), '', '')");
        print '
            Your ' . $row['itmname'] . ' has been added to consignment.</p>
            <p><a href=\'consignmentMarket.php\'>Visit the Market</a> or <a href=\'home.php\'>head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }
}

function buy(Database $db, Header $headers, array $user, int $userId, int $ID): void
{
    $query = $db->query("SELECT cm.cmCurrency, cm.cmPrice, cm.cmItem, cm.cmQuantity, cm.cmDaysLeft, cm.cmConsignor, i.itmname, i.itmid FROM conMarket cm LEFT JOIN items i ON i.itmid = cm.cmItem WHERE cmID = {$ID} AND cmExpire > 0");
    if (!mysqli_num_rows($query)) {
        print '
            <p>This item does not exist, no longer exists, or it was purchased while you were waiting in line.</p>
            <p><a href=\'consignmentMarket.php\'>Return</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    $curr = 'respect';
    if ($row['cmCurrency'] == 'cash') {
        $curr = 'money';
    }

    if ($row['cmPrice'] > $user[$curr]) {
        print '<p>You do not have the funds to buy this item.</p><p><a href=\'consignmentMarket.php\'>return to the market</a></p>';
        $headers->endpage();
        exit;
    }

    itemAdd($row['cmItem'], $row['cmQuantity'], $row['cmDaysLeft'], $userId, 0);

    $db->query("UPDATE users SET {$curr} = {$curr} - {$row['cmPrice']} where userid = {$userId}");
    $db->query("UPDATE users SET {$curr} = {$curr} + {$row['cmPrice']} where userid = {$row['cmConsignor']}");
    $db->query("UPDATE conMarket SET cmExpire = 0, cmBuyer = {$userId}, cmBuyTime = unix_timestamp() WHERE cmID = {$ID}");

    $purchase = moneyFormatter($row['cmPrice'], "") . ' tokens';
    if ($row['cmCurrency'] == 'cash') {
        $purchase = moneyFormatter($row['cmPrice']);
    }

    print '
        <p>Congratulations. You purchased the ' . $row['itmname'] . ' from the Market for ' . $purchase . '.</p>
        <p><a href=\'consignmentMarket.php\'>Back to the market</a> or <a href=\'home.php\'>head on home</a>.</p>
    ';

    logEvent($row['cmConsignor'], "Your {$row['itmname']} was sold on the Market for {$purchase}.");

    $qur = $db->query("SELECT userid, trackActionIP FROM users WHERE userid = {$row['cmConsignor']}");
    $ur = mysqli_fetch_assoc($qur);

    $db->query("INSERT INTO logsWealth (lwSender, lwSenderIP, lwReceiver, lwReceiverIP, lwAmount, lwTime, lwType, lwSource) VALUES ({$userId}, '{$user['trackActionIP']}', {$row['cmConsignor']}, '{$ur['trackActionIP']}', {$row['cmPrice']}, unix_timestamp(), '{$row['cmCurrency']}', 'market')");

    logItem($row['cmConsignor'], "{$ur['trackActionIP']}", $userId, "{$user['trackActionIP']}", "market", $row['itmid'], 1);
}

function remove(Database $db, Header $headers, int $userId, int $ID): void
{
    $query = $db->query("SELECT cm.cmItem, cm.cmDaysLeft, i.itmname FROM conMarket cm LEFT JOIN items i ON cm.cmItem = i.itmid WHERE cmID = {$ID} AND cmConsignor = {$userId} AND cmExpire > 0");
    if (!mysqli_num_rows($query)) {
        print '
            <p>This item does not exist, no longer exists, or you are not the owner and cannot remove it.</p>
            <p><a href=\'consignmentMarket.php\'>Return</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $row = mysqli_fetch_assoc($query);

    itemAdd($row['cmItem'], 1, $row['cmDaysLeft'], $userId, 0);

    $db->query("UPDATE conMarket SET cmExpire = 0, cmBuyer = {$userId}, cmBuyTime = unix_timestamp() WHERE cmID = {$ID}");

    print '
        <p>You have removed the ' . $row['itmname'] . ' from the market and sacrificed your Consignment Fee.</p>
        <p><a href=\'consignmentMarket.php\'>Back</a></p>
    ';
}

$application->header->endPage();
