<?php

require_once "globals.php";
global $headers, $userId;
pagePermission($lgn=1, $stff=0, $njl=0, $nhsp=0, $nlck=0);

print '
    <h3>Refer your Friends</h3>
    <div class=floatright>
        <table border=0 cellpadding=3 cellspacing=0 class=table>
            <tr>
                <td class=center>lvl</td>
                <td>Benefit</td>
            </tr>
            <tr>
                <td class=center>1</td>
                <td>5 Tokens of Respect</td>
            </tr>
            <tr>
                <td class=center>8</td>
                <td>' . itemInfo(301) . '</td>
            </tr>
            <tr>
                <td class=center>15</td>
                <td>' . itemInfo(53) . '</td>
            </tr>
            <tr>
                <td class=center>25</td>
                <td>'.itemInfo(338).'</td>
            </tr>
            <tr>
                <td class=center>30</td>
                <td>' . itemInfo(59) . '</td>
            </tr>
            <tr>
                <td class=center>100</td>
                <td>' . itemInfo(366) . '</td>
            </tr>
            <tr>
                <td class=center>150</td>
                <td>' . itemInfo(29) . '</td>
            </tr>
            <tr>
                <td class=center>200</td>
                <td>' . itemInfo(346) . '</td>
            </tr>
            <tr>
                <td class=center>300</td>
                <td>' . itemInfo(326) . '</td>
            </tr>
        </table>
    </div>
    <p>If you like this game, please invite your friends. They too will enjoy it, and you get benefits as well. Just cut and paste your referal link below and watch our Family grow! You get the benefit listed to the right when your referee obtains the level listed.</p>
    
    <strong>Straight HTML</strong> <em>Cut and paste into mail, chat, blogs, etc.</em><br>
    <pre>http://www.firstmafiawar.com/register.php?REF=' . $userId . '</pre>
    
    <br><br>
    
    <strong>Small Banner</strong> <em>Cut and paste into your web site</em><br>
    <img src=\'http://www.firstmafiawar.com/images/ads/fmw_ad_banner.gif\'><br>
    <pre>&lt;&#97;&#32;&#104;&#114;&#101;&#102;&#61;&quot;&#104;&#116;&#116;&#112;&#58;&#47;&#47;&#119;&#119;&#119;&#46;&#102;&#105;&#114;&#115;&#116;&#109;&#97;&#102;&#105;&#97;&#119;&#97;&#114;&#46;&#99;&#111;&#109;&#47;&#114;&#101;&#103;&#105;&#115;&#116;&#101;&#114;&#46;&#112;&#104;&#112;&#63;&#82;&#69;&#70;&#61;' . $userId . '&quot;&gt;<br>&lt;&#105;&#109;&#103;&#32;&#115;&#114;&#99;&#61;&quot;&#104;&#116;&#116;&#112;&#58;&#47;&#47;&#119;&#119;&#119;&#46;&#102;&#105;&#114;&#115;&#116;&#109;&#97;&#102;&#105;&#97;&#119;&#97;&#114;&#46;&#99;&#111;&#109;&#47;&#105;&#109;&#97;&#103;&#101;&#115;&#47;&#97;&#100;&#115;&#47;&#102;&#109;&#119;&#95;&#97;&#100;&#95;&#98;&#97;&#110;&#110;&#101;&#114;&#46;&#103;&#105;&#102;&quot;&gt;&lt;&#47;&#97;&gt;</pre>
    
    <p>Remember, the only way you can get the benefit if if they use one of these links!</p>
';

$headers->endpage();
