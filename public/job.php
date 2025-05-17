<?php

use Fmw\Database;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 0, $nlck = 1);

$interview = isset($_GET['interview']) ? mysql_num($_GET['interview']) : 0;
$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';

if (!$application->user['job']) {
    if (!$interview) {
        print "
            <h3>Employment</h3>
            <p>You do not yet have a job. Below is a list of available jobs.</p>
        ";

        $query = $application->db->query("SELECT jID, jNAME, jDESC FROM jobs");
        while ($row = mysqli_fetch_assoc($query)) {
            print "<p>{$row['jNAME']} - {$row['jDESC']} &middot; <a href='job.php?interview={$row['jID']}'><strong>interview</strong></a></p>";
        }
    } else {
        $row = mysqli_fetch_assoc($application->db->query("SELECT j.jOWNER, jr.jrID, jr.jrLABOURN, jr.jrIQN FROM jobs j LEFT JOIN jobranks jr ON j.jFIRST = jr.jrID WHERE j.jID = {$interview}"));
        print "
            <h3>Employment</h3>
            <p>{$row['jOWNER']}: So {$application->user['username']}, you were looking for a job with us?</p>
            <p>{$application->user['username']}: Yes please.</p>
        ";

        if ($application->user['labour'] >= $row['jrLABOURN'] && $application->user['IQ'] >= $row['jrIQN']) {
            $application->db->query("UPDATE users SET job = {$interview}, jobrank = {$row['jrID']} WHERE userid = {$userId};");
            print "
                <p>{$row['jOWNER']}: Okay {$application->user['username']}. You may work for us.  See you tomorrow.</p>
                <p><a href='job.php'>Back to work</a></p>
            ";
        } else {
            print "
                <h3>Employment</h3>
                <p>{$row['jOWNER']}: Sorry {$application->user['username']}, you are not skilled enough to work in this field. You'll need 
            ";

            if ($application->user['labour'] < $row['jrLABOURN']) {
                $s = $row['jrLABOURN'] - $application->user['labour'];
                print " {$s} more labour, ";
            }

            if ($application->user['IQ'] < $row['jrIQN']) {
                $s = $row['jrIQN'] - $application->user['IQ'];
                print " {$s} more IQ, ";
            }

            print "
                before you'll be able to work here.</p>
                p><a href='home.php'>Home</a></p>
            ";
        }
    }
} else {
    switch ($action) {
        case 'quit':
            quit_job($application->db, $userId);
            break;
        case 'promote':
            job_promote($application->db, $application->user, $userId);
            break;
        default:
            job_index($application->db, $application->user);
            break;
    }
}

function job_index(Database $db, array $user): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT j.jNAME, jr.jrNAME, jr.jrPAY, jr.jrIQG, jr.jrSTRG, jr.jrAGIG, jr.jrLABOURG FROM jobs j LEFT JOIN jobranks jr ON j.jID = jr.jrJOB WHERE jr.jrJOB = {$user['job']} AND jr.jrID = {$user['jobrank']}"));
    print "
        <h3>Employment</h3>
        <p>
            You currently work in {$row['jNAME']} as the {$row['jrNAME']}.<br>
            Each day you receive " . moneyFormatter($row['jrPAY']) . ", {$row['jrIQG']} IQ, {$row['jrSTRG']} strength, {$row['jrAGIG']} agility, and {$row['jrLABOURG']} labour.<br>You also have an opportunity to meet new contacts who can help you later.
        </p>
        <table width=75% cellspacing=3 class='table'>
            <tr>
                <th>Job Ranks</th>
                <th style='text-align:center;'>Pay</th>
                <th style='text-align:center;'>IQ Min.</th>
                <th style='text-align:center;'>Labour Min.</th>
            </tr>
    ";

    $qran = $db->query("SELECT jrNAME, jrPAY, jrIQN, jrLABOURN FROM jobranks WHERE jrJOB = {$user['job']} ORDER BY jrPAY;");
    while ($ran = mysqli_fetch_assoc($qran)) {
        print "
            <tr>
                <td>{$ran['jrNAME']}</td>
                <td style='text-align:right;'>" . moneyFormatter($ran['jrPAY']) . "</td>
                <td style='text-align:right;'>" . moneyFormatter($ran['jrIQN'], "") . "</td>
                <td style='text-align:right;'>" . moneyFormatter($ran['jrLABOURN'], "") . "</td>
            </tr>
        ";
    }

    print "
        </table>
        <br><hr><br>
        <p>
    ";

    $query = $db->query("SELECT jrID FROM jobranks WHERE jrPAY > {$row['jrPAY']} AND jrLABOURN <= {$user['labour']} AND jrIQN <= {$user['IQ']} AND jrJOB = {$user['job']} ORDER BY jrPAY DESC LIMIT 1");
    if (mysqli_num_rows($query) == 0) {
        print "";
    } else {
        print "<a href='job.php?action=promote'>Try To Get Promoted</a> &nbsp;&middot;&nbsp; ";
    }

    print "<a href='job.php?action=quit'>Quit this job</a></p>";
}

function job_promote(Database $db, array $user, int $userId): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT jr.jrPAY FROM jobs j LEFT JOIN jobranks jr ON j.jFIRST = jr.jrID WHERE j.jID = {$user['job']}"));
    $qpro = $db->query("SELECT jrID, jrNAME FROM jobranks WHERE jrPAY > {$row['jrPAY']} AND jrLABOURN <= {$user['labour']} AND jrIQN <= {$user['IQ']} AND jrJOB = {$user['job']} ORDER BY jrPAY DESC LIMIT 1");

    if (mysqli_num_rows($qpro) == 0) {
        print "
            <h3>Job</h3>
            <p>Sorry, you cannot be promoted at this time.</p>
            <p><a href='job.php'>Back</a></p>
        ";
    } else {
        $rpro = mysqli_fetch_assoc($qpro);
        $db->query("UPDATE users SET jobrank = {$rpro['jrID']} WHERE userid = {$userId}");

        print "
            <h3>Employment</h3>
            <p>Congratulations, you have been promoted to {$rpro['jrNAME']}</p>
            <p><a href='job.php'>Back to work</a></p>
        ";
    }
}

function quit_job(Database $db, int $userId): void
{
    $db->query("UPDATE users SET job = 0, jobrank = 0 WHERE userid = {$userId}");

    print "
        <h3>Employment</h3>
        <p>You have quit your job.</p>
        <p><a href='job.php'>Try for another</a></p>
    ";
}

print "
    <br>
    <div align=center>
        <img src='assets/images/photos/job.jpg' width='400' height='228' alt='Job'>
    </div>
";

$application->header->endPage();
