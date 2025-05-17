<?php

require_once "globals.php";
global $application, $userId;
pagePermission($lgn=1, $stff=0, $njl=0, $nhsp=0, $nlck=0);

print "
    <h3>Announcements</h3>
    <table width=95% cellpadding=3 cellspacing=0 class=table>
";

$result = $application->db->query("SELECT a_text, a_time FROM announcements ORDER BY a_time DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($result)) {
    print "
        <tr><td class=mostborders>" . mysql_tex_out($row['a_text']) . "</td></tr>
        <tr><td class=fewborders><span class=light>" . date('F j Y', $row['a_time']) . " at " . date('g:i a', $row['a_time']) . "</span></td></tr>
        <tr><td></td></tr>
    ";
}

print "</table>";

$application->db->query("UPDATE users SET newAnnounce = 0 WHERE userid = {$userId}");

$application->header->endPage();
