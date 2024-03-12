<?php

use Fmw\Database;

require_once "globals.php";
global $db, $headers, $user;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';

switch ($action) {
    case "help":
        bug_report();
        break;
    case "staff":
        getStats();
        break;
    case "proxy":
        proxy();
        break;
    case "test":
        test($db, $user);
        break;
    case "rules":
    default:
        help_rules();
        break;
}

function test(Database $db, array $user): void
{
    $tai = unserialize($user['trackActionInfo']);
    $myip = $tai['remoteaddr'] ?? 'unknown';
    $prox = $tai['httpvia'] ?? 'unknown';
    $forw = $tai['httpforward'] ?? 'unknown';
    $brow = $tai['useragent'] ?? 'unknown';
    $clnt = $tai['clientip'] ?? 'unknown';

    $ser = addslashes(serialize(array("remoteaddr" => $_SERVER['REMOTE_ADDR'], "httpvia" => $_SERVER['HTTP_VIA'], "httpforward" => $_SERVER['HTTP_X_FORWARDED_FOR'], "useragent" => $_SERVER['HTTP_USER_AGENT'], "clientip" => $_SERVER['HTTP_CLIENT_IP'])));
    $db->query("UPDATE users SET trackActionInfo = '{$ser}' WHERE userid = 1");

    print '
        <h3>What did we learn?</h3>
        My IP Address: ' . $myip . '<br>
        My Proxy IP: ' . $prox . '<br>
        My Forward IP: ' . $forw . '<br>
        My Browser: ' . $brow . '<br>
        My Client IP: ' . $clnt . '<br><br>
        ...<br><br>
        In the \'do I care\' file:
        Client Info
        ' . $_SERVER['HTTP_CLIENT_IP'] . '<br>
    ';
}


function getStats(): void
{
    /* Get Info about User */
    $stats['proxy'] = '';
    $stats['info'] = $_SERVER['HTTP_USER_AGENT'];
    $stats['page'] = $_SERVER['REQUEST_URI'];
    $stats['method'] = $_SERVER['REQUEST_METHOD'];

    /* Check Server Name */
    $stats['server'] = $_SERVER['SERVER_NAME'];
    if (!isset($_SERVER['SERVER_NAME'])) {
        $stats['server'] = 'localhost';
        if (isset($_SERVER['HOSTNAME'])) {
            $stats['server'] = $_SERVER['HOSTNAME'];
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $stats['server'] = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $stats['server'] = $_SERVER['SERVER_ADDR'];
        }
    }

    /* Check http Proxy and IP Address */
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $stats['proxy'] = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $stats['proxy'] = $_SERVER['REMOTE_ADDR'];
        }
        $stats['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $stats['ip'] = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $stats['ip'] = $_SERVER['REMOTE_ADDR'];
    }

    # Return the Array
    print 'Server: ' . $stats['server'] . '<br>Proxy: ' . $stats['proxy'] . '<br>Info: ' . $stats['info'] . '<br>Page: ' . $stats['page'] . '<br>Method: ' . $stats['method'] . '<br>Ip: ' . $stats['ip'] . '<br>';
}

function proxy(): void
{
    // use this script to detect whether a user is using a proxy server to connect to your website.
    if (isset($HTTP_X_FORWARDED_FOR)) {
        if ($HTTP_X_FORWARDED_FOR) {
            print '
                <b>Proxy Detected...</b><br>
                Your Actual IP Address:
                <i>' . $HTTP_X_FORWARDED_FOR . '</i><br>
                Your Proxy Server:
                <i>' . $_SERVER['HTTP_VIA'] . '</i>
                <br> You Proxy I.P address: ' . $_SERVER['REMOTE_ADDR'] . '<br>
            ';
        }
    }
}

function bug_report(): void
{
    print '
        <h3>Help! I don\'t know what I\'m doing!</h3>
        <p>If you have a question, the best place to start is the <a target=top href=\'wiki\'>Wiki</a>.  It\'s getting better all the time and should have what you need.  Start with the <a target=top href=\'wiki/doku.php?id=new_player:index\'>new player help</a> section for the best results. You should also check the <a href=\'forum.php\'>forums</a>.  There is a lot of current information there.</p>
        <p>If you are still confused, ask in the <a href=\'news.php\'>news</a> or pick your favorite Mafioso. Try not to ask Staff questions unless you think it may be a bug or you are not getting help elsewhere. However, do not wait until you are frustrated - we do not mind a few questions!</p>
        <h3>Help! I found a...</h3>
        <p>First take a deep breath and wait a minute or two. Did it go away? Good. Chances are staff are working on something and had a little trouble. Sorry about that. If you lost something please contact staff and we might replace it.</p>
        <p>Still there? OK. If you need help because you have found a bug or some other issue that only staff can assist with, please post your information in the <a href=\'forum.php?act=vforum&fid=3\'><strong>proper forum</strong></a>. Staffers do read through the forum a few times a day and when they encounter your problem they will advise you.</p>
        <p>If you feel your issue is of a private or critical nature (a REALLY bad bug or issue between players) please mail the <a href=\'mailbox.php?action=compose&ID=22\'><strong>staff mail account</strong></a>. Then be patient. Very few things are so critical they require an instant response, and while we are here to help, we are not here to serve.</p>
        <p>If it is REALLY, REALLY important and it simply cannot wait, please ask for a staffer in the <a href=\'newspaper.php?action=post\'><strong>News</strong></a>. Please do not post your difficulty, simply say "Is there a staffer online?" and someone will respond to you by mail.</p>
        <p>Finally if the site is completely broken, you would not be reading this page, but in case you have a really good memory, you can always email trouke directly at <a href=\'mailto:%68%65%6c%70%40%66%69%72%73%74%6d%61%66%69%61%77%61%72%2e%63%6f%6d\'>help at mafiaexperiment dot com</a>.  Yes, he reads his mail a few times a day, but he also sleeps and has a real job, so be patient.</p>
        <p>Thank you!</p>
    ';
}

function help_rules(): void
{
    print '
        <h3>Rules and Regulations</h3>
        <ol>
            <li><strong>Respect the game.</strong><p>We understand that you play other games and you should feel free to discuss them here. However, please do not advertise them or ask for referrals without clearing it with the staff and never ask people to quit this game. We worked very hard on this game, and with all its warts, we like it. Please honor the effort if not the result.</p></li>
            <li><strong>Respect the staff.</strong><p>Contrary to popular belief we are decent folks. We are unlikely to screw you over unless you really deserved it. If you feel one of us has wronged you feel free to talk to another, but after that let it be. If you have a problem that didn\'t start with us - talk to us and maybe we can help.</p></li>
            <li><strong>Respect the other players.</strong><p>It is assumed that the players in this game are adults - or at least pretending to be. We are not your parents and hope for a lively game with occasionally colorful language. However, harassment, excessive swearing, sexual vulgarity, racism, sexism, homophobia, or attacks on someone\'s religion will not be tolerated.</p></li>
            <li><strong>Do not scam other players or cheat the game.</strong><p>While this falls under respect, it bears repeating. Scamming will not be tolerated. If you made a deal, stick to it. If you find a bug, report it. Do not use automated software or scripts. You will be rewarded for your honesty and punished for your dishonesty.</p></li>
            <li><strong>You may have only <em>one</em> account.</strong><p>If you are on the same network (IP address) as another player, mail staff and let them know.</p></li>
            <li><strong>Give no one access to your account.</strong><p>You are responsible for anything your gangster does. If your account is hacked or you accidentally give away your password, let us know immediately.</p></li>
            <li><strong>Use your common sense.</strong><p>Yes it\'s not very common, but if you have a question, check the forum, if the answer is not there, ask a staffer. If you still cannot tell the difference between what is OK and what is not, take a break.</p></li>
        </ol>
        <p>These rules are subject to change without notice. You should check them from time to time, as ignorance will not be accepted as an excuse. Large changes are likely going to be announced or posted in the forum.</p>
        <h5>Penalties</h5>
        <p>Penalties vary with offence and are at the sole discretion of Staff. In some cases Staff will hand out a short-term penalty pending review by ' . mafioso(1) . '.  You may always appeal any penalty to Kef but his decisions are final though he has changed his mind (a fact he vehemently denies). The process will often follow this form...</p>
        <ul>
            <li><strong>Warning</strong><br>Unless it\'s really serious, staff will try and warn you that you are over the line or doing something incorrect.  Normally we\'ll assume innocence and ask you what\'s up. Staff may also give you a ' . itemInfo(86) . ' to remind you to be more careful.</li><br>
            <li><strong>Gag</strong><br>If it is an offense involving communication in some way (most are) staff will \'gag\' you.  This is not serious - it simply means you cannot read or post news, forums or mail. Generally it is for less than an hour, but sometimes it is for several.</li><br>
            <li><strong>Federal Jail</strong><br>If you have been particularly nefarious, staff may put you in Federal Jail or FedJail. You cannot play the game in any reasonable way and your accounts are frozen (though they can still be stolen) while in FedJail and it lasts a number of days.  Usually only one.</li><br>
            <li><strong>Wealth &amp; Gear Penalties</strong><br>If your behavior has not improved Staffs next step is to remove some or all of your wealth and possessions. This is at Staff discretion only and you will not get your gear back. It will either be donated to new players or simply removed.</li><br>
            <li><strong>Suspend Account</strong><br>The final penalty is removal from the game. If you have been so egregious in your behavior or the above penalties have not worked you may be suspended from the game. If you continue to attempt to access the game after a suspension, it is considered legal fraud and may subject to criminal persecution or civil lawsuit (to cover the costs in protecting against such activity).</li><br>
        </ul><br>
        <p>Our Staff is fairly lenient and it takes a <strong>lot</strong> to get to the bottom of the list. If you have a grievance chances are it will get resolved fairly. If you feel you have been treated unfairly by staff - let us know.</p>
    ';
}

$headers->endpage();
