<?php

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0);

print '
    <h3>Special Items &amp; Possessions</h3>
    <p>These are the special items in the game and the deserving players who own them.</p>
';

// Beneficial Items
print '<h5>Beneficial Items</h5>';

$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 606");
print 'The ' . itemInfo(606) . ' is gained by having the most Friends.<br>';
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 604");
print '<br>The ' . itemInfo(604) . ' are gained by being the top Jail Buster yesterday.<br>';
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 605");
print '<br>The ' . itemInfo(605) . ' is gained by being the top Jail Bailer yesterday.<br>';
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 608");
print '<br>The ' . itemInfo(608) . ' is gained by putting the most people in the hospital yesterday.<br>';
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 609");
print '<br>The ' . itemInfo(609) . ' are gained by putting the most people in jail.<br>';
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

print '<br>The <em>Estates</em> are typically gained by having the best house in town, and then being better than the others in that city.<br>';
$estates = array(610, 611, 612, 613, 629, 614, 615);
foreach ($estates as $estate) {
    $query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = {$estate}");
    while ($row = mysqli_fetch_assoc($query)) {
        print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . ' owns the ' . itemInfo($estate) . '<br>';
    }
}

print '<br>The ' . itemInfo(305) . ' is gained by donating to the game. Each month is a different unique item and each item lasts 30 days after opening.  In November it is ' . itemInfo(625) . '.<br>';
$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 625");
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

print '<br>In October it was ' . itemInfo(603) . '.<br>';
$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 603");
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

// Hurtful Items 
print '<br><h5>Harmful Items</h5>';

print 'The ' . itemInfo(607) . ' is gained by having the most Enemies.<br>';
$query = $application->db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 607");
while ($row = mysqli_fetch_assoc($query)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($row['inv_userid']) . '<br>';
}

print '<br>The ' . itemInfo(602) . ' is gained by attacking those significantly weaker than yourself.<br>';
$qdg = $application->db->query("SELECT inv_userid, sum(inv_qty) AS sumDagger FROM inventory WHERE inv_itemid = 602 GROUP BY inv_userid ORDER BY sumDagger DESC");
while ($rdg = mysqli_fetch_assoc($qdg)) {
    print ' &nbsp;&middot;&nbsp; ' . mafioso($rdg['inv_userid']) . ' has ' . $rdg['sumDagger'] . '<br>';
}

$application->header->endPage();
