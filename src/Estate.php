<?php

namespace Fmw;

class Estate implements BaseInterface
{
    private readonly Application $application;
    private string $estate;
    private string $user_estate;
    private array $current_estate;


    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->estate = "residence_{$this->application->user['location']}";
        $this->user_estate = "u.residence_{$this->application->user['location']}";
        $query = $this->application->db->query("SELECT h.hID, h.hNAME, h.hPRICE, u.userid FROM houses h LEFT JOIN users u ON h.hID = {$this->user_estate} WHERE u.userid = {$this->application->user['userid']}");
        $this->current_estate = mysqli_fetch_assoc($query);
    }

    public function index(): void
    {
        $data = [
            'error' => '',
        ];

        if ($this->application->user['autoOwned'] == 0) {
            $data['error'] = 'NO_AUTO';
        } else {
            if ($this->current_estate['hPRICE'] == 0) {
                $data['house'] = 'hovel';
            } else {
                $data['location'] = locationName($this->application->user['location']);
                $data['house'] = $this->current_estate['hNAME'];
                $data['willpower'] = moneyFormatter(($this->current_estate['hID'] * $this->current_estate['hID'] * 50), "");
                $data['houseId'] = $this->current_estate['hID'];
            }

            if (!$this->current_estate['hID']) {
                $this->current_estate['hID'] = 1;
            }

            $hq = $this->application->db->query("SELECT hID, hNAME, hPRICE FROM houses WHERE hID > {$this->current_estate['hID']} ORDER BY hID");
            while ($row = mysqli_fetch_assoc($hq)) {
                $row['willpower'] = moneyFormatter(($row['hID'] * $row['hID'] * 50), "");
                $row['price'] = moneyFormatter($row['hPRICE']);
                $data['houses'][] = $row;
            }
        }

        $this->application->template->render('estate.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data,
        ]);
    }

    public function buy_house(int $property): void
    {
        $data = [
            'error' => '',
        ];
        $npq = $this->application->db->query("SELECT hID, hNAME, hPRICE FROM houses WHERE hID = {$property}");
        $np = mysqli_fetch_assoc($npq);

        if ($np['hPRICE'] > $this->application->user['money']) {
            $data['error'] = 'NOT_ENOUGH_MONEY';
        } else {
            $sellPrice = ($this->current_estate['hPRICE'] * 0.8);
            $this->application->db->query("UPDATE users SET money = money + {$sellPrice}, {$this->estate} = 1 WHERE userid = {$this->application->user['userid']}");
            $this->application->db->query("UPDATE users SET money = money - {$np['hPRICE']}, {$this->estate} = {$np['hID']} WHERE userid = {$this->application->user['userid']}");
            $this->application->db->query("UPDATE users SET residence_total = (residence_1 * residence_1) + (residence_10 * residence_10) + (residence_25 * residence_25) + (residence_50 * residence_50) + (residence_100 * residence_100) + (residence_250 * residence_250) + (residence_500 * residence_500) WHERE userid = {$this->application->user['userid']}");

            $data['house'] = $np['hNAME'];
            $data['price'] = moneyFormatter($np['hPRICE']);

            setWillpower($this->application->user['userid']);
        }

        $this->application->template->render('estate/buy.html.twig', [
           'header' => $this->application->header->getHeaderData(),
           'sidebar' => $this->application->header->getSidebarData(),
           'data' => $data,
        ]);
    }

    public function sell_house(int $property): void
    {
        $data = [
            'error' => '',
        ];

        if ($this->current_estate['hPRICE'] == 0) {
            $data['error'] = 'CAN_NOT_SELL';
        } else {
            $sellPrice = ($this->current_estate['hPRICE'] * 0.8);
            $this->application->db->query("UPDATE users SET money = money + {$sellPrice}, {$this->estate} = 1 WHERE userid = {$this->application->user['userid']}");
            $this->application->db->query("UPDATE users SET residence_total = (residence_1 * residence_1) + (residence_10 * residence_10) + (residence_25 * residence_25) + (residence_50 * residence_50) + (residence_100 * residence_100) + (residence_250 * residence_250) + (residence_500 * residence_500) WHERE userid = {$this->application->user['userid']}");

            $data['house'] = $this->current_estate['hNAME'];
            $data['price'] = moneyFormatter($sellPrice);

            setWillpower($this->application->user['userid']);
        }

        $this->application->template->render('estate/sell.html.twig', [
            'header' => $this->application->header->getHeaderData(),
            'sidebar' => $this->application->header->getSidebarData(),
            'data' => $data
        ]);
    }
}