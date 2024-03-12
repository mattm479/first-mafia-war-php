<?php

require_once "globals.php";
global $headers, $user;

print '
    <h3>Donation &amp; Gift Packages</h3>
    <p>Your donations pay our infrastructure costs and staffing. We also donate 10% to a charity each month. In October that Charity will be in support of the Marine Toys for Tots Foundation, Champaign, IL. For your help, we provide a variety of benefits. Thank you for considering it.</p>
    <table width=95% cellpadding=0 cellspacing=0 class=table>
        <tr>
            <td valign=top>
';

if ($user['userid'] == 611 || $user['userid'] == 1) {
    print '
        <div class=floatright style=\'margin-right:1em;\'>
            <form action=\'https://www.paypal.com/cgi-bin/webscr\' method=POST>
                <input type=hidden name=cmd value=\'_xclick\'>
                <input type=hidden name=business value=\'paypal@kefern.com\'>
                <input type=hidden name=item_name value=\'www.firstmafiawar.com|DP|399|' . $user['userid'] . '\'>
                <input type=hidden name=amount value=\'1000.00\'>
                <input type=hidden name=return value=\'http://www.firstmafiawar.com/donatorDone.php?action=done\'>
                <input type=hidden name=cancel_return value=\'http://www.firstmafiawar.com/donatorDone.php?action=cancel\'>
                <input type=hidden name=notify_url value=\'http://www.firstmafiawar.com/donatorIPN.php\'>
                <input type=hidden name=currency_code value=\'USD\'>
                <input type=hidden name=tax value=0>
                <input type=image style=\'margin-bottom:2px\' src=\'assets/images/layout/donsp.jpg\' name=submit>
            </form>
            &nbsp; $1,000<br>
        </div>
    ';
}

print '
    You have donated ' . moneyFormatter($user['donatedM']) . ' so far this month. You will<br>earn additional rewards as you pass each milestone.<br>
    <ul>
        <li>$25 &nbsp;&nbsp; ' . iteminfo(310) . '</li>
        <li>$50 &nbsp;&nbsp; ' . iteminfo(318) . '</li>
        <li>$75 &nbsp;&nbsp; ' . iteminfo(338) . '</li>
        <li>$100 &nbsp;' . iteminfo(366) . '</li>
        <li>$150 &nbsp;' . iteminfo(346) . '</li>
        <li>$200 &nbsp;' . iteminfo(326) . '</li>
        <li>$250 &nbsp;' . iteminfo(311) . '</li>
        <li>$300 &nbsp;' . iteminfo(319) . '</li>
        <li>$350 &nbsp;' . iteminfo(339) . '</li>
        <li>...and more!</li>
    </ul>
    <hr style=\'margin-top:1.2em; margin-bottom:1.2em;\'>
    <div class=floatright style=\'margin-right:1em;\'>
        <form action=\'https://www.paypal.com/cgi-bin/webscr\' method=POST>
            <input type=hidden name=cmd value=\'_xclick\'>
            <input type=hidden name=business value=\'paypal@kefern.com\'>
            <input type=hidden name=item_name value=\'www.firstmafiawar.com|DP|301|' . $user['userid'] . ' . itemInfo(636) . '</li>
        <li>' . itemInfo(632) . '</li>
        <li>' . itemInfo(627) . '</li>
        <li>' . itemInfo(626) . '</li>
    </ul>
';

print "
    <hr style='margin-top:1.2em; margin-bottom:1.2em;'>
    <div class='floatright' style='margin-right:1em;'>
        <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
            <input type='hidden' name='cmd' value='_xclick'>
            <input type='hidden' name='business' value='paypal@kefern.com'>
            <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|302|{$user['userid']}'>
            <input type='hidden' name='amount' value='4.00'>
            <input type='hidden' name='no_shipping' value='1'>
            <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
            <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
            <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
            <input type='hidden' name='cn' value='Your Player ID'>
            <input type='hidden' name='currency_code' value='USD'>
            <input type='hidden' name='tax' value='0'>
            <input type='image' style='margin-bottom:2px' src='assets/images/layout/don4x1.jpg' alt='$4 donation' name='submit'>
        </form>
    </div>
    <strong>Regular Member</strong> (DP 2)<br>
    <ul>
        <li>\$20,000 game cash</li>
        <li>20 Tokens of Respect</li>
        <li>60 IQ</li>
        <li>30 days Donator Status <a href='wiki/doku.php?id=concepts:mafioso_abilities'>(more)</a></li>
    </ul>
    <hr style='margin-top:1.2em; margin-bottom:1.2em;'>
    <div class='floatright' style='margin-right:1em;'>
        <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
            <input type='hidden' name='cmd' value='_xclick'>
            <input type='hidden' name='business' value='paypal@kefern.com'>
            <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|303|{$user['userid']}'>
            <input type='hidden' name='amount' value='7.00'>
            <input type='hidden' name='no_shipping' value='1'>
            <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
            <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
            <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
            <input type='hidden' name='cn' value='Your Player ID'>
            <input type='hidden' name='currency_code' value='USD'>
            <input type='hidden' name='tax' value='0'>
            <input type='image' style='margin-bottom:2px' src='assets/images/layout/don7x1.jpg' alt='$7 donation' name='submit'>
        </form>
    </div>
    <strong>Supporting Member</strong> (DP 3)<br>
    <ul>
        <li>\$40,000</li>
        <li>40 Tokens of Respect</li>
        <li>120 IQ</li>
        <li>60 days Donator Status <a href='wiki/doku.php?id=concepts:mafioso_abilities'>(more)</a></li>
    </ul>
    <hr style='margin-top:1.2em; margin-bottom:1.2em;'>
    <div class='floatright' style='margin-right:1em;'>
        <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
            <input type='hidden' name='cmd' value='_xclick'>
            <input type='hidden' name='business' value='paypal@kefern.com'>
            <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|304|{$user['userid']}'>
            <input type='hidden' name='amount' value='9.00'>
            <input type='hidden' name='no_shipping' value='1'>
            <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
            <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
            <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
            <input type='hidden' name='cn' value='Your Player ID'>
            <input type='hidden' name='currency_code' value='USD'>
            <input type='hidden' name='tax' value='0'>
            <input type='image' style='margin-bottom:2px' src='assets/images/layout/don9x1.jpg' alt='$9 donation' name='submit'>
        </form>
    </div>
    <strong>Preferred Member</strong> (DP 4)<br>
    <ul>
        <li>\$60,000</li>
        <li>60 Tokens of Respect</li>
        <li>240 IQ</li>
        <li>90 days Donator Status <a href='wiki/doku.php?id=concepts:mafioso_abilities'>(more)</a></li>
    </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|305|{$user['userid']}'>
   <input type='hidden' name='amount' value='3.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px' src='assets/images/layout/don3x1.jpg' alt='$3 donation' name='submit'>
   </form>
  </div>
  <strong>Flavor of the Month</strong> (DP 5)<br>
  <ul>
   <li>Get " . itemInfo(625) . " for 30 days</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|313|{$user['userid']}'>
   <input type='hidden' name='amount' value='28.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=gym'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don28x10.jpg' alt='$28 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|371|{$user['userid']}'>
   <input type='hidden' name='amount' value='60.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=gym'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don60x25.jpg' alt='$60 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|372|{$user['userid']}'>
   <input type='hidden' name='amount' value='100.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=gym'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don100x50.jpg' alt='$100 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|310|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|311|{$user['userid']}'>
   <input type='hidden' name='amount' value='11.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don11x3.jpg' alt='$11 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|312|{$user['userid']}'>
   <input type='hidden' name='amount' value='16.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=gym'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don16x5.jpg' alt='$16 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>The Gym is for wimps</strong> (DP 6)<br>
  <ul>
   <li>21,000 Strength gain</li>
   <li>21,000 Agility gain</li>
   <li>21,000 Guard gain</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|317|{$user['userid']}'>
   <input type='hidden' name='amount' value='28.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don28x10.jpg' alt='$28 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|373|{$user['userid']}'>
   <input type='hidden' name='amount' value='60.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=crimepays'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don60x25.jpg' alt='$60 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|374|{$user['userid']}'>
   <input type='hidden' name='amount' value='100.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don100x50.jpg' alt='$100 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|314|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|315|{$user['userid']}'>
   <input type='hidden' name='amount' value='11.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=crimepays'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don11x3.jpg' alt='$11 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|316|{$user['userid']}'>
   <input type='hidden' name='amount' value='16.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don16x5.jpg' alt='$16 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Crime Pays</strong> (DP 7)<br>
  <ul>
   <li>A quick \$70,000,000</li>
   <li>" . itemInfo(10) . "</li>
   <li>" . itemInfo(637) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|321|{$user['userid']}'>
   <input type='hidden' name='amount' value='42.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don42x10.jpg' alt='$42 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|375|{$user['userid']}'>
   <input type='hidden' name='amount' value='90.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don90x25.jpg' alt='$90 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|376|{$user['userid']}'>
   <input type='hidden' name='amount' value='150.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don150x50.jpg' alt='$150 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|318|{$user['userid']}'>
   <input type='hidden' name='amount' value='6.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don6x1.jpg' alt='$6 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|319|{$user['userid']}'>
   <input type='hidden' name='amount' value='16.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don16x3.jpg' alt='$16 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|320|{$user['userid']}'>
   <input type='hidden' name='amount' value='24.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don24x5.jpg' alt='$24 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Respect <em>can</em> be Bought</strong> (DP 8)<br>
  <ul>
   <li>101 Tokens of Respect</li>
   <li>" . itemInfo(63) . "<br><br></li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|325|{$user['userid']}'>
   <input type='hidden' name='amount' value='35.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don35x10.jpg' alt='$35 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|377|{$user['userid']}'>
   <input type='hidden' name='amount' value='75.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don75x25.jpg' alt='$75 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|378|{$user['userid']}'>
   <input type='hidden' name='amount' value='125.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don125x50.jpg' alt='$125 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|322|{$user['userid']}'>
   <input type='hidden' name='amount' value='5.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don5x1.jpg' alt='$5 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|323|{$user['userid']}'>
   <input type='hidden' name='amount' value='13.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don13x3.jpg' alt='$13 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|324|{$user['userid']}'>
   <input type='hidden' name='amount' value='20.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don20x5.jpg' alt='$20 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Knowledge is Power</strong> (DP 9)<br>
  <ul>
   <li>303 IQ - Mafia Intelligence</li>
   <li>2x " . itemInfo(28) . "<br><br></li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|326|{$user['userid']}'>
   <input type='hidden' name='amount' value='17.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done&type=improve'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don17x1.jpg' alt='$17 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Self Improvement</strong> (DP 10)<br>
  <ul>
   <li>Get " . itemInfo(305) . "</li>
   <li>Get " . itemInfo(310) . "</li>
   <li>Get " . itemInfo(314) . "</li>
   <li>Get " . itemInfo(318) . "</li>
   <li>Get " . itemInfo(322) . "</li>
  </ul>
   </td>

   <td>&nbsp;</td>

   <td valign='top'>
  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|333|{$user['userid']}'>
   <input type='hidden' name='amount' value='28.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don28x10.jpg' alt='$28 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|379|{$user['userid']}'>
   <input type='hidden' name='amount' value='60.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don60x25.jpg' alt='$60 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|380|{$user['userid']}'>
   <input type='hidden' name='amount' value='100.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don100x50.jpg' alt='$100 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|330|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|331|{$user['userid']}'>
   <input type='hidden' name='amount' value='11.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don11x3.jpg' alt='$11 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|332|{$user['userid']}'>
   <input type='hidden' name='amount' value='16.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don16x5.jpg' alt='$16 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Pack of Beer</strong> (DP 11)<br>
  <ul>
   <li>4x " . itemInfo(9) . "</li>
   <li>4x " . itemInfo(16) . "</li>
   <li>4x " . itemInfo(18) . "</li>
   <li>2x " . itemInfo(65) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|336|{$user['userid']}'>
   <input type='hidden' name='amount' value='28.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don28x10.jpg' alt='$28 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|381|{$user['userid']}'>
   <input type='hidden' name='amount' value='60.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don60x25.jpg' alt='$60 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|382|{$user['userid']}'>
   <input type='hidden' name='amount' value='100.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don100x50.jpg' alt='$100 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|334|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|335|{$user['userid']}'>
   <input type='hidden' name='amount' value='11.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don11x3.jpg' alt='$11 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|336|{$user['userid']}'>
   <input type='hidden' name='amount' value='16.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don16x5.jpg' alt='$16 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Coffee Roastery</strong> (DP 12)<br>
  <ul>
   <li>20 " . itemInfo(68) . "</li>
   <li>10 " . itemInfo(56) . "</li>
   <li>4 " . itemInfo(57) . "</li>
   <li>2 " . itemInfo(64) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|341|{$user['userid']}'>
   <input type='hidden' name='amount' value='42.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don42x10.jpg' alt='$42 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|383|{$user['userid']}'>
   <input type='hidden' name='amount' value='90.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don90x25.jpg' alt='$90 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|384|{$user['userid']}'>
   <input type='hidden' name='amount' value='150.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don150x50.jpg' alt='$150 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|338|{$user['userid']}'>
   <input type='hidden' name='amount' value='6.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don6x1.jpg' alt='$6 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|339|{$user['userid']}'>
   <input type='hidden' name='amount' value='16.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don16x3.jpg' alt='$16 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|340|{$user['userid']}'>
   <input type='hidden' name='amount' value='24.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don24x5.jpg' alt='$24 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Basket of Wine</strong> (DP 13)<br>
  <ul>
   <li>8 " . itemInfo(63) . "</li>
   <li>4 " . itemInfo(14) . "</li>
   <li>2 " . itemInfo(55) . "</li>
   <li>1 " . itemInfo(627) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div style='float:right;width:150;margin-right:1em;'>
  <div style='float:right;width:75;position:relative;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|345|{$user['userid']}'>
   <input type='hidden' name='amount' value='35.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don35x10.jpg' alt='$35 donation for 10' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|385|{$user['userid']}'>
   <input type='hidden' name='amount' value='75.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don75x25.jpg' alt='$75 donation for 25' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|386|{$user['userid']}'>
   <input type='hidden' name='amount' value='125.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don125x50.jpg' alt='$125 donation for 50' name='submit'>
   </form>
  </div>
  <div style='float:left;width:75;position:relative;margin-right:2px;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|342|{$user['userid']}'>
   <input type='hidden' name='amount' value='5.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don5x1.jpg' alt='$5 donation for 1' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|343|{$user['userid']}'>
   <input type='hidden' name='amount' value='13.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don13x3.jpg' alt='$13 donation for 3' name='submit'>
   </form>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type='hidden' name='cmd' value='_xclick'>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|344|{$user['userid']}'>
   <input type='hidden' name='amount' value='20.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don20x5.jpg' alt='$20 donation for 5' name='submit'>
   </form>
  </div>
  </div>
  <strong>Case of Whiskey</strong> (DP 14)<br>
  <ul>
   <li>8 " . itemInfo(70) . "</li>
   <li>5 " . itemInfo(17) . "</li>
   <li>3 " . itemInfo(62) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|346|{$user['userid']}'>
   <input type='hidden' name='amount' value='15.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don15x1.jpg' alt='$15 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Family Party</strong> (DP 15)<br>
  <ul>
   <li>" . itemInfo(330) . "</li>
   <li>" . itemInfo(334) . "</li>
   <li>" . itemInfo(338) . "</li>
   <li>" . itemInfo(342) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|350|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Hospital Fundraiser</strong> (DP 16)<br>
  <ul>
   <li>8 " . itemInfo(12) . "</li>
   <li>4 " . itemInfo(13) . "</li>
   <li>2 " . itemInfo(67) . "</li>
   <li>" . itemInfo(24) . "</li>
   <li>" . itemInfo(71) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|354|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Police Conference</strong> (DP 17)<br>
  <ul>
   <li>8 " . itemInfo(27) . "</li>
   <li>4 " . itemInfo(26) . "</li>
   <li>2 " . itemInfo(66) . "</li>
   <li>" . itemInfo(25) . "</li>
   <li>" . itemInfo(54) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|358|{$user['userid']}'>
   <input type='hidden' name='amount' value='5.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don5x1.jpg' alt='$5 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Criminal Conference</strong> (DP 18)<br>
  <ul>
   <li>6 " . itemInfo(51) . "</li>
   <li>6 " . itemInfo(52) . "</li>
   <li>4 " . itemInfo(23) . "</li>
   <li>" . itemInfo(636) . "</li>
   <li>" . itemInfo(626) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|366|{$user['userid']}'>
   <input type='hidden' name='amount' value='10.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don10x1.jpg' alt='$10 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Convention Center</strong> (DP 19)<br>
  <ul>
   <li>" . itemInfo(350) . "</li>
   <li>" . itemInfo(354) . "</li>
   <li>" . itemInfo(358) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|370|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Exotic Weaponry</strong> (DP 20)<br>
  <ul>
   <li>5,000 each to Agility, Guard, &amp; Strength</li>
   <li>" . itemInfo(87) . "</li>
   <li>2x " . itemInfo(46) . "</li>
  </ul>

  <hr style='margin-top:1.2em; margin-bottom:1.2em;'>

  <div class='floatright' style='margin-right:1em;'>
   <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
   <input type=hidden name=cmd value=_xclick>
   <input type='hidden' name='business' value='paypal@kefern.com'>
   <input type='hidden' name='item_name' value='www.firstmafiawar.com|DP|306|{$user['userid']}'>
   <input type='hidden' name='amount' value='4.00'>
   <input type='hidden' name='no_shipping' value='1'>
   <input type='hidden' name='return' value='http://www.firstmafiawar.com/donatorDone.php?action=done'>
   <input type='hidden' name='cancel_return' value='http://www.firstmafiawar.com/donatorDone.php?action=cancel'>
   <input type='hidden' name='notify_url' value='http://www.firstmafiawar.com/donatorIPN.php'>
   <input type='hidden' name='cn' value='Your Player ID'>
   <input type='hidden' name='currency_code' value='USD'>
   <input type='hidden' name='tax' value='0'>
   <input type='image' style='margin-bottom:2px;' src='assets/images/layout/don4x1.jpg' alt='$4 donation for 1' name='submit'>
   </form>
  </div>
  <strong>Italian Bakery</strong> (DP 21)<br>
  <ul>
   <li>4 " . itemInfo(113) . "</li>
   <li>4 " . itemInfo(114) . "</li>
   <li>4 " . itemInfo(115) . "</li>
   <li>2 " . itemInfo(116) . "</li>
   <li>2 " . itemInfo(117) . "</li>
   <li>2 " . itemInfo(118) . "</li>
   <li>" . itemInfo(119) . "</li>
  </ul>

   </td>
  </tr>
  </table>
  <br>
";

$headers->endPage();
