<?php

namespace Fmw;

class Jail implements BaseInterface
{
    private readonly Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function index()
    {
        $this->application->template->render('jail.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => [ 'inmates' => $this->show_list() ]
        ]);
    }

    public function bribe(int $userId): void
    {
        $row = mysqli_fetch_assoc($this->application->db->query("SELECT level, jail, username, userid FROM users WHERE userid = {$userId}"));
        $cost = ($row['level'] * 225 * $row['jail']);

        $data = [
            'error' => '',
            'formatted_cost' => moneyFormatter($cost),
            'mafioso' => mafioso($userId)
        ];

        if (!$userId || $row['jail'] < 1) {
            $data['error'] = 'NOT_IN_JAIL';
        } elseif ($this->application->user['money'] < $cost) {
            $data['error'] = 'NOT_ENOUGH_MONEY';
        } elseif ((rand(1, 10) == 10)) {
            $data['error'] = 'FAILED_BRIBE';
            $time = rand(2, 5);
            $this->application->db->query("UPDATE users SET jail = jail + {$time}, hjReason = 'Caught bribing the guards for {$row['username']}' WHERE userid = {$this->application->user['userid']}");
        } else {
            $this->application->db->query("UPDATE users SET money = money - {$cost}, jailBails = jailBails + 1 WHERE userid = {$this->application->user['userid']}");
            $this->application->db->query("UPDATE users SET jail = 0 WHERE userid = {$userId}");

            logEvent($userId, mafiosoLight($this->application->user['userid'])." sprung you from jail by bribing the guards.");
        }

        $this->application->template->render('jail/bribe.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function bust(int $userId): void
    {
        $data = [
            'mafioso' => mafioso($userId),
            'userId' => $userId
        ];

        $this->application->template->render('jail/bust.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function do_bust(int $userId, int $respect): void
    {
        $row = mysqli_fetch_assoc($this->application->db->query("SELECT userid, jail, crimeLevel, level, username FROM users WHERE userid = {$userId}"));
        $data = [
            'error' => '',
            'mafioso' => mafioso($userId),
        ];

        if ($this->application->user['jail'] && $this->application->user['userid'] != $userId) {
            $data['error'] = 'USER_IN_JAIL';
        } elseif (!$userId || $row['jail'] < 1) {
            $data['error'] = 'NOT_IN_JAIL';
        } elseif ($this->application->user['respect'] < $respect) {
            $data['error'] = 'NOT_ENOUGH_RESPECT';
        } else {
            $rb = mysqli_fetch_assoc($this->application->db->query("SELECT userid FROM coursesdone WHERE userid = {$this->application->user['userid']} AND courseid = 25"));
            if (isset($rb['userid']) && $rb['userid'] == $this->application->user['userid']) {
                $respect *= 2;
            }

            $bustFormula = (((max(8, ($this->application->user['level'] / 9))) * $respect) / ($row['crimeLevel'] * $row['jail'])) * 100;
            $chance = max(15, min($bustFormula,95));
            if ($this->application->user['userid'] == $userId) {
                $chance = max(25, min($bustFormula,95));
            }

            if (rand(1, 100) < $chance) {
                $gain = $row['level'] * 5;
                $rc = mysqli_fetch_assoc($this->application->db->query("SELECT userid FROM coursesdone WHERE userid = {$this->application->user['userid']} AND courseid = 35"));
                if (isset($rc['userid']) && $rc['userid'] == $this->application->user['userid']) {
                    $respect--;
                    if ($respect <= 0) {
                        $respect = 0;
                    }
                }

                $this->application->db->query("UPDATE users SET exp = exp + {$gain}, respect = respect - {$respect}, jailBusts = jailBusts + 1 WHERE userid = {$this->application->user['userid']}");
                $this->application->db->query("UPDATE users SET jail = 0 WHERE userid = {$userId}");

                logEvent($userId, mafiosoLight($this->application->user['userid'])." busted you out of jail at great personal risk.");
            } else {
                $data['error'] = 'USER_BUSTED';
                $time = ($chance);
                $this->application->db->query("UPDATE users SET jail = jail + {$time}, hjReason = 'Caught trying to bust out {$row['username']}', respect = respect - {$respect}, jailBusts = jailBusts + 1 WHERE userid = {$this->application->user['userid']}");
            }
        }

        $this->application->template->render('jail/do_bust.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function show_list(): array
    {
        $inmates = [];

        $result = $this->application->db->query("SELECT userid, level, jail, hjReason FROM users WHERE jail > 0 ORDER BY jail DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $cost = moneyFormatter($row['level'] * 225 * $row['jail']);
            $inmate = [
                'mafioso' => mafioso($row['userid']),
                'reason' => $row['hjReason'],
                'time_remaining' => $row['jail'],
                'actions' => [
                    '<a title="costs respect!" href="jail.php?action=bust&uid=' . $row['userid'] . '">bust</a> &nbsp;&middot;&nbsp;',
                    ($this->application->user['jail'])
                        ? '<a href="attack.php?ID=' . $row['userid'] . '">attack</a>'
                        : '<a title="' . $cost . '" href="jail.php?action=bribe&uid=' . $row['userid'] . '">bribe</a>'
                ]
            ];

            $inmates[] = $inmate;
        }

        return $inmates;
    }
}