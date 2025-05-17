<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$fid = isset($_GET['fid']) ? mysql_num($_GET['fid']) : 0;
$iid = isset($_GET['iid']) ? mysql_num($_GET['iid']) : 0;
$qty = isset($_GET['qty']) ? mysql_num($_GET['qty']) : 0;
$uid = isset($_GET['uid']) ? mysql_num($_GET['uid']) : 0;
$familyId = isset($_POST['famid']) ? mysql_num($_POST['famid']) : 0;
$inventoryId = isset($_POST['invid']) ? mysql_num($_POST['invid']) : 0;
$quantity = isset($_POST['quant']) ? mysql_num($_POST['quant']) : 0;
$recus = isset($_POST['recus']) ? mysql_num($_POST['recus']) : 0;
$recfa = isset($_POST['recfa']) ? mysql_num($_POST['recfa']) : 0;
$useid = isset($_POST['useid']) ? mysql_num($_POST['useid']) : 0;

switch ($action) {
    case 'equp':
        equip($application->db, $application->header, $application->user, $iid);
        break;
    case 'remo':
        remo($application->db, $application->header, $application->user, $iid);
        break;
    case 'sell':
        sell($application->db, $application->header, $application->user, $fid, $iid, $uid, $qty);
        break;
    case 'sen2':
        sen2($application->db, $application->header, $application->user, $familyId, $inventoryId, $quantity, $recfa, $recus, $useid);
        break;
    case 'use2':
        use2($application->db, $application->header, $application->user, $familyId, $inventoryId, $quantity, $useid, $recus);
        break;
    case 'util':
        util($application->db, $application->header, $application->user, $familyId, $iid, $quantity, $uid);
        break;
    default:
        info($application->db, $application->header, $iid);
        break;
}

function equip(Database $db, Header $headers, array $user, int $iid): void
{
    print '<h3>Equip Item</h3>';

    $row = mysqli_fetch_assoc($db->query("SELECT iv.inv_id, iv.inv_itemid, it.itmid, it.itmtype, it.itmLevel, it.itmReload, it.itmCombatType FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$iid} AND iv.inv_userid = {$user['userid']}"));
    $qu = $db->query("SELECT iv.inv_id, it.itmid FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_equip = 'yes' AND it.itmtype = 60 AND iv.inv_userid = {$user['userid']}");

    if ($row['itmtype'] == 60 && mysqli_num_rows($qu) >= 1) {
        print '
            <p>You cannot have more than one protective item equipped at a time.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($row['itmLevel'] > $user['level']) {
        print '
            <p>You cannot equip that weapon. It is too complex for your Mafioso to use effectively. You need to be at least ' . $row['itmLevel'] . ' level to use it correctly.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if (!$row['inv_itemid']) {
        print '
            <p>You do not own that item and cannot use even one.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($user['money'] <= $row['itmReload']) {
        print '
            <p>You do not have enough money to ready that gear. It\'s not that expensive but fighting takes cash.</p>
            <p><a href=\'bank.php\'>Head to the bank</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET money = money - {$row['itmReload']} WHERE userid = {$user['userid']}");
    itemDelete($row['inv_id'], 1, $user['userid'], 0);
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES ({$row['itmid']}, 0, {$user['userid']}, 0, 1, 'yes')");

    if ($row['itmtype'] == 60) {
        print '<p>By preparing your ' . itemInfo($row['itmid']) . ' you have given yourself some protection against ' . itemCombatType($row['itmCombatType']) . ' attacks. It only cost you ' . moneyFormatter($row['itmReload']) . ' to be prepared.</p>';
    }

    if ($row['itmtype'] == 65) {
        print '<p>You remove the last safeties and prepare your ' . itemInfo($row['itmid']) . ' for use.  It only works once, but is a very effective ' . itemCombatType($row['itmCombatType']) . ' weapon. It only cost you ' . moneyFormatter($row['itmReload']) . ' to be prepared.</p>';
    }

    if ($row['itmtype'] == 70) {
        print '<p>You clean and load your weapon, then remove the safety on your ' . itemInfo($row['itmid']) . '.  It will need to be reloaded and cleaned when you\'re done, but for now your ' . itemCombatType($row['itmCombatType']) . ' weapon is ready. It only cost you ' . moneyFormatter($row['itmReload']) . ' to be prepared.</p>';
    }

    if ($row['itmtype'] == 80) {
        print '<p>You wipe off the blood from the last battle and repair the grip on your ' . itemInfo($row['itmid']) . '.  You are ready to do ' . itemCombatType($row['itmCombatType']) . ' damage to your enemies. It only cost you ' . moneyFormatter($row['itmReload']) . ' to be prepared.</p>';
    }

    print '<p><a href=\'mafiosoResults.php?attack=1\'>Find a fight</a> or <a href=\'home.php\'>return home</a>.</p>';
}

function remo(Database $db, Header $headers, array $user, int $iid): void
{
    print '<h3>Unequip Item</h3>';

    $row = mysqli_fetch_assoc($db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_id = {$iid} AND inv_userid = {$user['userid']}"));

    if (!$row['inv_itemid']) {
        print '
            <p>That item is not equipped so you cannot unequip it.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    itemDelete($row['inv_id'], 1, $user['userid'], 0);
    itemAdd($row['inv_itemid'], 0, $user['userid'], 0, 1);

    print '
        <p>You have successfully safetied or removed your combat ready gear.  Good luck without it!</p>
        <p><a href=\'home.php\'>Return home</a>.</p>
    ';
}

function info(Database $db, Header $headers, int $iid): void
{
    print '
        <h3>Item Information</h3>
        <div class=floatright>
            <img src=\'assets/images/photos/tableofGuns.jpg\' width=300 height=227 alt=\'Table of Guns\'>
        </div>
        <p>Some items have subtleties which are not described here.</p>
    ';

    $q = $db->query("SELECT itmid, itmusage, itmname, itmtype, itmdesc, itmCombatType, itmCombat, itmLevel, itmExpire, itmStore, itmBasePrice FROM items WHERE itmid = {$iid}");
    if ($iid == 0 || !mysqli_num_rows($q)) {
        print '
            <p>That is a very interesting item but you have no clue what it does.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $id = mysqli_fetch_assoc($q);

    print '
        <h5 title=\'' . $id['itmusage'] . '\'>' . $id['itmname'] . ' <span class=light>(' . itemType($id['itmtype']) . ')</span></h5>
        <p>' . $id['itmdesc'] . '<br>
    ';

    if ($id['itmCombat'] && $id['itmCombatType'] != 60) {
        print 'This weapon does ' . $id['itmCombat'] . ' damage of type ' . itemCombatType($id['itmCombatType']) . '. You must be ' . $id['itmLevel'] . ' level to use it.</p>';
    } elseif ($id['itmCombat'] && $id['itmCombatType'] == 60) {
        print 'This also protects against ' . $id['itmCombat'] . ' damage of type ' . itemCombatType($id['itmCombatType']) . ' if equipped as protection.</p>';
    }

    if ($id['itmExpire'] > 0) {
        print '<p>This item will expire in ' . $id['itmExpire'] . ' days or less. The number in parenthesis (as seen in your inventory) represent how many days you have left before yours is gone.</p>';
    }

    if ($id['itmStore'] == 0) {
        print '<p>This can be difficult to obtain and cannot be easily purchased in most stores.<br>';
    }

    if ($id['itmid'] == 5) {
        print 'You may sell this item anytime to passing merchants for ' . moneyFormatter($id['itmBasePrice']) . '</p>';
    } elseif ($id['itmBasePrice'] > 0) {
        print 'You may sell this item anytime to passing merchants for ' . moneyFormatter(round($id['itmBasePrice'] * 0.8)) . '</p>';
    } else {
        print 'This item has no value for passing merchants.</p>';
    }
}

function sell(Database $db, Header $headers, array $user, int $fid, int $iid, int $uid, int $qty): void
{
    $query = new mysqli_result($db);
    if ($uid > 0) {
        $query = $db->query("SELECT iv.inv_id, iv.inv_itemid, it.itmBasePrice, it.itmid FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$iid} AND iv.inv_userid = {$uid}");
    } elseif ($fid > 0) {
        $query = $db->query("SELECT iv.inv_id, iv.inv_itemid, it.itmBasePrice, it.itmid FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$iid} AND iv.inv_famid = {$fid}");
    }

    if (mysqli_num_rows($query) == 0) {
        print '
            <p>You do not own that item.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($fid > 0 && $user['gang'] != $fid) {
        print '
            <p>You are not a member of the Family and so cannot sell Family goods.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    if ($row['itmBasePrice'] < 1) {
        print '
            <p>This item cannot be sold.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($qty > 0) {
        $payment = $qty * (round($row['itmBasePrice'] * 0.8));
        if ($row['itmid'] == 5) {
            $payment = $qty * $row['itmBasePrice'];
        }

        itemDelete($iid, $qty, $uid, $fid);

        print '
            <h3>Sell Item</h3>
            <p>You sold ' . itemInfo($row['inv_itemid']) . ' x' . $qty . ' for ' . moneyFormatter($payment) . ' . </p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        if ($uid > 0) {
            logItem($uid, "{$user['trackActionIP']}", $uid, $user['trackActionIP'], 'sold', $row['inv_itemid'], $qty);
            $db->query("UPDATE users SET money = money + {$payment} WHERE userid = {$uid}");
        } else if ($fid > 0) {
            $db->query("UPDATE family SET famVaultCash = famVaultCash + {$payment} WHERE famID = {$fid}");
        }

        $headers->endpage();
        exit;
    }

    print '
        <h3>Sell Item</h3>
        <p>You are selling your ' . itemInfo($row['inv_itemid']) . '. How many would you like to sell?</p>
        <p>You own ' . $row['inv_qty'] . '.</p>
        <form action=\'items.php\' method=GET>
            <input type=hidden name=action value=\'sell\'>
            <input type=hidden name=iid value=\'' . $iid . '\'>
            <input type=hidden name=uid value=\'' . $uid . '\'>
            <input type=hidden name=fid value=\'' . $fid . '\'>
            Quantity: <input type=text size=10 name=qty value=\'1\'> &nbsp;&nbsp;
            <input type=submit value=\'Sell Items\'>
        </form>
    ';
}

function sen2(Database $db, Header $headers, array $user, int $familyId, int $inventoryId, int $quantity, int $recfa, int $recus, int $useid): void
{
    print '<h3>Sending Item</h3>';

    $query = new mysqli_result($db);
    if ($useid > 0) {
        $query = $db->query("SELECT iv.*, it.* FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$inventoryId} AND iv.inv_userid = {$useid}");
    } else if ($familyId > 0) {
        $query = $db->query("SELECT iv.*, it.* FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$inventoryId} AND iv.inv_famid = {$familyId}");
    }

    if (mysqli_num_rows($query) == 0) {
        print '
            <p>You do not own that item.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    if ($familyId > 0 && $user['gang'] != $familyId) {
        print '
            <p>You are not a member of the Family and so cannot transfer Family goods.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($quantity > $row['inv_qty'] || $quantity < 1) {
        print '
            <p>You are trying to send more than you have.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $qm = new mysqli_result($db);
    if ($recus > 0) {
        $qm = $db->query("SELECT userid, trackActionIP FROM users WHERE userid = {$recus}");
        if (mysqli_num_rows($qm) == 0) {
            print '
                <p>That Mafioso does not exist. Please try another recipient.</p>
                <p><a href=\'home.php\'>Head on home</a>.</p>
            ';

            $headers->endpage();
            exit;
        }

        $rm = mysqli_fetch_assoc($qm);
        itemDelete($inventoryId, $quantity, $useid, $familyId);
        itemAdd($row['inv_itemid'], $quantity, $row['inv_itmexpire'], $recus, 0);

        print '
            <p>You sent ' . itemInfo($row['inv_itemid']) . ' x' . $quantity . ' to ' . mafioso($recus) . '.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';
    } else if ($recfa > 0) {
        $qm = $db->query("SELECT famID FROM family WHERE famID = {$recfa}");
        if (mysqli_num_rows($qm) == 0) {
            print '
                <p>That Family does not exist. Please try another recipient.</p>
                <p><a href=\'home.php\'>Head on home</a>.</p>
            ';

            $headers->endpage();
            exit;
        }

        $rm = mysqli_fetch_assoc($qm);
        itemDelete($inventoryId, $quantity, $useid, $familyId);
        itemAdd($row['inv_itemid'], $quantity, $row['inv_itmexpire'], 0, $recfa);

        print '
            <p>You sent ' . itemInfo($row['inv_itemid']) . ' x' . $quantity . ' to ' . familyName($recfa) . '.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $timestamp = strtotime(date("Y-m-d H:i:s"));
        $description = mafiosoLight($useid) . ' donated ' . itemInfo($row['inv_itemid']) . ' x' . $quantity;
        $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES({$recfa}, {$timestamp}, '{$description}');");
    }

    if ($useid > 0 && $recus > 0) {
        logEvent($recus, " " . mafiosoLight($useid) . " sent you the " . iteminfo($row['inv_itemid']) . " x{$quantity}");
        logItem($useid, "{$user['trackActionIP']}", $recus, "{$rm['trackActionIP']}", 'transfer', $row['inv_itemid'], $quantity);
    } else if ($useid > 0 && $recfa > 0) {
        logEvent($recfa, " " . mafiosoLight($useid) . " sent you the " . iteminfo($row['inv_itemid']) . " x{$quantity}");
        logItem($useid, "{$user['trackActionIP']}", 0, $recfa, 'family', $row['inv_itemid'], $quantity);
    } else if ($familyId > 0) {
        logEvent($recus, "The " . familyName($familyId) . " Family sent you the " . iteminfo($row['inv_itemid']) . " x{$quantity}");
        logItem(0, $familyId, $recus, "{$rm['trackActionIP']}", 'family', $row['inv_itemid'], $quantity);
    }
}

function use2(Database $db, Header $headers, array $user, int $familyId, int $inventoryId, int $quantity, int $useid, int $recus): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT iv.inv_id, iv.inv_itemid, iv.inv_qty, it.itmid, it.itmtype, it.effect1_on, it.effect2_on, it.effect3_on, it.effect1, it.effect2, it.effect3, it.itmname FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$inventoryId} AND iv.inv_userid = {$useid}"));

    print '<h3>Using Item</h3>';

    if (!$row['inv_itemid']) {
        print '
            <p>You do not own that item and cannot use even one.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if (!$row['effect1_on'] && !$row['effect2_on'] && !$row['effect3_on'] && !in_array(array(90, 124, 96, 112), $row['itmid'])) {
        print '
            <h3>No Effect</h3>
            <p>You cannot use this item in this way.</p>
            <p><a href=\'home.php\'>Home</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($quantity > $row['inv_qty'] || $quantity < 1) {
        print '
            <p>You are trying to use more than you have.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $bonus = $quantity;
    if ($row['itmtype'] == 5) {
        itemAdd(90, $bonus, 0, $useid, 0);
        itemAdd(124, $bonus, 0, $useid, 0);
        logEvent($useid, 'You gained ' . $bonus . ' Bomb Box (Car bomb and Tear gas) to go with your DP.');
    }

    switch ($row['itmid']) {
        case 9:
            print '<p>Most of your increased Agility from this beer will turn to the shakes in the next half hour, so use it quickly.</p>';
            $tr = mysqli_fetch_assoc($db->query("SELECT agility FROM userstats WHERE userid = {$useid}"));
            $add1 = (0.5 * $quantity);
            $add = ($tr['agility'] * $add1);
            $db->query("UPDATE userstats SET agilityTemp = agilityTemp + {$add} WHERE userid = {$useid}");
            break;

        case 11:
            print '<p>The College student washes your car well - and you are brought current on your maintenance!</p>';
            $db->query("UPDATE users SET autoMaint = 1 WHERE userid = {$useid}");
            break;

        case 16:
            print '<p>Most of your increased Guard from this beer will slowly fade in the next half hour, so use it quickly.</p>';
            $tr = mysqli_fetch_assoc($db->query("SELECT guard FROM userstats WHERE userid = {$useid}"));
            $add1 = (0.5 * $quantity);
            $add = ($tr['guard'] * $add1);
            $db->query("UPDATE userstats SET guardTemp = guardTemp + {$add} WHERE userid = {$useid}");
            break;

        case 18:
            print '<p>Most of your increased Strength from this beer will slowly fade in the next half hour, so use it quickly.</p>';
            $tr = mysqli_fetch_assoc($db->query("SELECT strength FROM userstats WHERE userid = {$useid}"));
            $add1 = (0.5 * $quantity);
            $add = ($tr['strength'] * $add1);
            $db->query("UPDATE userstats SET strengthTemp = strengthTemp + {$add} WHERE userid = {$useid}");
            break;

        case 30:
            print '<p>Your kind donation allows you access to all your Funds.</p>';
            $db->query("UPDATE users SET moneySavingsFlag = 0, moneyTreasuryFlag = 0, moneyInvestFlag = 0 WHERE userid = {$useid}");
            break;

        case 46:
            print '<p>The increased protection the bodybuard provides will slowly fade in the next half hour, so get out there.</p>';
            $db->query("UPDATE userstats SET guardTemp = guardTemp + 50000000 WHERE userid = {$useid}");
            break;

        case 65:
            print '<p>The increased Strength from the soup will slowly fade in the next half hour, so use it quickly.</p>';
            $tr = mysqli_fetch_assoc($db->query("SELECT strength FROM userstats WHERE userid = {$useid}"));
            $add1 = (0.1 * $quantity);
            $add = ($tr['strength'] * $add1);
            $db->query("UPDATE userstats SET strengthTemp = strengthTemp + {$add} WHERE userid = {$useid}");
            break;

        case 71:
            $rdag = mysqli_fetch_assoc($db->query("SELECT inv_id, inv_qty FROM inventory WHERE inv_userid = {$useid} AND inv_itemid = 602"));
            for ($i = 0; $i < $quantity; $i++) {
                if ($rdag['inv_qty'] > 0) {
                    print '<p>You sell your +2 Dagger to the Swell Guy and he takes it off your hands. You pocket the $100 thoughtfully. It is not much, but it gets rid of that dagger. You wonder what he does with them...</p>';
                    itemDelete($rdag['inv_id'], 1, $useid);
                } else {
                    print '<p>You do not have that many +2 Daggers to sell. The Swell Guy is not so swell when he realizes you are wasting his time. He leaves without another word. You realize though that he left his wallet. $100, sweet!</p>';
                }
            }
            break;

        case 74:
            print '
                <p>You are using a False Passport. It allows you to travel anywhere you like if you have the cash. Nice.<br>So where do you want to go?</p>
                <p>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=1\'>Palermo</a><br>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=10\'>Rome</a><br>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=25\'>Monte Carlo</a><br>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=50\'>New York</a><br>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=100\'>Chicago</a><br>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=250\'>Montreal</a><br>
                    <a href=\'airport.php?action=fly&fp=' . $row['inv_id'] . '&destination=500\'>Caracas</a><br>
                </p>
            ';

            $headers->endpage();
            exit;
            break;

        case 75:
            print '<p>You have rung the Cowbell. You gain $4,444, 4 Tokens of Respect and 44 IQ. You also gain Donator Status for an additional 4 days!</p>';
            $add = (4 * $quantity);
            $db->query("UPDATE users SET donatordays = donatordays + {$add} WHERE userid = {$useid}");
            break;

        case 82:
            if ($user['level'] > 250) {
                print '<p>Nice person that you are, for having helped a new player down on their luck, you get a token of respect along with the $10 in the can.</p>';
                $db->query("UPDATE users SET respect = respect + {$quantity} WHERE userid = {$useid}");
            }
            break;

        case 90:
            $rloc = mysqli_fetch_assoc($db->query("SELECT location FROM users WHERE userid = {$recus}"));
            if ($rloc['location'] == 42 || $user['location'] == 42) {
                print '<p>One of you is hiding in a Bomb Shelter. I\'m not going to say who it is, but if you see only cement block around you I bet you can guess. You cannot fight through protection designed to stop a nuclear blast with your little bomb. Sorry, but you just have to wait until both of you are breathing unfiltered air.</p>';

                $headers->endpage();
                exit;
            }

            print '<p>You are planting a car bomb. This is a high risk business, but there is no turning back now. Geeze, I hope this thing doesn\'t go off too soon...</p>';

            $db->query("UPDATE users SET hp = 1, hospital = hospital + 110 + jail, hjReason = 'Car bomb planted by an enemy', autoOwned = 0, autoMaint = 0, autoValue = 0, jail = 0 WHERE userid = {$recus}");

            $qnv = $db->query("SELECT iv.inv_id, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$recus} AND i.itmtype = 3");
            while ($rnv = mysqli_fetch_assoc($qnv)) {
                if ($rnv['inv_id'] > 0) {
                    itemDelete($rnv['inv_id'], 1, $recus, 0);
                }
            }

            logEvent($recus, 'You turned the key... and blew up along with your car.');
            logEvent(1, mafiosoLight($user['userid']) . ' just carbombed ' . mafiosoLight($recus));
            itemDelete($inventoryId, 1, $user['userid'], 0);

            print '<br><br><p><strong>You did it!</strong><br>' . mafioso($recus) . ' was taken to the hospital for 110 minutes and their car has been destroyed - along with all their gear. Nicely done!</p>';

            $headers->endpage();
            exit;
            break;

        case 96:
            $qav = $db->query("SELECT userid FROM users WHERE gang = {$familyId} AND jail = 0 AND hospital = 0");
            $available = mysqli_num_rows($qav);
            $rnd = 3 - $available;
            if ($rnd >= 1) {
                $qinv = $db->query("SELECT inv.inv_id FROM inventory inv LEFT JOIN items i ON inv.inv_itemid = i.itmid WHERE inv.inv_famid = {$familyId} AND i.itmtype NOT IN (5, 50) ORDER BY RAND() LIMIT 1");
                if (mysqli_num_rows($qinv) == 0) {
                    print '<p>The thief returns empty handed.  Apparently that Family is poorer than you thought!</p>';

                    $headers->endpage();
                    exit;
                }

                $qinv = $db->query("SELECT inv.inv_id, inv.inv_itemid FROM inventory inv LEFT JOIN items i ON inv.inv_itemid = i.itmid WHERE inv.inv_famid = {$familyId} AND i.itmtype NOT IN (1, 7) ORDER BY RAND() LIMIT 3");
                while ($rinv = mysqli_fetch_assoc($qinv)) {
                    itemDelete($rinv['inv_id'], 1, 0, $familyId);
                    itemAdd($rinv['inv_itemid'], 1, 0, $user['userid'], 0);
                    logEvent($user['userid'], "You stole " . itemInfo($rinv['inv_itemid']) . " from " . familyName($familyId) . ".");
                    $item = itemInfo($rinv['inv_itemid']);
                    $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES ({$familyId}, unix_timestamp(), '{$item} was stolen by " . mafiosoLight($user['userid']) . " while you were out.')");
                }
            } else {
                print '<p>Their Family caught your thief and you failed.</p>';
            }
            break;

        case 111:
            $famr = $quantity * 10;
            $lock = $quantity * 2;
            print '<p>You have called in the Fire Brigade. A lot of sirens and screeching tires later and your Family has received 10 Family Respect. Further, for the next hour the Brigade will hose down anyone trying to come near your house so you are on lockdown, free, for an hour. Don\'t have a family? Oops, the Brigade is now protecting your hovel from the rats. Damn.</p>';
            $db->query("UPDATE family SET famRespect = famRespect + {$famr} WHERE famID = {$user['gang']}");
            $qus = $db->query("SELECT userid FROM users WHERE gang = {$user['gang']}");
            while ($rus = mysqli_fetch_assoc($qus)) {
                $db->query("UPDATE users SET gangLockdown = gangLockdown + {$lock} WHERE userid = {$rus['userid']}");
            }
            break;

        case 112:
            $qus = $db->query("SELECT userid FROM users WHERE gang = {$familyId}");
            while ($rus = mysqli_fetch_assoc($qus)) {
                $db->query("UPDATE users SET gangLockdown = gangLockdown - 4 WHERE userid = {$rus['userid']}");
                $db->query("UPDATE users SET gangLockdown = 0 WHERE userid = {$rus['userid']} AND gangLockdown < 1");
            }

            $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES({$familyId}, unix_timestamp(), '" . mafiosoLight($user['userid']) . " sent in the Midnight Bomber and reduced your lockdown.')");
            break;

        case 113:
            print '<p>Most of your increased Strength from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT strength FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 60) * $quantity;
            $add = ($tr['strength'] * $add1);

            $db->query("UPDATE userstats SET strengthTemp = strengthTemp + {$add} WHERE userid = {$useid}");
            break;

        case 114:
            print '<p>Most of your increased Agility from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT agility FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 60) * $quantity;
            $add = ($tr['agility'] * $add1);

            $db->query("UPDATE userstats SET agilityTemp = agilityTemp + {$add} WHERE userid = {$useid}");
            break;

        case 115:
            print '<p>Most of your increased Guard from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT guard FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 60) * $quantity;
            $add = ($tr['guard'] * $add1);

            $db->query("UPDATE userstats SET guardTemp = guardTemp + {$add} WHERE userid = {$useid}");
            break;

        case 116:
            print '<p>Most of your increased Strength from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT strength FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 30) * $quantity;
            $add = ($tr['strength'] * $add1);

            $db->query("UPDATE userstats SET strengthTemp = strengthTemp + {$add} WHERE userid = {$useid}");
            break;

        case 117:
            print '<p>Most of your increased Guard from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT guard FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 30) * $quantity;
            $add = ($tr['guard'] * $add1);

            $db->query("UPDATE userstats SET guardTemp = guardTemp + {$add} WHERE userid = {$useid}");
            break;

        case 118:
            print '<p>Most of your increased Agility from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT agility FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 30) * $quantity;
            $add = ($tr['agility'] * $add1);

            $db->query("UPDATE userstats SET agilityTemp = agilityTemp + {$add} WHERE userid = {$useid}");
            break;

        case 119:
            print '<p>Most of your increased Agility, Guard and Strength from this sweet will turn to the fat in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT agility, guard, strength FROM userstats WHERE userid = {$useid}"));
            $add1 = ($user['comRank'] / 30) * $quantity;
            $adda = ($tr['agility'] * $add1);
            $addg = ($tr['guard'] * $add1);
            $adds = ($tr['strength'] * $add1);

            $db->query("UPDATE userstats SET agilityTemp = agilityTemp + {$adda}, guardTemp = guardTemp + {$addg}, strengthTemp = strengthTemp + {$adds} WHERE userid = {$useid}");
            break;

        case 122:
            print '<p>Most of your increased Agility, Guard and Strength from this beer will turn to the shakes in the next half hour, so use it quickly.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT agility, guard, strength FROM userstats WHERE userid = {$useid}"));
            $add1 = (0.5 * $quantity);
            $addagi = ($tr['agility'] * $add1);
            $addgua = ($tr['guard'] * $add1);
            $addstr = ($tr['strength'] * $add1);

            $db->query("UPDATE userstats SET agilityTemp = agilityTemp + {$addagi}, guardTemp = guardTemp + {$addgua}, strengthTemp = strengthTemp + {$addstr} WHERE userid = {$useid}");
            break;

        case 124:
            $rloc = mysqli_fetch_assoc($db->query("SELECT location FROM users WHERE userid = {$recus}"));
            if ($rloc['location'] == 42 || $user['location'] == 42) {
                print '<p>One of you is hiding in a Bomb Shelter. I\'m not going to say who it is, but if you see only cement block around you I bet you can guess. You cannot fight through protection designed to stop a nuclear blast with your little bomb. Sorry, but you just have to wait until both of you are breathing unfiltered air.</p>';

                $headers->endpage();
                exit;
            }

            print '<p>You have thrown a tear gas canister at ' . mafioso($recus) . '. Now get out there and hit them before they get their wits about them.</p>';

            $tr = mysqli_fetch_assoc($db->query("SELECT agility, guard, strength FROM userstats WHERE userid = {$recus}"));
            $add1 = (0.3 * $quantity);
            $addagi = ($tr['agility'] * $add1);
            $addgua = ($tr['guard'] * $add1);
            $addstr = ($tr['strength'] * $add1);

            $db->query("UPDATE userstats SET agilityTemp = agilityTemp - {$addagi}, guardTemp = guardTemp - {$addgua}, strengthTemp = strengthTemp - {$addstr} WHERE userid = {$recus}");
            $db->query("UPDATE users SET hp = hp - level WHERE userid = {$recus}");
            $db->query("UPDATE users SET hp = 1 WHERE userid = {$recus} AND hp < 1");

            logEvent($recus, 'You were sipping coffee when ' . mafioso($useid) . ' threw tear gas at you.');
            itemDelete($inventoryId, 1, $user['userid'], 0);

            $headers->endpage();
            exit;
            break;

        case 301:
            print '<p>You have received DP 1, <em>Services</em>. You gain a ' . itemInfo(636) . ', a ' . itemInfo(632) . ', a ' . itemInfo(627) . ' and even a ' . itemInfo(626) . ' to wear around town.</p>';

            itemAdd(636, $quantity, 0, $useid, 0);
            itemAdd(632, $quantity, 0, $useid, 0);
            itemAdd(627, $quantity, 0, $useid, 0);
            itemAdd(626, $quantity, 0, $useid, 0);

            break;

        case 302:
            print '<p>You have received DP 2, <em>Regular Membership</em>. You gain $20,000, 20 Tokens of Respect and 60 IQ. You also gain Donator Status for an additional 30 days!</p>';

            $add = (30 * $quantity);

            $db->query("UPDATE users SET donatordays = donatordays + {$add} WHERE userid = {$useid}");
            break;

        case 303:
            print '<p>You have received DP 3, <em>Supporting Membership</em>. You gain $40,000, 40 Tokens of Respect and 120 IQ. You also gain Donator Status for an additional 60 days!</p>';

            $add = (60 * $quantity);

            $db->query("UPDATE users SET donatordays = donatordays + {$add} WHERE userid = {$useid}");
            break;

        case 304:
            print '<p>You have received DP 4, <em>Preferred Membership</em>. You gain $60,000, 60 Tokens of Respect and 240 IQ. You also gain Donator Status for an additional 90 days!</p>';

            $add = (90 * $quantity);

            $db->query("UPDATE users SET donatordays = donatordays + {$add} WHERE userid = {$useid}");
            break;

        case 305:
            print '<p>You have received DP 5, <em>Flavor of the Month</em>. By opening this package you get this months flavor for the next 30 days. Enjoy!</p>';

            itemAdd(625, 30, $useid, 0, $quantity);
            break;

        case 306:
            print '<p>A present from the <em>Italian Bakery</em> just for you!  Wow - so much sugar in one small package.  Enjoy it while it lasts!!</p>';

            $add4 = $quantity * 4;
            $add2 = $quantity * 2;

            itemAdd(113, $add4, 0, $useid, 0);
            itemAdd(114, $add4, 0, $useid, 0);
            itemAdd(115, $add4, 0, $useid, 0);
            itemAdd(116, $add2, 0, $useid, 0);
            itemAdd(117, $add2, 0, $useid, 0);
            itemAdd(118, $add2, 0, $useid, 0);
            itemAdd(119, $quantity, 0, $useid, 0);
            break;

        case 311:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 6s, <em>Statistic Boost</em>.</p>';

            itemAdd(310, $add, 0, $useid, 0);
            break;

        case 312:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 6s, <em>Statistic Boost</em>.</p>';

            itemAdd(310, $add, 0, $useid, 0);
            break;

        case 313:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 6s, <em>Statistic Boost</em>.</p>';

            itemAdd(310, $add, 0, $useid, 0);
            break;

        case 314:
            print '<p>You have received DP 7, <em>Crime Pays</em>. By opening this package you get a quick $70,000,000 a ' . itemInfo(10) . ' and a ' . itemInfo(637) . '.</p>';

            itemAdd(10, $quantity, 0, $useid, 0);
            itemAdd(637, $quantity, 0, $useid, 0);
            break;

        case 315:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 7s, <em>Crime Pays</em>.</p>';

            itemAdd(314, $add, 0, $useid, 0);
            break;

        case 316:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 7s, <em>Crime Pays</em>.</p>';

            itemAdd(314, $add, 0, $useid, 0);
            break;

        case 317:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 7s, <em>Crime Pays</em>.</p>';

            itemAdd(314, $add, 0, $useid, 0);
            break;

        case 318:
            print '<p>You have received DP 8, <em>Respect can be bought</em>. You gain 101 Tokens of Respect and a ' . itemInfo(63) . ' to wash it down.</p>';

            itemAdd(63, $quantity, 0, $useid, 0);
            break;

        case 319:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 8s, <em>Respect can be bought</em>.</p>';

            itemAdd(318, $add, 0, $useid, 0);
            break;

        case 320:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 8s, <em>Respect can be bought</em>.</p>';

            itemAdd(318, $add, 0, $useid, 0);
            break;

        case 321:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 8s, <em>Respect can be bought</em>.</p>';

            itemAdd(318, $add, 0, $useid, 0);
            break;

        case 322:
            print '<p>You have received DP 9, <em>Knowledge is Power</em>. You gain 303 IQ and a couple ' . itemInfo(28) . ' to help you advance your skills.</p>';

            $add = (2 * $quantity);

            itemAdd(28, $add, 0, $useid, 0);
            break;

        case 323:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 9s, <em>Knowledge is Power</em>.</p>';

            itemAdd(322, $add, 0, $useid, 0);
            break;

        case 324:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 9s, <em>Knowledge is Power</em>.</p>';

            itemAdd(322, $add, 0, $useid, 0);
            break;

        case 325:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 9s, <em>Knowledge is Power</em>.</p>';

            itemAdd(322, $add, 0, $useid, 0);
            break;

        case 326:
            print '<p>You have received DP 10, <em>Self Improvement</em>. Self Improvement is masturbation, but you need your fix.  You gain ' . itemInfo(305) . ', ' . itemInfo(310) . ', ' . itemInfo(314) . ', ' . itemInfo(318) . ', and ' . itemInfo(322) . '. Wow!</p>';

            itemAdd(305, $quantity, 0, $useid, 0);
            itemAdd(310, $quantity, 0, $useid, 0);
            itemAdd(314, $quantity, 0, $useid, 0);
            itemAdd(318, $quantity, 0, $useid, 0);
            itemAdd(322, $quantity, 0, $useid, 0);
            break;

        case 330:
            print '<p>You have received DP 11, <em>Pack of Beer</em>. Beer is good food. The Beer Cheese Soup is an added bonus too.</p>';

            $add = (4 * $quantity);

            itemAdd(9, $add, 0, $useid, 0);
            itemAdd(16, $add, 0, $useid, 0);
            itemAdd(18, $add, 0, $useid, 0);
            itemAdd(65, $add, 0, $useid, 0);
            break;

        case 331:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 11s, <em>Pack of Beer</em>.</p>';

            itemAdd(330, $add, 0, $useid, 0);
            break;

        case 332:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 11s, <em>Pack of Beer</em>.</p>';

            itemAdd(330, $add, 0, $useid, 0);
            break;

        case 333:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 11s, <em>Pack of Beer</em>.</p>';

            itemAdd(330, $add, 0, $useid, 0);
            break;

        case 334:
            print '<p>You have received DP 12, <em>Coffee Roastery</em>. Preparing for a long night, you have picked up 20 ' . itemInfo(68) . ', 10 ' . itemInfo(56) . ', 5 ' . itemInfo(57) . ', and 2 ' . itemInfo(64) . '</p>';

            $add20 = (20 * $quantity);
            $add10 = (10 * $quantity);
            $add5 = (5 * $quantity);
            $add2 = (2 * $quantity);

            itemAdd(68, $add20, 0, $useid, 0);
            itemAdd(56, $add10, 0, $useid, 0);
            itemAdd(57, $add5, 0, $useid, 0);
            itemAdd(64, $add2, 0, $useid, 0);
            break;

        case 335:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 12s, <em>Coffee Roastery</em>.</p>';

            itemAdd(334, $add, 0, $useid, 0);
            break;

        case 336:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 12s, <em>Coffee Roastery</em>.</p>';

            itemAdd(334, $add, 0, $useid, 0);
            break;

        case 338:
            print '<p>You have received DP 13, <em>Basket of Wine</em>. Preparing for pleasant afternoon, you have picked up 8 ' . itemInfo(63) . ', 5 ' . itemInfo(14) . ', 2 ' . itemInfo(55) . ', and a ' . itemInfo(627) . '</p>';

            $add8 = (8 * $quantity);
            $add5 = (5 * $quantity);
            $add2 = (2 * $quantity);

            itemAdd(63, $add8, 0, $useid, 0);
            itemAdd(14, $add5, 0, $useid, 0);
            itemAdd(55, $add2, 0, $useid, 0);
            itemAdd(627, $quantity, 2, $useid, 0);
            break;

        case 339:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 13s, <em>Basket of Wine</em>.</p>';

            itemAdd(338, $add, 0, $useid, 0);
            break;

        case 340:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 13s, <em>Basket of Wine</em>.</p>';

            itemAdd(338, $add, 0, $useid, 0);
            break;

        case 341:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 13s, <em>Basket of Wine</em>.</p>';

            itemAdd(338, $add, 0, $useid, 0);
            break;

        case 342:
            print '<p>You have received DP 14, <em>Case of Whiskey</em>. Preparing for an evening out, you have picked up 8 ' . itemInfo(70) . ', 5 ' . itemInfo(17) . ', and 3 ' . itemInfo(62) . '.</p>';

            $add8 = (8 * $quantity);
            $add5 = (5 * $quantity);
            $add3 = (3 * $quantity);

            itemAdd(70, $add8, 0, $useid, 0);
            itemAdd(17, $add5, 0, $useid, 0);
            itemAdd(62, $add3, 0, $useid, 0);
            break;

        case 343:
            $add = (3 * $quantity);

            print '<p>You have received ' . $add . ' DP 14s, <em>Case of Whiskey</em>.</p>';

            itemAdd(342, $add, 0, $useid, 0);
            break;

        case 344:
            $add = (5 * $quantity);

            print '<p>You have received ' . $add . ' DP 14s, <em>Case of Whiskey</em>.</p>';

            itemAdd(342, $add, 0, $useid, 0);
            break;

        case 345:
            $add = (10 * $quantity);

            print '<p>You have received ' . $add . ' DP 14s, <em>Case of Whiskey</em>.</p>';

            itemAdd(342, $add, 0, $useid, 0);
            break;

        case 346:
            print '<p>You have received DP 15, <em>Family Party</em>. Preparing for huge bash, you have picked up Donator Packs 41-44.  Damn that\'s nice!</p>';

            itemAdd(330, $quantity, 0, $useid, 0);
            itemAdd(334, $quantity, 0, $useid, 0);
            itemAdd(338, $quantity, 0, $useid, 0);
            itemAdd(342, $quantity, 0, $useid, 0);
            break;

        case 350:
            print '<p>You have received DP 16, <em>Hospital Fundraiser</em>. A little time with the cut-up crowd and you pick up 4 ' . itemInfo(12) . ', 2 ' . itemInfo(13) . ', a ' . itemInfo(67) . ', a ' . itemInfo(24) . ' and a ' . itemInfo(71) . '.</p>';

            $add8 = (8 * $quantity);
            $add4 = (4 * $quantity);
            $add2 = (2 * $quantity);

            itemAdd(12, $add8, 0, $useid, 0);
            itemAdd(13, $add4, 0, $useid, 0);
            itemAdd(67, $add2, 0, $useid, 0);
            itemAdd(24, $quantity, 0, $useid, 0);
            itemAdd(71, $quantity, 0, $useid, 0);
            break;

        case 354:
            print '<p>You have received DP 17, <em>Police Conference</em>. A little uncomfortable time in the conference center gets you 4 ' . itemInfo(27) . ', 2 ' . itemInfo(26) . ', a ' . itemInfo(66) . ', a ' . itemInfo(25) . ' and a ' . itemInfo(54) . '.</p>';

            $add8 = (8 * $quantity);
            $add4 = (4 * $quantity);
            $add2 = (2 * $quantity);

            itemAdd(27, $add8, 0, $useid, 0);
            itemAdd(26, $add4, 0, $useid, 0);
            itemAdd(66, $add2, 0, $useid, 0);
            itemAdd(25, $quantity, 0, $useid, 0);
            itemAdd(54, $quantity, 0, $useid, 0);
            break;

        case 358:
            print '<p>You have received DP 18, <em>Criminal Conference</em>. A little well spent time gets you 3 ' . itemInfo(51) . ', 3 ' . itemInfo(52) . ', 2 ' . itemInfo(601) . ', a ' . itemInfo(636) . ' and a ' . itemInfo(28) . '.</p>';

            $add6 = (6 * $quantity);
            $add4 = (4 * $quantity);

            itemAdd(51, $add6, 0, $useid, 0);
            itemAdd(52, $add6, 0, $useid, 0);
            itemAdd(23, $add4, 0, $useid, 0);
            itemAdd(636, $quantity, 0, $useid, 0);
            itemAdd(626, $quantity, 0, $useid, 0);
            break;

        case 366:
            print '<p>You have received DP 19, <em>Convention Center</em>. You work the Convention Center for a little while and collect ' . itemInfo(350) . ', ' . itemInfo(354) . ', and a ' . itemInfo(358) . '.</p>';

            itemAdd(350, $quantity, 0, $useid, 0);
            itemAdd(354, $quantity, 0, $useid, 0);
            itemAdd(358, $quantity, 0, $useid, 0);
            break;

        case 370:
            print '<p>You have received DP 20, <em>Exotic Weaponry</em>. You know who to talk to and Walter sets you up. You get the 5,000 in stats and a ' . itemInfo(87) . ', along with two ' . itemInfo(46) . '.</p>';

            $add2 = (2 * $quantity);

            itemAdd(87, $quantity, 0, $useid, 0);
            itemAdd(46, $add2, 0, $useid, 0);
            break;

        case 371:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 6s, <em>Statistic Boost</em>.</p>';

            itemAdd(310, $add, 0, $useid, 0);
            break;

        case 372:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 6s, <em>Statistic Boost</em>.</p>';

            itemAdd(310, $add, 0, $useid, 0);
            break;

        case 373:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 7s, <em>Crime Pays</em>.</p>';

            itemAdd(314, $add, 0, $useid, 0);
            break;

        case 374:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 7s, <em>Crime Pays</em>.</p>';

            itemAdd(314, $add, 0, $useid, 0);
            break;

        case 375:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 8s, <em>Respect can be bought</em>.</p>';

            itemAdd(318, $add, 0, $useid, 0);
            break;

        case 376:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 8s, <em>Respect can be bought</em>.</p>';

            itemAdd(318, $add, 0, $useid, 0);
            break;

        case 377:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 9s, <em>Knowledge is Power</em>.</p>';

            itemAdd(322, $add, 0, $useid, 0);
            break;

        case 378:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 9s, <em>Knowledge is Power</em>.</p>';

            itemAdd(322, $add, 0, $useid, 0);
            break;

        case 379:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 11s, <em>Pack of Beer</em>.</p>';

            itemAdd(330, $add, 0, $useid, 0);
            break;

        case 380:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 11s, <em>Pack of Beer</em>.</p>';

            itemAdd(330, $add, 0, $useid, 0);
            break;

        case 381:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 12s, <em>Coffee Roastery</em>.</p>';

            itemAdd(334, $add, 0, $useid, 0);
            break;

        case 382:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 12s, <em>Coffee Roastery</em>.</p>';

            itemAdd(334, $add, 0, $useid, 0);
            break;

        case 383:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 13s, <em>Basket of Wine</em>.</p>';

            itemAdd(338, $add, 0, $useid, 0);
            break;

        case 384:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 13s, <em>Basket of Wine</em>.</p>';

            itemAdd(338, $add, 0, $useid, 0);
            break;

        case 385:
            $add = (25 * $quantity);

            print '<p>You have received ' . $add . ' DP 14s, <em>Case of Whiskey</em>.</p>';

            itemAdd(342, $add, 0, $useid, 0);
            break;

        case 386:
            $add = (50 * $quantity);

            print '<p>You have received ' . $add . ' DP 14s, <em>Case of Whiskey</em>.</p>';

            itemAdd(342, $add, 0, $useid, 0);
            break;

        case 635:
            $add = (2 * $quantity);
            $add6 = (6 * $quantity);

            print '<p>Congratulations on your achievement. You have received some Respect, ' . $add . ' Donator Days, a whole cake (' . $add6 . ' pieces), and some Grappa to wash it all down. Good job!!</p>';

            $db->query("UPDATE users SET donatordays = donatordays + {$add} WHERE userid = {$useid}");

            itemAdd(91, $add6, 0, $useid, 0);
            itemAdd(55, $quantity, 0, $useid, 0);
            break;
    }

    for ($i = 0; $i < $quantity; $i++) {
        if ($row['effect1_on']) {
            $einfo = unserialize($row['effect1']);
            if ($einfo['inc_type'] == "percent") {
                if (in_array($einfo['stat'], array('energy', 'will', 'brave', 'hp'))) {
                    $inc = round($user['max' . $einfo['stat']] / 100 * $einfo['inc_amount']);
                } else {
                    $inc = round($user[$einfo['stat']] / 100 * $einfo['inc_amount']);
                }
            } else {
                $inc = $einfo['inc_amount'];
            }
            if ($einfo['dir'] == "pos") {
                if (in_array($einfo['stat'], array('energy', 'will', 'brave', 'hp'))) {
                    $user[$einfo['stat']] = min($user[$einfo['stat']] + $inc, $user['max' . $einfo['stat']]);
                } else {
                    $user[$einfo['stat']] += $inc;
                }
            } else {
                $user[$einfo['stat']] = max($user[$einfo['stat']] - $inc, 0);
            }
            $upd = $user[$einfo['stat']];
            if (in_array($einfo['stat'], array('strength', 'agility', 'guard', 'labour', 'IQ', 'ammo'))) {
                $db->query("UPDATE userstats SET {$einfo['stat']} = '{$upd}' WHERE userid = {$useid}");
            } else {
                $db->query("UPDATE users SET {$einfo['stat']} = '{$upd}' WHERE userid = {$useid}");
            }
        }

        if ($row['effect2_on']) {
            $einfo = unserialize($row['effect2']);
            if ($einfo['inc_type'] == "percent") {
                if (in_array($einfo['stat'], array('energy', 'will', 'brave', 'hp'))) {
                    $inc = round($user['max' . $einfo['stat']] / 100 * $einfo['inc_amount']);
                } else {
                    $inc = round($user[$einfo['stat']] / 100 * $einfo['inc_amount']);
                }
            } else {
                $inc = $einfo['inc_amount'];
            }
            if ($einfo['dir'] == "pos") {
                if (in_array($einfo['stat'], array('energy', 'will', 'brave', 'hp'))) {
                    $user[$einfo['stat']] = min($user[$einfo['stat']] + $inc, $user['max' . $einfo['stat']]);
                } else {
                    $user[$einfo['stat']] += $inc;
                }
            } else {
                $user[$einfo['stat']] = max($user[$einfo['stat']] - $inc, 0);
            }
            $upd = $user[$einfo['stat']];
            if (in_array($einfo['stat'], array('strength', 'agility', 'guard', 'labour', 'IQ', 'ammo'))) {
                $db->query("UPDATE userstats SET {$einfo['stat']} = '{$upd}' WHERE userid = {$useid}");
            } else {
                $db->query("UPDATE users SET {$einfo['stat']} = '{$upd}' WHERE userid = {$useid}");
            }
        }

        if ($row['effect3_on']) {
            $einfo = unserialize($row['effect3']);
            if ($einfo['inc_type'] == "percent") {
                if (in_array($einfo['stat'], array('energy', 'will', 'brave', 'hp'))) {
                    $inc = round($user['max' . $einfo['stat']] / 100 * $einfo['inc_amount']);
                } else {
                    $inc = round($user[$einfo['stat']] / 100 * $einfo['inc_amount']);
                }
            } else {
                $inc = $einfo['inc_amount'];
            }
            if ($einfo['dir'] == "pos") {
                if (in_array($einfo['stat'], array('energy', 'will', 'brave', 'hp'))) {
                    $user[$einfo['stat']] = min($user[$einfo['stat']] + $inc, $user['max' . $einfo['stat']]);
                } else {
                    $user[$einfo['stat']] += $inc;
                }
            } else {
                $user[$einfo['stat']] = max($user[$einfo['stat']] - $inc, 0);
            }
            $upd = $user[$einfo['stat']];
            if (in_array($einfo['stat'], array('strength', 'agility', 'guard', 'labour', 'IQ', 'ammo'))) {
                $db->query("UPDATE userstats SET {$einfo['stat']} = '{$upd}' WHERE userid = {$useid}");
            } else {
                $db->query("UPDATE users SET {$einfo['stat']} = '{$upd}' WHERE userid = {$useid}");
            }
        }
    }

    if ($row['itmid'] == 100) {
        print "<br>";
    } else {
        itemDelete($row['inv_id'], $quantity, $user['userid']);
    }

    print "<p>{$quantity} {$row['itmname']} used successfully.</p> ";

    if ($row['itmid'] == 28 && $user['cdays'] < 1 && $user['course'] > 0) {
        educationFinish($user['course'], $user['userid']);
    }

    print "<p><a href='home.php'>Head on home</a></p>";
}

function util(Database $db, Header $headers, array $user, int $familyId, int $iid, int $quantity, int $uid): void
{
    $row = new mysqli_result($db);
    if ($uid > 0) {
        $row = mysqli_fetch_assoc($db->query("SELECT iv.inv_id, iv.inv_equip, iv.inv_itemid, iv.inv_qty, it.itmid, it.itmdesc, it.itmusage, it.itmBasePrice, it.effect1_on, it.effect2_on, it.effect3_on, it.itmtype FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$iid} AND iv.inv_userid = {$uid}"));
    } elseif ($familyId > 0) {
        $row = mysqli_fetch_assoc($db->query("SELECT iv.inv_itemid, iv.inv_qty, it.itmid, it.itmdesc, it.itmusage, it.itmBasePrice FROM inventory iv LEFT JOIN items it ON iv.inv_itemid = it.itmid WHERE iv.inv_id = {$iid} AND iv.inv_famid = {$familyId}"));
    }

    if ($row['inv_qty'] == 0) {
        print '
            <p>You do not own that item and so cannot do anything with it.</p>
            <p><a href=\'home.php\'>Head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($familyId > 0 && $user['gang'] != $familyId) {
        print "
            <p>You are not a member of the Family and so cannot do anything useful with Family goods.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }

    print '
        <h3>Utilizing ' . itemInfo($row['inv_itemid']) . ' <span class=light>(' . $row['itmusage'] . ')</span></h3>
        <p>' . $row['itmdesc'] . '</p>
        <p><strong>You own ' . $row['inv_qty'] . '</strong> and may do any of the following with it.</p>
        <table cellspacing=0 cellpadding=2 class=table>
            <tr><td colspan=3> &rang;</td></tr>
    ';

    if (in_array($row['itmtype'], array(60, 65, 70, 80)) && $row['inv_equip'] == 'no') {
        print '
            <tr>
                <td colspan=2>
                    <form action=\'items.php\' method=GET>
                        <input type=hidden name=action value=\'equp\'>
                        <input type=hidden name=iid value=\'' . $row['inv_id'] . '\'>
                        Prepare it for battle with your enemies.
                </td>
                <td>
                        <input type=submit value=\'Equip it\'>
                    </form>
                </td>
            </tr>
            <tr><td colspan=3> &rang;</td></tr>
        ';
    }

    if ($row['effect1_on'] || $row['effect2_on'] || $row['effect3_on']) {
        print '
            <tr>
                <td>
                    <form action=\'items.php?action=use2\' method=POST>
                        <input type=hidden name=invid value=\'' . $iid . '\'>
                        <input type=hidden name=useid value=\'' . $uid . '\'>
                        Using more than one may not do anything.
                </td>
                <td>Quantity: <input type=text size=5 name=quant value=1></td>
                <td><input type=submit value=\'Use Item\'></form></td>
            </tr>
            <tr><td colspan=3> &rang;</td></tr>
        ';
    }

    if ($row['itmBasePrice'] >= 0) {
        print '
            <tr>
                <td>
                    <form action=\'items.php?action=sen2\' method=POST>
                        <input type=hidden name=invid value=\'' . $iid . '\'>
                        <input type=hidden name=useid value=\'' . $uid . '\'>
                        <input type=hidden name=famid value=\'' . $familyId . '\'>
                        Mafioso: ' . mafiosoMenu('recus') . '
                </td>
                <td>Quantity: <input type=text size=5 name=quant value=1></td>
                <td>
                        <input type=submit value=\'Send to Mafioso\'>
                    </form>
                </td>
            </tr>
            <tr><td colsapn=3> &rang;</td></tr>
            <tr>
                <td>
                    <form action=\'items.php?action=sen2\' method=POST>
                        <input type=hidden name=invid value=\'' . $iid . '\'>
                        <input type=hidden name=useid value=\'' . $uid . '\'>
                        <input type=hidden name=famid value=\'' . $familyId . '\'>
                        Family: <select name=recfa type=dropdown>
        ';

        $qfa = $db->query("SELECT famID, famName FROM family WHERE famRespect > 0 ORDER BY famName");
        while ($rfa = mysqli_fetch_assoc($qfa)) {
            print '<option value=\'' . $rfa['famID'] . '\'>' . $rfa['famName'] . '</option>';
        }

        print '
                    </select>
                </td>
                <td>Quantity: <input type=text size=5 name=quant value=\'1\'></td>
                <td>
                        <input type=submit value=\'Send to Family\'>
                    </form>
                </td>
            </tr>
            <tr><td colspan=3> &rang;</td></tr>'
        ;

        if ($familyId == 0) {
            print '
                <tr>
                    <td colspan=2>
                        <form action=\'consignmentMarket.php\' method=GET>
                            <input type=hidden name=action value=\'add\'>
                            <input type=hidden name=ID value=\'' . $iid . '\'>
                            Place it in the Consignment Market for up to 30 days.
                    </td>
                    <td>
                            <input type=submit value=\'Set the Price\'>
                        </form>
                    </td>
                </tr>
                <tr><td colspan=3> &rang;</td></tr>
            ';
        }
    }

    if ($row['itmBasePrice'] > 0) {
        $payment = round($row['itmBasePrice'] * 0.8);
        if ($row['itmid'] == 5) {
            $payment = $row['itmBasePrice'];
        }

        print '
            <tr>
                <td>
                    <form action=\'items.php\' method=GET>
                        <input type=hidden name=action value=\'sell\'>
                        <input type=hidden name=iid value=\'' . $iid . '\'>
                        <input type=hidden name=uid value=\'' . $uid . '\'>
                        <input type=hidden name=fid value=\'' . $familyId . '\'>
                        Sell to a passing merchant for ' . moneyFormatter($payment) . '.
                </td>
                <td>Quantity: <input type=text size=5 name=qty value=1></td>
                <td>
                        <input type=submit value=\'Sell it off\'>
                    </form>
                </td>
            </tr>
            <tr><td colspan=3> &rang;</td></tr>
        ';
    }

    print '</table>';
}

$application->header->endPage();
