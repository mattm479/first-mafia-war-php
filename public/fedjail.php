<?php

require_once "globals.php";
global $application;

print '
    <h3>Federal Jail</h3>
    This is for those who have no respect.  Show some and you may get out.<br>
    <table width=95% cellspacing=0 cellpadding=2 class=table>
        <tr>
            <th>Who</th>
            <th class=center>Days</th>
            <th>Reason</th>
        </tr>
';

$query = $application->db->query("SELECT userid, username, fedjail, fedjailReason FROM users WHERE fedjail > 0 ORDER BY fedjail, username");
while ($row = mysqli_fetch_assoc($query)) {
    print '
        <tr>
            <td>' . mafioso($row['userid']) . '</td>
            <td class=center>' . $row['fedjail'] . '</td>
            <td>' . $row['fedjailReason'] . '</td>
        </tr>
    ';
}

print '</table><br><br>';

$application->header->endPage();
