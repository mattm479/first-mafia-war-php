<?php

require_once "globals.php";
global $headers, $user;
pagePermission($lgn=1, $stff=0, $njl=0, $nhsp=0, $nlck=0);

print '
    <h3>Mafioso Search</h3>
    <div class=floatright><img src=\'assets/images/photos/father.jpg\' width=200 height=332 alt=\'Search out your Father\'></div>
    <p>Find your friends and enemies.  And that other guy.</p>
    <table width=65% cellpadding=3 cellspacing=0 class=table>
        <tr>
            <td width=70%>
                <h5>Search by Name</h5>
                <form action=\'mafiosoResults.php\' method=GET>
                    <input type=text size=25 name=name> &nbsp;
                    <input type=submit value=\'Search\'>
                </form><br>
                <h5>Search by Name</h5>
                <form action=\'viewuser.php\' method=GET>
                    '.mafiosoMenu('u').' &nbsp; &nbsp;
                    <input type=submit value=\'Search\'>
                </form><br>
                <h5>Search by Location</h5>
                <form action=\'mafiosoResults.php\' method=GET>
                    '.locationDropdown($user['level']).' &nbsp; &nbsp; &nbsp;
                    <input type=submit value=\'Search\'>
                </form><br>
            </td>
            <td width=30% valign=top>
                <h5>Specific searches</h5>
                <a href=\'mafiosoResults.php?staff=1\'>Staff</a><br>
                <a href=\'mafiosoResults.php?giovane=1\'>Giovane</a><br>
                <a href=\'mafiosoResults.php\'>Everyone</a><br>
                <a href=\'mafiosoResults.php?attack=1\'>Combat Targets</a><br>
            </td>
        </tr>
    </table><br>
';

$headers->endpage();
