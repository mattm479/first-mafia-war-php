<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action     = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$raffleid   = isset($_POST['raffleid']) ? mysql_num($_POST['raffleid']) : 0;
$tickets    = isset($_POST['tickets']) ? mysql_num($_POST['tickets']) : 0;

print '
    <h3>Mafia Raffle</h3>
    <p>The winning raffle ticket will be drawn daily around noon. Chances of winning are based entirely on the number of tickets purchased. Each ticket costs only 
';

if ($user['hospital'] > 1) {
    print '$24,000 from the candy stripers - what a deal!';
} else {
    print '$29,000.';
}

print '
    <p< (The numbers under tickets are the number you have/the total purchased so far.)</p>
    <div class=floatright>
        <br>
        <img src=\'assets/images/photos/raffle.jpg\' width=225 height=229 alt=Raffle>
    </div>
';

switch ($action) {
    case "buy":
        buy_ticket($db, $headers, $user, $userId, $raffleid, $tickets);
        break;
    default:
        index($db, $user, $userId);
        break;
}

function index(Database $db, array $user, int $userId): void
{
    print ' 
        <table width=65% cellspacing=0 cellpadding=2 class=table style=\'font-size:smaller;\'>
            <tr><td colspan=5>&nbsp;</td></tr>
            <tr>
                <th>Raffle</th>
                <th>Item to Win</th>
                <th>Days</th>
                <th style=\'text-align:center;\'>Tickets</th>
                <th style=\'text-align:center;\'>Action</th>
            </tr>
    ';

    if ($user['rankCat'] == 'Staff') {
        $query = $db->query("SELECT raID, raName, raItem FROM raffle WHERE raDaysLeft > 0 ORDER BY raDaysLeft");
    } else {
        $query = $db->query("SELECT raID, raName, raItem FROM raffle WHERE raDaysLeft > 0 AND raDaysLeft < 6 ORDER BY raDaysLeft");
    }

    while ($row = mysqli_fetch_assoc($query)) {
        $youtics = 0;
        $qcnt = $db->query("SELECT rtPurchaser FROM raffleTicket WHERE rtPurchaser = {$userId} AND rtRaffle = {$row['raID']}");
        while (mysqli_fetch_assoc($qcnt)) {
            $youtics += 1;
        }

        $tottics = 0;
        $qttt = $db->query("SELECT rtID FROM raffleTicket WHERE rtRaffle = {$row['raID']}");
        while (mysqli_fetch_assoc($qttt)) {
            $tottics += 1;
        }

        print '
            <tr>
                <td>' . $row['raName'] . '</td>
                <td>' . iteminfo($row['raItem']) . '</td>
                <td class=center>' . $row['raDaysLeft'] . '</td>
                <td class=center>' . $youtics . '/' . $tottics . '</td>
                <td class=center>
                    <form action=\'raffle.php?action=buy\' method=POST>
                        <input type=hidden name=mafiaid value=\'' . $userId . '\'>
                        <input type=hidden name=raffleid value=\'' . $row['raID'] . '\'>
                        <input type=text name=tickets value=\'1\' size=3>
                        <input type=submit value=Buy>
                    </form>
                </td>
            </tr>
        ';
    }

    print '</table>';
}

function buy_ticket(Database $db, Header $headers, array $user, int $userId, int $raffleid, int $tickets): void
{
    $multiplier = 29000;
    if ($user['hospital']) {
        $multiplier = 24000;
    }

    $price = $tickets * $multiplier;
    if ($user['money'] < $price) {
        print '<p>You do not have the cash for such a large ticket purchase. Don\'t mess with the bookies.</p>';
        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET money = money - {$price} where userid = {$userId}");
    for ($i = 0; $i < $tickets; $i++) {
        $db->query("INSERT INTO raffleTicket (rtRaffle, rtPurchaser) VALUES ({$raffleid}, {$userId})");
    }

    index($db, $user, $userId);
}

$headers->endpage();
