<?php

namespace Fmw;

use JetBrains\PhpStorm\NoReturn;

class Header
{
    /**
     * @var Database $_db
     */
    private readonly Database $_db;

    /**
     * @var array $_user
     */
    private readonly array $_user;

    /**
     * @var array $_settings
     */
    private readonly array $_settings;

    /**
     * @param Database $db
     * @param array $user
     * @param array $settings
     */
    public function __construct(Database $db, array $user, array $settings)
    {
        $this->_db = $db;
        $this->_user = $user;
        $this->_settings = $settings;
    }

    public function getHeaderData(): array
    {
        if ($this->_user['hp'] <= 0 && $this->_user['hospital'] == 0 && $this->_user['jail'] == 0 && $this->_user['attacking'] == 0) {
            $this->_db->query("UPDATE users SET hospital = 30, hjReason = 'You are too sick to move.' WHERE userid = {$this->_user['userid']}");
        }

        $experc = (int)($this->_user['exp'] / $this->_user['exp_needed'] * 100);
        $enperc = (int)($this->_user['energy'] / $this->_user['maxenergy'] * 100);
        $enopp = 100 - $enperc;
        $wiperc = (int)($this->_user['will'] / $this->_user['maxwill'] * 100);
        $wiopp = 100 - $wiperc;
        $brperc = (int)($this->_user['brave'] / $this->_user['maxbrave'] * 100);
        $bropp = 100 - $brperc;
        $hpperc = (int)($this->_user['hp'] / $this->_user['maxhp'] * 100);
        $hpopp = 100 - $hpperc;
        $mafioso = mafioso($this->_user['userid']);
        $cash = moneyFormatter($this->_user['money']);
        $accounts = moneyFormatter($this->_user['moneyChecking'] + $this->_user['moneySavings'] + $this->_user['moneyInvest'] + $this->_user['moneyTreasury']);
        $respect = number_format($this->_user['respect']);
        $seekPrice = number_format(550 * $this->_user['hideSearches']);

        return [
            'user' => $this->_user,
            'mafioso' => $mafioso,
            'cash' => $cash,
            'accounts' => $accounts,
            'respect' => $respect,
            'experc' => $experc,
            'enperc' => $enperc,
            'enopp' => $enopp,
            'wiperc' => $wiperc,
            'wiopp' => $wiopp,
            'brperc' => $brperc,
            'bropp' => $bropp,
            'hpperc' => $hpperc,
            'hpopp' => $hpopp,
            'script_name' => $_SERVER['SCRIPT_NAME'],
            'seek_price' => $seekPrice
        ];
    }

    public function getSidebarData(): array
    {
        $pv = ($this->_user['pollVote']) ? 'nobold' : 'ysbold';

        $bv = 'nobold';
        $bc = '';
        if ($this->_user['newAttacks'] > 0) {
            $bv = 'ysbold';
            $bc = ($this->_user['newAttacks'] > 9) ? '&#8734;' : '(' . $this->_user['newAttacks'] . ')';
        }

        $ev = 'nobold';
        $ec = '';
        if ($this->_user['newEvents'] > 0) {
            $ev = 'ysbold';
            $ec = ($this->_user['newEvents'] > 9) ? '&#8734;' : '(' . $this->_user['newEvents'] . ')';
        }

        $fv = 'nobold';
        $fc = '';
        if ($this->_user['newForum'] > 0) {
            $fv = 'ysbold';
            $fc = ($this->_user['newForum'] > 9) ? '&#8734;' : '(' . $this->_user['newForum'] . ')';
        }

        $mv = 'nobold';
        $mc = '';
        if ($this->_user['newMail'] > 0) {
            $mv = 'ysbold';
            $mc = ($this->_user['newMail'] > 9) ? '&#8734;' : '(' . $this->_user['newMail'] . ')';
        }

        $nv = 'nobold';
        $nc = '';
        if ($this->_user['newNews'] > 0) {
            $nv = 'ysbold';
            $nc = ($this->_user['newNews'] > 20) ? '&#8734;' : '(' . $this->_user['newNews'] . ')';
        }

        $av = ($this->_user['newAnnounce'] > 0) ? 'ysbold' : 'nobold';

        $thirtyDaysAgo = time() - (30 * 24 * 60 * 60);
        $allTimeDonators = [];
        /*$result = $this->_db->query("SELECT ldBuyer,sum(ldValue) AS sumValue FROM logsDonations LEFT JOIN users u ON ldBuyer=u.userid WHERE donateMshow='yes' GROUP BY ldBuyer ORDER BY sumValue DESC LIMIT 3");
        while ($row = mysqli_fetch_assoc($result)) {
            $allTimeDonators[] = mafiosoLight($row['ldBuyer']);
        }*/

        $donatorsLastThirtyDays = [];
        /*$result = $this->_db->query("SELECT ldBuyer,sum(ldValue) AS sumValue FROM logsDonations LEFT JOIN users u ON ldBuyer=u.userid WHERE ldTime>$thirtyDaysAgo AND donateMshow='yes' GROUP BY ldBuyer ORDER BY sumValue DESC LIMIT 3");
        while ($row = mysqli_fetch_assoc($result)) {
            $donatorsLastThirtyDays[] = mafiosoLight($row['ldBuyer']);
        }*/

        $donatorsThisMonth = [];
        /*$result = $this->_db->query("SELECT userid FROM users WHERE donatedM>0 AND donateMshow='yes' ORDER BY donatedM DESC LIMIT 3");
        while ($row = mysqli_fetch_assoc($result)) {
            $donatorsThisMonth[] = mafiosoLight($row['userid']);
        }*/

        if ($this->_user['attacking']) {
            $this->_db->query("UPDATE users SET respect = respect - 5, attacking = 0, hospital = 60, hjreason = 'Ran away from a fight.' WHERE userid = {$this->_user['userid']}");
            $_SESSION['attacking'] = 0;
        }

        return [
            'user' => $this->_user,
            'settings' => $this->_settings,
            'user_location' => locationname($this->_user['location']),
            'time' => date('g:i a'),
            'pv' => $pv,
            'bv' => $bv,
            'bc' => $bc,
            'ev' => $ev,
            'ec' => $ec,
            'fv' => $fv,
            'fc' => $fc,
            'mv' => $mv,
            'mc' => $mc,
            'nv' => $nv,
            'nc' => $nc,
            'av' => $av,
            'all_time_donators' => $allTimeDonators,
            'donators_last_thirty_days' => $donatorsLastThirtyDays,
            'donators_this_month' => $donatorsThisMonth
        ];
    }

    public function endPage(): void
    {
    }

    public function staffMenuArea(): void
    {
        print '
          <h6>General</h6>
          <a href=\'staff.php\'>Home</a><br>
          <a target=top href=\'http://www.firstmafiawar.com/wiki/doku.php?id=staff:index\'>Staff Wiki</a><br>
          <hr>
          <h6>Observation</h6>
          <a href=\'staffLogs.php?action=attlog\'>Attacks</a><br>
          <a href=\'staffLogs.php?action=eventlogs\'>Events</a><br>
          <a href=\'staffUsers.php?action=ipsrchform&gtx=lastip\'>IP Check</a><br>
          <a href=\'staffLogs.php?action=itmlogs\'>Items</a><br>
          <a href=\'staffLogs.php?action=maillogs\'>Mail</a><br>
          <a href=\'staffLogs.php?action=referrals\'>Referrals</a><br>
          <a href=\'staffItems.php\'>View Items</a><br>
          <a href=\'staffUsers.php?action=watchfuleye\'>Watchful Eye</a><br>
          <a href=\'staffLogs.php?action=wealthlogs\'>Wealth</a><br>
          <hr>
          <h6>Game Controls</h6>
          <a href=\'staff.php?action=mafiainquirer\'>Mafia Inquirer</a><br>
          <a href=\'staff.php?action=streetfight\'>Street Fight</a><br>
       ';

        if ($this->_user['rankCat'] == 'Staff' && $this->_user['rank'] != 'Sgarrista') {
            print '
              <a href=\'staffPunish.php?action=gagform\'>Set Gag Order</a><br>
              <a href=\'staffUsers.php?action=indgivform\'>Individual Giving</a><br>
          ';
        }

        if ($this->_user['rank'] == 'Capo') {
            print '
            <a href=\'staffUsers.php?action=grpgivform\'>Group Giving</a><br>
            <a href=\'staff.php?action=announce\'>Announcement</a><br>
            <a href=\'staffUsers.php?action=edituser\'>Edit Mafioso</a><br>
            <a href=\'staff.php?action=massmailer\'>Mass Mailer</a><br>
            <a href=\'staff.php?action=poll\'>Polling</a><br>
            <hr>
            <h6>Lordly Might</h6>
            <a href=\'staffLogs.php?action=donlog\'>Donation Logs</a><br>
            <a href=\'staff.php?action=basicset\'>Settings</a><br>
            <a href=\'staffItems.php?action=newitem\'>Create Item</a><br>
          ';
        }

        print '
             <hr>
             <br> ' . date('F j, Y') . '<br>' . date('g:i:s a') . '<br><br>
          </div>
          <div class=content>
              <div class=floatright><a href=\'home.php\'>- return to game -</a></div>
       ';
    }
}
