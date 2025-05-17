<?php

use Fmw\Database;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0);

$viewuser = mysql_num($_GET['u']);
$r = mysqli_fetch_assoc($application->db->query("SELECT u.*,us.*,g.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid LEFT JOIN family g ON g.famID=u.gang WHERE u.userid={$viewuser}"));

if ($r['userid'] == 0) {
    print '<h3>Missing Person</h3><p>Sorry, we could not find that Mafioso. Wait 24 hours and then notify the police they are missing.</p><p><a href=\'mafiosoSearch.php\'>Search for someone else</a></p>';
    $application->header->endPage();
    exit;
}

switch ($application->user['rankCat']) {
    case "Staff" :
        staffview($application->db, $application->user, $userId, $r);
        break;
    default  :
        usersview($application->db, $application->user, $userId, $r);
        break;
}

function usersview(Database $db, array $user, int $userId, array $r): void
{
    if ($user['donatordays'] > 0 && $r['rankCat'] == 'Player') {
        $donFriend = '&nbsp;&middot;&nbsp;<a href=\'friendsEnemies.php?action=addfriend&mid=' . $r['userid'] . '\'>Make Friend</a>';
        $donEnemy = '<a href=\'friendsEnemies.php?action=addenemy&mid=' . $r['userid'] . '\'>Make Enemy</a>&nbsp;&middot;&nbsp;';
    }

    if ($user['respectCut'] == 0) {
        $rescut = '<a href=\'wealth.php?act=disresp&mid=' . $r['userid'] . '\'><strong>Disrespect</strong></a>&nbsp;&middot;&nbsp;';
    }

    if ($user['respectGift'] < 2) {
        $resgift = '&nbsp;&middot;&nbsp;<a href=\'wealth.php?act=respect&mid=' . $r['userid'] . '\'><strong>Respect</strong></a>';
    }

    print '
        <table width=95% cellspacing=0 cellpadding=3 class=table>
            <tr><td colspan=4 class=center><h3 style=\'margin-bottom:.5em;\'>' . mafioso($r['userid']) . '<br><span class=signature style=\'font-size:smaller;\'>' . mysql_tex_out($r['signature']) . '</span></h3></td></tr>
    ';

    if ($userId != $r['userid']) {
        $carbomb = '';
        $rinv = mysqli_fetch_assoc($db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 90"));
        if ($rinv['inv_id'] > 0) {
            $carbomb = '<form action=\'items.php?action=use2\' method=POST><input type=hidden name=useid value=\'' . $userId . '\'><input type=hidden name=quant value=\'1\'><input type=hidden name=invid value=\'' . $rinv['inv_id'] . '\'><input type=hidden name=recus value=\'' . $r['userid'] . '\'><input type=submit value=\'Car Bomb\'></form>';
        }

        $teargas = '';
        $tinv = mysqli_fetch_assoc($db->query("SELECT inv_id FROM inventory WHERE inv_userid={$userId} AND inv_itemid=124"));
        if ($tinv['inv_id'] > 0) {
            $teargas = '<form action=\'items.php?action=use2\' method=POST><input type=hidden name=useid value=\'' . $userId . '\'><input type=hidden name=quant value=\'1\'><input type=hidden name=invid value=\'' . $tinv['inv_id'] . '\'><input type=hidden name=recus value=\'' . $r['userid'] . '\'><input type=submit value=\'Tear &nbsp;Gas\'></form>';
        }

        print '
            <tr>
                <td>&nbsp;</td>
                <td colspan=2 class=center>Transfer<br><a href=\'wealth.php?act=sendwea&use=money&mid=' . $r['userid'] . '\'>Cash</a>&nbsp;&middot;&nbsp;<a href=\'wealth.php?act=sendwea&use=moneyChecking&mid=' . $r['userid'] . '\'>Bank Funds</a>&nbsp;&middot;&nbsp;<a href=\'wealth.php?act=sendwea&use=respect&mid=' . $r['userid'] . '\'>Respect</a></td>
                <td class=floatright>' . $carbomb . $teargas . '<br></td>
            </tr>
            <tr>
                <td colspan=2><a href=\'mailbox.php?action=compose&ID=' . $r['userid'] . '\'>Mail</a>' . $resgift . $donFriend . '</td>
                <td colspan=2 style=\'text-align:right;\'>' . $donEnemy . $rescut . '<a href=\'attack.php?ID=' . $r['userid'] . '\'>Attack</a></td>
            </tr>
        ';
    }

    $rr = mysqli_num_rows($db->query("SELECT refID FROM referals WHERE refREFER = {$r['userid']}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT refREFER FROM referals WHERE refREFED = {$r['userid']}"));
    $ref = 'No one';
    if (isset($rf['refREFER']) && $rf['refREFER'] > 0) {
        $ref = mafiosoLight($rf['refREFER']);
    }

    $rfr = mysqli_fetch_assoc($db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'friend' AND clContact = {$r['userid']} GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
    $friends = isset($rfr['countValue']) ? $rfr['countValue'] : 0;

    $ren = mysqli_fetch_assoc($db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'enemy' AND clContact = {$r['userid']} GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
    $enemies = isset($ren['countValue']) ? $ren['countValue'] : 0;

    $fedj = ($r['fedjail'] > 0)
        ? '<font color=red>' . $r['fedjail'] . ' day(s) Federal Jail<br> ' . $r['fedjailReason'] . '</font><br>'
        : '';

    $fight = ($r['hospital'] || $r['jail'])
        ? '<font color=red>' . ($r['hospital'] + $r['jail']) . ' more minutes<br>' . $r['hjReason'] . '</font><br>'
        : '';

    $auto = '<p class=center>Walking</p>';
    if ($r['autoOwned'] > 1) {
        $rau = mysqli_fetch_assoc($db->query("SELECT * FROM autos WHERE auID={$r['autoOwned']}"));
        $auto = '<a href=\'automotive.php\'><img src=\'assets/images/autos/' . $rau['auName'] . '.jpg\' width=150 alt=\'' . $rau['auName'] . '\' title=\'' . $rau['auName'] . '\'></a><br>';
    }

    $mug = '<p class=center>No recent photo</p>';
    if ($r['display_pic']) {
        $mug = '<img src=\'' . $r['display_pic'] . '\' width=150 height=150 alt=\'' . $r['username'] . '\' title=\'' . $r['username'] . '\'><br>';
    }

    $birth = unserialize($r['birthday']);
    print '
            <tr>
                <th width=25% style=\'text-align:left;\'>&nbsp;General</th>
                <th width=25% style=\'text-align:left;\'>&nbsp;Financial</th>
                <th width=25% style=\'text-align:left;\'>&nbsp;Status</th>
                <th width=25%>Car &amp; Mug Shot</th>
            </tr>
            <tr>
                <td valign=top>Position: ' . $r['rankCat'] . ' ' . $r['rank'] . '<br> Birthday: ' . $birth['mth'] . ', ' . $birth['day'] . '<br> Gender: ' . $r['gender'] . '<br>Family: ' . familyName($r['gang']) . '<br></td>
                <td valign=top>Respect: ' . number_format($r['respect']) . '<br>Cash on hand: $' . number_format($r['money']) . '<br><br>Referrer: ' . $ref . '<br>Referals: ' . $rr . '<br>Friends: ' . $friends . '<br>Enemies: ' . $enemies . '</td>
                <td valign=top>Location: ' . locationName($r['location']) . '<br>Health: ' . $r['hp'] . '/' . $r['maxhp'] . '<br>Level: ' . $r['level'] . '<br>Combat Rank: ' . $r['comRank'] . '<br><br>' . $fedj . $fight . '</td>
                <td rowspan=2 valign=top class=center>' . $auto . $mug . '</td>
            </tr>
            <tr><td colspan=3>Recruited: ' . date('F j, Y', $r['trackSignupTime']) . ' &nbsp;&middot;&nbsp; <em>' . daysOld($r['trackSignupTime']) . '</em><br>Last seen: ' . date('F j, Y', $r['trackActionTime']) . ' &nbsp;&middot;&nbsp; <em>' . status($r['userid']) . '</em></td></tr>
        </table>
    ';
}


function staffview(Database $db, array $user, int $userId, array $r): void
{
    $eye = '<a href=\'staffUsers.php?action=begin_watch&mid=' . $r['userid'] . '\'>Watchful Eye</a>&nbsp;&middot;&nbsp;';
    if ($r['watchfulEye'] == 1) {
        $eye = '<a href=\'staffUsers.php?action=end_watchin&mid=' . $r['userid'] . '\'>Watchful Eye</a>&nbsp;&middot;&nbsp;';
    }

    $gagjail = '';
    $gifts = '';
    if ($user['rank'] != 'Sgarrista') {
        $gagjail = '<a href=\'staffPunish.php?action=gagform&mid=' . $r['userid'] . '\'>Gag Order</a>&nbsp;&middot;&nbsp;<a href=\'staffPunish.php?action=fedjail&mid=' . $r['userid'] . '\'>Federal Jail</a>&nbsp;&middot;&nbsp;';
        $gifts = '<form action=\'staffUsers.php?action=indgivsubm\' method=POST> <input type=hidden name=uid value=\'' . $r['userid'] . '\'> <input type=radio name=itm value=\'110\' checked>Wine Sample &nbsp; <input type=radio name=itm value=\'94\'>Fine Cigar &nbsp; <input type=radio name=itm value=\'76\'>Tequila &nbsp; <input type=radio name=itm value=\'75\'>Cowbell &nbsp; <input type=submit value=\'Give a Gift\'> </form>';
    }

    $acnt = '';
    if ($user['rank'] == 'Capo') {
        $acnt = '<a href=\'staffUsers.php?action=accsuspend&mid=' . $r['userid'] . '\'>Suspend Account</a>';
        if (!$r['login_name']) {
            $acnt = '<strong><a href=\'staffUsers.php?action=accapprove&mid=' . $r['userid'] . '\'>Renew Account</a></strong>';
        }
    }

    print '<span class=staffview>' . $eye . $gagjail . $acnt . $gifts . '</span>';
    if ($userId == 1) {
        $rinv = mysqli_fetch_assoc($db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 90"));
        if (isset($rinv['inv_id']) && $rinv['inv_id'] > 0) {
            print '<form action=\'items.php?action=use2\' method=POST><input type=hidden name=useid value=\'' . $userId . '\'><input type=hidden name=quant value=\'1\'><input type=hidden name=invid value=\'' . $rinv['inv_id'] . '\'><input type=hidden name=recus value=\'' . $r['userid'] . '\'><input type=submit value=\'Car Bomb\'></form>';
        }
    }

    $carbomb = '';
    print '
        <table width=95% cellspacing=0 cellpadding=3 class=table>
            <tr><td colspan=4 class=center><h3 style=\'margin-bottom:.5em;\'>' . mafioso($r['userid']) . '<br><span class=signature style=\'font-size:smaller;\'>' . $r['email'] . '<br>' . mysql_tex_out($r['signature']) . '</span></h3></td></tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan=2 class=center>Transfer<br><a href=\'wealth.php?act=sendwea&use=money&mid=' . $r['userid'] . '\'>Cash</a>&nbsp;&middot;&nbsp;<a href=\'wealth.php?act=sendwea&use=moneyChecking&mid=' . $r['userid'] . '\'>Bank Funds</a>&nbsp;&middot;&nbsp;<a href=\'wealth.php?act=sendwea&use=respect&mid=' . $r['userid'] . '\'>Respect</a></td>
                <td class=floatright>' . $carbomb . '</td>
            </tr>
            <tr>
                <td colspan=2><a href=\'mailbox.php?action=compose&ID=' . $r['userid'] . '\'>Mail</a>&nbsp;&middot;&nbsp;<a href=\'wealth.php?act=respect&mid=' . $r['userid'] . '\'><strong>Respect</strong></a></td>
                <td colspan=2 style=\'text-align:right;\'><a href=\'wealth.php?act=disresp&mid=' . $r['userid'] . '\'><strong>Disrespect</strong></a>&nbsp;&middot;&nbsp;<a href=\'attack.php?ID=' . $r['userid'] . '\'>Attack</a></td>
            </tr>
    ';

    $rr = mysqli_num_rows($db->query("SELECT refID FROM referals WHERE refREFER = {$r['userid']}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT refREFER FROM referals WHERE refREFED = {$r['userid']}"));
    $ref = 'No one';
    if ($rf && $rf['refREFER'] > 0) {
        $ref = mafiosoLight($rf['refREFER']);
    }

    $rfr = mysqli_fetch_assoc($db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'friend' AND clContact = {$r['userid']} GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
    $friends = isset($rfr['countValue']) ? $rfr['countValue'] : 0;

    $ren = mysqli_fetch_assoc($db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'enemy' AND clContact = {$r['userid']} GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
    $enemies = isset($ren['countValue']) ? $ren['countValue'] : 0;

    $fedj = ($r['fedjail'] > 0)
        ? '<font color=red>' . $r['fedjail'] . ' day(s) Federal Jail<br> ' . $r['fedjailReason'] . '</font><br>'
        : '';

    $fight = ($r['hospital'] || $r['jail'])
        ? '<font color=red>' . ($r['hospital'] + $r['jail']) . ' more minutes<br>' . $r['hjReason'] . '</font><br>'
        : '';

    $auto = '<p class=center>Walking</p>';
    if ($r['autoOwned'] > 1) {
        $rau = mysqli_fetch_assoc($db->query("SELECT * FROM autos WHERE auID={$r['autoOwned']}"));
        $auto = '<a href=\'automotive.php\'><img src=\'assets/images/autos/' . $rau['auName'] . '.jpg\' width=150 alt=\'' . $rau['auName'] . '\' title=\'' . $rau['auName'] . '\'></a><br>';
    }

    $mug = '<p class=center>No recent photo</p>';
    if ($r['display_pic']) {
        $mug = '<img src=\'' . $r['display_pic'] . '\' width=150 height=150 alt=\'' . $r['username'] . '\' title=\'' . $r['username'] . '\'><br>';
    }

    $birth = unserialize($r['birthday']);
    print '
        <tr>
            <th width=25% style=\'text-align:left;\'>&nbsp;General</th>
            <th width=25% style=\'text-align:left;\'>&nbsp;Financial</th>
            <th width=25% style=\'text-align:left;\'>&nbsp;Status</th>
            <th width=25%>Car &amp; Mug Shot</th>
        </tr>
        <tr>
            <td valign=top>Position: ' . $r['rankCat'] . ' ' . $r['rank'] . '<br>Donator Days: ' . number_format($r['donatordays']) . '<br> Birthday: ' . $birth['mth'] . ', ' . $birth['day'] . '<br>Gender: ' . $r['gender'] . '<br>Family: ' . familyName($r['gang']) . '<br><br>Strength: ' . number_format($r['strength']) . '<br>Agility: ' . number_format($r['agility']) . '<br>Guard: ' . number_format($r['guard']) . '<br>Labour: ' . number_format($r['labour']) . '<br>I.Q.&nbsp;: ' . number_format($r['IQ']) . '<br></td>
            <td valign=top>Respect: ' . number_format($r['respect']) . '<br>Cash on hand: $' . number_format($r['money']) . '<br>Checking: $' . number_format($r['moneyChecking']) . '<br>Savings: $' . number_format($r['moneySavings']) . '<br>Invested: $' . number_format($r['moneyInvest']) . '<br>T-Bills: $' . number_format($r['moneyTreasury']) . '<br><br>Referrer: ' . $ref . '<br>Referals: ' . $rr . '<br>Friends: ' . $friends . '<br>Enemies: ' . $enemies . '</td>
            <td valign=top>Location: ' . locationName($r['location']) . '<br>Health: ' . $r['hp'] . '/' . $r['maxhp'] . '<br>Level: ' . $r['level'] . '<br>Combat Rank: ' . $r['comRank'] . '<br>' . $fedj . $fight . '<br>Experience: ' . $r['exp'] . '/' . $r['exp_needed'] . '<br>Energy: ' . $r['energy'] . '/' . $r['maxenergy'] . '<br>Will: ' . $r['will'] . '/' . $r['maxwill'] . '<br>Bravery: ' . $r['brave'] . '/' . $r['maxbrave'] . '<br>Visits: ' . $r['visits'] . '<br></td>
            <td rowspan=2 valign=top class=center>' . $auto . $mug . '<br>
    ';

    if ($userId == 1) {
        $tai = isset($r['trackActionInfo']) ? unserialize($r['trackActionInfo']) : '';
        $myip = isset($tai['remoteaddr']) ? $tai['remoteaddr'] : 'unknown';
        $prox = isset($tai['httpvia']) ? $tai['httpvia'] : 'unknown';
        $forw = isset($tai['httpforward']) ? $tai['httpforward'] : 'unknown';
        $brow = isset($tai['useragent']) ? $tai['useragent'] : 'unknown';
        $clnt = isset($tai['clientip']) ? $tai['clientip'] : 'unknown';

        print '
            <div style=\'text-align:left;\'>
                IP Address: ' . $myip . '<br>
                Proxy IP: ' . $prox . '<br>
                Forward IP: ' . $forw . '<br>
                Browser: ' . $brow . '<br>
                Client IP: ' . $clnt . '<br>
            </div>
        ';
    }

    print '
            </td>
        </tr>
        <tr><td colspan=3><strong>Recruited:</strong> ' . date('F j, Y', $r['trackSignupTime']) . ' &nbsp;&middot;&nbsp; <em>' . daysOld($r['trackSignupTime']) . '</em>
    ';

    $qp = $db->query("SELECT userid FROM users WHERE userid != {$r['userid']} AND (trackActionIP = '{$r['trackSignupIP']}' OR trackSignupIP = '{$r['trackSignupIP']}')");
    print ' &nbsp; (<a href=\'staffUsers.php?ips=' . $r['trackSignupIP'] . '\'>' . $r['trackSignupIP'] . '</a>)<br>Same IP';
    while ($rp = mysqli_fetch_assoc($qp)) {
        print ' &middot; ' . mafiosoLight($rp['userid']);
    }

    print '<br><br><strong>Last seen:</strong> ' . date('F j, Y', $r['trackActionTime']) . ' &nbsp;&middot;&nbsp; <em>' . status($r['userid']) . '</em> ';
    $qp = $db->query("SELECT userid FROM users WHERE userid != {$r['userid']} AND (trackActionIP = '{$r['trackActionIP']}' OR trackSignupIP = '{$r['trackActionIP']}')");
    print ' &nbsp; (<a href=\'staffUsers.php?ips=' . $r['trackActionIP'] . '\'>' . $r['trackActionIP'] . '</a>)<br>Same IP';
    while ($rp = mysqli_fetch_assoc($qp)) {
        print ' &middot; ' . mafiosoLight($rp['userid']);
    }

    print '
                <br><br><form action=\'staffUsers.php?action=mafiososub\' method=POST>
                    <input type=hidden name=uid value=\'' . $r['userid'] . '\'>Approved &middot;
                    <input size=55 type=text name=tx2 value=\'' . $r['multiApproved'] . '\'>
                    <input type=submit value=\'Edit\'>
                </form>
            </td>
        </tr>
        <tr>
            <td valign=top colspan=3>
                <h5>Properties</h5>
                <table cellpadding=2 cellspacing=0 border=0 class=table style=\'font-size:smaller;\'>
                    <tr>
                        <td class=center>Palermo</td>
                        <td class=center>Rome</td>
                        <td class=center>Monte&nbsp;Carlo</td>
                        <td class=center>New York</td>
                        <td class=center>Chicago</td>
                        <td class=center>Montreal</td>
                        <td class=center>Caracas</td>
                    </tr>
                    <tr>
                        <td class=center>' . houseName($r['residence_1']) . '</td>
                        <td class=center>' . houseName($r['residence_10']) . '</td>
                        <td class=center>' . houseName($r['residence_25']) . '</td>
                        <td class=center>' . houseName($r['residence_50']) . '</td>
                        <td class=center>' . houseName($r['residence_100']) . '</td>
                        <td class=center>' . houseName($r['residence_250']) . '</td>
                        <td class=center>' . houseName($r['residence_500']) . '</td>
                    </tr>
                </table><br>
                <form action=\'staffUsers.php?action=mafiososub\' method=POST>
                    <input type=hidden name=uid value=\'' . $r['userid'] . '\'>
                    <strong>Comments</strong><br><textarea rows=8 cols=65 name=txt>' . mysql_tex_edit($r['staffnotes']) . '</textarea><br>
                    <input type=submit value=\'Edit\'>
                </form>
            </td>
            <td rowspan=2 valign=top class=lighter>
                <h5>You talking about my gear?</h5>
    ';

    print '<em>Equipped Protection</em><br>';
    $qi = $db->query("SELECT i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$r['userid']} AND iv.inv_equip = 'yes' AND i.itmtype = 60 ORDER BY i.itmCombatType, i.itmCombat");
    while ($ri = mysqli_fetch_assoc($qi)) {
        print '&nbsp;&nbsp; ' . itemInfo($ri['itmid']) . '<br>';
    }

    print '<br><em>Equipped Weapons</em><br>';
    $qi = $db->query("SELECT i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$r['userid']} AND iv.inv_equip = 'yes' AND i.itmtype != 60 ORDER BY i.itmCombatType, i.itmCombat");
    while ($ri = mysqli_fetch_assoc($qi)) {
        print '&nbsp;&nbsp; ' . itemInfo($ri['itmid']) . '<br>';
    }

    $inv = $db->query("SELECT i.itmtype, i.itmid, iv.inv_itmexpire, iv.inv_qty FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$r['userid']} ORDER BY i.itmtype, i.itmname");
    if (mysqli_num_rows($inv) > 0) {
        print '<table width=95% cellspacing=0 cellpadding=2 class=table> ';

        $lt = '';
        while ($i = mysqli_fetch_assoc($inv)) {
            if ($lt != itemType($i['itmtype'])) {
                $lt = itemType($i['itmtype']);
                print '
                    <tr><td colspan=2>&nbsp;</td></tr>
                    <tr><th colspan=2>' . $lt . '</th></tr>
                ';
            }

            $exp = '';
            if ($i['inv_itmexpire'] > 0) {
                $exp = ' (' . $i['inv_itmexpire'] . ')';
            }

            print '<tr><td>' . itemInfo($i['itmid']) . $exp . '</td><td class=center>' . $i['inv_qty'] . '</td></tr>';
        }

        print '</table>';
    }

    print '
            </td>
        </tr>
        <tr>
            <td valign=top colspan=3 class=lighter style=\'line-height:1.5em;\'>
                <h5>Attacks</h5>
    ';

    $qatt = $db->query("SELECT laTime, laDefender, laLogShort FROM logsAttacks WHERE laAttacker = {$r['userid']} OR laDefender = {$r['userid']} ORDER BY laTime DESC LIMIT 20;");
    while ($ratt = mysqli_fetch_assoc($qatt)) {
        print '<div class=floatright>' . date('m/d/y, h:i a', $ratt['laTime']) . '&nbsp;</div>&nbsp;' . mafiosoLight($ratt['laDefender']) . ' ' . $ratt['laLogShort'] . '<br>';
    }

    print '<br><h5>Events</h5>';
    $qevn = $db->query("SELECT leTime, leText FROM logsEvents WHERE leUser = {$r['userid']} ORDER BY leTime DESC LIMIT 20;");
    while ($revn = mysqli_fetch_assoc($qevn)) {
        print '<div class=floatright>' . date('m/d/y, h:i a', $revn['leTime']) . '&nbsp;</div> &nbsp; ' . $revn['leText'] . '<br>';
    }

    print '<br><h5>Wealth Transactions</h5>';
    $qw = $db->query("SELECT lwSource, lwReceiver, lwReceiverIP, lwSender, lwSenderIP, lwAmount, lwTime FROM logsWealth WHERE lwSender = {$r['userid']} OR lwReceiver = {$r['userid']} ORDER BY lwTime DESC LIMIT 20");
    while ($ml = mysqli_fetch_assoc($qw)) {
        $receiver = mafiosoLight($ml['lwReceiver']);
        $sender = mafiosoLight($ml['lwSender']);
        if ($ml['lwSource'] == 'family') {
            $receiver = mafiosoLight($ml['lwReceiver']);
            $sender = familyName($ml['lwSenderIP']);
            if ($ml['lwReceiver'] == '0') {
                $receiver = familyName($ml['lwReceiverIP']);
                $sender = mafiosoLight($ml['lwSender']);
            }
        }

        switch ($ml['lwType']) {
            case "tokens" :
                $wealth = number_format($ml['lwAmount']) . ' tokens.';
                break;
            case "cash" :
                $wealth = '$' . number_format($ml['lwAmount']) . ' in cash.';
                break;
            case "bank" :
                $wealth = '$' . number_format($ml['lwAmount']) . ' from checking.';
                break;
        }

        print '<div class=floatright>' . date('m/d/y, h:i a', $ml['lwTime']) . '&nbsp;</div> &nbsp; ' . $sender . ' gave ' . $receiver . ' ' . $wealth . '<br>';
    }

    print '<br><h5>Item Transactions</h5>';
    $qw = $db->query("SELECT liReason, liReceiver, liReceiverIP, liSender, liSenderIP, liTime FROM logsItems WHERE liSender = {$r['userid']} OR liReceiver = {$r['userid']} ORDER BY liTime DESC LIMIT 20");
    while ($ml = mysqli_fetch_assoc($qw)) {
        $receiver = mafiosoLight($ml['liReceiver']);
        $sender = mafiosoLight($ml['liSender']);
        if ($ml['liReason'] == 'family') {
            $receiver = mafiosoLight($ml['liReceiver']);
            $sender = familyName($ml['liSenderIP']);
            if ($ml['liReceiver'] == '0') {
                $receiver = familyName($ml['liReceiverIP']);
                $sender = mafiosoLight($ml['liSender']);
            }
        }

        print '<div class=floatright>' . date('m/d/y, h:i a', $ml['liTime']) . '&nbsp;</div> &nbsp; ' . $sender . ' gave ' . $receiver . ' ' . itemInfo($ml['liItem']) . '<br>';
    }

    print '<br><h5>Recent Mail</h5>';
    $mail = $db->query("SELECT mail_from, mail_to, mail_time, mail_text FROM mail WHERE mail_from = {$r['userid']} OR mail_to = {$r['userid']} ORDER BY mail_time DESC LIMIT 15");
    while ($ml = mysqli_fetch_assoc($mail)) {
        $forward = 'From: ' . mafiosoLight($ml['mail_from']) . ' To: ' . mafiosoLight($ml['mail_to']) . ' On: ' . date('F j Y', $ml['mail_time']) . ' at ' . date('g:i a', $ml['mail_time']);
        $forward .= "<br>" . mysql_tex_out($ml['mail_text']) . '<hr>';
        print $forward;
    }

    print '
                </td>
            </tr>
        </table>
    ';
}

$application->header->endPage();
