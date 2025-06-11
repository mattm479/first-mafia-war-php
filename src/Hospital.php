<?php

namespace Fmw;

class Hospital extends BaseClass
{
    /**
     * @param Application $application
     */
    public function __construct(Application $application) {
        parent::__construct($application);
    }

    public function index() {
        $this->render('hospital.html.twig', [ 'patients' => $this->show_list() ]);
    }

    function laugh(int $userId): void
    {
        $data = [
            'userid' => $userId,
            'user' => $this->application->user,
            'error' => ""
        ];

        if (!is_numeric($userId) || $userId == 0) {
            $data['error'] = "MISSING_USERID";
        } else {
            $row = mysqli_fetch_assoc($this->application->db->query("SELECT level, hospital, userid FROM users WHERE userid = {$userId}"));

            if ($row['hospital'] < 1) {
                $data['error'] = "NOT_IN_HOSPITAL";
            } else {
                $data['cost'] = $row['hospital'] * 3929;
                $data['formatted_cost'] = moneyFormatter($data['cost']);
                $data['mafioso'] = mafioso($userId);
                if ($this->application->user['money'] < $data['cost']) {
                    $data['error'] = "NOT_ENOUGH_MONEY";
                } else {
                    $this->application->db->query("UPDATE users SET money = money - {$data['cost']} WHERE userid = {$this->application->user['userid']}");
                    $this->application->db->query("UPDATE users SET hospital = hospital + 1 WHERE userid = {$userId}");

                    logEvent($userId, mafiosoLight($this->application->user['userid']) . " laughed at you in the hospital.");
                }
            }
        }

        $this->application->template->render('hospital/laugh.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function send_flowers(int $userId): void
    {
        $data = [
            'userid' => $userId,
            'user' => $this->application->user,
            'error' => ""
        ];

        if (!is_numeric($userId) || $userId == 0) {
            $data['error'] = "MISSING_USERID";
        } else {
            $row = mysqli_fetch_assoc($this->application->db->query("SELECT hospital, userid FROM users WHERE userid = {$userId}"));

            if ($row['hospital'] < 1) {
                $data['error'] = "NOT_IN_HOSPITAL";
            } else {
                $data['cost'] = $row['hospital'] * 3929;
                $data['formatted_cost'] = moneyFormatter($data['cost']);
                $data['mafioso'] = mafioso($userId);
                if ($this->application->user['money'] < $data['cost']) {
                    $data['error'] = "NOT_ENOUGH_MONEY";
                } else {
                    $this->application->db->query("UPDATE users SET money = money - {$data['cost']} WHERE userid = {$this->application->user['userid']}");
                    $this->application->db->query("UPDATE users SET hospital = hospital - 1 WHERE userid = {$userId}");

                    logEvent($userId, mafiosoLight($this->application->user['userid']) . " sent you flowers in the hospital.");
                }
            }
        }

        $this->application->template->render('hospital/flowers.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function show_list(): array
    {
        $data = [
            'user' => $this->application->user,
            'patients' => []
        ];

        $result = $this->application->db->query("SELECT hospital, userid, hjReason FROM users WHERE hospital > 0 ORDER BY hospital DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $patient = [
                'cost' => $row['hospital'] * 3929,
                'mafioso' => mafioso($row['userid']),
                'reason' => $row['hjReason'],
                'time_remaining' => $row['hospital'],
                'userid' => $row['userid'],
            ];

            $data['patients'][] = $patient;
        }

        return $data;
    }
}