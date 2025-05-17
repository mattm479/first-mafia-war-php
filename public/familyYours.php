<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$nu1 = isset($_POST['nu1']) ? mysql_num($_POST['nu1']) : 0;
$nu2 = isset($_POST['nu2']) ? mysql_num($_POST['nu2']) : 0;
$nu3 = isset($_POST['nu3']) ? mysql_num($_POST['nu3']) : 0;
$nu4 = isset($_POST['nu4']) ? mysql_num($_POST['nu4']) : 0;
$tx1 = isset($_POST['tx1']) ? mysql_tex($_POST['tx1']) : '';
$tx2 = isset($_POST['tx2']) ? mysql_tex($_POST['tx2']) : '';
$tx3 = isset($_POST['tx3']) ? mysql_tex($_POST['tx3']) : '';
$tx4 = isset($_POST['tx4']) ? mysql_tex($_POST['tx4']) : '';

if (!$application->user['gang']) {
    print '
        <h3>What Family?</h3>
        <p>Considering you are not currently in a Family, this area does not have much information for you.</p>
        <p><a href=\'familyList.php\'>Examine the current Families or start your own</a></p>
    ';

    $application->header->endPage();
    exit;
}

$if = mysqli_fetch_assoc($application->db->query("SELECT famID, famName, famDon, famHeadquarters, famRespect, famVaultCash, famVaultTokens, famDescInt, famDesc FROM family WHERE famID = {$application->user['gang']}"));
$count = mysqli_num_rows($application->db->query("SELECT userid FROM users WHERE gang = {$if['famID']}"));
$qwr = $application->db->query("SELECT famWarID FROM familyWar WHERE famWarEnd = 0 AND (famWarAtt = {$application->user['gang']} OR famWarDef = {$application->user['gang']})");

if (mysqli_num_rows($qwr) > 0) {
    $currentwar = ' &nbsp; <a class=light href=\'familyYours.php?action=warviews\'><font color=red>&nbsp;&middot;&nbsp; Your Family is at war! &nbsp;&middot;&nbsp;</font></a>';
}

$ib = mysqli_fetch_assoc($application->db->query("SELECT busID, busName FROM business WHERE busOwnerID = {$application->user['gang']}"));

$business = '&nbsp;No Store';
$reim = '';
if ($ib != null) {
    if (!$ib['busID'] && $application->user['gangrank'] <= 2) {
        $business = '<a title=\'Create a Family Store\' href=\'business.php?action=shcreate\'>Create Shop</a>';
    } else if ($ib['busID']) {
        $business = '<a title=\'Shop in the Family Store\' href=\'business.php?ID=' . $ib['busID'] . '\'>' . $ib['busName'] . '</a>';
    }
}

if ($application->user['gangrank'] <= 2) {
    $reim = '<span class=light><a title=\'Improve Family Respect\' href=\'familyYours.php?action=respecti\'>(imp)</a></span>';
}

if ($application->user['gangrank'] < 6) {
    $dial = '&nbsp;<a title=\'Exercise a little control\' href=\'familyYours.php?action=dialslap\'>Dial-a-Slap</a><br>';
}

print '
    <h3>' . $if['famName'] . ' Family' . $currentwar . '</h3>
    <div class=floatrightbox style=\'font-size:smaller;\'>
        <strong>Famly Don</strong><br>
        nbsp;' . mafiosoLight($if['famDon']) . '<br><br>
        <strong>Summary</strong><br>
        &nbsp;' . locationName($if['famHeadquarters']) . ' &nbsp; <a class=light title=\'Fly First Class\' href=\'airport.php?action=fly&destination=' . $if['famHeadquarters'] . '\'>(go)</a><br>
        nbsp;<a title=Membership href=\'familyYours.php?action=membersh\'>Members: ' . $count . '</a><br>
        &nbsp;Respect: ' . $if['famRespect'] . ' ' . $reim . '<br><br>
        <strong>Activities</strong><br>
        &nbsp;<a href=\'familyYours.php\'>Home &amp; Logs</a><br>
        &nbsp;<a href=\'familyYours.php?action=fcrimecu\'>Family Crime</a><br>
        &nbsp;<a href=\'forum.php\'>Forums</a><br>
        &nbsp;<a href=\'familyYours.php?action=warviews\'>Warfare</a><br>' . $dial . '<br>
        <strong>Gear</strong><br>
        &nbsp;<a title=\'Check the Inventory\' href=\'familyYours.php?action=inventry\'>Inventory</a><br>
        &nbsp;' . $business . '<br><br>
        <strong>Vault</strong>
';

if ($application->user['gangrank'] <= 4) {
    print '&nbsp; <a class=light title=\'Manage Vault\' href=\'familyYours.php?action=famvault\'>(manage)</a>';
}
print '
    <br>
   &nbsp;' . moneyFormatter($if['famVaultCash']) . '<br>
   &nbsp;' . $if['famVaultTokens'] . ' Respect<br>
   &nbsp;<a title=\'Support the Family\' href=\'familyYours.php?action=donatewe\'>&middot; Please Donate &middot;</a><br><br>
';

if ($application->user['gangrank'] <= 3) {
    print'<strong>Officer Duties</strong><br>';

    if ($if['famRespect'] < 100 && $application->user['gangrank'] == 1) {
        print '&nbsp;<a href=\'familyYours.php?action=dissolve\'><strong>Dissolve Family</strong></a><br>';
    }

    if ($application->user['gangrank'] == 1) {
        print '&nbsp;<a href=\'familyYours.php?action=renamefa\'>Rename Family</a><br>';
    }

    print '
        nbsp;<a href=\'familyYours.php?action=announce\'>Announcement</a><br>
        &nbsp;<a href=\'familyYours.php?action=describe\'>Description</a><br>
        &nbsp;<a href=\'familyYours.php?action=familyta\'>Family Tag</a><br>
    ';
}

if ($application->user['gangrank'] <= 2) {
    print '
        nbsp;<a href=\'familyYours.php?action=massmail\'>Mass Mail</a><br>
        &nbsp;<a href=\'familyYours.php?action=masspaym\'>Mass Payment</a><br><br>
    ';
}

print '
        <strong>Leave the Family?</strong><br>
       &nbsp;<a title=\'Leave the Family\' href=\'familyYours.php?action=leavefam\'>Yes - Please Retire</a><br>
    </div><br>
';

switch ($action) {
    case "announce":
        announcement($application->db, $application->header, $application->user, $if, $tx4);
        break;
    case "describe":
        description($application->db, $application->header, $application->user, $if, $tx4);
        break;
    case "dissolve":
        dissolve($application->db, $application->header, $application->user, $tx4);
        break;
    case "donatewe":
        donate_wealth($application->user);
        break;
    case "donatedo":
        donate_wealth_do($application->db, $application->header, $application->user, $userId, $nu1, $nu2, $nu3);
        break;
    case "fcrimecu":
        family_crime_current($application->db, $application->header, $application->user, $if, $count, $nu4);
        break;
    case "familyta":
        family_tag($application->db, $application->header, $application->user, $if, $tx4);
        break;
    case "famvault":
        family_vault($application->db, $application->header, $application->user, $if, $nu1, $nu2, $nu3);
        break;
    case "dialslap":
        dial_a_slap($application->db, $application->header, $application->user, $userId, $nu1, $nu2);
        break;
    case "inventry":
        inventory($application->db, $application->user, $if);
        break;
    case "leavefam":
        leave_family($application->header, $userId, $if);
        break;
    case "leavefdo":
        leave_family_do($application->db, $application->header, $application->user, $userId, $if);
        break;
    case "massmail":
        mass_mail($application->db, $application->user, $userId, $if, $tx1, $tx2);
        break;
    case "masspaym":
        mass_payment($application->db, $application->header, $application->user, $if, $count, $nu1);
        break;
    case "membered":
        member_edit($application->db, $application->user, $nu4);
        break;
    case "memberdo":
        member_edit_do($application->db, $application->header, $application->user, $nu1, $nu2, $tx1);
        break;
    case "membersh":
        membership($application->db, $application->user, $if, $count, $nu1, $nu4);
        break;
    case "removeme":
        remove_member($application->db, $application->header, $application->user, $userId, $if, $nu4);
        break;
    case "respecti":
        respect_improve($application->db, $application->header, $application->user, $if, $nu1);
        break;
    case "renamefa":
        rename_family($application->db, $application->header, $application->user, $if, $tx4);
        break;
    case "wardecla":
        war_declare($application->db, $application->header, $application->user, $nu1, $nu2);
        break;
    case "warviews":
        war_views($application->db, $application->user);
        break;
    case "warsrask":
        war_surrender($application->db, $application->header, $application->user, $nu1, $nu2, $nu4);
        break;
    case "warsracc":
        war_surrender_accept($application->db, $application->user, $nu4);
        break;
    case "warfarel":
        warfare_lockdown($application->db, $application->header, $application->user, $if, $tx1);
        break;
    case "warfarlr":
        warfare_lockdown_end($application->db, $application->header, $application->user, $userId, $nu4, $tx1);
        break;
    case "famindex":
    default:
        index($application->db, $application->user, $if, $tx4);
        break;
}

function index(Database $db, array $user, array $if, string $tx4): void
{
    if (!$tx4) {
        $tx4 = 'Events';
    }

    print '
        <div style=\'border-left: solid 1px rgb(102,102,102);padding-left:.5em;\'>' . mysql_tex_out($if['famDescInt']) . '</div><br>
        <h5>Most Recent ' . $tx4 . ' &nbsp; &nbsp; <span class=light>(<a href=\'familyYours.php?action=index&tx4=Attacks\'>Attacks</a> &nbsp;&middot;&nbsp; <a href=\'familyYours.php?action=index&tx4=Events\'>Events</a>)</span></h5>
        <table width=80% cellpadding=3 cellspacing=0 class=table>
    ';

    if ($tx4 == 'Attacks') {
        $qa = $db->query("SELECT a.laTime, a.laDefender, a.laLogShort, u.userid FROM logsAttacks a LEFT JOIN users u ON a.laAttacker = u.userid OR a.laDefender = u.userid WHERE u.gang = {$user['gang']} ORDER BY laTime DESC LIMIT 50;");
        while ($ra = mysqli_fetch_assoc($qa)) {
            print '<tr><td class=borders><div class=floatright><span class=light>' . date('F j Y', $ra['laTime']) . ' at ' . date('g:i a', $ra['laTime']) . '</span>&nbsp;</div>&nbsp;' . mafiosoLight($ra['laDefender']) . ' ' . $ra['laLogShort'] . '</td></tr>';
        }
    } else if ($tx4 == 'Events') {
        $qe = $db->query("SELECT gevTIME, gevTEXT FROM gangevents WHERE gevGANG = {$user['gang']} ORDER BY gevTIME DESC LIMIT 50");
        while ($re = mysqli_fetch_assoc($qe)) {
            print '<tr><td class=borders><div class=floatright><span class=light>' . date('F j Y', $re['gevTIME']) . ' at ' . date('g:i a', $re['gevTIME']) . '</span></div>&nbsp;' . $re['gevTEXT'] . '</td></tr>';
        }
    }

    print '</table>';
}

function announcement(Database $db, Header $headers, array $user, array $if, string $tx4): void
{
    print '<h5>Change Family Announcement</h5>';
    
    if (!$tx4) {
        print '
            <div style=\'border-left: solid 1px rgb(102,102,102);padding-left:.5em;\'>' . mysql_tex_out($if['famDescInt']) . '</div><br><br>
            <form action=\'familyYours.php\' method=GET>
                <input type=hidden name=action value=\'announce\'>
                <textarea name=tx4 cols=60 rows=7>' . mysql_tex_edit($tx4) . '</textarea>
                <br><input type=submit value=\'Change Announcement\'>
            </form>
        ';
    } else {
        if ($user['gangrank'] > 2) {
            print '<p>You are not powerful enough to change this Families announcement.</p>';
            
            $headers->endpage();
            exit;
        }
        
        $db->query("UPDATE family SET famDescInt = '$tx4' WHERE famID = {$user['gang']}");
        
        print '
            <p>Family internal announcement changed.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';
    }
}

function dissolve(Database $db, Header $headers, array $user, string $tx4): void
{
    print "<h5>Dissolve Family</h5>";

    if ($user['gangrank'] != 1) {
        print '<p>Bad monkey.  You cannot dissolve the family unless you are the Don and the Family has less than 100 Respect.</p>';
        
        $headers->endpage();
        exit;
    }
    
    if (!$tx4) {
        print '
            <div style=\'border-left: solid 1px rgb(102,102,102);padding-left:.5em;\'><br><br>
                <strong>This CANNOT be undone. Be sure you wish to do this.</strong><br><br>
            </div><br><br>
            <p>Dissolving your family means to kick everyone out, end all wars, throw away the Family wealth, and close down the Family store. Those who are at war with you will lose 100 Family Respect if they started the war, nothing if you started it.</p><br>
            <form action=\'familyYours.php\' method=GET>
                <input type=hidden name=action value=\'dissolve\'>
                <input type=hidden name=tx4 value=\'dissolveYES\'>
                <br><input type=submit value=\'Dissolve Family\'>
            </form>
        ';
        
        $headers->endpage();
        exit;
    }
    
    if ($tx4 == 'dissolveYES') {
        $db->query("UPDATE family SET famRespect = 0 WHERE famID = {$user['gang']}");
        $db->query("UPDATE users SET gang = 0, `rank` = 'Mafioso', gangtitle = 0, gangrank = 0, daysingang = 0 WHERE gang = {$user['gang']}");
        
        $rbus = mysqli_fetch_assoc($db->query("SELECT busOwnerID, busID FROM business WHERE busOwnerID = {$user['gang']}"));
        if ($rbus['busOwnerID'] == $user['gang']) {
            $db->query("DELETE FROM businessItems WHERE busItemBusID = {$rbus['busID']}");
            $db->query("DELETE FROM business WHERE busOwnerID = {$user['gang']}");
        }
        
        $qwd = $db->query("SELECT famWarAtt FROM familyWar WHERE famWarEnd = 0 AND famWarDef = {$user['gang']}");
        while ($rwd = mysqli_fetch_assoc($qwd)) {
            $db->query("UPDATE family SET famRespect = famRespect - 15 WHERE famID = {$rwd['famWarAtt']}");
        }
        
        $db->query("UPDATE familyWar SET famWarEnd = unix_timestamp(), famWarDisID = {$user['gang']} WHERE famWarDef = {$user['gang']} OR famWarAtt = {$user['gang']}");
        $db->query("DELETE FROM familyActiveCrime WHERE faFamily={$user['gang']}");
        
        newsPost(1, "The " . familyName($user['gang']) . " has been dissolved. They sent the last of their muscle to hit the families they were at war with and then faded into history.");
    }
}

function description(Database $db, Header $headers, array $user, array $if, string $tx4): void
{
    print '<h5>Change Family Description</h5>';

    if (!$tx4) {
        print '
            <div style=\'border-left: solid 1px rgb(102,102,102);padding-left:.5em;\'>' . mysql_tex_out($if['famDesc']) . '</div><br><br>
            <form action=\'familyYours.php\' method=GET>
                <input type=hidden name=action value=\'describe\'>
                <textarea name=tx4 cols=70 rows=20>' . mysql_tex_edit($tx4) . '</textarea>
                <br><input type=submit value=\'Change Description\'>
            </form>
        ';
    } else {
        if ($user['gangrank'] > 2) {
            print '<p>You are not powerful enough to change this Families description.</p>';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE family SET famDesc = '$tx4' WHERE famID = {$user['gang']}");

        print '
            <p>Family description changed.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';
    }
}

function dial_a_slap(Database $db, Header $headers, array $user, int $userId, int $nu1, int $nu2): void
{
    print '<h5>Dial-a-Slap</h5><br>';

    if (!$nu1) {
        print '
            <p>Under normal circumstances, you cannot attack your own Family. However, if a Family member is getting out of line, you can send two members around to remind them of their loyalties. Just call a couple Family members to head over there and slap them around.</p>
            <p>The cost is three points of Family Resepct and six Tokens of Respect from the target. Oh, and they naturally get to visit the hospital for a short time as well.</p>
        ';

        $qav = $db->query("SELECT userid FROM users WHERE gang = {$user['gang']} AND jail = 0 AND hospital = 0");
        $available = mysqli_num_rows($qav);
        if ($available > 2) {
            print '
                <form action=\'familyYours.php?action=dialslap\' method=POST>
                    <input type=hidden name=nu2 value=\'' . $userId . '\'>
                    ' . mafiosoMenu('nu1', "AND gang={$user['gang']} AND userid!={$userId}") . ' &nbsp;
                    <input type=submit value=\'Send a Slap\'>
                </form>
            ';
        } else {
            print '<p>You do not have two family members to send - even if you go yourself! You cannot send two family members to slap someone if you do not have the proper staff.</p>';
        }
    } else {
        if ($user['gangrank'] > 5) {
            print '<p>You are not powerful enough to dial-a-slap.</p>';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE family SET famRespect = famRespect - 3 WHERE famID = {$user['gang']}");
        $db->query("UPDATE users SET respect = respect - 6, hospital = hospital + 60, hjReason = 'Their Family slapped them down.', jail = 0 WHERE userid = {$nu1}");

        logEvent($nu1, "You have been slapped by " . mafioso($nu2) . ".");

        print '
            <p>You have successfully slapped ' . mafiosoName($nu1) . '. Nice.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';

        $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'Family member '" . mafiosoLight($nu1) . "' was slapped by '" . mafiosoLight($nu2) ."'')");
    }
}

function donate_wealth(array $user): void
{
    print '
        <h5>Wealth donations to the Family</h5>
        <p>You have ' . moneyFormatter($user['money']) . ' in cash, ' . moneyFormatter($user['moneyChecking']) . ' in your checking account and ' . number_format($user['respect']) . ' Tokens of Respect to give.</p>
        <form action=\'familyYours.php?action=donatedo\' method=POST>
            <table width=60% cellspacing=0 cellpadding=3 class=table>
                <tr>
                    <td>
                        <strong>Cash on Hand:</strong><br>
                        <input type=text name=nu1 size=15 value=\'0\'>
                    </td>
                    <td>
                        <strong>Checking Account:</strong><br>
                        <input type=text name=nu2 size=15 value=\'0\'>
                    </td>
                    <td>
                        <strong>Tokens:</strong><br>
                        <input type=text name=nu3 size=5 value=\'0\'>
                    </td>
                    <td><br><input type=submit value=\'Donate\'></td>
                </tr>
            </table>
        </form><br><br>
        Your current donation balance:<br>
        Cash: ' . $user['gangWealth'] . '<br>Respect: ' . $user['gangRespect'];
}

function donate_wealth_do(Database $db, Header $headers, array $user, int $userId, int $nu1, int $nu2, int $nu3): void
{
    print '<h5>Wealth donations to the Family</h5>';

    if ($nu1 < 0 || $nu2 < 0 || $nu3 < 0) {
        print '
            <p>The Family does not provide loans. For trying to cheat you have also lost Respect. You may appeal to the staff to have it replaced if you wish.</p>
            <p><a href=\'familyYours.php\'>Return to the Family</a></p>
        ';

        $db->query("UPDATE users SET respect = respect - 2 WHERE userid = {$userId}");

        $headers->endpage();
        exit;
    }

    if ($nu1 > $user['money']) {
        print '
            <p>You are trying to donate more cash than you have on hand.</p>
            <p><a href=\'familyYours.php?action=donat\'>Try Again</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($nu2 > $user['moneyChecking']) {
        print '
            <p>You are trying to donate more money than you have in the bank.</p>
            <p><a href=\'familyYours.php?action=donat\'>Try again</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($nu3 > $user['respect']) {
        print '
            <p>You are trying to donate more Respect than you have.</p>
            <p><a href=\'familyYours.php?action=donat\'>Try again</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET money = money - {$nu1}, moneyChecking = moneyChecking - {$nu2}, gangWealth = gangWealth + {$nu1} + {$nu2}, respect = respect - {$nu3}, gangRespect = gangRespect + {$nu3} WHERE userid = {$userId}");
    $db->query("UPDATE family SET famVaultCash = famVaultCash + {$nu1} + {$nu2}, famVaultTokens = famVaultTokens + {$nu3} WHERE famID = {$user['gang']}");
    $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), '" . mafiosoLight($userId) . " donated " . moneyFormatter($nu1 + $nu2) . " and {$nu3} Tokens of Respect.');");

    if ($nu1 > 0) {
        $db->query("INSERT INTO logsWealth(lwSender, lwSenderIP, lwReceiver, lwReceiverIP, lwAmount, lwTime, lwType, lwSource) VALUES ({$userId}, '{$user['trackActionIP']}', 0, '{$user['gang']}', $nu1, unix_timestamp(), 'cash', 'family')");
    }

    if ($nu2 > 0) {
        $db->query("INSERT INTO logsWealth(lwSender, lwSenderIP, lwReceiver, lwReceiverIP, lwAmount, lwTime, lwType, lwSource) VALUES ({$userId}, '{$user['trackActionIP']}', 0, '{$user['gang']}', $nu2, unix_timestamp(), 'bank', 'family')");
    }

    if ($nu3 > 0) {
        $db->query("INSERT INTO logsWealth(lwSender, lwSenderIP, lwReceiver, lwReceiverIP, lwAmount, lwTime, lwType, lwSource) VALUES ({$userId}, '{$user['trackActionIP']}', 0, '{$user['gang']}', $nu3, unix_timestamp(), 'tokens', 'family')");
    }

    print '
        <p>You donated ' . moneyFormatter($nu1) . ' in cash, ' . moneyFormatter($nu2) . ' from your bank account and ' . number_format($nu3) . ' Tokens of Respect to the Family. Your Don thanks you for your generosity.</p>
        <p><a href=\'familyYours.php\'>Back to the Family.</a></p>
    ';
}

function family_crime_current(Database $db, Header $headers, array $user, array $if, int $count, int $nu4): void
{
    print '<h5>Family Crimes</h5>';

    if ($nu4) {
        $rcr = mysqli_fetch_assoc($db->query("SELECT fcDays, fcName FROM familyCrime WHERE fcID = {$nu4}"));
        $rca = mysqli_fetch_assoc($db->query("SELECT faID FROM familyActiveCrime WHERE faCrime = {$nu4} AND faFamily = {$user['gang']}"));

        if ($rca['faID'] > 0) {
            $db->query("DELETE FROM familyActiveCrime WHERE faID = {$rca['faID']}");
            $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'Your family has abandoned a crime: {$rcr['fcName']}')");

            print '<p>You have abandoned your crime, ' . $rcr['fcName'] . '. You gain nothing from the time spent in the planning stages but the Family members involved are now free for other tasks.</p>';
        } else {
            $rcount = mysqli_fetch_assoc($db->query("SELECT sum(fc.fcMembers) AS famused FROM familyActiveCrime fa LEFT JOIN familyCrime fc ON fa.faCrime = fc.fcID WHERE fa.faFamily = {$user['gang']}"));
            $rcr = mysqli_fetch_assoc($db->query("SELECT fcMembers, fcDays FROM familyCrime WHERE fcID = {$nu4}"));

            if ($rcr['fcMembers'] > ($count - $rcount['famused'])) {
                print '<p>You do not have enough Family Members to commit that crime. Go find fresh recruits!</a>';

                $headers->endpage();
                exit;
            } else {
                $db->query("INSERT INTO familyActiveCrime(faCrime, faDaysLeft, faFamily) VALUES({$nu4}, {$rcr['fcDays']}, {$if['famID']})");
                $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'Your family is commiting a new crime: {$rcr['fcName']}')");

                print '<p>You have begun your new crime, ' . $rcr['fcName'] . '. It will take a few days to coordinate before you pull it off.  ' . $rcr['fcDays'] . ' to be exact. Family Crimes occur in the tiny little hours of the early morning.</p>';
            }
        }
    } else {
        print '
            <table width=80% cellspacing=0 cellspacing=3 class=table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Staff</th>
                    <th>Days</th>
                    <th>Action</th>
                    <td></td>
                </tr>
        ';

        $qcr = $db->query("SELECT fcID, fcName, fcDescription, fcMembers, fcDays FROM familyCrime WHERE fcMembers <= {$count} ORDER BY fcID");
        while ($rcr = mysqli_fetch_assoc($qcr)) {
            $rcount = mysqli_fetch_assoc($db->query("SELECT sum(fc.fcMembers) AS famused FROM familyActiveCrime fa LEFT JOIN familyCrime fc ON fa.faCrime = fc.fcID WHERE fa.faFamily = {$user['gang']}"));
            $rac = mysqli_fetch_assoc($db->query("SELECT faDaysLeft,  FROM familyActiveCrime WHERE faCrime = {$rcr['fcID']} AND faFamily = {$user['gang']}"));

            $do = '';
            if ($user['gangrank'] < 8 and $rac['faDaysLeft'] > 0) {
                $do = '<span title=\'Days to Completion\' class=light>' . $rac['faDaysLeft'] . ' left</span>';
            } elseif ($rac['faDaysLeft'] > 0) {
                $do = '<a href=\'familyYours.php?action=fcrimecu&nu4=' . $rcr['fcID'] . '\'><span title=\'Cancel early\' class=light>' . $rac['faDaysLeft'] . ' left</span></a>';
            } elseif ($rcr['fcMembers'] > ($count - $rcount['famused'])) {
                $do = '<span title=\'Family not large enough\' class=light>wait</span>';
            } elseif ($user['gangrank'] == 1) {
                $do = '<a href=\'familyYours.php?action=fcrimecu&nu4=' . $rcr['fcID'] . '\'><span title=\'Begin crime\' class=lighter>Commit</span></a>';
            }

            print '
                <tr>
                    <td valign=top>' . $rcr['fcName'] . '</td>
                    <td>' . $rcr['fcDescription'] . '</td>
                    <td class=center>' . $rcr['fcMembers'] . '</td>
                    <td class=center>' . $rcr['fcDays'] . '</td>
                    <td class=center>' . $do . '</td>
                </tr>
            ';
        }

        print '</table>';
    }
}

function family_tag(Database $db, Header $headers, array $user, array $if, string $tx4): void
{
    print '<h5>Change Family Tag</h5>';

    if (!$tx4) {
        print '
            <div style=\'border-left: solid 1px rgb(102,102,102);padding-left:.5em;\'>' . mysql_tex_out($if['famTag']) . '</div><br><br>
            <form action=\'familyYours.php\' method=GET>
                <input type=hidden name=action value=\'familyta\'>
                New tag: <input type=text name=tx4 value=\'\'> &nbsp;&nbsp;
                <input type=submit value=\'Change Family Tag\'>
            </form>
        ';
    } else {
        if ($user['gangrank'] > 2) {
            print '<p>You are not powerful enough to change your Families description.</p>';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE family SET famTag = '$tx4' WHERE famID = {$user['gang']}");

        print '<p>Family Tag changed.</p><p><a href=\'familyYours.php\'>Family Home</a></p>';
    }
}

function family_vault(Database $db, Header $headers, array $user, array $if, int $nu1, int $nu2, int $nu3): void
{
    print '
        <h5>Family Wealth</h5>
        <p>The vault has ' . moneyFormatter($if['famVaultCash']) . ' in cash and ' . number_format($if['famVaultTokens']) . ' Tokens of Respect.</p>
    ';

    if ($user['gangrank'] > 4) {
        print '<p>Bad monkey. No messing about in the Vault without authority.</p>';

        $headers->endpage();
        exit;
    }

    print '
        <form action=\'familyYours.php?action=famvault\' method=POST>
            <p>Give <input type=text size=10 name=nu1> and <input type=text size=5 name=nu2> Tokens of Respect to <select name=nu3 type=dropdown>
    ';

    $query = $db->query("SELECT userid, username FROM users WHERE gang = {$if['famID']}");
    while ($row = mysqli_fetch_assoc($query)) {
        print '<option value=\'' . $row['userid'] . '\'>' . $row['username'] . '</option>';
    }

    print '
                </select>
            </p><br>
            <input type=submit value=\'Give it away\'>
        </form><br><br>
    ';

    if ($nu1 > 0 || $nu2 > 0) {
        if ($nu1 > $if['famVaultCash']) {
            print '<p>The vault does not have that much cash!</p>';

            $headers->endpage();
            exit;
        } else if ($nu2 > $if['famVaultTokens']) {
            print '<p>The vault does not have that much Respect!</p>';

            $headers->endpage();
            exit;
        }

        $row = mysqli_fetch_assoc($db->query("SELECT trackActionIP FROM users WHERE userid = {$nu3}"));
        if ($nu1 < 0) {
            $nu1 = 0;
        }

        if ($nu2 < 0) {
            $nu2 = 0;
        }

        $db->query("UPDATE users SET moneyChecking = moneyChecking + {$nu1}, gangWealth = gangWealth - {$nu1}, respect = respect + {$nu2}, gangRespect = gangRespect - {$nu2} WHERE userid = {$nu3}");
        $db->query("UPDATE family SET famVaultCash = famVaultCash - {$nu1}, famVaultTokens = famVaultTokens - {$nu2} WHERE famID = {$if['famID']}");

        logEvent($nu3, "You were given " . moneyFormatter($nu1) . " and {$nu2} Tokens of Respect from your Family.");

        if ($nu1 > 0) {
            logWealth(0, $user['gang'], $nu3, $row['trackActionIP'], $nu1, 'bank', 'family');
        }

        if ($nu2 > 0) {
            logWealth(0, $user['gang'], $nu3, $row['trackActionIP'], $nu1, 'tokens', 'family');
        }

        $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$if['famID']}, unix_timestamp(), '" . mafiosoLight($nu3) . " was given " . moneyFormatter($nu1) . " and {$nu2} Tokens of Respect from the Family.')");

        print mafiosoLight($nu3) . ' was given ' . moneyFormatter($nu1) . ' and ' . number_format($nu2) . ' Tokens of Respect from the Family.';

        $ur = mysqli_fetch_assoc($db->query("SELECT gangWealth, gangRespect FROM users WHERE userid = {$nu3}"));

        print '
            Their current donation balance:<br>
            Cash: ' . $ur['gangWealth'] . '<br>Respect: ' . $ur['gangRespect'];
    }
}

function inventory(Database $db, array $user, array $if): void
{
    print '
        <h5 style=\'margin-bottom:-1em;\'>Family Possessions</h5>
        <table width=80% cellspacing=0 cellpadding=1 class=table style=\'line-height:1.5em;\'>
            <tr>
                <td valign=top width=310>
    ';

    $inv1 = $db->query("SELECT iv.inv_id, iv.inv_qty, inv_itemid, inv_itmexpire, i.itmtype, i.itmname, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_famid = {$user['gang']} AND i.itmtype < 40 ORDER BY i.itmtype, i.itmname");

    $lt = '';
    while ($i = mysqli_fetch_assoc($inv1)) {
        if ($lt != itemType($i['itmtype'])) {
            $lt = itemType($i['itmtype']);
            print '<h4>' . $lt . '</h4>';
        }

        if ($user['gangrank'] < 5) {
            $details = '<a title=\'Send, trade or sell item\' href=\'items.php?action=util&fid=' . $user['gang'] . '&iid=' . $i['inv_id'] . '\'>Utilize</a>';
        } else {
            $details = '
                <form action=\'mailbox.php?action=send\' method=POST>
                    <input type=hidden name=mailTo value=\'' . $if['famDon'] . '\'>
                    <input type=hidden name=subject value=\'Family Inventory Request\'>
                    <input type=hidden name=message value=\'May I please have a ' . $i['itmname'] . ' from our Family Inventory?\'>
                    <input type=submit value=\'Request Item\'>
                </form>
            ';
        }

        print '<span class=floatrightfixed>' . $details . '</span>' . itemInfo($i['itmid']);

        if ($i['inv_qty'] > 1) {
            print '&nbsp;x' . $i['inv_qty'];
        }

        if ($i['inv_itemid'] > 600) {
            print '&nbsp; <span title=\'days left\'>(' . $i['inv_itmexpire'] . ')';
        }

        print '<br>';
    }

    print '
        </td>
        <td width=310 valign=top style=\'border-left: solid 1px rgb(102,102,102);padding-top:-10px;padding-left:5px;\'>
    ';

    $inv2 = $db->query("SELECT iv.inv_id, iv.inv_qty, inv_itemid, inv_itmexpire, i.itmtype, i.itmname, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_famid = {$user['gang']} AND i.itmtype > 39 ORDER BY i.itmtype, i.itmname");

    $lt = '';
    while ($i = mysqli_fetch_assoc($inv2)) {
        if ($lt != itemType($i['itmtype'])) {
            $lt = itemType($i['itmtype']);
            print '<h4>' . $lt . '</h4>';
        }

        if ($user['gangrank'] < 5) {
            $details = '<a title=\'Send, trade or sell item\' href=\'items.php?action=util&fid=' . $user['gang'] . '&iid=' . $i['inv_id'] . '\'>Utilize</a>';
        } else {
            $details = '
                <form action=\'mailbox.php?action=send\' method=POST>
                    <input type=hidden name=mailTo value=\'' . $if['famDon'] . '\'>
                    <input type=hidden name=subject value=\'Family Inventory Request\'>
                    <input type=hidden name=message value=\'May I please have a ' . $i['itmname'] . ' from our Family Inventory?\'>
                    <input type=submit value=\'Request Item\'>
                </form>
            ';
        }

        print '<span class=floatrightfixed>' . $details . '</span>' . itemInfo($i['itmid']);

        if ($i['inv_qty'] > 1) {
            print '&nbsp;x' . $i['inv_qty'];
        }

        if ($i['inv_itemid'] > 600) {
            print '&nbsp; <span title=\'days left\'>(' . $i['inv_itmexpire'] . ')';
        }

        print '<br>';
    }

    print '
                </td>
            </tr>
        </table><br>
    ';
}

function leave_family(Header $headers, int $userId, array $if): void
{
    print '<h5>Leaving the Family</h5><br>';

    if ($if['famDon'] == $userId) {
        print '
            <p>You cannot leave while you are the Don of your Family.</p>
            <p><a href=\'familyYours.php?action=membersh\'>Please select a new Don first</a>.</p>
        ';

        $headers->endpage();
        exit;
    }
    print '<p>Are you sure you want to leave your Family?  It\'s awfully cold out there, and dark. I heard there were wolves too.</p><p><a href=\'familyYours.php?action=leavefdo\'>I am sure. Get me out of here.</a></p>';
}

function leave_family_do(Database $db, Header $headers, array $user, int $userId, array $if): void
{
    print '<h5>Leaving the Family</h5><br>';

    if ($if['famDon'] == $userId) {
        print '
            <p>You cannot leave while you are the Don of your Family.</p>
            <p><a href=\'familyYours.php?action=membersh\'>Please select a new Don first</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET gang = 0, daysingang = 0, gangtitle = '', gangrank = 0, gangLockdown = 0, gangWealth = 0, gangRespect = 0 WHERE userid = {$userId}");
    $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES ({$user['gang']}, unix_timestamp(), '" . mafiosoLight($userId) . " left the Family. Go get \'em.');");

    print '<br><br><p class=center>You left your Family.<br>You feel very alone and exposed.</p><br><br>';
}

function mass_mail(Database $db, array $user, int $userId, array $if, string $tx1, string $tx2): void
{
    print '<h5>Mail Entire Family</h5>';

    if (!$tx2) {
        print '
            <form action=\'familyYours.php?action=massmail\' method=POST>
                <input type=hidden name=tx1 value=\'' . $if['famName'] . ' Announcement\'><br>
                <textarea name=tx2 rows=10 cols=75></textarea><br>
                <input type=submit value=\'Send Mail\'>
            </form>
        ';
    } else if ($user['gangrank'] <= 2) {
        $query = $db->query("SELECT userid, username FROM users WHERE gang = {$user['gang']}");
        while ($row = mysqli_fetch_assoc($query)) {
            mailMafioso($userId, $row['userid'], $tx1, $tx2);

            print '<p>Mail sent to ' . $row['username'] . '.</p>';
        }

        print '
            <p>Mass mail complete.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';
    }
}

function mass_payment(Database $db, Header $headers, array $user, array $if, int $count, int $nu1): void
{
    print '
        <h5>Cash Payments to the entire Family</h5>
        <form action=\'familyYours.php?action=masspaym\' method=POST>
            Amount: <input type=text name=nu1>
            <input type=submit value=\'Send Money\'>
        </form><br><br>
    ';

    if ($nu1 > 0) {
        if (($count * $nu1) > $if['famVaultCash']) {
            print '<p>You do not have that kind of money, what were you thinking?<br>A donation like this requires ' . moneyFormatter($count * $nu1) . ' and you only have ' . moneyFormatter($if['famVaultCash']) . '.</p>';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE family SET famVaultCash = famVaultCash - ($count * {$nu1}) WHERE famID = {$user['gang']}");
        $query = $db->query("SELECT userid, username, trackActionIP FROM users WHERE gang = {$user['gang']}");
        while ($row = mysqli_fetch_assoc($query)) {
            logEvent($row['userid'], "You were given " . moneyFormatter($nu1) . " from your Family.");
            logWealth(0, $user['gang'], $row['userid'], $row['trackActionIP'], $nu1, 'bank', 'family');

            $db->query("UPDATE users SET moneyChecking = moneyChecking + {$nu1} WHERE userid = {$row['userid']}");

            print '<p>Money sent to ' . $row['username'] . '.</p>';
        }

        $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'The Family was given " . moneyFormatter($nu1) . " each.')");

        print '<p>Mass payment complete!</p>';
    }
}

function member_edit(Database $db, array $user, int $nu4): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT gangrank, gangtitle FROM users WHERE userid = {$nu4}"));
    print '
        <form action=\'familyYours.php?action=memberdo\' method=POST>
            <input type=hidden name=nu1 value=\'' . $nu4 . '\'>
            <table width=65% cellspacing=0 cellpadding=3 class=table>
                <tr>
                    <th style=\'text-align:left;\'>Member</th>
                    <th style=\'text-align:left;\'>Title</th>
                </tr>
                <tr>
                    <td>' . mafiosoLight($nu4) . '</td>
                    <td><input type=text name=tx1 size=15 value=\'' . $row['gangtitle'] . '\'></td>
                </tr>
                <tr><td colspan=3>&nbsp;</td></tr>
                <tr>
                    <th style=\'text-align:left;\'>Current Rank</th>
                    <th style=\'text-align:left;\'>New</th>
                </tr>
                <tr>
                    <td>
    ';

    if ($row['gangrank'] == 1) {
        print 'Don';
    } else if ($row['gangrank'] == 2) {
        print 'Bastone';
    } else if ($row['gangrank'] == 3) {
        print 'Consiglieri';
    } else if ($row['gangrank'] == 4) {
        print 'Contabile';
    } else if ($row['gangrank'] == 5) {
        print 'Caporegime';
    } else if ($row['gangrank'] == 6) {
        print 'Sotto Capo';
    } else {
        print 'Sgarrista';
    }

    print '
        </td>
        <td>
            <input type=radio name=nu2 value=\'2\'>Bastone <em>(underboss)</em><br>
            <input type=radio name=nu2 value=\'3\'>Consiglieri <em>(advisor)</em><br>
            <input type=radio name=nu2 value=\'4\'>Contabile <em>(financier)</em><br>
            <input type=radio name=nu2 value=\'5\'>Caporegime <em>(lieutenants)</em><br>
            <input type=radio name=nu2 value=\'6\'>Sotto Capo <em>(lesser boss)</em><br>
            <input type=radio name=nu2 value=\'7\'>Sgarrista <em>(members)</em><br><br>
    ';

    if ($user['gangrank'] == 1) {
        print '
            <input type=radio name=nu2 value=\'1\'>Family Don<br><br>
            <span class=light>There can be only one Family Don.<br> Making them Don means stepping down.<br>Be sure.</span>
        ';
    }


    print '
                </td>
            </table><br>
            <input type=submit value=\'Edit Member\'>
        </form><br><br>
        <p><a href=\'familyYours.php?action=removeme&nu4=' . $nu4 . '\'>Remove this member from the Family.</a> This will cause them to lose respect.</p>
    ';
}

function member_edit_do(Database $db, Header $headers, array $user, int $nu1, int $nu2, string $tx1): void
{
    if ($user['gangrank'] > 2) {
        print '<p>You do not have authority to make this change.</p>';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET gangtitle = '{$tx1}' WHERE userid = {$nu1}");

    if ($nu2 == 1) {
        $db->query("UPDATE users SET gangrank = 7, `rank` = 'Mafioso' WHERE gangrank = 1 AND gang = {$user['gang']}");
        $db->query("UPDATE family SET famDon = {$nu1} WHERE famID = {$user['gang']}");
    }

    if ($nu2 > 0) {
        $db->query("UPDATE users SET gangrank = {$nu2} WHERE userid = {$nu1}");
    }

    if ($nu2 == 1) {
        $db->query("UPDATE users SET `rank` = 'Don' WHERE userid = {$nu1}");
    }

    print '
        <p>' . mafioso($nu1) . ' has been edited.</p>
        <p><a href=\'familyYours.php?action=membersh\'>Return to Membership</a></p>
    ';
}

function membership(Database $db, array $user, array $if, int $count, int $nu1, int $nu4): void
{
    print '
        <table width=80% border=0 cellspacing=0 cellpadding=2 class=table style=\'font-size:smaller;\'>
            <tr>
                <th style=\'text-align:left;\'>Members <span class=light style=\'font-weight:normal;\'>(rank)</span></th>
                <th style=\'text-align:left;\'>Title</th>
                <th>Level</th>
                <th>CR</th>
                <th>Lock</th>
                <th colspan=2>$$/Res Donate</th>
                <th>Location</th>
                <th>Status</th>
                <th>Days</th>
                <th>
    ';

    if ($user['gangrank'] <= 2) {
        print "Edit";
    } else {
        print "&nbsp;";
    }

    print '</th></tr>';

    $query = $db->query("SELECT userid, comRank, level, money, respect, location, gangLockdown, gangrank, gangtitle, gangRespect, gangWealth, daysingang FROM users WHERE gang = {$user['gang']} ORDER BY gangrank");
    while ($row = mysqli_fetch_assoc($query)) {
        $hosp = '';
        if ($row['hospital'] > 1) {
            $hosp = '<span class=lighter>(H ' . $row['hospital'] . ')</span>';
        }

        $jail = '';
        if ($row['jail'] > 1) {
            $jail = '<span class=lighter>(J ' . $row['jail'] . ')</span>';
        }

        print '
            <tr>
                <td><span class=light>' . $row['gangrank'] . ')</span> ' . mafiosoLight($row['userid']) . '</td>
                <td>' . $row['gangtitle'] . '</td>
                <td class=center>' . $row['level'] . '</td>
                <td class=center>' . $row['comRank'] . '</td>
                <td class=center>' . $row['gangLockdown'] . '</td>
                <td style=\'text-align:right;\'>$' . number_format($row['gangWealth']) . '</td>
                <td style=\'text-align:left;\'>/ ' . number_format($row['gangRespect']) . '</td>
                <td class=center>' . locationName($row['location']) . ' ' . $hosp . ' ' . $jail . '</td>
                <td class=center>' . status($row['userid']) . '</td>
                <td class=center>' . $row['daysingang'] . '</td>
                <td class=center>
        ';

        if ($user['gangrank'] <= 2) {
            print '<a href=\'familyYours.php?action=membered&nu4=' . $row['userid'] . '\'>edit</a>';
        } else {
            print '&nbsp;';
        }

        print '</td></tr>';
    }

    print '</table>';

    if ($user['gangrank'] <= 2) {
        print '<br><h5>Invite Mafioso to your Family</h5>';

        if ($nu1 == 0 && $nu4 == 0) {
            $appCount = mysqli_num_rows($db->query("SELECT userid FROM users WHERE gangrank = {$if['famID']}"));
            if (($count + $appCount) < 10 || $user['gang'] == 1) {
                print '
                    <form action=\'familyYours.php?action=membersh\' method=POST>
                        ' . mafiosoMenu('nu1', "AND gang=0") . ' &nbsp;
                        <input type=submit value=\'Invite to Family\'>
                    </form>
                ';
            } else {
                print '<p>You cannot invite more members until you reduce the number of applications you have out there. You currently have ' . $count . ' members and ' . $appCount . ' applicants and you only have room for 10 members.</p>';
            }
        }

        if ($nu1 > 0) {
            logEvent($nu1, familyName($user['gang']) . " Inviation. <a href='family.php?action=apps&fid={$user['gang']}&mid={$nu1}&did=2'><strong>Accept</strong></a> or <a href='family.php?action=apps&fid={$user['gang']}&mid={$nu1}&did=1'><strong>Refuse</strong></a>.");
            $db->query("UPDATE users SET gangrank = {$user['gang']} WHERE userid = {$nu1}");
            print '<p>You successfully invited ' . mafioso($nu1) . ' to join your Family.</p>';
            $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES ({$if['famID']} ,unix_timestamp(), 'Offered " . mafiosoLight($nu4) . " Family invitation.');");
        }

        $qo = $db->query("SELECT userid FROM users WHERE gangrank = {$if['famID']} AND gangrank != 1");

        print '<br><h5>Pending Applications</h5>';

        while ($or = mysqli_fetch_assoc($qo)) {
            print '<p><a href=\'familyYours.php?action=membersh&nu4=' . $or['userid'] . '\'>remove offer</a> &nbsp;&middot;&nbsp; ' . mafioso($or['userid']) . '</p>';
        }

        if ($nu4 > 0) {
            $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES ({$if['famID']}, unix_timestamp(), 'Removed " . mafiosoLight($nu4) . " Family invitation.');");
            $db->query("UPDATE users SET gangrank = 0 WHERE userid = {$nu4}");

            logEvent($nu4, "The " . familyName($if['famID']) . " invitation has been revoked.");

            print '<p>You successfully removed the invite for ' . mafioso($nu4) . '.</p>';
        }
    }
}

function remove_member(Database $db, Header $headers, array $user, int $userId, array $if, int $nu4): void
{
    print '<h5>Remove Family Member</h5>';

    if ($nu4 == $if['famDon']) {
        print '
            <p>You cannot remove the Family Don in this way.<br>You must dissolve the Family as a crime organization in order to accomplish that.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($nu4 == $userId) {
        print '
            <p>You cannot kick yourself out in this way.<br>If you wish to leave, <a href=\'familyYours.php?action=leave\'>simply retire</a>.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $query = $db->query("SELECT userid FROM users WHERE userid = {$nu4} AND gang = {$if['famID']}");
    if (mysqli_num_rows($query) > 0) {
        $db->query("UPDATE users SET gang = 0, daysingang = 0, gangtitle = '', gangrank = 0, gangRespect = 0, gangWealth = 0, respect = respect - 5 WHERE userid = {$nu4}");
        print '<p>' . mafioso($nu4) . ' was removed from the Family to their great personal disgrace.</p>';
        $mafianame = mafiosoLight($nu4);
        logEvent($nu4, "You were disgracefully removed from {$if['famName']} and lost Respect.");

        $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), '{$mafianame} was kicked out of the Family.');");
    } else {
        print '
            <p>You cannot remove a non-existent user.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';
    }
}

function rename_family(Database $db, Header $headers, array $user, array $if, string $tx4): void
{
    print '<h5>Rename Family</h5>';

    if (!$tx4) {
        print '
            <div style=\'border-left: solid 1px rgb(102,102,102);padding-left:.5em;\'>' . mysql_tex_out($if['famName']) . '</div><br><br>
            <p>Renaming a Family is not something to be done lightly. You have a reputation built upon your name.  However, there are times when it is needed in spite of those considerations.  Know that if you proceed in this course, you will sacrifice 10% of your Family Respect (minimum of 10).</p>
            <form action=\'familyYours.php\' method=GET>
                <input type=hidden name=action value=\'renamefa\'>
                New Name: <input type=text name=tx4 value=\'\'> &nbsp;&nbsp;
                <input type=submit value=\'Change Family Name\'>
            </form>
        ';
    } else {
        if ($user['gangrank'] > 1) {
            print '<p>You are not powerful enough to change your the description.</p>';

            $headers->endpage();
            exit;
        }

        if ($if['famRespect'] < 11) {
            print '<p>Your Family does not have enough of a reputation to warrant a name change. Either improve your respect above 11, or simply dissolve and reform.</p>';

            $headers->endpage();
            exit;
        }

        $red = round($if['famRespect'] * 0.1);
        $db->query("UPDATE family SET famName='{$tx4}', famRespect = famRespect - {$red} WHERE famID = {$user['gang']}");

        print '
            <p>The Family name has been changed and respect reduced.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';
    }
}

function respect_improve(Database $db, Header $headers, array $user, array $if, int $nu1): void
{
    print '<h5>Improve Family Respect</h5><br>';

    if (!$nu1) {
        print '
            <p>You can increase your Families Respect by spreading around a lot of cash in the community. It is expensive though - $10 million for each gesture of Respect from the neighbors.</p>
            <form action=\'familyYours.php?action=respecti\' method=POST>
                How much Respect do you want to buy? &nbsp;
                <input type=text name=nu1 size=5 value=\'\'> &nbsp;&nbsp;
                <input type=submit value=\'Improve Respect\'>
            </form>
        ';
    } else {
        if ($user['gangrank'] > 2) {
            print '<p>You are not powerful enough to improve respect this way.</p>';

            $headers->endpage();
            exit;
        }

        $cost = $nu1 * 10000000;
        if ($if['famVaultCash'] < $cost) {
            print '<p>Your Family does not have that much money. Sorry.</p>';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE family SET famRespect = famRespect + {$nu1}, famVaultCash = famVaultCash - {$cost} WHERE famID = {$user['gang']}");

        print '
            <p>Family Respect Improved. Nicely done!</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';

        $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'Family Respect improved by {$nu1} with the expense of a ton of cash.')");
    }
}

function war_declare(Database $db, Header $headers, array $user, int $nu1, int $nu2): void
{
    print '<h5>Declare War</h5>';

    if ($nu2 > 0) {
        if ($nu2 == 1) {
            $db->query("UPDATE users SET gangLockdown = gangLockdown + 2 WHERE gangrank = 1 AND gang IN ({$user['gang']}, {$nu1})");
        }

        $db->query("INSERT INTO familyWar (famWarType, famWarAtt, famWarAttPoints, famWarDef, famWarDefPoints, famWarBegin, famWarEnd, famWarSurID, famWarDisID, famWarSurMes) VALUES ({$nu2}, {$user['gang']}, 0, {$nu1}, 0, unix_timestamp(), 0, 0, 0, '')");

        $famus = familyName($user['gang']);
        $famthem = familyName($nu1);

        $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), '<strong>We have declared war on {$famthem}</strong>'), ({$nu1}, unix_timestamp(), '<strong>{$famus} has declared war on you!</strong>'");
        newsPost(1, familyName($user['gang']) . ' has challenged ' . familyName($nu1) . ' to a ' . warType($nu2) . '.<br>Everybody run for cover and grab the popcorn.<br>This is going to be fun!');
        print '<p>You have challenged them to a ' . warType($nu2) . '!</p><p><a href=\'familyYours.php?action=warviews\'>View the action</a></p>';

        $headers->endpage();
        exit;
    }

    $rw = mysqli_fetch_assoc($db->query("SELECT famWarAtt, famWarDef FROM familyWar WHERE famWarEnd = 0 AND (famWarAtt = {$user['gang']} OR famWarDef = {$user['gang']})"));
    if ($rw['famWarAtt'] == $nu1 || $rw['famWarDef'] == $nu1) {
        print '
            <br><p>You are already in a war with that Family. Finish what you started you cannot escalate the war until this is finished.</p>
            <p><a href=\'familyYours.php?action=warviews\'>Check your progress</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    print '
        <p>What kind of warfare did you have in mind today?</p>
        <form action=\'familyYours.php?action=wardecla\' method=POST>
            <input type=hidden name=nu1 value=' . $nu1 . '>
            <input type=radio name=nu2 value=1> Don Duel<br>
            <input type=radio name=nu2 value=2 checked> Skirmish<br>
            <input type=radio name=nu2 value=3> Light Battle<br>
            <input type=radio name=nu2 value=4> Turf War<br>
            <input type=radio name=nu2 value=5> Vendetta<br><br>
            <input type=submit value=\'Go to War\'>
        </form>
    ';
}

function warfare_lockdown(Database $db, Header $headers, array $user, array $if, string $tx1): void
{
    print '<h5>Put Family on Lockdown</h5>';

    if ($tx1 == 'yes') {
        if ($if['famLockdown'] == 1) {
            print '<p>The Family has already been in Lockdown today and you must wait until tomorrow to lock up again.</p>';

            $headers->endpage();
            exit;
        }

        if (11 > $if['famRespect']) {
            print '<p>The Family does not have that much Respect!</p>';

            $headers->endpage();
            exit;
        }

        if (32000000 > $if['famVaultCash']) {
            print '<p>The vault does not have that much cash!</p>';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE family SET famVaultCash = famVaultCash - 32000000, famRespect = famRespect - 10, famLockdown = 1 WHERE famID = {$user['gang']}");

        $query = $db->query("SELECT userid, gangLockdown FROM users WHERE gang = {$user['gang']}");
        while ($row = mysqli_fetch_assoc($query)) {
            $db->query("UPDATE users SET gangLockdown = 8 WHERE userid = {$row['userid']}");
        }

        print '
            <p>The Family is on Lockdown.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';

        $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'Your family is on Lockdown')");
    } else {
        print '<p>If you put the Family on lockdown, you will not be able to make any attacks in your home town. Nor will you be able to wander the streets in leisure (go to the market, buy a car, do some crime, visit the Don, etc.) However, no one will be able to attack you in your home town either. It lasts 8 hours but can be dropped sooner. It may not be used again the same day and it may not be extended. The mercenaries you hire to protect your home are reliable and professional. They cost 10 Tokens of <em>Family</em> Respect and $32,000,000 and they do not provide refunds.</p>';

        if ($if['famLockdown'] == 1) {
            print '
                <p>The Family has already been in Lockdown today and you must wait until tomorrow to lock up again.</p>
                <p><a href=\'familyYours.php\'>Family home</a></p>
            ';
        } else {
            print '
                <form action=\'familyYours.php?action=warfarel\' method=POST>
                    <input type=hidden name=tx1 value=\'yes\'>
                    <input type=submit value=\'Lockdown\'>
                </form>
            ';
        }
    }
}

function warfare_lockdown_end(Database $db, Header $headers, array $user, int $userId, int $nu4, string $tx1): void
{
    print '<h5>End Family Lockdown</h5>';

    if ($userId == $nu4) {
        $db->query("UPDATE users SET gangLockdown = 0 WHERE userid = {$userId}");

        print '
            <p>The other members of your Family may still be on Lockdown.</p>
            <p><a href=\'familyYours.php?action=membersh\'>Family Membership List</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($tx1 == 'yes') {
        $query = $db->query("SELECT userid, gangLockdown FROM users WHERE gang = {$user['gang']}");
        while ($row = mysqli_fetch_assoc($query)) {
            $db->query("UPDATE users SET gangLockdown = 0 WHERE userid = {$row['userid']}");
        }

        print '
            <p>The Family is free from Lockdown.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';

        $db->query("INSERT INTO gangevents(gevGANG, gevTIME, gevTEXT) VALUES({$user['gang']}, unix_timestamp(), 'Your family is no longer on Lockdown.')");
    } else {
        print '
            <p>Removing the Family Lockdown will not provide and refunds.</p>
            <form action=\'familyYours.php?action=warfarlr\' method=POST>
                <input type=hidden name=tx1 value=\'yes\'>
                <input type=submit value=\'Remove Lockdown\'>
            </form>
        ';
    }
}

function war_surrender(Database $db, Header $headers, array $user, int $nu1, int $nu2, int $nu4): void
{
    print '<h5>War Surrender</h5><br>';

    if ($nu4 > 0) {
        print '<p>You are surrendering to the ' . familyName($nu4) . ' Family. If they accept, your war will end, and you will pay less in war reparations than if you had lost.';

        $war = mysqli_fetch_assoc($db->query("SELECT famWarType, famWarID FROM familyWar WHERE famWarEnd = 0 AND ((famWarAtt = {$nu4} AND famWarDef = {$user['gang']}) OR (famWarAtt = {$user['gang']} AND famWarDef = {$nu4}))"));
        if ($war['famWarType'] > 1) {
            print ' You will still pay $' . number_format(($war['famWarType'] - 1) * 3000000) . '.</p>';
        } else {
            print ' As it is a Don vs Don fight, nothing will be lost but your pride if they accept.</p>';
        }

        print '
            <p>
                <form action=\'familyYours.php?action=warsrask\' method=POST>
                    <input type=hidden name=nu1 value=\'' . $war['famWarID'] . '\'>
                    <input type=hidden name=nu2 value=' . $nu4 . '>
                    <input type=submit value=\'Send Surrender Request\'>
                </form>
            </p>
        ';

        $headers->endpage();
        exit;
    }

    if ($nu1 > 0) {
        $db->query("UPDATE familyWar SET famWarSurID = {$user['gang']} WHERE famWarID = {$nu1}");

        print '
            <p>You have offered surrender to ' . familyName($nu2) . '.</p>
            <p><a href=\'familyYours.php\'>Family Home</a></p>
        ';
    }
}

function war_surrender_accept(Database $db, array $user, int $nu4): void
{
    $war = mysqli_fetch_assoc($db->query("SELECT famWarID, famWarType FROM familyWar WHERE famWarEnd = 0 AND famWarSurID = {$nu4}"));
    if ($war['famWarType'] == 1) {
        $wgai = 0;
        $lgai = 0;
        $cash = 0;
    } else {
        $wgai = ($war['famWarType'] - 1) * 2;
        $lgai = ($war['famWarType'] - 1) * 1;
        $cash = ($war['famWarType'] - 1) * 3000000;
    }

    $db->query("UPDATE familyWar SET famWarEnd = unix_timestamp() WHERE famWarID = {$war['famWarID']}");
    $db->query("UPDATE family SET famRespect = famRespect + {$wgai}, famVaultCash = famVaultCash + {$cash} + {$cash} WHERE famID = {$user['gang']}");
    $db->query("UPDATE family SET famRespect = famRespect - {$lgai}, famVaultCash = famVaultCash - {$cash} WHERE famID = {$nu4}");

    $famlos = mysqli_fetch_assoc($db->query("SELECT famVaultCash, famID FROM family WHERE famID = {$nu4}"));
    if ($famlos['famVaultCash'] < 0) {
        $db->query("UPDATE family SET famRespect = famRespect - {$wgai}, famVaultCash = 1 WHERE famID = {$nu4}");
    }

    newsPost(1, 'The ' . familyName($user['gang']) . ' Family has forced the ' . familyName($nu4) . ' to surrender their ' . warType($war['famWarType']) . '.');

    print '
        <p>You have accepted the surrender terms of ' . familyName($nu4) . '. The war is over!</p>
        <p><a href=\'familyYours.php\'>Family Home</a></p>
    ';
}

function war_views(Database $db, array $user): void
{
    $lock = '';
    if ($user['gangrank'] <= 2) {
        $lock = '<a href=\'familyYours.php?action=warfarel\'>&middot; Lockdown &middot;</a>';
    }

    $locktime = '';
    if ($user['gangLockdown'] > 0) {
        $locktime = 'You are on Lockdown for another ' . $user['gangLockdown'] . ' hours. &nbsp;&nbsp; <a href=\'familyYours.php?action=warfarlr\'>&middot; End Lockdown &middot;</a>';
    }

    print '
        <h5>Current wars &nbsp; <span class=light>' . $lock . ' &nbsp;&nbsp; ' . $locktime . '</span></h5>
        <table width=80% cellspacing=0 cellpadding=2 class=table style=\'font-size:smaller;\'>
    ';

    $wq = $db->query("SELECT famWarAtt, famWarDef, famWarBegin, famWarEnd, famWarSurID, famWarSurMes, famWarAttPoints, famWarDefPoints FROM familyWar WHERE famWarEnd = 0 AND (famWarAtt = {$user['gang']} OR famWarDef = {$user['gang']})");
    if (mysqli_num_rows($wq) == 0) {
        print '<p>You are not currently at war. sad.</p>';
    }

    while ($row = mysqli_fetch_assoc($wq)) {
        $attacker = 'They';
        $them = $row['famWarAtt'];
        if ($user['gang'] == $row['famWarAtt']) {
            $attacker = 'You';
            $them = $row['famWarDef'];
        }

        $begd = date('F j, Y, g:ia', $row['famWarBegin']);
        print '
            <tr>
                <th style=\'text-align:left;\'>' . familyName($row['famWarAtt']) . ' (' . $row['famWarAttPoints'] . ') vs ' . familyName($row['famWarDef']) . ' (' . $row['famWarDefPoints'] . ')</th>
                <th colspan=5 style=\'font-weight:normal;text-align:right;\'>' . $attacker . ' started it ' . $begd . '</th>
                <th style=\'font-weight:normal;text-align:right;\'>
        ';

        if ($user['gangrank'] <= 2) {
            print '<a href=\'familyYours.php?action=warsrask&nu4=' . $them . '\'>Surrender</a></th>';
            if ($row['famWarSurID'] == $them) {
                print '</tr><tr><td colspan=7 class=center style=\'padding:.5em;\'><strong>' . familyName($them) . ' surrender requested -> <a href=\'familyYours.php?action=warsracc&nu4=' . $them . '\'>Accept</a></strong></td>';
            }
        } else {
            print '&nbsp;</th>';
        }

        print '
            </tr>
            <tr>
                <td>&nbsp; &nbsp;<strong>Targets</strong></td>
                <td class=center><strong>Level</strong></td>
                <td class=center><strong>Rank</strong></td>
                <td class=center><strong>Location</strong></td>
                <td class=center><strong>Status</strong></td>
                <td class=center><strong>Health</strong></td>
                <td class=center><strong>Action</strong></td>
            </tr>
        ';

        $opp = $db->query("SELECT hospital, jail, userid, level, location, hp, maxhp, comRank FROM users where gang = {$them} ORDER BY level DESC");
        while ($oppr = mysqli_fetch_assoc($opp)) {
            $hosp = '';
            if ($oppr['hospital'] > 1) {
                $hosp = '<span class=lighter>(H ' . $oppr['hospital'] . ')</span>';
            }

            $jail = '';
            if ($oppr['jail'] > 1) {
                $jail = '<span class=lighter>(J ' . $oppr['jail'] . ')</span>';
            }

            print '
                <tr>
                    <td>&nbsp; &nbsp;' . mafiosoLight($oppr['userid']) . '</td>
                    <td class=center>' . $oppr['level'] . '</td>
                    <td class=center>' . $oppr['comRank'] . '</td>
                    <td class=center>' . locationName($oppr['location']) . ' ' . $hosp . ' ' . $jail . '</td>
                    <td class=center>' . status($oppr['userid']) . '</td>
                    <td class=center>' . $oppr['hp'] . '/' . $oppr['maxhp'] . '</td>
                    <td class=center><a href=\'attack.php?ID=' . $oppr['userid'] . '\'>attack</a></td>
                </tr>
            ';
        }

        print '<tr><td colspan=7>&nbsp;</td></tr>';
    }

    print '</table><br>';

    if ($user['gangrank'] <= 2) {
        print '
            <h5>Declare War</h5>
            <form action=\'familyYours.php?action=wardecla\' method=POST>
                You are declaring war on the
                <input type=hidden name=subm value=\'submit\'>
                <select name=nu1 type=dropdown>
        ';

        $query = $db->query("SELECT famID, famName FROM family WHERE famID != {$user['gang']} AND famRespect > 0 AND famID > 1");
        while ($row = mysqli_fetch_assoc($query)) {
            $warq = $db->query("SELECT famWarAtt, famWarDef FROM familyWar WHERE famWarEnd = 0 AND (famWarAtt = {$user['gang']} OR famWarDef = {$user['gang']}) AND (famWarAtt={$row['famID']} OR famWarDef={$row['famID']})");
            $warr = mysqli_fetch_assoc($warq);
            if ($warr['famWarAtt'] != $row['famID'] && $warr['famWarDef'] != $row['famID']) {
                print '<option value=\'' . $row['famID'] . '\'>' . $row['famName'] . '</option>';
            }
        }

        print '
                    </select> 
                    Family. &nbsp;&nbsp;
                    <input type=submit value=\'Declare\'>
                </form>
            </p><br><br>
        ';
    }

    print '<h5>Past wars</h5>';

    $wq = $db->query("SELECT famWarAtt, famWarDef, famWarBegin, famWarEnd, famWarSurID, famWarDisID, famWarAttPoints, famWarDefPoints FROM familyWar WHERE famWarEnd > 0 AND (famWarAtt = {$user['gang']} OR famWarDef = {$user['gang']}) ORDER BY famWarBegin DESC");
    while ($row = mysqli_fetch_assoc($wq)) {
        $attacker = 'They';
        $them = $row['famWarAtt'];
        if ($user['gang'] == $row['famWarAtt']) {
            $attacker = 'You';
            $them = $row['famWarDef'];
        }

        $begd = date('F j, Y, g:ia', $row['famWarBegin']);
        $endd = date('F j, Y, g:ia', $row['famWarEnd']);

        if ($row['famWarSurID'] > 0) {
            $result = familyName($row['famWarSurID']) . ' surrendered';
        } else if ($row['famWarDisID'] > 0) {
            $result = familyName($row['famWarDisID']) . ' lost';
        }

        print '<p><strong>' . familyName($them) . ' War</strong><br>' . $attacker . ' started it on ' . $begd . ' and it ended ' . $endd . '.<br>' . $result . ' with ' . $row['famWarAttPoints'] . ' points for ' . familyName($row['famWarAtt']) . ' and ' . $row['famWarDefPoints'] . ' for ' . familyName($row['famWarDef']) . '.</p> ';
    }
}

$application->header->endPage();
