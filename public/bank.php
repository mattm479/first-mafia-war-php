<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action     = isset($_GET['act']) ? mysql_tex($_GET['act']) : '';
$from       = isset($_POST['from']) ? mysql_tex($_POST['from']) : '';
$to         = isset($_POST['to']) ? mysql_tex($_POST['to']) : '';
$amount     = isset($_POST['amount']) ? mysql_num($_POST['amount']) : 0;
$courseDone = mysqli_fetch_assoc($application->db->query("SELECT userid FROM coursesdone WHERE courseid = 32 AND userid = {$application->user['userid']}"));
$invest     = 'no';
$bankHours  = array(
    "Sunday" => "Closed",
    "Monday" => array("Open" => "09", "Close" => "18"),
    "Tuesday" => array("Open" => "09", "Close" => "18"),
    "Wednesday" => array("Open" => "09", "Close" => "18"),
    "Thursday" => array("Open" => "09", "Close" => "20"),
    "Friday" => array("Open" => "09", "Close" => "18"),
    "Saturday" => array("Open" => "09", "Close" => "12")
);

if (isset($courseDone['userid']) && $courseDone['userid'] == $application->user['userid']) {
    $invest = 'yes';
}

print '
    <h3>First Mafia War Bank</h3>
    <div class=floatright>
        <img src=\'assets/images/photos/bank.jpg\' width=200 height=310 alt=\'Nice Teller\'>
    </div>
';

switch ($action) {
    case "clear":
        clear($application->db, $application->user, $application->user['userid']);
        break;
    case "transfer":
        transfer($application->db, $application->header, $application->user, $application->user['userid'], $amount, $from, $to, $invest);
        break;
    default:
        show_list($application->user, $invest);
        break;
}

function clear(Database $db, array $user, int $userId): void
{
    $fee = $user['moneyInvest'] * 0.05;
    $db->query("UPDATE users SET moneyInvest = moneyInvest - {$fee}, moneyInvestFlag = 0 where userid = {$userId}");

    print '
        <p>You spoke to your investment manager and they have liquidated some of your funds. It cost you ' . moneyFormatter($fee) . ' from that account which has been paid.</p>
        <p><a href=\'bank.php\'>Return to the bank</a></p>
    ';
}

function transfer(Database $db, Header $headers, array $user, int $userId, int $amount, string $from, string $to, string $invest): void
{
    $message = validate_transfer($db, $user, $userId, $amount, $from, $to, $invest);
    if ($message != '') {
        print $message;

        $headers->endpage();
        exit;
    }

    $ri = mysqli_fetch_assoc($db->query("SELECT inv_itemid FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 637"));
    $fee = 150;
    if ($ri != null && $ri['inv_itemid'] == 637) {
        print 'Free transfers while the banker is working for you.';
        $fee = 0;
    }

    $gain = $amount - $fee;
    $extra = '';
    if ($from == 'moneySavings') {
        $extra = "moneySavingsFlag = 1,";
    } elseif ($from == 'moneyTreasury') {
        $extra = "moneyTreasuryFlag = 3,";
    } elseif ($from == 'moneyInvest') {
        $extra = "moneyInvestFlag = 3,";
    }

    $db->query("UPDATE users SET {$extra} {$to} = {$to} + {$gain}, {$from} = {$from} - {$amount} where userid = {$userId}");
    
    print '
        <p>You have successfully transferred ' . moneyFormatter($amount - $fee) . '.</p>
        <p><a href=\'bank.php\'>Return to the bank</a></p>
    ';
}

function show_list(array $user, string $invest): void
{
    $savingsFlag = "available";
    if ($user['moneySavingsFlag'] == 1) {
        $savingsFlag = '<span class=offline>tomorrow</span>';
    }

    $investmentFlag = 'available';
    if ($invest != 'yes') {
        $investmentFlag = '<span title=\'You must invest first\' class=offline>Unavailable</span>';
    } else {
        if ($user['moneyInvestFlag'] > 1) {
            $investmentFlag = '<span class=offline>' . $user['moneyInvestFlag'] . ' days</span>';
        } elseif ($user['moneyInvestFlag'] == 1) {
            $investmentFlag = '<span class=offline>tomorrow</span>';
        }
    }

    $treasuryFlag = 'available';
    if ($user['donatordays'] == 0) {
        $treasuryFlag = '<span title=\'Donators Only\' class=offline>Unavailable</span>';
    } else {
        if ($user['moneyTreasuryFlag'] > 1) {
            $treasuryFlag = '<span class=offline>' . $user['moneyTreasuryFlag'] . ' days</span>';
        } elseif ($user['moneyTreasuryFlag'] == 1) {
            $treasuryFlag = '<span class=offline>tomorrow</span>';
        }
    }

    print '
        <p>You currently have <strong>' . moneyFormatter($user['moneyChecking'] + $user['moneySavings'] + $user['moneyInvest'] + $user['moneyTreasury']) . '</strong> in your various accounts and ' . moneyFormatter($user['money']) . ' on hand.<br>What you are hiding in your mattress is anyone\'s guess.</p>
        <table border=0 cellpadding=4 cellspacing=0 class=table>
            <tr>
                <td valign=top>
                    Cash on Hand<br>
                    Checking Account<br>
                    Savings Account<br>
                    Investments<br>
                    Treasury Bills
                </td>
                <td valign=top>' . moneyFormatter($user['money']) . '<br>' . moneyFormatter($user['moneyChecking']) . '<br>' . moneyFormatter($user['moneySavings']) . '<br>' . moneyFormatter($user['moneyInvest']) . '<br>' . moneyFormatter($user['moneyTreasury']) . '</td>
                <td valign=top><em>available<br>available<br>' . $savingsFlag . '<br>' . $investmentFlag . '<br>' . $treasuryFlag . '</em></td>
            </tr>
        </table><br>
        <strong>Transfer Money</strong><br>
        <form action=\'bank.php?act=transfer\' method=POST>
            from
            <select name=from type=dropdown>
                <option value=\'money\'>Cash on Hand</option>
                <option value=\'moneyChecking\'>Checking Account</option>
    ';

    if ($user['moneySavingsFlag'] == 0 && $user['moneySavings'] > 0) {
        print '<option value=\'moneySavings\'>Savings Account</option>';
    }

    if ($user['moneyInvestFlag'] == 0 && $user['moneyInvest'] > 0) {
        print '<option value=\'moneyInvest\'>Investments</option>';
    }

    if ($user['moneyTreasuryFlag'] == 0 && $user['moneyTreasury'] > 0) {
        print '<option value=\'moneyTreasury\'>Treasury Bills</option>';
    }

    print '
        </select> 
        to
        <select name=to type=dropdown>
            <option value=\'money\'>Cash on Hand</option>
            <option value=\'moneyChecking\'>Checking Account</option>
            <option value=\'moneySavings\'>Savings Account</option>
    ';

    if ($invest == 'yes') {
        print '<option value=\'moneyInvest\'>Investments</option>';
    }

    if ($user['donatordays'] > 0) {
        print '<option value=\'moneyTreasury\'>Treasury Bills</option>';
    }

    print '
            </select> &nbsp;
            $<input type=text name=amount value=\'\' size=9> &nbsp;
            <input type=submit value=\'Transfer\'>
        </form><br>
        <p>ALL transfers require a $150 bribe to make sure it ends up in the right place unless you have a banker working for you.</p>
    ';

    if ($invest == 'yes') {
        print '<p>You may, if you wish, tell your investment company to liquidate some of your holdings. The cost is about two days worth of interest (5%) but you will clear any withdrawal delay remaining on the funds. <a href=\'bank.php?act=clear\'>Please clear the fund delay on my Investments</a>.</p>';
    }

    print "
        <hr width=90%><br>
        <p>Banking institutions in the 1960\'s were large formal affairs and strictly regulated. You may have access to several different types of accounts depending on your position in society.</p>
        <ul>
            <li><em>Cash on Hand</em> &middot; You may use at any time, but it may be stolen easily in battle.</li>
            <li><em>Checking Account</em> &middot; Though it does not earn interest, it is much harder to access by thieves.</li>
            <li><em>Savings Account</em> &middot; Earns 1% interest per day (.04% compounded hourly) but you may only withdraw funds once a day.</li>
            <li><em>Investments</em> &middot; Earns ~1.5% interest per day (.06% compounded hourly) but you may only withdraw funds once every 3 days.</li>
            <li><em>Treasury Bills</em> &middot; Available only to Donators, T-Bills earn ~2.5% interest per day (0.1% compounded hourly) but you may only withdraw funds once every 3 days.</li>
        </ul><br>
        <p>Cash may be stolen in muggings. Bankers can tap into your Checking Account. Mob Accountants can tap into your Savings Account. All Accounts are susceptible to review by the Federal Regulators. Investments are immune to harassment by other players.</p>
    ";
}

function validate_transfer(Database $db, array $user, int $userId, int $amount, string $from, string $to, string $invest): string {
    $message = '';

    if ($amount > $user[$from]) {
        $message = '
            <p>You do not have enough money in that account to make the transfer.</p>
            <p><a href=\'bank.php\'>Return to the bank</a></p>
        ';
    }

    if ($from == 'moneyInvest' && $user['moneyInvestFlag'] > 0) {
        $message = '<p>You cannot transfer money from your Investments until cash flow improves.</p>';
    }

    if ($from == 'moneyTreasury' && $user['moneyTreasuryFlag'] > 0) {
        $message = "<p>You cannot transfer money from that Account until the hold has been lifted.</p>";
    }

    if ($from == 'moneySavings' && $user['moneySavingsFlag'] > 0) {
        $message = '<p>You cannot transfer money from that Account until the hold has been lifted.</p>';
    }

    if ($amount < 0) {
        $message = '
            <p>The bank does not provide loans to the likes of you. For even trying they have berated you publicly and you lost Respect.</p>
            <p><a href=\'bank.php\'>Return to the bank</a></p>
        ';

        $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$userId}");
    }

    if ($amount < 151) {
        $message = '
            <p>That small an amount will not even cover the costs! Please transfer a larger amount.</p>
            <p><a href=\'bank.php\'>Return to the bank</a></p>
        ';
    }

    if ($to == 'moneyInvest' && $invest != 'yes') {
        $message = '
            <p>You have not yet made your initial investment.</p>
            <p><a href=\'bank.php\>Return to the bank</a></p>
        ';
    }

    if ($to == 'moneyTreasury' && $user['donatordays'] == 0) {
        $message = '
            <p>You are not a Donator and therefor cannot invest in Treasury Bills.</p>
            <p><a href=\'bank.php\'>Return to the bank</a></p>
        ';
    }

    return $message;
}

$application->header->endPage();
