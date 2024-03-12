<?php

use Fmw\Database;
use Fmw\Header;

require_once "sglobals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 1, $njl = 0, $nhsp = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';

switch ($action) {
    case 'newitem':
        new_item($userId);
        break;
    case 'createitem':
        create_item($db, $userId);
        break;
    case 'giveitem':
        give_item_form($db, $user);
        break;
    case 'giveitemsub':
        give_item_submit($user);
        break;
    case 'viewitems':
    default:
        view_items($db, $headers, $user);
        break;
}

function new_item(int $userId): void
{
    if ($userId != 1) {
        unauthorized($userId, 1);
    }

    print '
        <h3>Create new Item</h3>
        <form action=\'staffItems.php?action=createitem\' method=POST>
            <table width=95% cellspacing=0 cellpadding=2 class=table>
                <tr>
                    <td>Name:</td>
                    <td><input type=text size=20 name=itmname></td>
                    <td valign=top rowspan=9>
                        &nbsp; <textarea rows=6 cols=60 name=itmdesc></textarea><br><br><hr>
                        <br><strong>First Item Effect</strong><br>
                        ' . itemAbility() . '
                        <br><strong>Second Item Effect</strong><br>
                        ' . itemAbility() . '
                        <br><strong>Third Item Effect</strong><br>
                        ' . itemAbility() . '
                    </td>
                </tr>
                <tr>
                    <td valign=top>ID:</td>
                    <td><input type=text size=20 name=itmid><br><span class=light>(leave blank for next ID in series)</span></td>
                </tr>
                <tr>
                    <td>Usage:</td>
                    <td><input type=text size=20 name=itmusage></td>
                </tr>
                <tr>
                    <td>Type:</td>
                    <td>
                        <select name=itmtype type=dropdown>
                            <option value=5> &nbsp;Donator Pack</option>
                            <option value=10> &nbsp;Contacts</option>
                            <option value=20> &nbsp;Gear</option>
                            <option value=30> &nbsp;Car Gear</option>
                            <option value=40> &nbsp;Nourishment</option>
                            <option value=50> &nbsp;Specialty</option>
                            <option value=60> &nbsp;Protection</option>
                            <option value=65> &nbsp;Bombs</option>
                            <option value=70> &nbsp;Firearms</option>
                            <option value=80> &nbsp;Melee Weapons</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Expire:</td>
                    <td><input type=text size=10 name=itmExpire value=0></td>
                </tr>
                <tr>
                    <td><br>Combat:</td>
                    <td>
                        <br><select name=itmCombatType type=dropdown>
                            <option value=0 selected> &nbsp;Not a Weapon &nbsp;</option>
                            <option value=1> &nbsp;Experience</option>
                            <option value=2> &nbsp;Financial</option>
                            <option value=3> &nbsp;Physical</option>
                            <option value=4> &nbsp;Respect</option>
                            <option value=5> &nbsp;Statistics</option>
                            <option value=6> &nbsp;Stealth</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Bonus:</td>
                    <td><input type=text name=itmCombat size=10 value=0></td>
                </tr>
                <tr>
                    <td><br>Level:</td>
                    <td><br><input type=text name=itmLevel size=10 value=0></td>
                </tr>
                <tr>
                    <td><br>Price:</td>
                    <td><br><input type=text size=10 name=itmBasePrice value=\'$ 0\'></td>
                </tr>
                <tr>
                    <td valign=top>Store:</td>
                    <td><input type=text size=10 name=itmStore value=0><br><span class=light>(0 None, 1 Staff, 2 Family)</span><br><br></td>
                </tr>
                <tr><td colspan=3 class=center><br><hr><br><input type=submit value=\'Add Item To Game\'></td></tr>
            </table>
        </form>
    ';
}

function create_item(Database $db, int $userId): void
{
    if ($userId != 1) {
        unauthorized($userId, 1);
    }

    $itmid = isset($_POST['itmid']) ? mysql_num($_POST['itmid']) : 0;
    $itmname = isset($_POST['itmname']) ? mysql_tex($_POST['itmname']) : '';
    $itmdesc = isset($_POST['itmdesc']) ? mysql_tex($_POST['itmdesc']) : '';
    $itmusage = isset($_POST['itmusage']) ? mysql_tex($_POST['itmusage']) : '';
    $itmtype = isset($_POST['itmtype']) ? mysql_num($_POST['itmtype']) : 5;
    $itmExpire = isset($_POST['itmExpire']) ? mysql_num($_POST['itmExpire']) : 0;
    $itmCombatType = isset($_POST['itmCombatType']) ? mysql_num($_POST['itmCombatType']) : 0;
    $itmCombat = isset($_POST['itmCombat']) ? mysql_num($_POST['itmCombat']) : 0;
    $itmLevel = isset($_POST['itmLevel']) ? mysql_num($_POST['itmLevel']) : 0;
    $itmBasePrice = isset($_POST['itmBasePrice']) ? mysql_num($_POST['itmBasePrice']) : 0;
    $itmStore = isset($_POST['itmStore']) ? mysql_num($_POST['itmStore']) : 0;
    $effect1on = isset($_POST['effect1on']) ? mysql_num($_POST['effect1on']) : 0;
    $effect1stat = isset($_POST['effect1stat']) ? mysql_tex($_POST['effect1stat']) : '';
    $effect1dir = isset($_POST['effect1dir']) ? mysql_tex($_POST['effect1dir']) : '';
    $effect1amount = isset($_POST['effect1amount']) ? mysql_num($_POST['effect1amount']) : 0;
    $effect1type = isset($_POST['effect1type']) ? mysql_tex($_POST['effect1type']) : '';
    $effect2on = isset($_POST['effect2on']) ? mysql_num($_POST['effect2on']) : 0;
    $effect2stat = isset($_POST['effect2stat']) ? mysql_tex($_POST['effect2stat']) : '';
    $effect2dir = isset($_POST['effect2dir']) ? mysql_tex($_POST['effect2dir']) : '';
    $effect2amount = isset($_POST['effect2amount']) ? mysql_num($_POST['effect2amount']) : 0;
    $effect2type = isset($_POST['effect2type']) ? mysql_tex($_POST['effect2type']) : '';
    $effect3on = isset($_POST['effect3on']) ? mysql_num($_POST['effect3on']) : 0;
    $effect3stat = isset($_POST['effect3stat']) ? mysql_tex($_POST['effect3stat']) : '';
    $effect3dir = isset($_POST['effect3dir']) ? mysql_tex($_POST['effect3dir']) : '';
    $effect3amount = isset($_POST['effect3amount']) ? mysql_num($_POST['effect3amount']) : 0;
    $effect3type = isset($_POST['effect3type']) ? mysql_tex($_POST['effect3type']) : '';

    $rmd = 9;
    if ($itmtype == 80) {
        $rmd = 3;
    }

    if (!($itmCombatType & 1)) {
        $rmd = $rmd * 2;
    }

    $itmReload = $rmd * $itmCombat;
    $efx1 = addslashes(serialize(array("stat" => $effect1stat, "dir" => $effect1dir, "inc_type" => $effect1type, "inc_amount" => $effect1amount)));
    $efx2 = addslashes(serialize(array("stat" => $effect2stat, "dir" => $effect2dir, "inc_type" => $effect2type, "inc_amount" => $effect2amount)));
    $efx3 = addslashes(serialize(array("stat" => $effect3stat, "dir" => $effect3dir, "inc_type" => $effect3type, "inc_amount" => $effect3amount)));

    $db->query("INSERT INTO items VALUES({$itmid}, {$itmtype}, '{$itmname}', '{$itmusage}', {$itmExpire}, {$itmBasePrice}, {$itmStore}, {$itmLevel}, {$itmCombat}, {$itmCombatType}, {$itmReload}, '{$itmdesc}', {$effect1on}, '{$efx1}', {$effect2on}, '{$efx2}', {$effect3on}, '{$efx3}')");

    $i = mysqli_insert_id($db);

    print '
        <h3>New Item Creation</h3>
        <p>The ' . itemInfo($i) . ' was added to the game successfully.</p>
    ';
}

function give_item_form(Database $db, array $user): void
{
    if ($user['rankCat'] != 'Staff' || $user['rank'] == 'Sgarrista') {
        header("Location: home.php");
    }

    print "
        <h3>Give item to Player</h3>
        <p>Remember they didn't earn this - so don't be too generous!</p>
        <form action='staffItems.php?action=giveitemsub' method='post'>
            Player: " . mafiosoMenu('user') . " &nbsp; &nbsp;
            Item: <select name=item type=dropdown>
    ";

    $query = $db->query("SELECT itmid, itmname FROM items ORDER BY itmname");
    while ($row = mysqli_fetch_assoc($query)) {
        print '<option value=\'' . $row['itmid'] . '\'>' . $row['itmname'] . '</option>';
    }

    print "
            </select> &nbsp; &nbsp;
            Quantity: <input type='text' name='qty' size='5' value='1'><br><br>
            <input type='submit' value='Give Item'>
        </form>
    ";
}

function give_item_submit(array $user): void
{
    if ($user['rankCat'] != 'Staff' || $user['rank'] == 'Sgarrista') {
        header("Location: home.php");
    }

    itemAdd($_POST['item'], $_POST['qty'], 0, $_POST['user'], 0);

    print "
        <h3>Give item to Player</h3>
        <p>You gave {$_POST['qty']} of item ID {$_POST['item']} to user ID {$_POST['user']}<br><a href='staff.php'>Staff Home</a></p>
    ";

    staffLogAdd("Gave {$_POST['qty']} of item ID {$_POST['item']} to user ID {$_POST['user']}");
}

function view_items(Database $db, Header $headers, array $user): void
{
    if ($user['rankCat'] != 'Staff') {
        header("Location: home.php");

        print "
            <p>Begone foul beast.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Item Overview</h3>
        <table border=0 cellpadding=2 cellspacing=0 class='table' style='font-size:smaller;'>
    ";

    $title = "";
    $query = $db->query("SELECT itmid, itmtype, itmusage, itmBasePrice, itmStore, itmLevel, itmCombat, itmCombatType, itmExpire, itmdesc FROM items ORDER BY itmtype, itmname");
    while ($row = mysqli_fetch_assoc($query)) {
        if ($title != itemType($row['itmtype'])) {
            $title = itemType($row['itmtype']);

            print "
                <tr><td><br></td></tr>
                <tr>
                    <th>{$title}</th>
                    <th>Usage</th>
                    <th class='center'>Base Price</th>
                    <th class='center'>Store</th>
                    <th class='center'>Level</th>
                    <th class='center'>Combat</th>
                    <th>Expire?</th>
                    <th>Item ID</th>
                </tr>
            ";
        }

        print "
            <tr>
                <td>" . itemInfo($row['itmid']) . "</td>
                <td>{$row['itmusage']}</td>
                <td align='right'>{$row['itmBasePrice']}</td>
                <td class='center'>{$row['itmStore']}</td>
                <td class='center'>{$row['itmLevel']}</td>
                <td class='center'>{$row['itmCombat']}/" . itemCombatType($row['itmCombatType']) . "</td>
                <td align='center'>{$row['itmExpire']}</td>
                <td align='center'>{$row['itmid']}</td>
            </tr>
            <tr><td colspan=8 style='font-style:italic; border-bottom: dashed 1px rgb(153,153,153);'>{$row['itmdesc']}</td></tr>
        ";
    }

    print "</table>";
}

function itemAbility(string $on = 'on', string $ab = 'ab', string $dr = 'dr', string $am = 'am', string $tp = 'tp'): string
{
    return '
        <input type=radio name=' . $on . ' value=1>Yes
        <input type=radio name=' . $on . ' value=0 checked>No &nbsp;
        <select name=\'' . $dr . '\' type=dropdown>
            <option value=\'pos\'> &nbsp;Increase</option>
            <option value=\'neg\'> &nbsp;Decrease</option>
        </select> &nbsp;
        <select name=\'' . $ab . '\' type=dropdown>
            <option value=\'agility\'> &nbsp;Agility</option>
            <option value=\'visits\'> &nbsp;Visits</option>
            <option value=\'brave\'> &nbsp;Brave</option>
            <option value=\'cdays\'> &nbsp;Education Days Left</option>
            <option value=\'energy\'> &nbsp;Energy</option>
            <option value=\'guard\'> &nbsp;Guard</option>
            <option value=\'hp\'> &nbsp;Health</option>
            <option value=\'hospital\'> &nbsp;Hospital Time</option>
            <option value=\'IQ\'> &nbsp;IQ</option>
            <option value=\'jail\'> &nbsp;Jail Time</option>
            <option value=\'labour\'> &nbsp;Labour</option>
            <option value=\'money\'> &nbsp;Cash on Hand</option>
            <option value=\'moneyChecking\'> &nbsp;Checking Account</option>
            <option value=\'moneySavings\'> &nbsp;Savings Account</option>
            <option value=\'strength\'> &nbsp;Strength</option>
            <option value=\'respect\'> &nbsp;Tokens of Respect</option>
            <option value=\'will\'> &nbsp;Will</option>
        </select> &nbsp;
        &nbsp; <input type=text name=\'' . $am . '\' size=5 value=0>
        <select name=\'' . $tp . '\' type=dropdown>
            <option value=\'figure\'> &nbsp;Value</option>
            <option value=\'percent\'> &nbsp;Percent</option>
        </select><br>
    ';
}

$headers->endpage();
