<?php

namespace Fmw;

class Automotive extends BaseClass
{
    public function __construct($application)
    {
        parent::__construct($application);
    }

    public function index(): void
    {
        $data = [
            'user' => $this->application->user
        ];

        if ($this->application->user['autoOwned'] > 0) {
            $query = $this->application->db->query("SELECT auID, auName, auPrice FROM autos WHERE auID = {$this->application->user['autoOwned']}");
            $data['auto'] = mysqli_fetch_assoc($query);
            $data['trade_in'] = moneyFormatter(round($data['auto']['auPrice'] * 0.6));
        }

        $carq = $this->application->db->query("SELECT auID, auName, auPrice FROM autos ORDER BY auID");
        while($row = mysqli_fetch_assoc($carq)) {
            $row['price'] = moneyFormatter($row['auPrice']);
            $data['autos'][] = $row;
        }

        $this->render('automotive.html.twig', $data);
    }

    public function buy(int $userId, int $autoId): void
    {
        $data = [
            'error' => ''
        ];

        $qau = $this->application->db->query("SELECT auID, auName, auPrice FROM autos WHERE auID = {$autoId}");
        $rau = mysqli_fetch_assoc($qau);

        if ($rau['auPrice'] > $this->application->user['money']) {
            $data['error'] = 'NOT_ENOUGH_MONEY';
        } else {
            if ($this->application->user['autoOwned'] > 0) {
                $auq = $this->application->db->query("SELECT auID, auPrice FROM autos WHERE auID = {$this->application->user['autoOwned']}");
                $aur = mysqli_fetch_assoc($auq);
                $tradeIn = round($aur['auPrice'] * 0.6);

                $this->application->db->query("UPDATE users SET money = money + {$tradeIn}, autoOwned = 0, autoMaint = 0, autoValue = 0 WHERE userid = {$userId}");
            }

            $this->application->db->query("UPDATE users SET money = money - {$rau['auPrice']}, autoOwned = {$rau['auID']}, autoValue = {$rau['auPrice']}, autoMaint = 1 WHERE userid = {$userId}");
            $data['name'] = $rau['auName'];
            $data['price'] = moneyFormatter($rau['auPrice']);
        }

        $this->render('automotive/buy.html.twig', $data);
    }

    public function sell(int $userId, int $autoId): void
    {
        $data = [
            'error' => ''
        ];

        $qau = $this->application->db->query("SELECT auID, auPrice FROM autos WHERE auID = {$autoId}");
        $rau = mysqli_fetch_assoc($qau);

        if ($rau['auID'] != $this->application->user['autoOwned']) {
            $data['error'] = 'DO_NOT_OWN';
        } else {
            $tradeIn = round($rau['auPrice'] * 0.6);
            $data['trade_in'] = moneyFormatter($tradeIn);

            $this->application->db->query("UPDATE users SET money = money + {$tradeIn}, autoOwned = 0, autoMaint = 0, autoValue = 0 WHERE userid = {$userId}");
        }

        $this->render('automotive/sell.html.twig', $data);
    }
}