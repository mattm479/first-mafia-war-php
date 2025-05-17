<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$businessId = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$tuneUp = isset($_GET['tune']) ? mysql_num($_GET['tune']) : 0;
$businessAuto = isset($_POST['bauto']) ? mysql_tex($_POST['bauto']) : '';
$busId = isset($_POST['busID']) ? mysql_num($_POST['busID']) : 0;
$businessName = isset($_POST['bname']) ? mysql_tex($_POST['bname']) : '';
$description = isset($_POST['descr']) ? mysql_tex($_POST['descr']) : '';
$itemCost = isset($_POST['icost']) ? mysql_num($_POST['icost']) : 0;
$itemId = isset($_POST['itmID']) ? mysql_num($_POST['itmID']) : 0;
$location = isset($_POST['locat']) ? mysql_num($_POST['locat']) : 0;
$modified = isset($_POST['modif']) ? mysql_num($_POST['modif']) : 0;
$owner = isset($_POST['owner']) ? mysql_num($_POST['owner']) : 0;
$quantity = isset($_POST['quant']) ? mysql_num($_POST['quant']) : 0;

switch ($action) {
    case "purchase":
        purchase($application->db, $application->header, $application->user, $userId, $busId, $itemId, $itemCost, $owner, $quantity);
        break;
    case "shcreate":
        shop_create($application->db, $application->user, $businessAuto, $businessName, $businessId, $description, $location, $modified, $owner);
        break;
    case "shstocki":
        shop_stock($application->db, $application->header, $businessId, $itemId, $itemCost);
        break;
    case "sptuneup":
        special_tuneup($application->db, $application->header, $application->user, $userId, $tuneUp);
        break;
    case "spgetcar":
        special_get_car($application->db, $application->header, $application->user, $userId);
        break;
    case "shopping":
    default:
        shopping($application->db, $application->header, $application->user, $userId, $businessId);
        break;
}

function purchase(Database $db, Header $headers, array $user, int $userId, int $busId, int $itemId, int $itemCost, int $owner, int $quantity): void
{
    if (!$quantity || $quantity < 1) {
        print '
            <h3>Downtown Stores</h3>
            <p>You cannot purchase so few!</p>
            <p><a href=\'explore.php\'>Visit the City</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $query = $db->query("SELECT itmname, itmBasePrice FROM items WHERE itmid = {$itemId}");
    if (mysqli_num_rows($query) == 0) {
        print '
            <h3>Downtown Stores</h3>
            <p>There is nothing like that in the world.</p>
            <p><a href=\'explore.php\'>Visit the City</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $itemDescription = mysqli_fetch_assoc($query);
    $price = $itemCost * $quantity;
    if ($user['money'] < $price) {
        print '
            <h3>Downtown Stores</h3>
            <p>You do not have enough money to buy that many!</p>
            <p><a href=\'explore.php\'>Visit the City</a> or <a href=\'bank.php\'>head to your bank</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $profit = (($itemCost - $itemDescription['itmBasePrice']) * $quantity) * 0.8;
    itemAdd($itemId, $quantity, 0, $userId, 0);

    $db->query("UPDATE users SET money = money - {$price} WHERE userid = {$userId}");
    $db->query("UPDATE family SET famVaultCash = famVaultCash + {$profit} WHERE famID = {$owner}");

    logItem(0, 0, $userId, $user['trackActionIP'], 'market', $itemId, $quantity);

    print '
        <h3>Downtown Stores</h3>
        <p>You purchased ' . $quantity . ' ' . $itemDescription['itmname'] . '(s) for ' . moneyFormatter($price) . '.</p>
        <p><a href=\'business.php?ID=' . $busId . '\'>Return to shop</a> or <a href=\'explore.php\'>visit the city</a>.</p>
    ';
}

function shopping(Database $db, Header $headers, array $user, int $userId, int $businessId): void
{
    $title = '';
    $rbus = mysqli_fetch_assoc($db->query("SELECT busID, busLocation, busOwnerID, busAuto, busName, busDescription FROM business WHERE busID = {$businessId}"));
    $qitm = $db->query("SELECT bi.busItemItemCost, i.itmtype, i.itmid, i.itmdesc FROM businessItems bi LEFT JOIN items i ON bi.busItemItemID = i.itmid WHERE bi.busItemBusID = {$businessId} ORDER BY i.itmtype, i.itmname");

    if (!$rbus['busID']) {
        print '
            <h3>Downtown Business</h3>
            <p>This business does not exist excepting in your own mind. Please try again.</p>
            <p><a href=\'explore.php\'>Back to town</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($businessId == 12 && $user['location'] != 0) {
        if ($rbus['busLocation'] != $user['location'] && $rbus['busOwnerID'] != $user['gang']) {
            print '
                <h3>Downtown Business</h3>
                <p>This business is located where you are not. That can only work if you are buying from your Families own shop - and you are not doing that either.</p>
                <p><a href=\'explore.php\'>Back to town</a></p>
            ';

            $headers->endpage();
            exit;
        }
    }

    if ($rbus['busAuto'] == 'yes' && $user['autoOwned'] == 0) {
        print '
            <h3>Uptown Business</h3>
            <p>This business can only be reached Uptown. Until the public transportation system expands, the only way to get uptown is to drive.</p>
            <p><a href=\'explore.php\'>Back to town</a></p>
        ';

        $headers->endpage();
        exit;
    }

    print '
        <h3>' . $rbus['busName'] . ' <span class=lightest>&nbsp; &middot; &nbsp; ' . familyName($rbus['busOwnerID']) . '</span></h3>
        <p style=\'margin-bottom:0em;\'>' . mysql_tex_out($rbus['busDescription']) . '</p>
    ';

    if ($rbus['busID'] == 1) {
        print '<br><a href=\'business.php?action=sptuneup&tune=' . $userId . '\'>Get a tune up for $1,100</a><br>';
    }

    print '<table width=95% cellspacing=0 cellpadding=3 class=table style=\'font-size:smaller;\'>';
    while ($ritm = mysqli_fetch_assoc($qitm)) {
        if ($title != itemType($ritm['itmtype'])) {
            $title = itemType($ritm['itmtype']);
            print '
                <tr><td colspan=3>&nbsp;</td></tr>
                <tr>
                    <th width=20%>' . $title . '</th>
                    <th width=65%>Description</th>
                    <th width=15%>&nbsp;</th>
                </tr>
            ';
        }

        print '
            <tr>
                <td>' . itemInfo($ritm['itmid']) . '<br>' . moneyFormatter($ritm['busItemItemCost']) . '</td>
                <td>' . $ritm['itmdesc'] . '</td>
                <td align=right>
                    <form action=\'business.php?action=purchase\' method=POST>
                        <input type=hidden name=busID value=\'' . $rbus['busID'] . '\'>
                        <input type=hidden name=icost value=\'' . $ritm['busItemItemCost'] . '\'>
                        <input type=hidden name=itmID value=\'' . $ritm['itmid'] . '\'>
                        <input type=hidden name=owner value=\'' . $rbus['busOwnerID'] . '\'>
                        <input type=text name=quant size=3 value=\'1\'>
                        <input type=submit value=Buy>
                    </form>
                </td>
            </tr>
            <tr><td colspan=3 style=\'border-top:solid 1px rgb(153,153,153);\'></td></tr>
        ';
    }

    print '</table>';

    if (($rbus['busOwnerID'] == $user['gang'] && $user['gangrank'] < 3) || $userId == 1) {
        print '
            <h5>Manage the Business</h5>
            <form action=\'business.php?action=shcreate\' method=POST>
                <input type=hidden name=busID value=\'' . $rbus['busID'] . '\'>
                <input type=hidden name=bauto value=\'' . $rbus['busAuto'] . '\'>
                <input type=hidden name=bname value=\'' . $rbus['busName'] . '\'>
                <input type=hidden name=owner value=\'' . $rbus['busOwnerID'] . '\'>
                <input type=hidden name=descr value=\'' . $rbus['busDescription'] . '\'>
                <input type=hidden name=locat value=\'' . $rbus['busLocation'] . '\'>
                <input type=submit value=\'Modify Storefront\'>
            </form>
            <table width=75% cellpadding=2 cellspacing=0 class=table style=\'font-size: smaller;\'>
                <tr>
                    <th>Item Name</th>
                    <th>Information</th>
                    <th>Minimum Value</th>
                </tr>
        ';

        $qi = $db->query("SELECT itmid, itmBasePrice, itmusage FROM items WHERE itmLevel <= {$user['location']} AND itmStore IN (2, {$user['gang']}) ORDER BY itmtype DESC, itmname");
        while ($ri = mysqli_fetch_assoc($qi)) {
            $minvalue = moneyFormatter($ri['itmBasePrice']);
            $qb = $db->query("SELECT busItemID FROM businessItems WHERE busItemBusID = {$businessId} AND busItemItemID = {$ri['itmid']}");
            $do = '<input type=hidden name=icost value=0><input type=submit value=\'Remove Item\'>';
            if (mysqli_num_rows($qb) == 0) {
                $do = '<input type=text name=icost size=8 value=\'' . $minvalue . '\'> <input type=submit value=Add>';
            }

            print '
                <tr>
                    <td>' . itemInfo($ri['itmid']) . '</td>
                    <td>' . $ri['itmusage'] . '</td>
                    <td align=center>
                        <form action=\'business.php?action=shstocki\' method=POST>
                            <input type=hidden name=busID value=\'' . $rbus['busID'] . '\'>
                            <input type=hidden name=itmID value=\'' . $ri['itmid'] . '\'>
                            <input type=hidden name=owner value=\'' . $rbus['busOwnerID'] . '\'>
                            ' . $do . '
                        </form>
                    </td>
                </tr>
            ';
        }

        print '</table>';
    }
}

function special_tuneup(Database $db, Header $headers, array $user, int $userId, int $tuneUp): void
{
    print '<h3>Orfanos Filling Station <span class=lightest>&nbsp; &middot; &nbsp; ' . familyName(4) . '</span></h3>';

    if ($user['location'] != 50 || $userId != $tuneUp) {
        print '
            <p>You cannot get a tune up without taking your own car to the filling station.</p>
            <p><a href=\'airport.php\'>Head to the airport</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($user['money'] < 1100) {
        print '
            <p>It costs $1,100 to get your car tuned up. Come back when you have the cash.</p>
            <p><a href=\'bank.php\'>Head to the bank</a> or <a href=\'home.php\'>head on home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET money = money - 1100, autoMaint = 1 WHERE userid = {$userId}");
    print '
        <p>Your car was tuned up successfully. Your car is properly maintained and is running smoothly - and cheaply!</p>
        <p><a href=\'business.php?ID=1\'>Back to the Filling Station</a> or <a href=\'explore.php\'>head into town</a>.</p>
    ';
}

function special_get_car(Database $db, Header $headers, array $user, int $userId): void
{
    print '
        <h3>Hire a Car</h3>
        <p>You have no car of your own, so to move around you have hired a car and driver - you will not be caught dead on a bus! It costs $1,100 and you must be back in about an hour.</p>
    ';

    if ($user['money'] < 1100) {
        print '<p>You do not have the $1,100 it takes to hire a car so you are still on foot.</p>';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET money = money - 1100, autoOwned = 1 WHERE userid = {$userId}");
}

function shop_create(Database $db, array $user, int $businessAuto, string $businessName, int $businessId, string $description, int $location, int $modified, int $owner): void
{
    print '<h3>Create or Modify Business</h3><br>';

    if ($modified == 1) {
        if ($businessId == 0) {
            $db->query("INSERT INTO business (busOwnerID, busName, busLocation, busDescription, busAuto) VALUES ({$owner}, '{$businessName}', {$location}, '{$description}', '{$businessAuto}');");
            $id = $db->insert_id;
        } else {
            $db->query("UPDATE business SET busName = '{$businessName}', busLocation = {$location}, busDescription = '{$description}', busAuto='{$businessAuto}' WHERE busID = {$businessId}");
            $id = $businessId;
        }

        $db->query("UPDATE family SET famHeadquarters = {$location} WHERE famID = {$owner}");
        print '
            <p>The ' . $businessName . ' Business is ready to go!</p>
            <p><a href=\'business.php?ID=' . $id . '\'>Stock the shelves</a>.</p>
        ';
    } else {
        print '
            <p>Families may only own <strong>one</strong> storefront business. The location of your business dictates where your Family lives, so be careful where you put yours.</p>
            <form action=\'business.php?action=shcreate\' method=POST>
                <input type=hidden name=owner value=\'' . $user['gang'] . '\'>
                <input type=hidden name=modif value=1>
                <input type=hidden name=busID value=\'' . $businessId . '\'>
                Named <input type=text name=bname size=20 value=\'' . $businessName . '\'>
        ';

        if ($user['rankCat'] == 'Staff' || $businessId == 0) {
            print ' in ' . locationDropdown($user['level'], 'locat') . ' &nbsp; Uptown? <input type=text name=bauto size=4 value=\'' . $businessAuto . '\'><br><br>';
        } else {
            print '<input type=hidden name=locat value=\'' . $location . '\'><input type=text name=bauto size=4 value=\'no\'><br><br>';
        }

        print '
                 Description<br>
                 <textarea rows=3 cols=75 name=descr>' . mysql_tex_out($description) . '</textarea><br>
                 <input type=submit value=\'Create or Modify Business\'>
             </form><br>
         ';
    }
}

function shop_stock(Database $db, Header $headers, int $businessId, int $itemId, int $itemCost): void
{
    $rbus = mysqli_fetch_assoc($db->query("SELECT busID, busName, busOwnerID FROM business WHERE busID = {$businessId}"));
    print '<h3>' . $rbus['busName'] . ' <span class=lightest>&nbsp; &middot; &nbsp; ' . familyName($rbus['busOwnerID']) . '</span></h3>';

    if ($itemCost == 0) {
        $db->query("DELETE FROM businessItems WHERE busItemBusID = {$businessId} AND busItemItemID = {$itemId}");
        print '
            <p>You have successfully removed ' . itemInfo($itemId) . ' from your shop.</p>
            <p><a href=\'business.php?ID=' . $businessId . '\'>Return to your business</a></p>
        ';
    } else {
        $ritm = mysqli_fetch_assoc($db->query("SELECT itmid, itmBasePrice FROM items WHERE itmid = {$itemId}"));
        if ($itemCost < $ritm['itmBasePrice']) {
            print '
                <p>You cannot sell this item for less than the cost to you. Please try and set an appropriate price.</p>
                <p><a href=\'business.php?ID=' . $businessId . '\'>Return to your shop</a>.</p>
            ';

            $headers->endpage();
            exit;
        }

        $db->query("INSERT INTO businessItems (busItemBusID, busItemItemID, busItemItemCost) VALUES({$businessId}, {$itemId}, {$itemCost})");

        $profit = ($itemCost - $ritm['itmBasePrice']) * 0.8;
        print '
            <p>You have successfully added ' . itemInfo($itemId) . ' to your shop and set the cost at ' . moneyFormatter($itemCost) . '.<br>Your Family will earn a profit of ' . moneyFormatter($profit) . ' on each sale.</p>
            <p><a href=\'business.php?ID=' . $businessId . '\'>Return to your business</a></p>
        ';
    }
}

$application->header->endPage();
