<?php

use Fmw\Database;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$delete = isset($_GET['delete']) ? mysql_num($_GET['delete']) : 0;
$direct = isset($_GET['direct']) ? mysql_tex($_GET['direct']) : '';

switch ($action) {
    case "attacks":
        attacks($application->db, $userId);
        break;
    case "general":
    default:
        general($application->db, $application->user, $userId, $delete, $direct);
        break;
}

function attacks(Database $db, int $userId): void
{
    print '
        <h3>Recent Battles</h3>
        <table width=95% cellpadding=1 cellspacing=0 class=table>
    ';

    $query = $db->query("SELECT laTime, laDefender, laLogShort FROM logsAttacks WHERE laAttacker = {$userId} OR laDefender = {$userId} ORDER BY laTime DESC LIMIT 30;");
    while ($row = mysqli_fetch_assoc($query)) {
        print '
            <tr>
                <td class=borders>
                    <div class=floatright><span class=light>' . date('F j Y', $row['laTime']) . ' at ' . date('g:i a', $row['laTime']) . '</span>&nbsp;</div>
                    &nbsp;' . mafiosoLight($row['laDefender']) . ' ' . $row['laLogShort'] . '
                </td>
            </tr>
            <tr><td></td></tr>
        ';
    }

    print '</table>';

    $db->query("UPDATE users SET newAttacks = 0 WHERE userid = {$userId}");
}

function general(Database $db, array $user, int $userId, int $delete, string $direct): void
{
    if ($delete > 0) {
        $db->query("UPDATE logsEvents SET leRead = 2 WHERE leID = {$delete} AND leUser = {$userId}");
    }

    if ($direct == 'Deleted') {
        $query = $db->query("SELECT leTime, leID, leText FROM logsEvents WHERE leUser = {$userId} AND leRead = 2 ORDER BY leTime DESC LIMIT 30;");
    } elseif ($direct == 'Older') {
        $query = $db->query("SELECT leTime, leID, leText FROM logsEvents WHERE leUser = {$userId} AND leRead = 1 ORDER BY leTime DESC LIMIT 30;");
    } else {
        $query = $db->query("SELECT leTime, leID, leText FROM logsEvents WHERE leUser = {$userId} AND leRead = 0 ORDER BY leTime DESC LIMIT 30;");
        $direct = 'New';
    }

    print '
        <h3>Current Events &nbsp; <span class=light>(' . $direct . ')</span></h3>
        &nbsp; <a class=lighter href=\'events.php\'>New</a> &nbsp;&middot;&nbsp;
        <a class=lighter href=\'events.php?action=general&direct=Older\'>Viewed</a> &nbsp;&middot;&nbsp;
        <a class=lighter href=\'events.php?action=general&direct=Deleted\'>Deleted</a><br><br>
        <table width=95% cellpadding=1 cellspacing=0 class=table>
    ';

    while ($row = mysqli_fetch_assoc($query)) {
        print '
            <tr>
                <td class=borders>
                    <div class=floatright>
                        <span class=light>' . date('F j Y', $row['leTime']) . ' at ' . date('g:i a', $row['leTime']) . '</span>
                         - <a href=\'events.php?refresh=' . $row['leID'] . '\'>refresh</a>&nbsp;&middot;&nbsp;<a href=\'events.php?delete=' . $row['leID'] . '\'>delete</a>&nbsp;
                     </div>
                     ' . $row['leText'] . '
                 </td>
             </tr>
             <tr><td></td></tr>
         ';
    }

    print '</table>';

    if ($user['newEvents'] > 0 && $direct == 'New') {
        $db->query("UPDATE logsEvents SET leRead = 1 WHERE leUser = {$userId} AND leRead = 0");
        $db->query("UPDATE users SET newEvents = 0 WHERE userid = {$userId}");
    }
}

$application->header->endPage();
