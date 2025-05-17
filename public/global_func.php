<?php

global $application, $userId;

use Fmw\Application;

function attackGangWar($winner, $loser, $gangBase = 1): string
{
    global $application;

    $win = mysqli_fetch_assoc($application->db->query("SELECT gang,gangrank,userid FROM users WHERE userid = {$winner}"));
    $lose = mysqli_fetch_assoc($application->db->query("SELECT gang,gangrank,userid FROM users WHERE userid = {$loser}"));
    $war = mysqli_fetch_assoc($application->db->query("SELECT famWarID, famWarType, famWarAttPoints, famWarAtt, famWarDefPoints, famWarDef FROM familyWar WHERE famWarEnd = 0 AND ((famWarAtt = {$win['gang']} AND famWarDef = {$lose['gang']}) OR (famWarDef = {$win['gang']} AND famWarAtt = {$lose['gang']}))"));

    if (isset($war['famWarType']) && $war['famWarType'] > 1 || ($war['famWarType'] == 1 && $win['gangrank'] == 1 && $lose['gangrank'] == 1)) {
        $application->db->query("UPDATE familyWar SET famWarAttPoints = famWarAttPoints - {$gangBase} WHERE famWarAtt = {$lose['gang']} AND famWarID = {$war['famWarID']}");
        $application->db->query("UPDATE familyWar SET famWarDefPoints = famWarDefPoints - {$gangBase} WHERE famWarDef = {$lose['gang']} AND famWarID = {$war['famWarID']}");
        $application->db->query("UPDATE familyWar SET famWarAttPoints = famWarAttPoints + {$gangBase} WHERE famWarAtt = {$win['gang']} AND famWarID = {$war['famWarID']}");
        $application->db->query("UPDATE familyWar SET famWarDefPoints = famWarDefPoints + {$gangBase} WHERE famWarDef = {$win['gang']} AND famWarID = {$war['famWarID']}");

        // Did you win the fight?
        $targ = ($war['famWarType'] * 10 + 10) - $gangBase;
        $wgai = $war['famWarType'] * 2;
        $lgai = $war['famWarType'] * 1;
        $cash = $war['famWarType'] * 3000000;

        if (($war['famWarAttPoints'] >= $targ && $war['famWarAtt'] == $win['gang']) || ($war['famWarDefPoints'] >= $targ && $war['famWarDef'] == $win['gang'])) {
            $application->db->query("UPDATE familyWar SET famWarEnd = unix_timestamp(), famWarDisID = {$lose['gang']} WHERE famWarID = {$war['famWarID']}");
            $application->db->query("UPDATE family SET famRespect = famRespect + {$wgai}, famVaultCash = famVaultCash + {$cash} + {$cash} WHERE famID = {$win['gang']}");
            $application->db->query("UPDATE family SET famRespect = famRespect - {$lgai}, famVaultCash = famVaultCash - {$cash} WHERE famID = {$lose['gang']}");

            $famlos = mysqli_fetch_assoc($application->db->query("SELECT famVaultCash, famID FROM family WHERE famID = {$lose['gang']}"));

            if ($famlos['famVaultCash'] < 0) {
                $application->db->query("UPDATE family SET famRespect = famRespect - {$wgai}, famVaultCash = 1 WHERE famID = {$lose['gang']}");
            }

            newsPost(1, 'The ' . familyName($win['gang']) . ' Family has won their ' . warType($war['famWarType']) . ' with the ' . familyName($lose['gang']) . ' Family and gained $' . number_format($cash * 2) . ' in booty.');
        }

        return '<p>Your Families are at war, and your actions have affected the outcome.</p>';
    }

    return '';
}

function checkLevel(Application $application): void
{
    if ($application->user['exp'] >= $application->user['exp_needed']) {
        $expu = $application->user['exp'] - $application->user['exp_needed'];
        $application->user['level']++;
        $application->user['energy'] += 2;
        $application->user['brave'] += 2;
        $application->user['maxenergy'] += 2;
        $application->user['maxbrave'] += 2;
        $application->user['hp'] += 6;
        $application->user['maxhp'] += 6;
        $expneeded = max(20, abs($application->user['level'] * $application->user['level'] * $application->user['level'] * 3));
        $expu = min($expu, $expneeded);

        $application->db->query("UPDATE users SET exp_needed = {$expneeded}, level = level + 1, exp = {$expu}, energy = energy + 2, brave = brave + 2, maxenergy = maxenergy + 2, maxbrave = maxbrave + 2, hp = hp + 6, maxhp = maxhp + 6 WHERE userid = {$application->user['userid']}");

        $refq = $application->db->query("SELECT refID FROM referals WHERE refREFED = {$application->user['userid']}");
        if (mysqli_num_rows($refq) > 0) {
            $q = $application->db->query("SELECT u.userid, u.username, r.refREFED, r.refREFER FROM users u LEFT JOIN referals r ON u.userid = r.refREFED WHERE u.userid = {$application->user['userid']}");
            $row = mysqli_fetch_assoc($q);
            $item = 0;

            if ($application->user['level'] == 8) {
                $item = 301;
            } elseif ($application->user['level'] == 15) {
                $item = 53;
            } elseif ($application->user['level'] == 25) {
                $item = 338;
            } elseif ($application->user['level'] == 30) {
                $item = 59;
            } elseif ($application->user['level'] == 100) {
                $item = 366;
            } elseif ($application->user['level'] == 150) {
                $item = 29;
            } elseif ($application->user['level'] == 200) {
                $item = 346;
            } elseif ($application->user['level'] == 300) {
                $item = 326;
            }

            if ($item > 0) {
                itemAdd($item, 0, $row['refREFER'], 0, 1);
                logEvent($row['refREFER'], "Your referral {$application->user['username']} earned you a " . iteminfo($item) . " by making level {$application->user['level']}");
            }
        }

        if ($application->user['level'] > 1 && $application->user['level'] < 10) {
            itemAdd(11, 0, $application->user['userid'], 0, 1);
            itemAdd(27, 0, $application->user['userid'], 0, 1);
            itemAdd(51, 0, $application->user['userid'], 0, 1);
            itemAdd(52, 0, $application->user['userid'], 0, 1);
            itemAdd(56, 0, $application->user['userid'], 0, 1);
            itemAdd(72, 0, $application->user['userid'], 0, 1);
            itemAdd(94, 0, $application->user['userid'], 0, 1);
            itemAdd(601, 3, $application->user['userid'], 0, 1);

            logEvent($application->user['userid'], "For surviving and gaining a level you gained a few gifts.");
        }
    }
}

function daysOld($start, $end = 0): string
{
    if ($end == 0) {
        $end = time();
    }

    $age = $end - $start;
    $unit = 'secs';

    if ($age >= 60) {
        $age = (int)($age / 60);
        $unit = 'mins';

        if ($age >= 60) {
            $age = (int)($age / 60);
            $unit = 'hours';

            if ($age >= 24) {
                $age = (int)($age / 24);
                $unit = "days";
            }
        }
    }

    return $age . ' ' . $unit;
}

function educationFinish($courseId, $userId): void
{
    global $application;

    $application->db->query("INSERT INTO coursesdone (userid, courseid) VALUES ({$userId}, {$courseId})");
    $application->db->query("UPDATE users SET course = 0, cdays = 0 WHERE userid = {$userId}");

    $course = mysqli_fetch_assoc($application->db->query("SELECT crID, crNAME, crSTR, crGUARD, crLABOUR, crAGIL, crIQ FROM courses WHERE crID = {$courseId}"));

    if ($courseId == 21) {
        $application->db->query("UPDATE users SET maxhp = maxhp + 50 WHERE userid = {$userId}");
    }

    if ($courseId == 22) {
        $application->db->query("UPDATE users SET respect = respect + 100 WHERE userid = {$userId}");
    }

    if ($courseId == 29) {
        itemAdd(93, 0, $userId, 0, 1);
    }

    if ($courseId == 30) {
        itemAdd(100, 0, $userId, 0, 1);
    }

    $upd = '';
    $ev = '';
    if ($course['crSTR'] > 0) {
        $upd .= ", us.strength = us.strength + {$course['crSTR']}";
        $ev .= ", {$course['crSTR']} strength";
    }

    if ($course['crGUARD'] > 0) {
        $upd .= ", us.guard = us.guard + {$course['crGUARD']}";
        $ev .= ", {$course['crGUARD']} guard";
    }

    if ($course['crLABOUR'] > 0) {
        $upd .= ", us.labour = us.labour + {$course['crLABOUR']}";
        $ev .= ", {$course['crLABOUR']} labour";
    }

    if ($course['crAGIL'] > 0) {
        $upd .= ", us.agility = us.agility + {$course['crAGIL']}";
        $ev .= ", {$course['crAGIL']} agility";
    }

    if ($course['crIQ'] > 0) {
        $upd .= ", us.IQ = us.IQ + {$course['crIQ']}";
        $ev .= ", {$course['crIQ']} IQ";
    }

    $ev = substr($ev, 1);

    if ($upd) {
        $application->db->query("UPDATE users u LEFT JOIN userstats us ON u.userid = us.userid SET us.userid = us.userid $upd WHERE u.userid = {$userId}");
    }

    logEvent($userId, "You have finished {$course['crNAME']}. <a href='education.php'>Get a new Mentor</a>");
}

function familyName($familyId): string
{
    global $application;

    $family = mysqli_fetch_assoc($application->db->query("SELECT famTag, famID, famName FROM family WHERE famID = {$familyId}"));

    return (isset($family['famID']) && $family['famID'] != 0) ? "<a title='{$family['famTag']}' href='family.php?action=view&ID={$family['famID']}'>{$family['famName']}</a>" : "None";
}

function getRank($stat, $myKey, $userId = 0)
{
    global $application;

    $result = $application->db->query("SELECT count(us.userid) AS statRank FROM userstats us LEFT JOIN users u ON us.userid = u.userid WHERE us.{$myKey} > {$stat} AND us.userid != {$userId}");

    return $result->fetch_object()->statRank + 1;
}

function houseName($houseId)
{
    global $application;

    $house = mysqli_fetch_assoc($application->db->query("SELECT hID, hNAME FROM houses WHERE hID = {$houseId}"));

    return $house['hNAME'] ?? "None";
}

function itemAdd($itemId, $quantity, $expires = 0, $userId = 0, $familyId = 0): void
{
    global $application;

    if ($userId > 0) {
        $inventoryItemId = mysqli_fetch_assoc($application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid={$itemId} AND inv_equip='no'"));
    } else if ($familyId > 0) {
        $inventoryItemId = mysqli_fetch_assoc($application->db->query("SELECT inv_id FROM inventory WHERE inv_famid = {$familyId} AND inv_itemid = {$itemId} AND inv_equip='no'"));
    }

    $item = mysqli_fetch_assoc($application->db->query("SELECT itmid, itmExpire FROM items WHERE itmid = {$itemId}"));
    if (isset($inventoryItemId) && $inventoryItemId['inv_id'] > 0 && $item['itmExpire'] == 0) {
        $application->db->query("UPDATE inventory SET inv_qty = inv_qty + {$quantity} WHERE inv_id = {$inventoryItemId['inv_id']}");
    } else {
        if (($expires > $item['itmExpire'] || $expires == 0) && $item['itmExpire'] > 0) {
            $expires = $item['itmExpire'];
        }

        $application->db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES ({$itemId}, {$expires}, {$userId}, {$familyId}, {$quantity}, 'no')");
    }

    if ($userId > 0) {
        setWillpower($userId);
    }
}

function itemDelete($inventoryId, $quantity, $userId = 0, $familyId = 0): void
{
    global $application;

    if ($userId > 0) {
        $result = $application->db->query("SELECT inv_id, inv_qty FROM inventory WHERE inv_userid = {$userId} AND inv_id = {$inventoryId}");
    } else if ($familyId > 0) {
        $result = $application->db->query("SELECT inv_id, inv_qty FROM inventory WHERE inv_famid = {$familyId} AND inv_id = {$inventoryId}");
    }

    if (isset($result) && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['inv_qty'] > $quantity) {
            $application->db->query("UPDATE inventory SET inv_qty = inv_qty - {$quantity} WHERE inv_id = {$inventoryId}");
        } else {
            $application->db->query("DELETE FROM inventory WHERE inv_id = {$inventoryId}");
        }
    }

    if ($userId > 0) {
        setWillpower($userId);
    }
}

function itemInfo($itemId): string
{
    global $application;

    $result = $application->db->query("SELECT itmusage, itmid, itmname, itmCombat, itmCombatType FROM items WHERE itmid = {$itemId}");
    $item = mysqli_fetch_assoc($result);

    if ($item['itmCombat'] > 0) {
        return '<a title=\'' . $item['itmusage'] . ' &middot; ' . itemCombatType($item['itmCombatType']) . '(' . $item['itmCombat'] . ')\' href=\'items.php?iid=' . $item['itmid'] . '\'>' . $item['itmname'] . '</a>';
    } else {
        return '<a title=\'' . $item['itmusage'] . '\' href=\'items.php?iid=' . $item['itmid'] . '\'>' . $item['itmname'] . '</a>';
    }
}

function itemName($itemId): string
{
    global $application;

    $result = $application->db->query("SELECT itmid, itmname FROM items WHERE itmid = {$itemId}");
    $item = mysqli_fetch_assoc($result);

    return $item['itmname'];
}

function itemCombatType($itemCombatType): string
{
    if ($itemCombatType == 1) {
        return 'Physical';
    }
    if ($itemCombatType == 2) {
        return 'Stealth';
    }
    if ($itemCombatType == 3) {
        return 'Experience';
    }
    if ($itemCombatType == 4) {
        return 'Financial';
    }
    if ($itemCombatType == 5) {
        return 'Statistics';
    }
    if ($itemCombatType == 6) {
        return 'Respect';
    }

    return 'Unknown';
}

function itemType($itemType): string
{
    if ($itemType == 5) {
        return 'Donator Pack';
    } elseif ($itemType == 10) {
        return 'Contacts';
    } elseif ($itemType == 20) {
        return 'Gear';
    } elseif ($itemType == 30) {
        return 'Car Gear';
    } elseif ($itemType == 40) {
        return 'Nourishment';
    } elseif ($itemType == 50) {
        return 'Specialty';
    } elseif ($itemType == 60) {
        return 'Protection';
    } elseif ($itemType == 65) {
        return 'Bombs';
    } elseif ($itemType == 70) {
        return 'Firearms';
    } elseif ($itemType == 80) {
        return 'Melee Weapons';
    }

    return 'Unknown';
}

function itemRandom($rank = 4, $bonus = ''): int
{
    global $application;

    $rank = max(0, $rank + rand(0, 3) - 2);
    $search = '';

    switch ($rank) {
        case '0':
            $search = 'WHERE itmid = 84';
            break;
        case '1':
            $search = 'WHERE itmLevel = 2';
            break;
        case '2':
            $search = 'WHERE itmLevel = 3';
            break;
        case '3':
            $search = 'WHERE itmLevel = 4';
            break;
        case '4':
            $search = 'WHERE itmLevel = 5';
            break;
        case '5':
            $search = 'WHERE itmLevel = 5 OR itmLevel = 6';
            break;
        case '6':
            $search = 'WHERE itmLevel = 6';
            break;
        case '7':
            $search = 'WHERE itmLevel = 7';
            break;
        case '8':
            $search = 'WHERE itmLevel = 8';
            break;
        case '9':
            $search = 'WHERE itmLevel = 9';
            break;
    }

    $item = mysqli_fetch_assoc($application->db->query("SELECT itmid FROM items {$search} ORDER BY RAND() LIMIT 1"));

    return $item['itmid'];
}

function locationDropdown($limit, $locationName = "location"): string
{
    $ret = '<select name=\'' . $locationName . '\' type=dropdown>';

    if ($limit > 0) {
        $ret .= '<option value=1>Palermo</option>';
    }
    if ($limit > 9) {
        $ret .= '<option value=10>Rome</option>';
    }
    if ($limit > 24) {
        $ret .= '<option value=25>Monte Carlo</option>';
    }
    if ($limit > 49) {
        $ret .= '<option value=50>New York</option>';
    }
    if ($limit > 99) {
        $ret .= '<option value=100>Chicago</option>';
    }
    if ($limit > 249) {
        $ret .= '<option value=250>Montreal</option>';
    }
    if ($limit > 499) {
        $ret .= '<option value=500>Caracas</option>';
    }

    $ret .= '</select>';

    return $ret;
}

function locationName($locationId = 0): string
{
    if ($locationId == 0) {
        return 'The Manor House';
    }
    if ($locationId == 1) {
        return 'Palermo';
    }
    if ($locationId == 10) {
        return 'Rome';
    }
    if ($locationId == 25) {
        return 'Monte Carlo';
    }
    if ($locationId == 42) {
        return 'Bomb Shelter';
    }
    if ($locationId == 50) {
        return 'New York';
    }
    if ($locationId == 100) {
        return 'Chicago';
    }
    if ($locationId == 250) {
        return 'Montreal';
    }
    if ($locationId == 500) {
        return 'Caracas';
    }

    return 'Unknown';
}

function logAttack($attackerUserId, $defenderUserId, $result, $shortDescription, $longDescription, $itemId = 0): void
{
    global $application;

    $application->db->query("INSERT INTO logsAttacks (laAttacker, laDefender, laResult, laTime, laLogShort, laLogLong, laItem) VALUES ({$attackerUserId}, {$defenderUserId}, '{$result}', unix_timestamp(), \"{$shortDescription}\", \"{$longDescription}\", {$itemId})");
}

function logEvent($userId, $text): void
{
    global $application;

    $row = mysqli_fetch_assoc($application->db->query("SELECT `rank` FROM users WHERE userid = {$userId}"));

    if ($row['rank'] != 'Giovane' && $row['rank'] != 'Inattivo') {
        $timestamp = strtotime(date('Y-m-d H:i:s'));
        $application->db->query("INSERT INTO logsEvents (leUser, leTime, leRead, leText) VALUES ({$userId}, '{$timestamp}', 0, \"{$text}\")");
        $application->db->query("UPDATE users SET newEvents = newEvents + 1 WHERE userid = {$userId}");
    }
}

function logItem($send, $sendIP, $receive, $receiveIP, $reason, $item, $quantity): void
{
    global $application;

    $application->db->query("INSERT INTO logsItems (liSender, liSenderIP, liReceiver, liReceiverIP, liReason, liItem, liQuantity, liTime) VALUES({$send}, '{$sendIP}', {$receive}, '{$receiveIP}', '{$reason}', {$item}, {$quantity}, unix_timestamp())");
}

function logWealth($send, $sendIP, $receive, $receiveIP, $amount, $type, $source): void
{
    global $application;

    $application->db->query("INSERT INTO logsWealth (lwSender, lwSenderIP, lwReceiver, lwReceiverIP, lwAmount, lwTime, lwType, lwSource) VALUES ({$send}, '{$sendIP}', {$receive}, '{$receiveIP}', {$amount}, unix_timestamp(), '{$type}', '{$source}')");
}

function mafioso($mafiosoId): string
{
    global $application;

    if ($mafiosoId == 22) {
        return '<strong>Staff Support</strong>';
    }

    $userId = $_SESSION['userId'];
    $mafioso = mysqli_fetch_assoc($application->db->query("SELECT u.rankCat, u.watchfulEye, u.gang, u.signature, u.userid, u.username, u.gangtitle, u.fedjail, u.donatordays, u.hospital, u.jail, u.trackActionTime, f.famTag, f.famName, f.famID FROM users u LEFT JOIN family f ON u.gang = f.famID WHERE u.userid = {$mafiosoId}"));
    $user = mysqli_fetch_assoc($application->db->query("SELECT rankCat FROM users WHERE userid = {$userId}"));
    $family = '';
    $we = '';

    if ($mafioso != null && $mafioso['watchfulEye'] == 1 && $user['rankCat'] == 'Staff') {
        $we = '<span class=staffview title=\'Super Double Secret Probation\'>&#920;&nbsp;</span>';
    }

    $result = $application->db->query("SELECT clType FROM contactList WHERE clSource = {$userId} AND clContact = {$mafiosoId}");
    if (mysqli_num_rows($result) > 0) {
        $contactType = mysqli_fetch_assoc($result);
        if ($contactType['clType'] == 'friend') {
            $we = '<span class=staffview title=Friend>&#10004;&nbsp;</span>';
        }
        if ($contactType['clType'] == 'enemy') {
            $we = '<span class=staffview title=Enemy>&#8224;&nbsp;</span>';
        }
    }

    if ($mafioso != null && $mafioso['famTag']) {
        $family = '<a title=\'' . $mafioso['famName'] . '\' href=\'family.php?action=view&ID=' . $mafioso['famID'] . '\'><span class=familyid>&middot; ' . $mafioso['famTag'] . '</span></a>&nbsp;';
    }

    if ($mafioso != null) {
        if ($mafioso['rankCat'] == 'Staff') {
            return $we . '<a title=\'' . mysql_tex_out($mafioso['signature']) . '\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><font class=staffer>' . $mafioso['username'] . ' <span class=lighter>[' . $mafioso['gangtitle'] . ']</span></font></a> ' . $family;
        } else if ($mafioso['fedjail']) {
            return $we . '<a title=\'In Federal Jail\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><span class=staffview>' . $mafioso['username'] . ' [' . $mafioso['userid'] . ']</span></a>';
        } else if ($mafioso['donatordays']) {
            return $we . '<a title=\'' . mysql_tex_out($mafioso['signature']) . '\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><font class=donator>' . $mafioso['username'] . '</font> <span class=userid>[' . $mafioso['userid'] . ']</span></a> ' . $family;
        } else {
            return $we . '<a title=\'' . mysql_tex_out($mafioso['signature']) . '\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><font class=nondonator>' . $mafioso['username'] . '</font> <span class=userid>[' . $mafioso['userid'] . ']</span></a> ' . $family;
        }
    }

    return "";
}

function mafiosoLight($mafiosoId): string
{
    global $application;

    if ($mafiosoId == 22) {
        return '<strong>Staff Support</strong>';
    }

    $result = $application->db->query("SELECT rankCat, watchfulEye, gang, signature, userid, username, gangtitle, fedjail, donatordays, hospital, jail, trackActionTime FROM users WHERE userid = {$mafiosoId}");
    $mafioso = mysqli_fetch_assoc($result);

    $we = '';

    if ($mafioso['watchfulEye'] == 1 && $application->user['rankCat'] == 'Staff') {
        $we = '<span class=staffview title=\'Super Double Secret Probation\'>&#920;&nbsp;</span>';
    }
    if ($mafioso['rankCat'] == 'Staff') {
        return $we . '<a title=\'' . mysql_tex_out($mafioso['signature']) . '\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><font class=staffer style=\'font-weight:normal;\'>' . $mafioso['username'] . ' <span class=lighter>[' . $mafioso['gangtitle'] . ']</span></font></a>';
    } else if ($mafioso['fedjail']) {
        return $we . '<a title=\'In Federal Jail\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><span class=staffview style=\'font-weight:normal;\'>' . $mafioso['username'] . ' [' . $mafioso['userid'] . ']</span></a>';
    } else if ($mafioso['donatordays']) {
        return $we . '<a title=\'' . mysql_tex_out($mafioso['signature']) . '\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><font class=donator style=\'font-weight:normal;\'>' . $mafioso['username'] . '</font> <span class=userid>[' . $mafioso['userid'] . ']</span></a>';
    } else {
        return $we . '<a title=\'' . mysql_tex_out($mafioso['signature']) . '\' href=\'viewuser.php?u=' . $mafioso['userid'] . '\'><font class=nondonator style=\'font-weight:normal;\'>' . $mafioso['username'] . '</font> <span class=userid>[' . $mafioso['userid'] . ']</span></a>';
    }
}

function mafiosoMenu($name = "mafioso", $additional = ""): string
{
    global $application;

    $menu = '<select name=\'' . $name . '\' type=dropdown>';
    $result = $application->db->query("SELECT userid, username FROM users WHERE `rank` NOT IN ('Giovane', 'Inattivo') {$additional} ORDER BY username");

    while ($row = mysqli_fetch_assoc($result)) {
        $menu .= '<option value=\'' . $row['userid'] . '\'>' . $row['username'] . ' [' . $row['userid'] . ']</option>';
    }

    $menu .= '</select>';

    return $menu;
}

function mafiosoName($mafiosoId = 0): string
{
    global $application;

    if ($mafiosoId == 22) {
        return '<strong>Staff Support</strong>';
    }

    $row = mysqli_fetch_assoc($application->db->query("SELECT username FROM users WHERE userid = {$mafiosoId}"));

    return $row['username'];
}

function mailMafioso($from, $to, $subject, $text): void
{
    global $application;

    if ($to == 22) {
        $application->db->query("INSERT INTO mail (mail_read, mail_from, mail_to, mail_time, mail_subject, mail_text, mail_directory) VALUES (0, {$from}, {$to}, unix_timestamp(), '{$subject}', '{$text}', 'Staff')");
        $application->db->query("UPDATE users SET newMail = newMail + 1 WHERE rankCat = 'Staff'");
    } else {
        $application->db->query("INSERT INTO mail (mail_read, mail_from, mail_to, mail_time, mail_subject, mail_text, mail_directory) VALUES (0, {$from}, {$to}, unix_timestamp(), '{$subject}', '{$text}', 'Inbox')");
        $application->db->query("UPDATE users SET newMail = newMail + 1 WHERE userid = {$to}");
    }
}

function moneyFormatter($amount, $symbol = '$'): string
{
    return $symbol . number_format($amount);
}

function mysql_num($var): float|int
{
    $bs = array(",", "$", ".");
    $var = str_replace($bs, "", $var);

    return abs((int)$var);
}

function mysql_tex($var): string
{
    global $application;

    if ($application->user['rankCat'] == 'Staff') {
        $var = str_replace("[img]", '%3Cimg src=', $var);
        $var = str_replace("[/img]", ' width=200 height=200%3E', $var);
    }

    $var = str_replace("[", '', $var);
    $var = str_replace("]", '', $var);
    $var = str_replace("\r", "<br>", $var);
    $var = str_replace("\n", "", $var);
    $var = str_replace("<br>", "%3Cbr%3E", $var);
    $var = str_replace("<em>", "%3Cem%3E", $var);
    $var = str_replace("</em>", "%3C%2Fem%3E", $var);
    $var = str_replace("<strong>", "%3Cstrong%3E", $var);
    $var = str_replace("</strong>", "%3C%2Fstrong%3E", $var);

    $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
        '@<style[^>]*?>.*?</style>@siU',   // Strip style tags properly
        '@<[\/\!]*?[^<>]*?>@si',           // Strip out HTML tags
        '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments and CDATA
    );

    $var = preg_replace($search, '', $var);
    $var = str_replace("%3Cimg", "<img", $var);
    $var = str_replace("200%3E", "200>", $var);
    $var = str_replace("%3Cbr%3E", "<br>", $var);
    $var = str_replace("%3Cem%3E", "<em>", $var);
    $var = str_replace("%3C%2Fem%3E", "</em>", $var);
    $var = str_replace("%3Cstrong%3E", "<strong>", $var);
    $var = str_replace("%3C%2Fstrong%3E", "</strong>", $var);

    return mysqli_real_escape_string($application->db, $var);
}

function mysql_tex_edit($var): string
{
    global $application;

    $var = str_replace("<br>", "\r", $var);
    if ($application->user['rankCat'] == 'Staff') {
        $var = str_replace("<img src=", "[img]", $var);
        $var = str_replace(" width=200 height=200>", "[/img]", $var);
    }

    return stripslashes($var);
}

function mysql_tex_out($var): string
{
    return stripslashes($var);
}

function newsPost($user, $article = 0, $image = 0, $blue = 0): void
{
    global $application;

    $article = str_ireplace("asshole", 'ass', $article);
    $article = str_ireplace("chink", 'pile of junk', $article);
    $article = str_ireplace("cunt", 'idiot', $article);
    $article = str_ireplace("fuck", 'fark', $article);
    $article = str_ireplace("fag", 'lame', $article);
    $article = str_ireplace("nigger", 'pile of junk', $article);
    $article = str_ireplace("pussy", 'wus', $article);

    $application->db->query("INSERT INTO news (newsFrom, newsTime, newsText, newsImage, newsBlueRoom, newsDelete) VALUES ({$user}, unix_timestamp(), \"{$article}\", \"{$image}\", {$blue}, 0)");
    $application->db->query("UPDATE users SET newNews = newNews + 1 WHERE userid != {$user}");
}

function pagePermission($mustLogin = 0, $staff = 0, $noJail = 0, $noHospital = 0, $lockdown = 0): void
{
    global $application;

    if ($mustLogin == 1 && $_SESSION['loggedin'] == 0) {
        header("Location: index.php");
        exit;
    }

    if ($staff == 1 && $application->user['rankCat'] != 'Staff') {
        header('Refresh: 5; url=home.php');
        print '
            <h3>Staff Only</h3>
            <p>This area is reserved for the staff.</p>
            <p>Your transgression has been logged. We will assume you are here by accident, but continued attempts will result in punishment - possibly even some time in the dreaded <em>Harmony Hut</em>.</p>
            <p><a href=\'home.php\'>Head back home</a></p>
        ';
        exit;
    } else if ($noJail == 1 && $application->user['jail'] > 0) {
        header('Refresh: 5; url=jail.php');
        print '
            <h3>You cannot make it there</h3>
            <p>Much as you would like to, you cannot do this while in jail. That is the point after all - jail is a punishment.</p>
            <p>There are things you can do in jail, and a few ways out, but this is not one of them.</p>
            <p><a href=\'jail.php\'>Head back to jail</a> or <a href=\'home.php\'>head to your cell</a></p>
        ';
        exit;
    } else if ($noHospital == 1 && $application->user['hospital'] > 0) {
        header('Refresh: 5; url=hospital.php');
        print '
            <h3>You cannot make it there</h3>
            <p>Much as you would like to, you cannot do this while sick in the hospital. That is the point after all you are too sick to move.</p>
            <p>There are things you can do from your bed, and a few ways out, but this is not one of them.</p>
            <p><a href=\'hospital.php\'>Head back to the hospital</a> or <a href=\'home.php\'>return to your room</a></p>
        ';
        exit;
    } else if ($lockdown == 1) {
        $row = mysqli_fetch_assoc($application->db->query("SELECT famID, famHeadquarters FROM family WHERE {$application->user['gang']} = famID"));
        if ($row['famHeadquarters'] == $application->user['location'] && $application->user['gangLockdown'] > 0) {
            header('Refresh: 5; url=familyYours.php');
            print '
                <h3>You cannot make it there</h3>
                <p>Much as you would like to, you cannot do this while your family is on Lockdown. You are hiding from the enemy and trying this will expose you and the Don forbids it.</p>
                <p><a href=\'familyYours.php\'>Head to the Family</a> or <a href=\'home.php\'>return home</a></p>
            ';
            exit;
        }
    }
}

function setWillpower($userId = 0): void
{
    global $application;

    if ($userId == 0) {
        $userId = $application->user['userid'];
    }

    $row = mysqli_fetch_assoc($application->db->query("SELECT SUM(inv_qty) as countPF FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 120"));
    $pf = $row['countPF'] * 1000 + 100;

    $application->db->query("UPDATE users SET maxwill = {$pf} + (residence_total * 50) WHERE userid = {$userId}");
    $application->db->query("UPDATE users SET will = maxwill WHERE will > maxwill AND userid = {$userId}");
}

function staffLogAdd($text): void
{
    global $application;

    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $text = mysqli_real_escape_string($application->db, $text);

    $application->db->query("INSERT INTO stafflog (user, time, action, ip) VALUES ({$application->user['userid']}, unix_timestamp(), '{$text}', '{$ipAddress}')");
}

function status($mafiosoId): string
{
    global $application;

    $mafioso = mysqli_fetch_assoc($application->db->query("SELECT trackActionTime, `rank`, rankCat FROM users WHERE userid = {$mafiosoId}"));
    $la = time() - $mafioso['trackActionTime'];
    $unit = 'sec';

    if ($la >= 60) {
        $la = (int)($la / 60);
        $unit = 'mins';

        if ($la >= 60) {
            $la = (int)($la / 60);
            $unit = 'hours';

            if ($la >= 24) {
                $la = (int)($la / 24);
                $unit = 'days';
            }
        }
    }

    if ($mafioso['rank'] == 'Giovane') {
        return '<font class=giovane>Giovane</font>';
    }
    if ($mafioso['rank'] == 'Associate') {
        return '<font class=offline>Associate</font>';
    }
    if ($mafioso['rankCat'] == 'Staff' && $application->user['rankCat'] == 'Player') {
        return '<font class=staffer>Watching</font>';
    }
    if ($mafioso['rank'] == 'Inattivo') {
        return '<font class=inactive>Inattivo</font>';
    }
    if ($unit == "sec" || ($unit == "mins" && $la < 5)) {
        return '<font class=online>Online</font>';
    }
    if ($unit == "mins" && $la < 15) {
        return '<font class=onlinejust>Online</font>';
    }
    if ($unit == "mins" && $la < 60) {
        return '<font class=recent>' . $la . ' ' . $unit . '</font>';
    } else {
        return '<font class=offline>' . $la . ' ' . $unit . '</font>';
    }
}

function unauthorized($mafiosoId, $severity): void
{
    global $application;

    $result = $application->db->query("SELECT email FROM users WHERE userid = {$mafiosoId}");
    $mafioso = mysqli_fetch_assoc($result);
    $email = $mafioso['email'];
    $headers = "From: boomer@firstmafiawar.com\r\n";
    $subject = "First Mafia War Account ";

    if ($severity == 3) {
        print '<p>You are NOT authorized to view this page.</p><p>No soup for you!</p>';
        $application->db->query("UPDATE users SET login_name = '', force_logout = 1 WHERE userid = {$mafiosoId}");
        $body = 'Please do not attempt to view or otherwise access areas of the game that you do not have permission to access. If it was a mistake, no problem, but it is going on your permanent record for future reference.\n\nYour login has been disabled until you contact me and we sort out the details.\n\n-Boomer';
        staffLogAdd(mafiosoLight($mafiosoId) . " has had their account suspended.");
    } else if ($severity == 2) {
        print '<p>You are NOT authorized to view this page.</p><p>Bad Monkey, no banana.</p>';
        $application->db->query("UPDATE users SET fedjail = 1, fedjailReason = '4) Attempted Unauthorized Access' WHERE userid = {$mafiosoId}");
        $body = 'Please do not attempt to view or otherwise access areas of the game that you do not have permission to access. If it was a mistake, no problem, but it is going on your permanent record for future reference.\n\nYou were logged out and placed in federal jail until midnight mafia time.\n\n-Boomer';
        staffLogAdd(mafiosoLight($mafiosoId) . " has been placed in fedjail.");
    } else {
        print '<p>You are not authorized to view this page.</p><p>Please go somewhere else.</p>';
        $application->db->query("UPDATE users SET force_logout = 1 WHERE userid = {$mafiosoId}");
        $body = 'Please do not attempt to view or otherwise access areas of the game that you do not have permission to access. If it was a mistake, no problem, but it is going on your permanent record for future reference.\n\nYou were logged out, but you can get back in anytime.\n\n-Boomer';
    }

    if (mail($email, $subject, $body, $headers)) {
        staffLogAdd(mafiosoLight($mafiosoId) . " viewed an unauthorized page.");
    }

    $application->header->endPage();
    exit;
}

function warType($type = 1): string
{
    return match ($type) {
        1 => 'Don duel',
        2 => 'skirmish',
        3 => 'light battle',
        4 => 'turf war',
        5 => 'vendetta',
        default => '',
    };
}
