<?php

namespace Fmw;

class Hospital implements BaseInterface
{
    /**
     * @var Application $_application
     */
    private readonly Application $_application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application) {
        $this->_application = $application;
    }

    public function index() {
        $this->_application->template->render('hospital.html.twig', [
            'header' => $this->_application->header->getHeaderData(),
            'sidebar' => $this->_application->header->getSidebarData(),
            'data' => $this->show_list()
        ]);
    }

    function laugh(int $userId): void
    {
        $data = [
            'userid' => $userId,
            'user' => $this->_application->user,
            'error' => ""
        ];

        if (!is_numeric($userId) || $userId == 0) {
            $data['error'] = "MISSING_USERID";
        } else {
            $row = mysqli_fetch_assoc($this->_application->db->query("SELECT level, hospital, userid FROM users WHERE userid = {$userId}"));

            if ($row['hospital'] < 1) {
                $data['error'] = "NOT_IN_HOSPITAL";
            } else {
                $data['cost'] = $row['hospital'] * 3929;
                $data['formatted_cost'] = moneyFormatter($data['cost']);
                $data['mafioso'] = mafioso($userId);
                if ($this->_application->user['money'] < $data['cost']) {
                    $data['error'] = "NOT_ENOUGH_MONEY";
                } else {
                    $this->_application->db->query("UPDATE users SET money = money - {$data['cost']} WHERE userid = {$this->_application->user['userid']}");
                    $this->_application->db->query("UPDATE users SET hospital = hospital + 1 WHERE userid = {$userId}");

                    logEvent($userId, mafiosoLight($this->_application->user['userid']) . " laughed at you in the hospital.");
                }
            }
        }

        $this->_application->template->render('hospital/laugh.html.twig', [
            'header' => $this->_application->header->getHeaderData(),
            'sidebar' => $this->_application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function send_flowers(int $userId): void
    {
        $data = [
            'userid' => $userId,
            'user' => $this->_application->user,
            'error' => ""
        ];

        if (!is_numeric($userId) || $userId == 0) {
            $data['error'] = "MISSING_USERID";
        } else {
            $row = mysqli_fetch_assoc($this->_application->db->query("SELECT hospital, userid FROM users WHERE userid = {$userId}"));

            if ($row['hospital'] < 1) {
                $data['error'] = "NOT_IN_HOSPITAL";
            } else {
                $data['cost'] = $row['hospital'] * 3929;
                $data['formatted_cost'] = moneyFormatter($data['cost']);
                $data['mafioso'] = mafioso($userId);
                if ($this->_application->user['money'] < $data['cost']) {
                    $data['error'] = "NOT_ENOUGH_MONEY";
                } else {
                    $this->_application->db->query("UPDATE users SET money = money - {$data['cost']} WHERE userid = {$this->_application->user['userid']}");
                    $this->_application->db->query("UPDATE users SET hospital = hospital - 1 WHERE userid = {$userId}");

                    logEvent($userId, mafiosoLight($this->_application->user['userid']) . " sent you flowers in the hospital.");
                }
            }
        }

        $this->_application->template->render('hospital/flowers.html.twig', [
            'header' => $this->_application->header->getHeaderData(),
            'sidebar' => $this->_application->header->getSidebarData(),
            'data' => $data
        ]);
    }

    function show_list(): array
    {
        $data = [
            'user' => $this->_application->user,
            'patients' => []
        ];

        $result = $this->_application->db->query("SELECT hospital, userid, hjReason FROM users WHERE hospital > 0 ORDER BY hospital DESC");
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