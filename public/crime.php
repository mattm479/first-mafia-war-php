<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$aut = isset($_GET['aut']) ? mysql_num($_GET['aut']) : 0;
$com = isset($_GET['com']) ? mysql_num($_GET['com']) : 0;
$cmt = isset($_GET['cmt']) ? mysql_num($_GET['cmt']) : 0;
$fld = isset($_GET['fld']) ? mysql_num($_GET['fld']) : 0;
$glp = isset($_GET['glp']) ? mysql_num($_GET['glp']) : 0;
$gwl = isset($_GET['gwl']) ? mysql_num($_GET['gwl']) : 0;
$pnt = isset($_GET['pnt']) ? mysql_num($_GET['pnt']) : 0;
$jim = isset($_GET['jim']) ? mysql_num($_GET['jim']) : 0;
$sid = isset($_GET['sid']) ? mysql_num($_GET['sid']) : 0;
$lko = isset($_GET['lko']) ? mysql_num($_GET['lko']) : 0;
$uiq = isset($_GET['uiq']) ? mysql_num($_GET['uiq']) : 0;
$lab = isset($_GET['lab']) ? mysql_num($_GET['lab']) : 0;

print '
    <h3>Criminal Centre</h3>
    <div class=floatright style=\'text-align:center;\'>
        <img src=\'assets/images/photos/letMeExplain.jpg\' width=210 height=300 alt=\'Looking for Something to Do\'><br><br>
';

if ($aut > 0) {
    print '</div>';
} else {
    print '
            <form action=\'crime.php\' method=GET>
                ' . mafiosoMenu('aut', 'AND autoOwned > 0 AND userid != ' . $userId . ' AND rankCat != "Staff"') . '<br>
                <input type=submit value="Chop Shop Opportunity">
            </form>
        </div>
    ';
}

if ($aut > 0) {
    autotheft($application->db, $application->header, $application->user, $userId, $aut, $fld, $glp, $gwl, $uiq, $lab);
} else if ($com > 0) {
    commit($application->db, $application->header, $application->user, $userId, $com);
} else if ($cmt > 0) {
    committed($application->db, $application->header, $application->user, $userId, $gwl, $pnt, $cmt, $fld, $glp, $jim, $sid, $lko, $uiq, $lab);
} else {
    index($application->db, $application->user);
}

function autotheft(Database $db, Header $headers, array $user, int $userId, int $aut, int $fld, int $glp, int $gwl, int $uiq, int $lab): void
{
    $cost = round($user['maxbrave'] * 0.09);
    $bonus = 1;
    $ur = mysqli_fetch_assoc($db->query("SELECT autoOwned FROM users WHERE userid = {$aut}"));
    $ura = mysqli_fetch_assoc($db->query("SELECT auName FROM autos WHERE auID = {$ur['autoOwned']}"));

    if ($fld == $userId) {
        if ($user['crimeToken'] != $aut || $user['crimeToken'] == $userId) {
            $headers->endpage();
            exit;
        }

        if ($user['brave'] < $cost) {
            print '
                <p>You are not nearly brave enough to perform this crime.</p>
                <p><a href=\'crime.php\'>Try an easier one.</a></p>
            ';

            $headers->endpage();
            exit;
        }

        $db->query("UPDATE users SET crimeToken = 0, brave = brave - {$cost}, crimes = crimes + 1 WHERE userid = {$userId}");
        if ($glp > 0) {
            $bonus += 1;
        }

        if ($uiq > 0) {
            $db->query("UPDATE userstats SET IQ = IQ - {$uiq} WHERE userid={$userId}");
            $bonus += 1;
        }

        if ($lab > 0) {
            $db->query("UPDATE userstats SET labour = labour - {$lab} WHERE userid = {$userId}");
            $lab = 1;
        }

        $suc = 2 + $lab + $gwl;
        $rnd = rand(1, 5);
        if ($user['rankCat'] == 'Staff') {
            print 'College Kid: ' . $gwl . ', Labour: ' . $lab . ', <br>IQ: ' . $uiq . ' Auto Mechanic: ' . $glp . '<br>Base Success: ' . $suc . ' <br>Random Roll: ' . $rnd . '<br><br>';
        }

        if ($rnd <= $suc) {
            $rnv = mysqli_fetch_assoc($db->query("SELECT iv.inv_id, i.itmBasePrice, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$aut} AND i.itmtype = 30 ORDER BY RAND() LIMIT 1"));
            if ($rnv['inv_id'] > 0) {
                itemDelete($aut, $rnv['inv_id'], 1, 0);
                $value = round(($rnv['itmBasePrice'] * 0.8) * $bonus);
                $db->query("UPDATE users SET moneyChecking = moneyChecking + {$value} WHERE userid = {$userId}");
                print '<p>Nicely done. The ' . itemInfo($rnv['itmid']) . ' that ' . mafioso($aut) . ' so prized is now yours. The Chop Shop credits your checking account $' . number_format($value);
                if ($rnd == $suc) {
                    print ' but ' . mafioso($aut) . ' <em>does</em> suspect it was you, so you better keep your head down.</p>';
                    logEvent($aut, 'Your car was chopped you lost a ' . itemInfo($rnv['itmid']) . '! ' . mafiosoLight($userId) . ' was seen lurking nearby.');
                } else {
                    print ' and ' . mafioso($aut) . ' has <em>no idea</em> that you did it!</p>';
                    logEvent($aut, 'Your car was chopped and they got your ' . itemInfo($rnv['itmid']) . '!');
                }
            } else {
                print '<p>While you were generally succesful ' . mafioso($aut) . ' has nothing but the car and that is a bit tougher than you can manage right now. You can try again later when they have better gear.</p>';
            }
        } else {
            print '<p>You have failed! You have failed in the one task set before you, to steal a bit of chrome from dear ' . mafiosoLight($aut) . '.  You could not do it.  Likely chased off by a street kid...  Pitiful.</p>';
        }

        print '<p><a href=\'crime.php?aut=' . $aut . '\'>Try again</a> or <a href=\'crime.php\'>head back to the main crime page</a>.</p>';

        $headers->endpage();
        exit;
    }

    print '<p>Stripping a car is fun, and really pissed off the victim. You won\'t get their actual car, but you will get one of the accoutrements that make a fine car worth having. The purpose of this is cash not parts - you already have enough parts - so the Chop Shop will kindly add the cash (after their cut of course) directly to your checking account.  Be aware though, they\'ll only let you visit a few times a day before they cut you off.</p>';

    if ($user['crimes'] > 9) {
        print '<p>You can only chop a car a few times a day.  Well, you can trash their car all you want with combat, but the Chop Shop gets a little nervous if you\'re there too many times.</p>';

        $headers->endpage();
        exit;
    }

    if ($ura['auName'] == '') {
        print '<p>You do realize that ' . mafioso($aut) . ' is not driving a car right? I understand you want to trash their ride, but well, they don\'t have one so there is not much you can do at this time.</p>';
    } else {
        $db->query("UPDATE users SET crimeToken = {$aut} WHERE userid = {$userId}");

        print '
            <p>You are trying to chop parts off the car owned by ' . mafioso($aut) . '. They are currently driving a very fine ' . $ura['auName'] . '. It will take ' . $cost . ' Bravery to steal their Chrome.  Do you also want some help with the crime?</p>
            <form action=\'crime.php?aut=' . $aut . '\' method=POST>
                <input type=hidden name=fld value=' . $userId . '>
                <table width=65% cellspacing=0 cellpadding=1 class=table>
        ';

        $rglp = mysqli_fetch_assoc($db->query("SELECT inv_userid, inv_id FROM inventory WHERE inv_itemid = 632 AND inv_userid = {$userId} AND inv_equip = 'no'"));
        if ($rglp['inv_userid'] == $userId) {
            print '
                <tr>
                    <td><input type=checkbox name=glp value=1 checked> Auto Mechanic</td>
                    <td>(vastly increase profits by learning how to steal without damage)</td>
                </tr>
            ';
        }

        print '<tr><td colspan=2><br></td></tr>';

        $rgwl = mysqli_fetch_assoc($db->query("SELECT inv_userid, inv_id FROM inventory WHERE inv_itemid = 11 AND inv_userid = {$userId} AND inv_equip = 'no'"));
        if ($rgwl['inv_userid'] == $userId) {
            print '
                <tr>
                    <td><input type=checkbox name=gwl value=1> Use College Kid</td>
                    <td>(use a smart kid to dramatically improve your chances)</td>
                </tr>
            ';
        }

        print '
                    <tr><td colspan=2><br></td></tr>
                    <tr>
                        <td><input type=checkbox name=uiq value=' . $cost . '> Spend ' . $cost . ' IQ</td>
                        <td>(vastly increase profits by taking it to the right chop shop)</td>
                    </tr>
                    <tr>
                        <td><input type=checkbox name=lab value=' . $cost . '> Spend ' . $cost . ' Labor</td>
                        <td>(use a crew to dramatically improve your chances)</td>
                    </tr>
                    <tr><td colspan=2><br></td></tr>
                    <tr><td colspan=2><input type=submit value=\'Chop it!\'></td></tr>
                </table>
            </form>
        ';

        print '<br><br><p><em>NOTE: This is a beta version of the Car Crimes. Straight up - the gains are generally less than the cost to get them.  But you do get to tweak other players by stealing their stuff and you get a few bucks in your pocket for the trouble.  It is designed to be used with your \'leftover\' bravery after you\'ve done a crime spree - or if you\'re really just hatin\' on someone.  Enjoy.</em></p>';
    }
}

function index(Database $db, array $user): void
{
    print '<table width=65% cellspacing=0 cellpadding=2 class=table>';

    $title = "";
    $view = max($user['level'] * 1.3, 20);
    $qcri = $db->query("SELECT crID, crCash, crWill, crIQ, crRsp, crName, crGroupName, crBrave FROM crime WHERE crBrave < {$view} ORDER BY crGroup DESC, crID DESC LIMIT 16");
    while ($row = mysqli_fetch_assoc($qcri)) {
        if ($row['crBrave'] < $user['brave']) {
            if ($title != $row['crGroupName']) {
                $title = $row['crGroupName'];
                print '
                    <tr><td colspan=3>&nbsp;</td></tr>
                    <tr>
                        <th style=\'text-align:left;\'>' . $row['crGroupName'] . '</th>
                        <th>Bravery</th><th>Gain</th>
                        <th>Bonus</th>
                        <th></th>
                    </tr>
                ';
            }

            print '<tr><td>' . $row['crName'] . '</td><td class=center>' . $row['crBrave'] . '</td><td class=center>$' . number_format($row['crCash']) . '</td><td class=center>';

            if ($row['crWill'] > 0) {
                print $row['crWill'] . ' Will';
            } elseif ($row['crIQ'] > 0) {
                print $row['crIQ'] . ' IQ';
            } elseif ($row['crRsp'] >= 1 || $row['crID'] == 6) {
                $mod = 1;
                if ($user['level'] > 250) {
                    $mod = 2;
                }

                print max(0, $row['crRsp'] - $mod) . '-' . $row['crRsp'] . ' Respect';
            } else {
                print 'Item';
            }

            print '</td><td class=center><a title=\'good luck!\' href=\'crime.php?com=' . $row['crID'] . '\'>Commit</a></td> </tr>';
        }
    }

    print '</table>';
}

function commit(Database $db, Header $headers, array $user, int $userId, int $com): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT crBrave, crDescription, crID, crName, crMaxLevel FROM crime WHERE crID = {$com}"));
    if ($user['level'] > $row['crMaxLevel'] && $row['crMaxLevel'] > 0) {
        print '
            <p>You are far too powerful to be performing these petty crimes yourself.  Get someone in your Family to handle such minor matters.</p>
            <p><a href=\'crime.php\'>Try a harder one.</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($user['brave'] < $row['crBrave']) {
        print '
            <p>You are not nearly brave enough to perform this crime.</p>
            <p><a href=\'crime.php\'>Try an easier one.</a></p>
        ';

        $headers->endpage();
        exit;
    }

    print '
        <p>You are about to ' . $row['crName'] . ' for ' . $row['crBrave'] . ' Bravery.</p><p>' . $row['crDescription'] . '</p>
        <form action=\'crime.php?cmt=' . $row['crID'] . '\' method=POST>
            <table width=65% cellspacing=0 cellpadding=1 class=table>
    ';

    $rfld = mysqli_fetch_assoc($db->query("SELECT inv_userid, inv_id FROM inventory WHERE inv_itemid = 79 AND inv_userid = {$userId} AND inv_equip = 'no'"));
    if (isset($rfld['inv_userid']) && $rfld['inv_userid'] == $userId && $user['autoOwned'] > 0) {
        print '
            <tr>
                <td><input type=checkbox name=fld value=5 checked> Floodlights</td>
                <td>(slightly easier but jail more likely)</td>
            </tr>
        ';
    }
    
    $rjim = mysqli_fetch_assoc($db->query("SELECT userid FROM coursesdone WHERE courseid = 31 AND userid = {$userId}"));
    if (isset($rjim['userid']) && $rjim['userid'] == $userId) {
        print '
            <tr>
                <td><input type=checkbox name=jim value=4 checked> Jimmy</td>
                <td>(greater financial gain but slightly harder)</td>
            </tr>
        ';
    }
    
    $query = $db->query("SELECT inv_userid, inv_id, inv_itemid FROM inventory WHERE inv_itemid IN (604, 605) AND inv_userid = {$userId} AND inv_equip = 'no'");
    while ($rglpgwl = mysqli_fetch_assoc($query)) {
        switch ($rglpgwl['inv_itemid']) {
            case 604:
                print '
                    <tr>
                        <td><input type=checkbox name=glp value=15 checked> Lockpick Grease</td>
                        <td>(crime much easier to commit)</td>
                    </tr>
                ';
                break;
            case 605:
                print '
                    <tr>
                        <td><input type=checkbox name=gwl value=25 checked> Greased Wallet</td>
                        <td>(jail much less likely)</td>
                    </tr>
                ';
                break;
            default:
                break;
        }
    }

    print '<tr><td colspan=2><br></td></tr>';

    $query = $db->query("SELECT inv_userid, inv_id, inv_itemid FROM inventory WHERE inv_itemid IN (52, 51, 80) AND inv_userid = {$userId} AND inv_equip = 'no'");
    while ($rsidlkopnt = mysqli_fetch_assoc($query)) {
        switch ($rsidlkopnt['inv_itemid']) {
            case 52:
                print '
                    <tr>
                        <td><input type=checkbox name=sid value=' . $rsidlkopnt['inv_id'] . '> Use Sidekick</td>
                        <td>(much easier but slightly less financial gain)</td>
                    </tr>
                ';
                break;
            case 51:
                print '
                    <tr>
                        <td><input type=checkbox name=lko value=' . $rsidlkopnt['inv_id'] . '> Use Lookout</td>
                        <td>(jail less likely but slightly less financial gain)</td>
                    </tr>
                ';
                break;
            case 80:
                print '
                    <tr>
                        <td><input type=checkbox name=pnt value=' . $rsidlkopnt['inv_id'] . '> Remove Paint Job</td>
                        <td>(jail less likely)</td>
                    </tr>
                ';
                break;
            default:
                break;
        }
    }

    print '
                <tr><td colspan=2><br></td></tr>
                <tr>
                    <td><input type=checkbox name=uiq value=' . $row['crBrave'] . '> Spend ' . $row['crBrave'] . ' IQ</td>
                    <td>(slightly greater financial gain)</td>
                </tr>
                <tr>
                    <td><input type=checkbox name=lab value=' . $row['crBrave'] . '> Spend ' . $row['crBrave'] . ' Labor</td>
                    <td>(slightly easier but slightly less financial gain)</td>
                </tr>
                <tr><td colspan=2><br></td></tr>
                <tr><td colspan=2><input type=submit value=\'Commit Crime\'></td></tr>
            </table>
        </form>
    ';

    $query = "UPDATE users SET crimeToken = {$row['crID']} WHERE userid = {$userId}";
    $db->query($query);
}

function committed(Database $db, Header $headers, array $user, int $userId, int $gwl, int $pnt, int $cmt, int $fld, int $glp, int $jim, int $sid, int $lko, int $uiq, int $lab): void
{
    global $application;

    if ($user['crimeToken'] == 0) {
        $headers->endpage();
        exit;
    }

    $row = mysqli_fetch_assoc($db->query("SELECT crBrave, crGroup, crWill, crIQ, crID, crRsp, crCash, crName, crSuccess, crJail, crJailReason, crFail FROM crime WHERE crID = {$cmt}"));
    $db->query("UPDATE users SET crimeToken = 0, brave = brave - {$row['crBrave']}, crimeLevel = {$row['crGroup']} WHERE userid = {$userId}");
    if ($sid > 0) {
        itemDelete($userId, $sid, 1);
        $sid = 20;
    }

    if ($lko > 0) {
        itemDelete($userId, $lko, 1);
        $lko = 20;
    }

    if ($pnt > 0) {
        itemDelete($userId, $pnt, 1);
        $pnt = 10;
    }

    if ($uiq > 0) {
        $db->query("UPDATE userstats SET IQ = IQ - {$uiq} WHERE userid = {$userId}");
        $jim += 1;
    }

    $cmd = 1;
    if ($lab > 0) {
        $db->query("UPDATE userstats SET labour = labour - {$lab} WHERE userid = {$userId}");
        $lab = 5;
        $cmd = .9;
    }

    $wmd = min(round(($user['will'] / $user['maxwill']) * 1.5, 2), 1);
    $lmd = round(($user['level'] + 10) / ($row['crBrave'] * 3) * 100);
    $suc = round($lmd * $wmd) + $sid + $lab + $glp + $fld - $jim;
    $fin = $suc;
    $rnd = rand(1, 100);
    if ($user['rankCat'] == 'Staff') {
        print 'Will Mod: ' . $wmd . ' Level Mod: ' . $lmd . '<br>Sidekick: ' . $sid . ' Lookout: ' . $lko . ' IQ: ' . $uiq . ' Labour: ' . $lab . ' Floodlights: ' . $fld . ' Jimmy: -' . $jim . ' Lockpicks: ' . $glp . '<br>Base Success: ' . $suc . ' Subtraction Range: 1-' . round($user['level'] / 19) . '<br>Random Roll: ' . $rnd . ' Final Success: ' . $fin . '<br><br>';
    }

    if ($rnd < $fin) {
        $will = 0;
        $iq = 0;
        $resp = 0;
        $bonus = '';

        if ($row['crWill'] > 0) {
            $will = min(($user['maxwill'] - $user['will']), $row['crWill']);
            $bonus .= $will . ' willpower<br>';
        } elseif ($row['crIQ'] > 0) {
            $iq = $row['crIQ'];
            $bonus .= $iq . ' IQ<br>';
        } elseif ($row['crRsp'] >= 1 || $row['crID'] == 6) {
            $mod = 1;
            if ($user['level'] > 250) {
                $mod = 2;
            }

            $resp = max(0, $row['crRsp'] - rand(0, $mod));
            $bonus .= $resp . ' Respect<br>';
        } else {
            if (rand(1, 3) == 1) {
                switch ($cmt) {
                    case '3':
                        $itm = itemRandom(1);
                        break;
                    case '7':
                        $itm = itemRandom(2);
                        break;
                    case '11':
                        $itm = itemRandom(3);
                        break;
                    case '15':
                        $itm = itemRandom(4);
                        break;
                    case '19':
                        $itm = itemRandom(5);
                        break;
                    case '23':
                        $itm = itemRandom(6);
                        break;
                    case '27':
                        $itm = itemRandom(7);
                        break;
                    case '31':
                        $itm = itemRandom(8);
                        break;
                }

                itemAdd($itm, 1, 0, $userId, 0);
                $bonus .= itemInfo($itm) . '<br>';
            } else {
                $rgr = mysqli_fetch_assoc($db->query("SELECT itmid FROM items WHERE itmtype = 30 ORDER BY RAND() LIMIT 1"));
                itemAdd($rgr['itmid'], 1, 0, $userId, 0);
                $bonus .= itemInfo($rgr['itmid']) . '<br>';
            }
        }

        $expgain = $user['level'] * $user['level'] * (($row['crID'] * 2) - (max(round($user['level'] / 95), 0)));
        $expperc = round($expgain / $user['exp_needed'] * 100);
        $jmd = '1.' . $jim;
        $cash = round(($row['crCash'] * $jmd) * $cmd);

        $db->query("UPDATE users SET money = money + {$cash}, respect = respect + {$resp}, exp = exp + {$expgain}, will = will + {$will} WHERE userid = {$userId}");
        $db->query("UPDATE userstats SET IQ = IQ + {$iq} WHERE userid = {$userId}");

        print '
            <p>Congratulations! Your attempt to ' . $row['crName'] . ' was a success!</p>
            <p>' . $row['crSuccess'] . '</p>
            <p>You gained:<br>$' . number_format($cash) . '<br>' . $expperc . '% experience<br>' . $bonus . '<br><p><a href=\'crime.php?com=' . $row['crID'] . '\'>Perform this crime again</a> or <a href=\'crime.php\'>try a different crime</a>.</p>
        ';

        checkLevel($application);

        $headers->endpage();
        exit;
    }

    $suc = round($row['crBrave'] * .25);
    $lrn = rand(0, ceil($user['level'] / 9));
    $fin = $suc - $lrn - $gwl - $lko + $fld - $pnt;
    $rnd = rand(1, 100);
    if ($user['rankCat'] == 'Staff') {
        print 'Greased Wallet: -' . $gwl . ' Lookout: -' . $lko . ' Floodlights: +' . $fld . ' Paint Job: -' . $pnt . '<br>Base Jail: ' . $suc . ' Subtraction Range: 1-' . round($user['level'] / 9) . '<br>Random Roll: ' . $rnd . ' Final Jail: ' . $fin . '<br><br>';
    }

    if ($rnd < $fin) {
        print '
            <p>Your attempt to ' . $row['crName'] . ' was a failure and you\'re going to jail.</p>
            <p>' . $row['crJail'] . '</p>
            <p><a href=\'jail.php\'>Go to Jail</a></p>
        ';

        $db->query("UPDATE users SET jail = {$row['crID']}, hjReason = '{$row['crJailReason']}' WHERE userid = {$userId}");
        $headers->endpage();
        exit;
    }

    print '
        <p>Your attempt to ' . $row['crName'] . ' was a failure.</p>
        <p>' . $row['crFail'] . '</p><p><a href=\'crime.php?com=' . $row['crID'] . '\'>Perform this crime again</a> or <a href=\'crime.php\'>try a different crime</a>.</p>
    ';
}

$application->header->endPage();
