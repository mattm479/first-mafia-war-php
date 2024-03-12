<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn=1, $stff=0, $njl=1, $nhsp=1, $nlck=1);

$action = isset($_GET['act']) ? mysql_tex($_GET['act']) : '';
$autoId = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;

print '
   <h3>Harry Maher\'s Used Car Lot</h3>
   <p><em>Well, this is the first time the customer ever high-pressured the salesman.</em></p>
   <div class=floatright>
       <img src=\'assets/images/photos/usedCarLot.jpg\' width=247 height=338 alt=\'Used Car Lot\'>
   </div>
';

switch($action) {
   case 'buy':
       buy($db, $headers, $user, $userId, $autoId);
       break;
   case 'sell':
       sell($db, $headers, $user, $userId, $autoId);
       break;
   default:
       index($db, $user);
       break;
}

function index(Database $db, array $user): void
{
    print '<p>Welcome to the best place to buy cars anywhere in the world. Well, OK, the only place to buy cars anywhere in the world - it is a state of mind. We have a number of excellent cars here. Please pick the one you want and drive it home today!</p><p>Remember, your car and everything you buy for it, requires maintenance. Your banker will handle the payments (damn sticky fingers) but maintenance goes up a little every day you ignore your cars needs.</p>';

    if ($user['autoOwned'] > 0) {
        $qau = $db->query("SELECT auID, auName, auPrice FROM autos WHERE auID = {$user['autoOwned']}");
        $rau = mysqli_fetch_assoc($qau);
        $tradeIn = round($rau['auPrice'] * 0.6);

        print '
            <p>You own a fine car, the ' . $rau['auName'] . '. I cannot imagine you would want to sell it, but if you do, I will take ' . moneyFormatter($tradeIn) . ' in cash or trade. Oh well you know, depreciation man.</p>
            <p><a href=\'automotive.php?act=sell&ID='.$rau['auID'].'\'><strong>Sell your car</strong></a>.</p>
        ';

    }

    $carq = $db->query("SELECT auID, auName, auPrice FROM autos ORDER BY auID");

    print '<table width=60% cellpadding=3 cellspacing=0 class=table>';

    while($row = mysqli_fetch_assoc($carq)) {
        print '
            <tr>
                <td><img src=\'assets/images/autos/' . $row['auName'] . '.jpg\'></td>
                <td><strong>' . $row['auName'] . '</strong><br>' . moneyFormatter($row['auPrice']) . ' &nbsp; <- <a href=\'automotive.php?act=buy&ID=' . $row['auID'] . '\'>buy</a></td>
            </tr>
        ';
    }

    print '</table>';
}

function buy(Database $db, Header $headers, array $user, int $userId, int $autoId): void
{
    $qau = $db->query("SELECT auID, auName, auPrice FROM autos WHERE auID = {$autoId}");
    $rau = mysqli_fetch_assoc($qau);

    if ($rau['auPrice'] > $user['money']) {
        print '
            <p>You do not have enough cash to buy that car and they are not taking credit.</p>
            <p><a href=\'bank.php\'>Visit the bank</a> or <a href=\'automotive.php\'>buy a cheaper car</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($user['autoOwned'] > 0) {
        $auq = $db->query("SELECT auID, auPrice FROM autos WHERE auID = {$user['autoOwned']}");
        $aur = mysqli_fetch_assoc($auq);
        $tradeIn = round($aur['auPrice'] * 0.6);

        $db->query("UPDATE users SET money = money + {$tradeIn}, autoOwned = 0, autoMaint = 0, autoValue = 0 WHERE userid = {$userId}");
    }

    $db->query("UPDATE users SET money = money - {$rau['auPrice']}, autoOwned = {$rau['auID']}, autoValue = {$rau['auPrice']}, autoMaint = 1 WHERE userid = {$userId}");

    print '
        <p>Congratulations on your new car. You purchased the ' . $rau['auName'] . ' for ' . moneyFormatter($rau['auPrice']) . '</p>
        <p><a href=\'home.php\'>Drive home</a></p>
    ';
}

function sell(Database $db, Header $headers, array $user, int $userId, int $autoId): void
{
    $qau = $db->query("SELECT auID, auPrice FROM autos WHERE auID = {$autoId}");
    $rau = mysqli_fetch_assoc($qau);

    if ($rau['auID'] == $user['autoOwned']) {
        $tradeIn = round($rau['auPrice'] * 0.6);

        $db->query("UPDATE users SET money = money + {$tradeIn}, autoOwned = 0, autoMaint = 0, autoValue = 0 WHERE userid = {$userId}");

        print '
            <p>You sold your car for ' . moneyFormatter($tradeIn) . ' and no longer have the expense of maintenance. However, your energy will now recover more slowly as you have to walk everywhere.</p>
            <p><a href=\'home.php\'>Walk home</a> or <a href=\'automotive.php\'>buy another car</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    print '
        <p>You do not own that car. Stop trying to sell cars you do not own or I will have to call the Police.</p>
        <p><a href=\'automotive.php\'>Back to the lot</a>.</p>
    ';
}

$headers->endpage();
