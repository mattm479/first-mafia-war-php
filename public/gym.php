<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 1, $nlck = 0);

$action     = isset($_GET['act']) ? mysql_tex($_GET['act']) : '';
$agi        = isset($_POST['agi']) ? mysql_num($_POST['agi']) : 0;
$gua        = isset($_POST['gua']) ? mysql_num($_POST['gua']) : 0;
$str        = isset($_POST['str']) ? mysql_num($_POST['str']) : 0;
$lab        = isset($_POST['lab']) ? mysql_num($_POST['lab']) : 0;
$help       = "";
$bruno      = 0;
$chiun      = 1;
$helga      = 0;
$hartman    = 1;

$query = $application->db->query("SELECT inv_userid, inv_itemid FROM inventory WHERE inv_userid={$userId} AND inv_itemid IN (627, 681)");
while ($row = mysqli_fetch_assoc($query)) {
    if (isset($row['inv_userid']) && $row['inv_userid'] == $userId) {
        if ($row['inv_itemid'] == 627) {
            $help .= " Helga the " . itemInfo(627) . " is using her skills to give you a better average.";
            $helga = 10;
        }

        if ($row['inv_itemid'] == 681) {
            $help .= " Gny. Sgt. Hartman motivates you to do more, and so you use less energy than you plan.";
            $hartman = 0.75;
        }
    }
}

$query = $application->db->query("SELECT userid, courseid FROM coursesdone WHERE courseid IN (33, 34) AND userid = {$userId}");
while ($row = mysqli_fetch_assoc($query)) {
    if ((isset($row['userid']) && $row['userid'] == $userId) || $application->user['jail'] > 0) {
        if ($application->user['jail'] > 0 || $row['courseid'] == 33) {
            $help .= " Bruno the Jail yard Trainer is helping you achieve <strong>real</strong> results increasing your potential.";
            $bruno = 5;
        }

        if ($row['courseid'] == 34) {
            $help .= " Chiun is helping you strengthen your fingers and focus reducing your willpower loss.";
            $chiun = 0.7;
        }
    }
}

switch ($action) {
    case "workout":
        workout($application->db, $application->header, $application->user, $userId, $agi, $gua, $str, $lab);
        break;
    case "index":
    default:
        index($application->db, $application->user, $userId, $agi, $gua, $str, $lab, $help, $bruno, $chiun, $helga, $hartman);
        break;
}

function index(Database $db, array $user, int $userId, int $agi, int $gua, int $str, int $lab, string $help, int $bruno, float $chiun, int $helga, float $hartman): void
{
    $lwork = '';

    if ($user['gymWorkout'] > 0) {
        $amt = $agi + $gua + $str + $lab;
        $will = $user['will'];
        $maxwill = $user['maxwill'];
        $lvlm = round(($user['level'] * $chiun) / 15);

        if ($lvlm < 1) {
            $lvlm = 1;
        }

        $min = 20 + $helga;
        $max = 35 + $bruno;
        $gains = [
            'agility' => 0,
            'guard' => 0,
            'strength' => 0,
            'labour' => 0
        ];
        while ($will > 0 && ($agi > 0 || $gua > 0 || $str > 0 || $lab > 0)) {
            $des = $will / $maxwill;
            if ($agi > 0) {
                $gains['agility'] += round(rand($min, $max) * $des);
                $agi -= 1;
                $will -= rand(5, $lvlm);
            }

            if ($gua > 0) {
                $gains['guard'] += round(rand($min, $max) * $des);
                $gua -= 1;
                $will -= rand(5, $lvlm);
            }

            if ($str > 0) {
                $gains['strength'] += round(rand($min, $max) * $des);
                $str -= 1;
                $will -= rand(5, $lvlm);
            }

            if ($lab > 0) {
                $gains['labour'] += round(rand($min, $max) * $des);
                $lab -= 1;
                $will -= rand(5, $lvlm);
            }
        }

        $amt = round($amt * $hartman);
        $user['energy'] -= $amt;
        if ($will > 0) {
            $user['will'] = $will;
        } else {
            $user['will'] = 0;
            $will = 0;
        }

        $user['agility'] += $gains['agility'];
        $user['guard'] += $gains['guard'];
        $user['strength'] += $gains['strength'];
        $user['labour'] += $gains['labour'];

        $db->query("UPDATE users u INNER JOIN userstats us USING (userid) SET u.gymWorkout = 0, u.will = {$will}, u.energy = energy - {$amt}, us.agility = agility + {$gains['agility']}, us.guard = guard + {$gains['guard']}, us.strength = strength + {$gains['strength']}, us.labour = labour + {$gains['labour']} WHERE u.userid = {$userId}");

        $lwork = '<p><strong>Last Workout</strong>:';
        if ($gains['agility'] > 0) {
            $lwork .= ' You run around the track and gain ' . number_format($gains['agility']) . ' agility.';
        }

        if ($gains['guard'] > 0) {
            $lwork .= ' You practice ducking and blocks and gain ' . number_format($gains['guard']) . ' guard.';
        }

        if ($gains['strength'] > 0) {
            $lwork .= ' You lift weights and gain ' . number_format($gains['strength']) . ' strength.';
        }

        if ($gains['labour'] > 0) {
            $lwork .= ' You carry boxes to the warehouse and gain ' . number_format($gains['labour']) . ' labour.';
        }

        $lwork .= ' Nicely done.</p>';
    }

    $two = floor($user['energy'] * 0.5);
    $three = floor($user['energy'] * 0.33);
    $four = floor($user['energy'] * 0.25);
    $statC = unserialize($user['gymPreference']);
    $agiC = floor($user['energy'] * ($statC['agi'] * 0.01));
    $guaC = floor($user['energy'] * ($statC['gua'] * 0.01));
    $strC = floor($user['energy'] * ($statC['str'] * 0.01));
    $labC = floor($user['energy'] * ($statC['lab'] * 0.01));

    print '
        <h3>Gym</h3> ' . $lwork . '
        <p>What would you like to improve? &nbsp; <strong>You have ' . number_format($user['energy']) . ' energy available to you.</strong><br>' . $help . '</p>
        <table width=95% cellpadding=2 cellspacing=0 class=table>
            <tr>
                <td class=center>
                    <form action=\'gym.php?act=workout\' method=POST>
                        <input type=hidden name=\'agi\' value=\'' . $four . '\'>
                        <input type=hidden name=\'gua\' value=\'' . $four . '\'>
                        <input type=hidden name=\'str\' value=\'' . $four . '\'>
                        <input type=hidden name=\'lab\' value=\'' . $four . '\'>
                        <input type=submit value=\'All stats\'>
                    </form>
                </td>
                <td class=center>
                    <form action=\'gym.php?act=workout\' method=POST>
                        <input type=hidden name=\'agi\' value=\'' . $three . '\'>
                        <input type=hidden name=\'gua\' value=\'' . $three . '\'>
                        <input type=hidden name=\'str\' value=\'' . $three . '\'>
                        <input type=submit value=\'War stats\'>
                    </form>
                </td>
                <td class=center>
                    <form action=\'gym.php?act=workout\' method=POST>
                        <input type=hidden name=\'agi\' value=\'' . $two . '\'>
                        <input type=hidden name=\'gua\' value=\'' . $four . '\'>
                        <input type=hidden name=\'str\' value=\'' . $four . '\'>
                        <input type=submit value=\'War double agility\'>
                    </form>
                </td>
                <td class=center>
                    <form action=\'gym.php?act=workout\' method=POST>
                        <input type=hidden name=\'agi\' value=\'' . $agiC . '\'>
                        <input type=hidden name=\'gua\' value=\'' . $guaC . '\'>
                        <input type=hidden name=\'str\' value=\'' . $strC . '\'>
                        <input type=hidden name=\'lab\' value=\'' . $labC . '\'>
                        <input type=submit value=\'Custom Presets\'><br>
                        <a href=\'preferences.php?action=gym\'>(adjust)</a><br>
                    </form>
                </td>
            </tr>
            <tr><td class=center colspan=4>&middot; ----- &middot;<br><br></td></tr>
            <tr>
                <td class=center>
                    <form action=\'gym.php?act=workout\' method=POST>
                        <p style=\'margin-bottom:.5em;\'><strong>Agility</strong> &middot; ' . number_format($user['agility']) . ' #' . getRank($user['agility'], 'agility') . '</p>
                        <input type=text name=\'agi\' size=6 value=\'\'>
                </td>
                <td class=center>
                    <p style=\'margin-bottom:.5em;\'><strong>Guard</strong> &middot; ' . number_format($user['guard']) . ' #' . getRank($user['guard'], 'guard') . '</p>
                    <input type=text name=\'gua\' size=6 value=\'\'>
                </td>
                <td class=center>
                    <p style=\'margin-bottom:.5em;\'><strong>Strength</strong> &middot; ' . number_format($user['strength']) . ' #' . getRank($user['strength'], 'strength') . '</p>
                    <input type=text name=\'str\' size=6 value=\'\'>
                </td>
                <td class=center>
                    <p style=\'margin-bottom:.5em;\'><strong>Labour</strong> &middot; ' . number_format($user['labour']) . ' #' . getRank($user['labour'], 'labour') . '</p>
                    <input type=text name=\'lab\' size=6 value=\'\'>
                </td>
            </tr>
            <tr>
                <td class=center colspan=4>
                        <input type=submit value=\'Manual Training\'>
                    </form>
                </td>
            </tr>
        </table>
    ';
}

function workout(Database $db, Header $headers, array $user, int $userId, int $agi, int $gua, int $str, int $lab): void
{
    $amt = $agi + $gua + $str + $lab;

    if ($amt == 0) {
        print '
            <h3>Gym</h3>
            <p>You know you have to use <em>some</em> energy, right?</p>
            <p><a href=\'gym.php\'>Try something harder</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($amt > $user['energy']) {
        print '
            <h3>Gym</h3>
            <p>You do not have enough energy to train that hard.</p>
            <p><a href=\'gym.php\'>Try something easier</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET gymWorkout = {$amt} WHERE userid = {$userId}");

    print '
            <h3>Gym</h3><br>
            <form action=\'gym.php\' method=POST>
                <p>That\'s quite a workout you have planned. Good luck! &nbsp;
                <input type=hidden name=\'agi\' value=\'' . $agi . '\'>
                <input type=hidden name=\'gua\' value=\'' . $gua . '\'>
                <input type=hidden name=\'str\' value=\'' . $str . '\'>
                <input type=hidden name=\'lab\' value=\'' . $lab . '\'>
                <input type=submit value=\'Continue\'>
            </form>
        </p><br>
    ';
}

print '
    <br><br>
    <div align=center>
        <img src=\'assets/images/photos/gym.jpg\' height=239 width=386 alt=\'Workout Spot\'>
    </div>
';

$application->header->endPage();
