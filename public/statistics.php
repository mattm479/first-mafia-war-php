<?php
require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0);

print '
    <h3>Bureau of Statistics</h3>
    <p>Numbers are like people; torture them enough and they\'ll tell you anything.</p>
';

// Members
$members = mysqli_num_rows($application->db->query("SELECT userid FROM users WHERE rankCat = 'Player'"));
$membdonat = mysqli_num_rows($application->db->query("SELECT userid FROM users WHERE rankCat = 'Player' AND donatordays > 0"));
$membrefer = mysqli_num_rows($application->db->query("SELECT refID FROM referals"));
$membfamil = mysqli_num_rows($application->db->query("SELECT userid FROM users WHERE rankCat = 'Player' AND gang > 0"));
$male = mysqli_num_rows($application->db->query("SELECT userid FROM users WHERE rankCat = 'Player' AND gender = 'Male'"));
$fem = mysqli_num_rows($application->db->query("SELECT userid FROM users WHERE rankCat = 'Player' AND gender = 'Female'"));
$fam = mysqli_num_rows($application->db->query("SELECT famID FROM family WHERE famID != 1 AND famRespect > 0"));

print '
    <h5>Users</h5>
    <p>
        &nbsp;&middot;&nbsp; ' . $members . ' active players (no Staff, Giovani or inactive players)<br>
        &nbsp;&middot;&nbsp; ' . $male . ' males and ' . $fem . ' females<br>
        &nbsp;&middot;&nbsp; ' . $membfamil . ' currently in Families<br>
        &nbsp;&middot;&nbsp; ' . $membdonat . ' are current donators<br>
        &nbsp;&middot;&nbsp; There have been ' . $membrefer . ' total referrals<br>
    </p>
';

// Wealth
$rres = mysqli_fetch_assoc($application->db->query("SELECT sum(respect) AS sumRespect FROM users WHERE rankCat = 'Player'"));
$avgres = round($rres['sumRespect'] / $members);
$rfam = mysqli_fetch_assoc($application->db->query("SELECT sum(famVaultCash) AS sumFamily FROM family WHERE famID != 1 AND famRespect > 0"));
$avgfam = round($rfam['sumFamily'] / $fam);
$rmon = mysqli_fetch_assoc($application->db->query("SELECT sum(money) AS sumMoney FROM users WHERE rankCat = 'Player'"));
$avgmon = round($rmon['sumMoney'] / $members);
$rchk = mysqli_fetch_assoc($application->db->query("SELECT sum(moneyChecking) AS sumChecking FROM users WHERE rankCat = 'Player'"));
$avgchk = round($rchk['sumChecking'] / $members);
$rsav = mysqli_fetch_assoc($application->db->query("SELECT sum(moneySavings) AS sumSavings FROM users WHERE rankCat = 'Player'"));
$avgsav = round($rsav['sumSavings'] / $members);
$rinv = mysqli_fetch_assoc($application->db->query("SELECT sum(moneyInvest) AS sumInvest FROM users WHERE rankCat = 'Player'"));
$avginv = round($rinv['sumInvest'] / $members);
$rtre = mysqli_fetch_assoc($application->db->query("SELECT sum(moneyTreasury) AS sumTreasury FROM users WHERE rankCat = 'Player'"));
$avgtre = round($rtre['sumTreasury'] / $membdonat);

$sumWealth = $rmon['sumMoney'] + $rchk['sumChecking'] + $rsav['sumSavings'] + $rinv['sumInvest'] + $rtre['sumTreasury'];
$avgwel = round($sumWealth / $members);

print '
    <h5>Wealth</h5>
    <p>
        &nbsp;&middot;&nbsp; ' . number_format($rres['sumRespect']) . ' total Tokens of Respect with an average of ' . number_format($avgres) . ' per player<br>
        &nbsp;&middot;&nbsp; $' . number_format($rfam['sumFamily']) . ' in Family Vaults with an average of $' . number_format($avgfam) . ' per Family<br><br>
        &nbsp;&middot;&nbsp; $' . number_format($sumWealth) . ' in individual Wealth with an average of $' . number_format($avgwel) . ' per player<br>
        &nbsp;&middot;&nbsp; $' . number_format($rmon['sumMoney']) . ' in Cash with an average of $' . number_format($avgmon) . ' per player<br>
        &nbsp;&middot;&nbsp; $' . number_format($rchk['sumChecking']) . ' in Checking with an average of $' . number_format($avgchk) . ' per player<br>
        &nbsp;&middot;&nbsp; $' . number_format($rsav['sumSavings']) . ' in Savings with an average of $' . number_format($avgsav) . ' per player<br>
        &nbsp;&middot;&nbsp; $' . number_format($rinv['sumInvest']) . ' in Investments with an average of $' . number_format($avginv) . ' per player<br>
        &nbsp;&middot;&nbsp; $' . number_format($rtre['sumTreasury']) . ' in T-Bills with an average of $' . number_format($avgtre) . ' per donator
    </p>
';

// Items 
$ritm = mysqli_fetch_assoc($application->db->query("SELECT sum(i.inv_qty) AS sumItems FROM inventory i LEFT JOIN items it ON i.inv_itemid = it.itmid"));
$rwep = mysqli_fetch_assoc($application->db->query("SELECT sum(i.inv_qty) AS sumItems FROM inventory i LEFT JOIN items it ON i.inv_itemid = it.itmid WHERE it.itmtype IN (65, 70, 80)"));
$rger = mysqli_fetch_assoc($application->db->query("SELECT sum(i.inv_qty) AS sumItems FROM inventory i LEFT JOIN items it ON i.inv_itemid = it.itmid WHERE it.itmtype IN (20, 30)"));
$rnor = mysqli_fetch_assoc($application->db->query("SELECT sum(i.inv_qty) AS sumItems FROM inventory i LEFT JOIN items it ON i.inv_itemid = it.itmid WHERE it.itmtype = 40"));
$rcon = mysqli_fetch_assoc($application->db->query("SELECT sum(i.inv_qty) AS sumItems FROM inventory i LEFT JOIN items it ON i.inv_itemid = it.itmid WHERE it.itmtype = 10"));
$rmat = mysqli_fetch_assoc($application->db->query("SELECT sum(inv_qty) AS sumItems FROM inventory WHERE inv_itemid = 5"));

print '
    <h5>Items</h5>
    <p>
        &nbsp;&middot;&nbsp; ' . number_format($ritm['sumItems']) . ' total items in circulation<br>
        &nbsp;&middot;&nbsp; ' . number_format($rwep['sumItems']) . ' unequipped weapons<br>
        &nbsp;&middot;&nbsp; ' . number_format($rcon['sumItems']) . ' contacts ready to serve<br>
        &nbsp;&middot;&nbsp; ' . number_format($rger['sumItems']) . ' bits of gear (' . number_format($rmat['sumItems']) . ' Mattresses)<br>
        &nbsp;&middot;&nbsp; ' . number_format($rnor['sumItems']) . ' tasty drinks and food<br>
    </p>
';

// Communication
$totala = mysqli_num_rows($application->db->query("SELECT laID FROM logsAttacks"));
$totalf = mysqli_num_rows($application->db->query("SELECT fpID FROM forumPosts"));
$totale = mysqli_num_rows($application->db->query("SELECT leID FROM logsEvents"));
$totalm = mysqli_num_rows($application->db->query("SELECT mail_id FROM mail"));
$totaln = mysqli_num_rows($application->db->query("SELECT newsID FROM news"));

print '
    <h5>Communication</h5>
    <p>
        &nbsp;&middot;&nbsp; ' . number_format($totalf) . ' active forum posts<br>
        &nbsp;&middot;&nbsp; ' . number_format($totaln) . ' todays news posts<br>
        &nbsp;&middot;&nbsp; ' . number_format($totalm) . ' recent and archived mails<br>
        &nbsp;&middot;&nbsp; ' . number_format($totala) . ' attacks in the last month<br>
        &nbsp;&middot;&nbsp; ' . number_format($totale) . ' events in the last month<br>
    </p>
';

$application->header->endPage();
