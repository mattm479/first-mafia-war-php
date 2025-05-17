<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$poll = isset($_POST['poll']) ? mysql_num($_POST['poll']) : 0;
$choice = isset($_POST['choice']) ? mysql_num($_POST['choice']) : 0;
$application->user['pollVote'] = isset($application->user['pollVote']) ? unserialize($application->user['pollVote']) : '';

print '
    <h3>Polling Booth</h3><br>
    <div class=floatright>
        &nbsp;&nbsp;&nbsp;&nbsp; Decisions, decisions... which one to pick?<br>
        <img src=\'assets/images/photos/adsRifles.jpg\' width=300 height=496 alt=Options>
    </div>
';

switch ($action) {
    case 'view':
        view($application->db);
        break;
    case 'poll':
    default:
        poll($application->db, $application->header, $application->user, $userId, $poll, $choice);
        break;
}

function poll(Database $db, Header $headers, array $user, int $userId, int $poll, int $choice): void
{
    print '<p>Please cast your vote and <a href=\'polling.php?action=view\'>view previous polls</a>.</p> ';

    // Add the vote to the total
    if ($choice && $poll) {
        if ($user['pollVote'][$poll]) {
            print '
                <p>You have already voted in this poll.</p>
                <p><a href=\'explore.php\'>Visit the city</a></p>
            ';

            $headers->endpage();
            exit;
        }

        if (mysqli_num_rows($db->query("SELECT id FROM polls WHERE active = 1 AND id = {$poll}")) == 0) {
            print '
                <p>There are no current polls.</p>
                <p><a href=\'polling.php?action=view\'>Check out our past polls</a></p>
            ';

            $headers->endpage();
            exit;
        }

        $user['pollVote'][$poll] = $choice;
        $ser = addslashes(serialize($user['pollVote']));

        $db->query("UPDATE users SET pollVote = '$ser' WHERE userid = {$userId}");
        $db->query("UPDATE polls SET voted{$choice} = voted{$choice} + 1, votes = votes + 1 WHERE active = 1 AND id = {$poll}");

        print '
            <p>Your vote has been cast. Thank you for voting!</p>
            <p><a href=\'polling.php\'>Back To Polling Booth</a></p>
        ';

        $headers->endpage();
        exit;
    } else { // Vote process
        $query = $db->query("SELECT * FROM polls WHERE active = 1");
        if (!mysqli_num_rows($query)) {
            print '<p>There are no active polls at this time.</p>';

            $headers->endpage();
            exit;
        }

        while ($row = mysqli_fetch_assoc($query)) {
            // The vote so far
            if ($user['pollVote'][$row['id']]) {
                print '
                    <table width=50% cellspacing=0 cellpadding=3 class=table>
                        <tr><td colspan=2>' . $row['question'] . '<br><br></td></tr>
                        <tr>
                            <th style=\'text-align:left;\'>Votes</th>
                            <th style=\'text-align:left;\'>Options</th></tr>
                ';

                for ($i = 1; $i <= 10; $i++) {
                    if ($row['choice' . $i]) {
                        $k = 'choice' . $i;
                        $ke = 'voted' . $i;
                        $perc = 0;
                        if ($row['votes'] != 0) {
                            $perc = number_format($row[$ke] / $row['votes'] * 100);
                        }

                        print '
                            <tr>
                                <td><img title=\'' . $row[$ke] . ' votes (' . $perc . '%)\' src=\'assets/images/bargreen.gif\' alt=Bar width=\'' . $perc . '\' height=5></td>
                                <td>' . $row[$k] . '</td>
                            </tr>
                        ';
                    }
                }

                $myvote = $row['choice' . $user['pollVote'][$row['id']]];

                print '
                        <tr><td colspan=2>&nbsp;</td></tr>
                        <tr><th colspan=2 style=\'text-align:left; font-weight:normal;\'>Your Vote: ' . $myvote . '</th></tr>
                    </table>
                ';
            } else {
                // Go ahead and vote
                print '
                    <form action=\'polling.php\' method=POST>
                        <input type=hidden name=poll value=\'' . $row['id'] . '\'>
                        <table cellpadding=3 cellspacing=0 width=50% class=table>
                            <tr><td colspan=2>' . $row['question'] . '<br><br></td></tr>
                            <tr>
                                <th>Vote</th>
                                <th style=\'text-align:left\'>Options</th>
                            </tr>
                ';

                for ($i = 1; $i <= 10; $i++) {
                    if ($row['choice' . $i]) {
                        $k = 'choice' . $i;
                        $c = "";
                        if ($i == 1) {
                            $c = "checked='checked'";
                        }

                        print '
                            <tr>
                                <td class=center><input type=radio name=choice value=\'' . $i . '\' ' . $c . '></td>
                                <td>' . $row[$k] . '</td>
                            </tr>
                        ';
                    }
                }

                print '
                            <tr>
                                <td class=center><input type=submit value=\'Vote\'></td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </form>
                ';
            }
        }
    }
}

function view(Database $db): void
{
    $query = $db->query("SELECT * FROM polls WHERE active = 0 ORDER BY id desc");
    while ($row = mysqli_fetch_assoc($query)) {
        print '
            <table width=55% cellspacing=0 cellpadding=3 class=table>
                <tr><td colspan=2>' . $row['question'] . '</td></tr>
                <tr>
                    <th style=\'text-align:left;\'>Votes</th>
                    <th style=\'text-align:left\'>Options</th>
                </tr>
        ';

        for ($i = 1; $i <= 10; $i++) {
            if ($row['choice' . $i]) {
                $k = 'choice' . $i;
                $ke = 'voted' . $i;
                $perc = 0;
                if ($row['votes'] != 0) {
                    $perc = $row[$ke] / $row['votes'] * 100;
                }

                print '
                    <tr>
                        <td><img title=\'' . $row[$ke] . ' votes (' . $perc . '%)\' src=\'assets/images/bargreen.gif\' alt=Bar width=\'' . $perc . '\' height=5></td>
                        <td>' . $row[$k] . '</td>
                    </tr>
                ';
            }
        }

        print '
                <tr><td>&nbsp;</td></tr>
            </table>
        ';
    }
}

$application->header->endPage();
