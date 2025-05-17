<?php

$menuHide = 1;
require_once "globals.php";
global $application, $userId;
pagePermission($lgn=1, $stff=0, $njl=0, $nhsp=1, $nlck=0);

$actionDo  = isset($_GET['action'])     ? mysql_tex($_GET['action'])    : '';
$weaponID  = isset($_GET['wepid'])      ? mysql_num($_GET['wepid'])     : null;
$targetID  = isset($_GET['ID'])         ? mysql_num($_GET['ID'])        : 0;
$winID     = isset($_POST['winID'])     ? mysql_num($_POST['winID'])    : 0;
$stolemod  = isset($_POST['stole'])     ? mysql_num($_POST['stole'])    : 0;
$rspmod    = isset($_POST['rsp'])       ? mysql_num($_POST['rsp'])      : 0;
$loc       = isset($_POST['loc'])       ? mysql_tex($_POST['loc'])      : '';
$took      = isset($_POST['took'])      ? mysql_tex($_POST['took'])     : '';
$enhance   = isset($_POST['enhance'])   ? mysql_num($_POST['enhance'])  : 0;

$opponent = mysqli_fetch_assoc($application->db->query("SELECT u.mugGear, u.mugRespect, u.rank, u.rankCat, u.comRank, u.respect, u.money, u.moneyChecking, u.moneySavings, u.exp, u.exp_needed, u.level, u.userid, u.hp, u.hospital, u.maxhp, u.gang, u.username, u.jail, u.gangLockdown, u.location, us.* FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.userid = {$targetID}"));
$rcd = mysqli_fetch_assoc($application->db->query("SELECT cl.clContact FROM contactList cl LEFT JOIN coursesdone cd ON cd.userid = cl.clSource WHERE cd.courseid = 26 AND cd.userid={$application->user['userid']} AND cl.clType = 'enemy' AND cl.clContact = {$opponent['userid']}"));
$rce = mysqli_fetch_assoc($application->db->query("SELECT clContact FROM contactList WHERE clType = 'enemy' AND clSource = {$application->user['userid']} AND clContact = {$opponent['userid']}"));
$rcf = mysqli_fetch_assoc($application->db->query("SELECT clContact FROM contactList WHERE clType = 'friend' AND clSource = {$application->user['userid']} AND clContact = {$opponent['userid']}"));

if ($opponent['gang' ] > 0) { $rrg = mysqli_fetch_assoc($application->db->query("SELECT famHeadquarters FROM family WHERE famID = {$opponent['gang']}"));}
if ($application->user['gang'] > 0) { $rirg = mysqli_fetch_assoc($application->db->query("SELECT famHeadquarters FROM family WHERE famID = {$application->user['gang']}"));}
if ($weaponID > 0) {
    $irwep = mysqli_fetch_assoc($application->db->query("SELECT iv.inv_id, i.itmtype, i.itmid, i.itmCombat, i.itmCombatType FROM items i LEFT JOIN inventory iv ON i.itmid = iv.inv_itemid WHERE iv.inv_userid = {$application->user['userid']} AND iv.inv_equip = 'yes' AND i.itmid = {$weaponID}"));
    if ($irwep['itmid'] == 0) { $irwep = mysqli_fetch_assoc($application->db->query("SELECT itmid, itmCombat, itmCombatType FROM items WHERE itmid = 7")); }

    $urwep = mysqli_fetch_assoc($application->db->query("SELECT iv.inv_id, i.itmid, i.itmtype, i.itmCombat, i.itmCombatType FROM items i LEFT JOIN inventory iv ON i.itmid = iv.inv_itemid WHERE iv.inv_userid = {$opponent['userid']} AND iv.inv_equip = 'yes' AND i.itmtype != 60 ORDER BY RAND() LIMIT 1"));
    if ($urwep['itmid'] == 0 || $opponent['jail'] > 0) { $urwep = mysqli_fetch_assoc($application->db->query("SELECT itmid, itmCombat, itmCombatType FROM items WHERE itmid = 7")); }
}

// Set Armor
$irarm = mysqli_fetch_assoc($application->db->query("SELECT i.itmid, i.itmCombat, i.itmCombatType FROM items i LEFT JOIN inventory iv ON i.itmid = iv.inv_itemid WHERE iv.inv_userid = {$application->user['userid']} AND iv.inv_equip='yes' AND i.itmtype=60"));
if ($irarm['itmid'] == 0 OR $application->user['jail'] > 0) { $irarm = mysqli_fetch_assoc($application->db->query("SELECT itmid, itmCombat, itmCombatType FROM items  WHERE itmid = 102")); }

$urarm = mysqli_fetch_assoc($application->db->query("SELECT i.itmid, i.itmCombat, i.itmCombatType FROM items i LEFT JOIN inventory iv ON i.itmid = iv.inv_itemid WHERE iv.inv_userid = {$opponent['userid']} AND iv.inv_equip = 'yes' AND i.itmtype = 60 ORDER BY rand() LIMIT 1"));
if ($urarm['itmid'] == 0 || $opponent['jail'] > 0) { $urarm = mysqli_fetch_assoc($application->db->query("SELECT itmid, itmCombat, itmCombatType FROM items WHERE itmid = 102")); }

// Special Attack Sidebar
print "
        <h6>Attacking</h6>
        <p>Once you begin the attack, don\'t stop.</p>
        <p>If you stop attacking in any way, you will suffer more than if you were to simply lose. Only the weak and lame run from a fight they began.</p>
        <p>Remember that attacking and fighting are the foundations upon which you test your own strength. However, there is also respect in the fight. Attacking online players without cause is likely to get you a lot of broken bones by their friends.</p>
        <p>So fight with a purpose - and fight to win!</p>
    </div>
    <div class=content>
    <h3>Warfare</h3>
    <div class=floatright>
        <img src='../public/assets/images/photos/streetFighting.jpg' width=222 height=529 alt='street fighting'>
    </div>
";

// the end before the beginning
if ($actionDo == 'finish' && $application->user['attacking'] > 0) {
   $gan = 2;
   $tim = 15;
   $application->db->query("UPDATE users SET attacking = 0 WHERE userid={$application->user['userid']}");

   if ($winID == $application->user['userid']) {
       $win = $application->user['userid'];
       $los = $opponent['userid'];
       $loslvl = $opponent['level'];
       $lxp = $opponent['exp'];
       $lexpn = $opponent['exp_needed'];
       $wxp = $application->user['exp'];
       $wexpn = $application->user['exp_needed'];
       $winwepcom = $irwep['itmCombat'];
       $losarmcom = $urarm['itmCombat'];
       $winweptyp = $irwep['itmCombatType'];
       $losarmtyp = $urarm['itmCombatType'];
   } elseif ($winID == $opponent['userid']) {
       $win = $opponent['userid'];
       $los = $application->user['userid'];
       $loslvl = $application->user['level'];
       $lxp = $application->user['exp'];
       $lexpn = $application->user['exp_needed'];
       $wxp = $opponent['exp'];
       $wexpn = $opponent['exp_needed'];
       $winwepcom = $urwep['itmCombat'];
       $losarmcom = $irarm['itmCombat'];
       $winweptyp = $urwep['itmCombatType'];
       $losarmtyp = $irarm['itmCombatType'];
   } else {
       print '<p>What are you trying to pull anyway?</p>';

       $application->header->endPage();
       exit;
   }

   $exp = $loslvl * $loslvl * $loslvl;
   $exp = rand(round($exp * 0.5), round($exp * 0.8));
   $stt = $loslvl;
   $rsp = 0;
   $stole = 0;
   $stoleCheck = 0;
   $stoleSave = 0;

   if ($winweptyp == 1) { $tim = $tim + round($winwepcom * 0.1); }
   if ($losarmtyp == 1) { $tim = $tim - round($losarmcom * 0.1); }
   if ($winweptyp == 3) { $exp = $exp + ($winwepcom * $loslvl); }
   if ($losarmtyp == 3) { $exp = $exp - ($losarmcom * $loslvl); }
   if ($winweptyp == 5) { $stt = $stt + $winwepcom; }
   if ($losarmtyp == 5) { $stt = $stt + $losarmcom; }

   $gxp = round(max(100, min($exp, ($wexpn - $wxp))));
   $gxpp = round($gxp / $wexpn * 100);
   $rxp = round(min(($exp * 0.1), $lxp));
   $rxpp = round($rxp / $lexpn * 100);
   $rst = round($stt * 0.2);

   if ($tim < 5) { $tim = 5; }

   $loca = '(J)';
   $count = 'count_jail';
   if ($loc == 'hospital') {
       $loca = '(H)';
       $count = 'count_hospital';
   }

   if ($enhance == '01') {
       if ($application->user['attacksID'] > 0) { $application->db->query("UPDATE users SET attacks = attacks - 1 WHERE userid = {$application->user['userid']}"); }

       $how = 'lost to';
       $who = mafiosoLight($opponent['userid']);
       $finale = '
           <p>You lost a fight to ' . mafioso($opponent['userid']) . '. That is embarrasing.</p>
           <p>As an added bonus, you lost ' . $rxpp . '% of the experience you needed to achieve your next level, all your health, ' . $rst . ' from your combat abilities and you get to spend the next ' . $tim . ' minutes in the county ' . $loc . '. So it is not true what they say about learning from your mistakes. Clearly, you are doomed to repeat them.</p>
       ';

       print $finale;

       $_SESSION['attacklog'] .= $finale;
       $atklog = mysql_tex($_SESSION['attacklog']);

       logAttack($opponent['userid'], $application->user['userid'],'lost',"{$tim} mins{$loca}, {$how} {$who}","{$atklog}",'');
   } else {
       if ($application->user['attacksID'] > 0) { $application->db->query("UPDATE users SET attacks = attacks + 2 WHERE userid = {$application->user['userid']}"); }

       $how = 'beaten by';
       $who = mafiosoLight($application->user['userid']);
       $comment = '';
       $with = '';
       if ($enhance != 0) {
           $inv = mysqli_fetch_assoc($application->db->query("SELECT inv_id, * FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid = {$enhance}"));

           switch($enhance) {
               case '10' :
                   $with = 'with a Mob Accountant';
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $stoleSave = round($opponent['moneySavings'] * 0.1);
                   $took += $stoleSave;

                   if ($stoleSave < 1) {
                       print 'Your Mob Accountant was unable to take anything out of their Savings account as there isn\'t anything there!<br>';
                   } else {
                       print 'While they are out cold, your mob accountant took ' . moneyFormatter($stoleSave) . ' from their Savings Account and transferred it to yours.';
                   }
                   break;
               case '12' :
                   $tim = $tim + 10;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Nurse you added ten minutes to their time.';
                   $with = 'with a Nurse';
                   break;
               case '13' :
                   $tim = $tim + 20;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Doctor you added twenty minutes to their time.';
                   $with = 'with a Doctor';
                   break;
               case '24' :
                   $tim = $tim + 90;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Chief of Medicine you added ninety minutes to their time.';
                   $with = 'with a Chief of Medicine';
                   break;
               case '25' :
                   $tim = $tim + 90;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Consiglieri you added ninety minutes to their time.';
                   $with = 'with a Consiglieri';
                   break;
               case '26' :
                   $tim = $tim + 20;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Police Sergeant you added twenty minutes to their time.';
                   $with = 'with a Sergeant';
                   break;
               case '27' :
                   $tim = $tim + 10;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Police Officer you added ten minutes to their time.';
                   $with = 'with an Officer';
                   break;
               case '54' :
                   $who = 'a Patsy';
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Fall Guy you avoid the repercussions of the hit.';
                   break;
               case '66' :
                   $tim = $tim + 40;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Detective you added forty minutes to their time.';
                   $with = 'with a Detective';
                   break;
               case '67' :
                   $tim = $tim + 40;
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Surgeon you added forty minutes to their time.';
                   $with = 'with a Surgeon';
                   break;
               case '92' :
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $with = 'with a Pick Pocket';
                   $linv = mysqli_fetch_assoc($application->db->query("SELECT inv.inv_id, inv.inv_itemid FROM inventory inv LEFT JOIN items i ON inv.inv_itemid = i.itmid WHERE inv.inv_userid = {$los} AND i.itmtype NOT IN (5, 50) AND inv.inv_equip != 'yes' AND inv.inv_qty > 0 ORDER BY rand() LIMIT 1"));

                   if ($linv['inv_id'] == 0) {
                       $comment = ' You were unable to steal anything more because they don\'t have anything worth stealing.';
                   } else {
                       $comment = '<p>By using the Pick Pocket, you steal two '.itemInfo($linv['inv_itemid']).' while they\'re down.</p>';
                       itemDelete($linv['inv_id'], 1, $los);
                       itemAdd($linv['inv_itemid'], 2,0,$win,0);
                       logEvent($los, mafiosoLight($win) . " stole two " . itemInfo($linv['inv_itemid']) . " from you after beating you.");
                   }
                   break;
               case '93' :
                   $with = 'with a Flyer';
                   if ($opponent['gang'] > 0) {
                       $rand = rand(1,2);
                       if ($rand == 1) {
                           $comment = ' By distributing flyers around the neighborhood, you try to reduce their family respect.';
                           $application->db->query("UPDATE family SET famRespect = famRespect - 1 WHERE famID = {$opponent['gang']}");
                       } else {
                           $comment = ' You distributed flyers around the neighborhood, trying to reduce their family respect, but you got the name wrong so it did not work. Better luck next time, sorry.';
                       }
                   } else {
                       $comment = ' Though you distributed flyers, they are not actually in a family so it does not to much to reduce their family respect.';
                   }
                   break;
               case '95' :
                   $who = 'a Federal Regulator';
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using a Federal Regulator they lost 10% of all their money and you get a $2,000,000 bonus.';
                   $application->db->query("UPDATE users SET moneySavings = moneySavings * 0.9, money = money * 0.9, moneyChecking = moneyChecking * 0.9, moneyTreasury = moneyTreasury * 0.9 WHERE userid = {$opponent['userid']}");
                   $application->db->query("UPDATE users SET moneyChecking = moneyChecking + 2000000 WHERE userid = {$application->user['userid']}");
                   break;
               case '636' :
                   $tim = $tim + 20;
                   $who = 'a Street Kid';
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $comment = ' By using Street Kids you added twenty minutes to their time and they cannot remember clearly who hit them.';
                   break;
               case '637' :
                   $with = 'with a Banker';
                   itemDelete($inv['inv_id'], 1, $application->user['userid']);
                   $stoleCheck = round($opponent['moneyChecking'] * 0.1);
                   $took += $stoleCheck;

                   if ($stoleCheck < 1) {
                       print ' Your Banker was unable to take anything out of their Checking account as there isn\'t anything there!';
                   } else {
                       print ' While they are out cold, your banker took ' . moneyFormatter($stoleCheck) . ' from their Checking Account and transferred it to yours.';
                   }
                   break;
           }
       }

       $rsp = $rsp + $rspmod;
       $stolen = $stolemod + $stoleCheck + $stoleSave;
       $finale = '
           <p>You won your fight with ' . mafioso($opponent['userid']) . '. Good job!</p>
           <p>You gained ' . $gxpp . '% of the experience you needed to achieve your next level, ' . $stt . ' to your combat abilities, ' . $rsp . ' tokens of respect, and ' . moneyFormatter($stolen) . ' in cash. They get to spend the next ' . $tim . ' minutes in the county ' . $loc . ', lose ' . $rxpp . '% of the experience they needed for their next level, ' . $rst . ' from their combat abilities as well as the cash and respect. ' . $comment . '</p>
       ';

       print $finale;

       $_SESSION['attacklog'] .= $finale;
       $atklog = mysql_tex($_SESSION['attacklog']);

       $what = '';
       if ($took != '') { $what = 'taking nothing'; }

       logAttack($application->user['userid'], $opponent['userid'],'won',"{$tim} mins{$loca}, {$how} {$who} {$with} {$what}","{$atklog}",0);
   }

   $application->db->query("UPDATE users SET exp = exp + {$gxp}, respect = respect + {$rsp}, money = money + {$stolemod}, moneyChecking = moneyChecking + {$stoleCheck}, moneySavings = moneySavings + {$stoleSave}, {$count} = {$count} + 1 WHERE userid = {$win}");
   $application->db->query("UPDATE userstats SET strength = strength + {$stt}, agility = agility + {$stt}, guard = guard + {$stt} WHERE userid = {$win}");
   $application->db->query("UPDATE users SET exp = exp - {$rxp}, hp = 1, {$loc} = {$loc} + $tim, hjReason = \"{$how} {$who} {$with} {$what}\", respect = respect - {$rsp}, money = money - {$stolemod}, moneyChecking = moneyChecking - {$stoleCheck}, moneySavings = moneySavings - {$stoleSave} WHERE userid = {$los}");

   if ($loc == 'hospital') {
       $application->db->query("UPDATE users SET jail = 0 WHERE userid = {$los}");
   } else {
       $application->db->query("UPDATE users SET hospital = 0 WHERE userid = {$los}");
   }

   if ($opponent['rankCat'] == 'Player') {
      $application->db->query("UPDATE userstats SET strength = strength - {$rst}, agility = agility - {$rst}, guard = guard - {$rst} WHERE userid = {$los}");
   }

   $application->db->query("UPDATE userstats SET strength = strength + 10, agility = agility + 10, guard = guard + 10 WHERE userid = {$los} AND (strength < 10 OR agility < 10 OR guard < 10)");

   if ($application->user['jail'] < 1 && ($application->user['comRank'] < $opponent['comRank']) && ($opponent['rank'] != 'Giovane' && $opponent['rank']!='Associate')) {
       $diff = $opponent['comRank'] - $application->user['comRank'];
       if ($application->user['level'] < 25) {
          $diff = 19;
       }

       if ($rce['clContact'] == $opponent['userid']) {
          $diff = $diff - 20;
       }

       if (rand(20, 50) < $diff || $rcf['clContact'] == $opponent['userid']) {
          $dr = 1;
          $irdag = $application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid=602");
          $ird = mysqli_num_rows($irdag);

          print "<p>You have {$ird} daggers.</p>";

          if ($ird > 2){
              $dr = 2;
          }

          if ($ird > 9) {
              $dr = 3;
          }

          if ($ird > 19) {
              $dr = 4;
          }

          if ($ird > 29) {
              $dr = 5;
          }

          itemAdd(602,1,$dr, $application->user['userid'],0);
          $dg = 1;

          if (rand(51, 100) < $diff) {
              itemAdd(602,1, $dr, $application->user['userid'],0);
              $dg = $dg + 1;
          }

          if (rand(101, 150) < $diff) {
              itemAdd(602,1, $dr, $application->user['userid'],0);
              $dg = $dg + 1;
          }

          if (rand(151, 200) < $diff) {
              itemAdd(602,1, $dr, $application->user['userid'],0);
              $dg = $dg + 1;
          }

          if (rand(201, 250) < $diff) {
              itemAdd(602,1, $dr, $application->user['userid'],0);
              $dg = $dg + 1;
          }

          logEvent($application->user['userid'],"You earned {$dg} " . itemInfo(602) . "(s) which will last {$dr} days. ({$application->user['comRank']}/{$opponent['comRank']})");

          if ($application->user['attacksID'] > 0) { $application->db->query("UPDATE users SET attacks = attacks - 2 WHERE userid = {$application->user['userid']}"); }
       }
   }

   attackGangWar($win, $los, $gan);

   if ($irwep['itmtype'] == 65) { itemDelete($irwep['inv_id'],1, $application->user['userid'],0); }
   if ($irwep['itmid'] != 7 && ($irwep['itmtype'] == 70 || $irwep['itmtype'] == 80)) {
      $application->db->query("UPDATE inventory SET inv_equip = 'no' WHERE inv_id = {$irwep['inv_id']}");
      print '<p>Do you wish to re-ready your weapon?<br><a title=\'' . moneyFormatter($irwep['itmCombat'] * 12) . '\' href=\'items.php?action=equp&iid=' . $irwep['inv_id'] . '\'>Oh yes please</a> &nbsp;&nbsp; &middot; &nbsp;&nbsp; <a href=\'home.php\'>No thanks, I\'ll just head on home</a></p>';
   } else {
      print '<p><a href=\'home.php\'>Head on home</a></p>';
   }

   $application->header->endPage();
   exit;
}

// Clear out the problems
if ($opponent['location'] == 42 || $application->user['location'] == 42) {
   print '<p>One of you is hiding in a Bomb Shelter. I\'m not going to say who it is, but if you see only cement block around you I bet you can guess. You cannot fight through protection designed to stop a nuclear blast with your pea-shooter. Sorry, but you just have to wait until both of you are breathing unfiltered air.</p>';

   $application->header->endPage();
   exit;
}

if ($targetID == $application->user['userid']) {
   print '
       <p>Only the crazy attack themselves, and we will not let you be that crazy here.</p>
       <p><a href=\'home.php\'>Home</a></p>
   ';

   $application->header->endPage();
   exit;
}

if ($application->user['energy'] < $application->user['maxenergy'] / 4) {
   print '
       <p>You can only attack someone when you have enough energy.</p>
       <p><a href=\'home.php\'>Home</a>.</p>
   ';

   $application->header->endPage();
   exit;
}

if ($application->user['hp'] <= 5 || $application->user['hospital'] > 0) {
   print '
       <p>When you are that sick, you can only attack your unconscious.</p>
       <p><a href=\'home.php\'>Home</a></p>
   ';

   $application->header->endPage();
   exit;
}

if ($opponent['hp'] < $opponent['maxhp'] / 4 || $opponent['hospital'] > 0) {
   print '
       <p>Your target is entirely too sick to fight. You can\'t get them out to play.</p>
       <p><a href=\'home.php\'>Home</a>.</p>
   ';

   $application->header->endPage();
   exit;
}

if ($application->user['gang'] == $opponent['gang'] && $application->user['gang'] > 0) {
   print '
       <p>You are in the same Family! Attacking members of your own Family is not permitted. Try slapping them around instead.</p>
       <p><a href=\'home.php\'>Go home</a>.</p>
   ';

   $application->header->endPage();
   exit;
}

if ($application->user['jail'] > 0 && $opponent['jail'] <= 0) {
    print '
        <p>You are in in jail and your target is not. You will have to get out before you can continue.</p>
        <p><a href=\'jail.php\'>Visit the jail</a></p>
    ';

    $application->header->endPage();
    exit;
}

if ($opponent['jail'] > 0 && $application->user['jail'] <= 0) {
    print '
        <p>They are in in jail. You will have to get them out or join them before you can continue.</p>
        <p><a href=\'jail.php\'>Visit the jail</a></p>
    ';

    $application->header->endPage();
    exit;
}

$irprep = mysqli_fetch_assoc($application->db->query("SELECT inv_itemid FROM inventory WHERE inv_itemid = 85 AND inv_userid = {$application->user['userid']} AND inv_equip = 'yes'"));
$urprep = mysqli_fetch_assoc($application->db->query("SELECT inv_itemid FROM inventory WHERE inv_itemid = 87 AND inv_userid = {$opponent['userid']} AND inv_equip = 'yes'"));

if (isset($irprep['inv_itemid']) && $irprep['inv_itemid'] == 85 && isset($urprep['inv_itemid']) && $urprep['inv_itemid'] != 87) {
    print '';
} else {
    if ($opponent['gangLockdown'] > 0 && $opponent['jail'] < 1 && $rrg['famHeadquarters'] == $opponent['location']) {
        print '<p>You cannot attack them while they are hunkered down in their Family Home. Wait until they leave town or end up in jail.</p>';

        $application->header->endPage();
        exit;
    }
}

if ($application->user['gangLockdown'] > 0 && $application->user['jail'] < 1 && $rirg['famHeadquarters'] == $application->user['location']) {
    print '<p>You cannot make attacks while you are hunkered down in your Family Home. To make further attacks, please leave town or go to jail.</p>';

    $application->header->endPage();
    exit;
}

if ($application->user['location'] == $opponent['location'] || ($application->user['jail'] > 0 && $opponent['jail'] > 0) || $rcd['clContact'] == $opponent['userid']) {
   $_SESSION['attacklog'] = "";
} else {
   print '
       <p>You can usually only attack someone in the same location.</p>
       <p><a href=\'home.php\'>Home</a>.</p>
   ';

   $application->header->endPage();
   exit;
}

// Set Weapon
if ($weaponID == 0) {
   if ($application->user['jail'] > 0) {
       print '
           <p>You are both in jail so you\'re both using shivs to settle the score.</p>
           <p><a href=\'attack.php?ID='.$targetID.'&amp;wepid=7\'>Continue</a></p>
       ';
   } else if ($opponent['gangLockdown'] > 0 && $opponent['jail'] < 1 && $rrg['famHeadquarters'] == $opponent['location'] && $irprep['inv_itemid'] == 85) {
       print '<p>You are using your ' . itemInfo(85) . ' to hit them inside their Family home. <a href=\'attack.php?ID=' . $targetID . '&amp;wepid=85\'>Burn them out!</a></p>';
   } else {
       $qw = $application->db->query("SELECT i.itmid, i.itmCombatType, i.itmCombat FROM items i LEFT JOIN inventory iv ON i.itmid = iv.inv_itemid WHERE iv.inv_userid = {$application->user['userid']} AND iv.inv_equip = 'yes' AND itmtype != 60 ORDER BY i.itmCombatType, i.itmCombat");
       print '
           <p>You are about to attack ' . mafiosoLight($opponent['userid']) . ' who may be protected by a ' . itemInfo($urarm['itmid']) . ' <span class=light>('.itemCombatType($urarm['itmCombatType']) . ' ' . $urarm['itmCombat'] . ')</span>.</p>
           <p>Please select a weapon for this battle by clicking on it:<br><ul>
       ';

       if (mysqli_num_rows($qw) > 0) {
           while ($rw = mysqli_fetch_assoc($qw)) {
               print '<li><a href=\'attack.php?ID=' . $targetID . '&amp;wepid=' . $rw['itmid'] . '\'>' . itemName($rw['itmid']) . ' <span class=light>(' . itemCombatType($rw['itmCombatType']) . ' ' . $rw['itmCombat'] . ')</span></a></li>';
           }

           print '</ul>';
       } else {
           print '
               <p>You have nothing to fight with. What are you thinking?<br>You wade in with your fists and a shiv against the best the 1960\'s had to offer.</p>
               <p><a href=\'attack.php?ID=' . $targetID . '&amp;wepid=7\'>Continue</a></p>
           ';
       }
   }

   $application->header->endPage();
   exit;
}

$begin = '<p>You are fighting with a ' . itemInfo($irwep['itmid']) . ' and protected by a ' . itemInfo($irarm['itmid']) . '.<br>Your opponent is fighting with a ' . itemInfo($urwep['itmid']) . ' and protected by a ' . itemInfo($urarm['itmid']) . '.</p>';
print $begin;

$_SESSION['attacklog'] .= $begin;

// Set Skills
$iragi = max(1,$application->user['agility'] + $application->user['agilityTemp']);
$irgua = max(1,$application->user['guard'] + $application->user['guardTemp']);
$irstr = max(1,$application->user['strength'] + $application->user['strengthTemp']) * 1.3;
$uragi = max(1,$opponent['agility'] + $opponent['agilityTemp']);
$urgua = max(1,$opponent['guard'] + $opponent['guardTemp']);
$urstr = max(1,$opponent['strength'] + $opponent['strengthTemp']) * 1.3;

// Set Base Damage (weapon-armor)
if ($irwep['itmCombatType'] == $urarm['itmCombatType']) {
   $irdammod = $irwep['itmCombat'] - round($urarm['itmCombat'] * 1.5);
} else if (($irwep['itmCombatType'] + $urarm['itmCombatType']) & 1) {
   $irdammod = $irwep['itmCombat'] - round($urarm['itmCombat'] * 0.75);
} else {
   $irdammod = $irwep['itmCombat'] - $urarm['itmCombat'];
}

if ($urwep['itmCombatType'] == $irarm['itmCombatType']) {
   $urdammod = $urwep['itmCombat'] - round($irarm['itmCombat'] * 1.5);
} else if (($urwep['itmCombatType'] + $irarm['itmCombatType']) & 1) {
   $urdammod = $urwep['itmCombat'] - round($irarm['itmCombat'] * 0.75);
} else {
   $urdammod = $urwep['itmCombat'] - $irarm['itmCombat'];
}

if ($irwep['itmid'] == 31 && $application->user['attacksID'] > 0) { $irdammod = 40; }

// Outside modifiers (daggers)
$iriv1 = $application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid = 602");
if (mysqli_num_rows($iriv1) > 0) {
   $idagrmod = (mysqli_num_rows($iriv1) * 19);
   $irdammod = $irdammod - $idagrmod;
   $iragi = $iragi - ($idagrmod * 1995);
   $irgua = $irgua - ($idagrmod * 1995);
   $irstr = $irstr - ($idagrmod * 1995);

   print '<p>Your Daggers are reducing your damage by ' . $idagrmod . ' and your combat statistics by ' . number_format($idagrmod * 995) . '!</p>';
}

$uriv1 = $application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$opponent['userid']} AND inv_itemid = 602");
if (mysqli_num_rows($uriv1) > 0) {
   $udagrmod = (mysqli_num_rows($uriv1) * 14);
   $urdammod = $urdammod - $udagrmod;
   $uragi = $uragi - ($udagrmod * 995);
   $urgua = $urgua - ($udagrmod * 995);
   $urstr = $urstr - ($udagrmod * 995);

   print '<p>Your opponents Daggers are reducing their damage by ' . $udagrmod . ' and their combat statistics by ' . number_format($udagrmod * 995) . '!</p>';
}

// Outside Skill modifiers (estates)
$iriv2 = $application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid = 611");
if (mysqli_num_rows($iriv2) > 0) {
   $iragi = $iragi * 1.3;
   $irstr = $irstr * 1.3;
   $irgua = $irgua * 1.3;

   print '<p>Your Estate boosts your combat skills!</p>';
}

$uriv2 = $application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$opponent['userid']} AND inv_itemid = 611");
if (mysqli_num_rows($uriv2) > 0) {
   $uragi = $uragi * 1.3;
   $urstr = $urstr * 1.3;
   $urgua = $urgua * 1.3;

   print '<p>Their Estate boosts their combat skills!</p>';
}

// Base Damage done
if ($urgua < 10) { $urgua = 10; }
if ($irgua < 10) { $irgua = 10; }
$irdam = max(1,(($irstr) / $urgua) * ($irdammod * 0.1));
$urdam = max(1,(($urstr) / $irgua) * ($urdammod * 0.1));

// Reduce energy and begin fight protection
$energyused = floor($application->user['maxenergy'] / 4);
$application->db->query("UPDATE users SET energy = energy - {$energyused} WHERE userid = {$application->user['userid']}");
$application->db->query("UPDATE users SET attacking = {$opponent['userid']} WHERE userid = {$application->user['userid']}");

$application->user['attacking'] = $opponent['userid'];

while ($application->user['hp'] > 1 && $opponent['hp'] > 1) {
   // Attacker Swings
   $hitratio = max(15, min(60 * (($iragi * 1.1) / $uragi),95));

   if (rand(1, 100) <= $hitratio || $application->user['rankCat'] == 'Staff') {
       $irdamage = round($irdam * rand(8, 15));

       if ($irdamage < 1) { $irdamage = 1; }
       if ($application->user['rankCat'] == 'Staff') { $irdamage = 3000; }
       if (($opponent['hp'] - $irdamage) <= 1) {
           $irdamage = $opponent['hp'];
           $opponent['hp'] = 1;
       } else {
           $opponent['hp'] -= $irdamage;
       }

       $application->db->query("UPDATE users SET hp = hp - {$irdamage} WHERE userid = {$opponent['userid']}");

       print '<font class=online>-> You hit ' . $opponent['username'] . ' doing ' . $irdamage . ' damage <span class=light>(' . $opponent['hp'] . ' left)</span></font><br>';

       $_SESSION['attacklog'] .= 'Attacker did ' . $irdamage . ' dropping Defender to ' . $opponent['hp'].'<br>';
   } else {
       print '<font class=recent>-| You tried to hit ' . $opponent['username'] . ' but missed.</font><br>';

       $_SESSION['attacklog'] .= 'Attacker missed<br>';
   }

   // Defender returns fire
   $hitratio = max(15, min(60 * (($uragi * 1.1) / $iragi),95));

   if (rand(1, 100) <= $hitratio && $application->user['rankCat'] != 'Staff') {
       $urdamage = round($urdam * rand(8, 15));

       if ($urdamage < 1) { $urdamage = 1; }
       if (($application->user['hp'] - $urdamage) <= 1) {
           $urdamage = $application->user['hp'];
           $application->user['hp'] = 1;
       } else {
           $application->user['hp'] -= $urdamage;
       }

       $application->db->query("UPDATE users SET hp = hp - {$urdamage} WHERE userid = {$application->user['userid']}");

       print '<font class=inactive><- ' . $opponent['username'] . ' hit you doing ' . $urdamage . ' damage <span class=light>(' . $application->user['hp'] . ' left)</span></font><br>';

       $_SESSION['attacklog'] .= 'Defender did ' . $urdamage . ' dropping Attacker to ' . $application->user['hp'] . '<br>';
   } else {
       print '<font class=recent>|- ' . $opponent['username'] . ' tried to hit you but missed.</font><br>';

       $_SESSION['attacklog'] .= 'Defender missed<br>';
   }

   print '<div style=\'font-size:.5em;\'><br></div>';
}

$win = $opponent['userid'];
$los = $application->user['userid'];
if ($opponent['hp'] <= 1) {
    $win = $application->user['userid'];
    $los = $opponent['userid'];
}

if ($urwep['itmtype'] == 65) {
   itemDelete($urwep['inv_id'],1, $opponent['userid'],0);
}

if ($application->user['jail'] > 0) {
   $loc = 'jail';
   $loca = 'jailed';
} else {
   $rnd = rand(1, 3);
   $loc = 'hospital';
   $loca = 'hospitalized';

   if ($rnd == 1) {
       $loc = 'jail';
       $loca = 'jailed';
   }
}

if ($los == $application->user['userid']) {
   print '
       <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
           <input type=hidden name=winID value=\'' . $opponent['userid'] . '\'>
           <input type=hidden name=loc value=\'' . $loc . '\'>
           <input type=hidden name=enhance value=\'01\'>
           <input type=submit value=\'Crawl Away\'>
       </form>
   ';
}

$application->db->query("UPDATE users SET newAttacks = newAttacks + 1 WHERE userid = {$opponent['userid']}");

if ($win == $application->user['userid']) {
   if ($irwep['itmCombatType'] == 1 && $loc == 'hospital') {
       print '
           <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
               <input type=hidden name=winID value=\'' . $application->user['userid'] . '\'>
               <input type=hidden name=loc value=\'' . $loc . '\'>
               <select name=enhance type=dropdown>
                   <option value=\'00\'>Leave them</option>
       ';

       $qi = $application->db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid IN (12, 13, 24, 54, 67, 93) ORDER BY inv_itemid");
       while ($ri = mysqli_fetch_assoc($qi)) {
           print '<option value=\'' . $ri['inv_itemid'] . '\'>' . itemInfo($ri['inv_itemid']) . '</option>';
       }

       print '
                   </select> &nbsp; &nbsp; 
               <input type=submit value=\'Dump the Body\'>
           </form>
       ';
   }

   if ($irwep['itmCombatType'] == 1 && $loc == 'jail') {
       print '
           <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
               <input type=hidden name=winID value=\'' . $application->user['userid'] . '\'>
               <input type=hidden name=loc value=\'' . $loc . '\'>
               <select name=enhance type=dropdown>
                   <option value=\'00\'>Leave them</option>
       ';

       $qi = $application->db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid IN (25, 26, 27, 54, 66, 93) ORDER BY inv_itemid DESC");
       while ($ri = mysqli_fetch_assoc($qi)) {
           print '<option value=\'' . $ri['inv_itemid'] . '\'>' . itemInfo($ri['inv_itemid']) . '</option>';
       }

       print '
                   </select> &nbsp; &nbsp; 
               <input type=submit value=\'Dump the Body\'>
           </form>
       ';
   }

   if ($irwep['itmCombatType'] == 2) {
       $rnd = rand(1, $opponent['mugGear']);

       if ($urarm['itmCombatType'] == 2 && $irwep['itmid'] !=47 ) {
           print '<p>You were unable to steal one of their items due to their protection... or just your own bad luck.</p>';
       } else if ($rnd == 1) {
           $linv = mysqli_fetch_assoc($application->db->query("SELECT inv.inv_id, inv.inv_itemid FROM inventory inv LEFT JOIN items i ON inv.inv_itemid = i.itmid WHERE inv.inv_userid = {$opponent['userid']} AND i.itmtype NOT IN (5, 50) AND inv.inv_equip != 'yes' ORDER BY rand() LIMIT 1"));

           if ($linv['inv_id'] == 0) {
               print '<p>You were unable to steal anything because they don\'t have anything worth stealing.</p>';
           } else {
               $application->db->query("UPDATE users SET mugGear = mugGear + 1 WHERE userid = {$opponent['userid']} AND rankCat = 'Player'");

               print '<p>By using a bit of stealth, you steal a ' . itemInfo($linv['inv_itemid']) . ' while they\'re down.</p>';

               itemDelete($linv['inv_id'], 1, $los);
               itemAdd($linv['inv_itemid'],1,0, $win,0);

               $took = 'a ' . itemName($linv['inv_itemid']);
           }
       } else {
           print '<p>You were unlucky and did not steal one of their items.</p>';
       }

       print '
           <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
               <input type=hidden name=winID value=\'' . $application->user['userid'] . '\'>
               <input type=hidden name=loc value=\'' . $loc . '\'>
               <input type=hidden name=took value=\'' . $took . '\'>
               <select name=enhance type=dropdown>
                   <option value=\'00\'>Leave them</option>
       ';

       $qi = $application->db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid IN (54, 92, 93, 636) ORDER BY inv_itemid DESC");
       while ($ri = mysqli_fetch_assoc($qi)) {
           print '<option value=\'' . $ri['inv_itemid'] . '\'>' . itemInfo($ri['inv_itemid']) . '</option>';
       }

       print '
                   </select> &nbsp; &nbsp; 
               <input type=submit value=\'Dump the Body\'>
           </form>
       ';
   }

   if ($irwep['itmCombatType'] == 3 || $irwep['itmCombatType'] == 5) {
       print '
           <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
               <input type=hidden name=winID value=\'' . $application->user['userid'] . '\'>
               <input type=hidden name=loc value=\'' . $loc . '\'>
               <select name=enhance type=dropdown>
                   <option value=\'00\'>Leave them</option>
       ';

       $qi = $application->db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid IN (54, 93) ORDER BY inv_itemid DESC");
       while ($ri = mysqli_fetch_assoc($qi)) {
           print '<option value=\'' . $ri['inv_itemid'] . '\'>' . itemInfo($ri['inv_itemid']) . '</option>';
       }

       print '
                   </select> &nbsp; &nbsp; 
               <input type=submit value=\'Dump the Body\'>
           </form>
       ';
   }

   if ($irwep['itmCombatType'] == 4) {
       $rnd = rand(1, 2);

       if ($urarm['itmCombatType'] == 4) {
           print '<p>You were unable to steal their wallet due to their protection.</p>';
       } else if ($opponent['money'] <=0 ) {
           print '<p>You were unable to steal any cash because they don\'t have any.</p>';
       } else if ($rnd == 1) {
           print '<p>You were unlucky and unabe to steal anything from their wallet.</p>';
       } else {
           $stole_percent = rand(5, 9) / 100;
           $stole = round($opponent['money'] * $stole_percent);

           print '<p>While they are out cold, you ' . moneyFormatter($stole) . ' from their wallet and take off.</p>';

           $took = moneyFormatter($stole);
       }

       print '
           <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
               <input type=hidden name=winID value=\'' . $application->user['userid'] . '\'>
               <input type=hidden name=loc value=\'' . $loc . '\'>
               <input type=hidden name=took value=\'' . $took . '\'>
               <input type=hidden name=stole value=\'' . $stole . '\'>
               <select name=enhance type=dropdown>
                   <option value=\'00\'>Leave them</option>
       ';

       $qi = $application->db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid IN (10, 54, 93, 95, 637) ORDER BY inv_itemid DESC");
       while ($ri = mysqli_fetch_assoc($qi)) {
           print '<option value=\'' . $ri['inv_itemid'] . '\'>' . itemInfo($ri['inv_itemid']) . '</option>';
       }

       print '
                   </select> &nbsp; &nbsp; 
               <input type=submit value=\'Dump the Body\'>
           </form>
       ';
   }

   if ($irwep['itmCombatType'] == 6) {
       $rnd = rand(1, $opponent['mugRespect']);
       $rsp = 0;

       if ($urarm['itmCombatType'] == 6) {
           print '<p>You were unable to assume one point of their Respect due to their protection.</p>';
       } else if ($opponent['respect'] <= 0) {
           print '<p>You were unable to assume any of their Respect because they don\'t have any.</p>';
       } else if ($rnd == 1) {
           $application->db->query("UPDATE users SET mugRespect = mugRespect + 1 WHERE userid = {$opponent['userid']} AND rankCat = 'Player'");

           print '<p>By pointing out to the general public you just stomped them hard, you take one of their tokens of respect for yourself.</p>';

           $took = 'a token of respect';
           $rsp = 1;
       } else {
           print '<p>You were unlucky and unable to assume one point of their Respect.</p>';
       }

       print '
           <form action=\'attack.php?action=finish&ID=' . $targetID . '&wepid=' . $weaponID . '\' method=POST>
               <input type=hidden name=winID value=\'' . $application->user['userid'] . '\'>
               <input type=hidden name=loc value=\'' . $loc . '\'>
               <input type=hidden name=took value=\'' . $took . '\'>
               <input type=hidden name=rsp value=\'' . $rsp . '\'>
               <select name=enhance type=dropdown>
                   <option value=\'00\'>Leave them</option>
       ';

       $qi = $application->db->query("SELECT inv_id, inv_itemid FROM inventory WHERE inv_userid = {$application->user['userid']} AND inv_itemid IN (54, 93) ORDER BY inv_itemid DESC");
       while ($ri = mysqli_fetch_assoc($qi)) {
           print '<option value=\'' . $ri['inv_itemid'] . '\'>' . itemInfo($ri['inv_itemid']).'</option>';
       }

       print '
                   </select> &nbsp; &nbsp; 
               <input type=submit value=\'Dump the Body\'>
           </form>
       ';
   }
}

$application->header->endPage();
