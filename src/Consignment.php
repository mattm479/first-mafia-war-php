<?php

namespace Fmw;

class Consignment extends BaseClass
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    public function index(int $userId): void
    {
        $this->render('consignment.html.twig', $this->getData($userId));
    }

    private function getData(int $userId): array
    {
        $data = [
            'error' => '',
            'items' => [],
            'userId' => $userId
        ];

        $query = $this->application->db->query("SELECT cm.cmConsignor, cm.cmExpire, cm.cmID, cm.cmItem, cm.cmDaysLeft, cm.cmCurrency, cm.cmPrice, i.itmname, i.itmtype FROM conMarket cm LEFT JOIN items i ON cm.cmItem = i.itmid WHERE cmExpire > 0 ORDER BY i.itmtype, i.itmname, cm.cmPrice");
        while ($row = mysqli_fetch_assoc($query)) {
            $row['item_type'] = itemType($row['itmtype']);
            $row['mafioso'] = mafioso($row['cmConsignor']);
            $row['item_info'] = itemInfo($row['cmItem']);
            $row['cash_price'] = moneyFormatter($row['cmPrice']);
            $row['token_price'] = moneyFormatter($row['cmPrice'], "") . ' tokens';

            $data['items'][] = $row;
        }

        return $data;
    }

    public function add(int $userId, int $ID, int $AID, string $currency, int $price): void
    {
        $data = [
            'error' => '',
            'id' => $ID,
            'price' => $price
        ];

        // Set the price and confirm
        if ($ID > 0) {
            $query = $this->application->db->query("SELECT iv.inv_id, iv.inv_userid, i.itmname, i.itmBasePrice FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE inv_id = {$ID} and inv_userid = {$userId}");
            if (!mysqli_num_rows($query)) {
                $data['error'] = 'NO_ITEM';
            } else {
                $row = mysqli_fetch_assoc($query);

                $value = moneyFormatter($row['itmBasePrice']);
                if ($row['itmBasePrice'] == 0) {
                    $value = 'priceless';
                }

                $data['item_name'] = $row['itmname'];
                $data['value'] = $value;
            }
        }

        // Process the item and add to Market
        if ($price > 0) {
            $query = $this->application->db->query("SELECT iv.inv_itmexpire, iv.inv_id, iv.inv_itemid, i.itmname FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE inv_id = {$AID} and inv_userid = {$userId}");
            $row = mysqli_fetch_assoc($query);
            $curr = 'respect';
            if ($currency == 'cash') {
                $curr = 'money';
            }

            $fee = max(round($price / 10), 1);
            if ($fee > $this->application->user[$curr]) {
                $data['error'] = 'NOT_ENOUGH_CURRENCY';
            } else {
                $this->application->db->query("UPDATE users SET {$curr} = {$curr} - {$fee} where userid = {$userId}");
                itemDelete($row['inv_id'], 1, $userId);
                $dura = 30;
                if ($row['inv_itmexpire'] > 0) {
                    $dura = $row['inv_itmexpire'];
                }

                $this->application->db->query("INSERT INTO conMarket (cmItem, cmDaysLeft, cmQuantity, cmPrice, cmCurrency, cmExpire, cmConsignor, cmAddTime, cmBuyer, cmBuyTime) VALUES ({$row['inv_itemid']}, {$row['inv_itmexpire']}, 1, {$price}, '{$currency}', '{$dura}', {$userId}, unix_timestamp(), 0, 0)");
                $data['item_name'] = $row['itmname'];
            }
        }

        $this->render('consignment/add.html.twig', $data);
    }

    public function buy(int $userId, int $ID): void
    {
        $data = [
            'error' => '',
        ];
        $query = $this->application->db->query("SELECT cm.cmCurrency, cm.cmPrice, cm.cmItem, cm.cmQuantity, cm.cmDaysLeft, cm.cmConsignor, i.itmname, i.itmid FROM conMarket cm LEFT JOIN items i ON i.itmid = cm.cmItem WHERE cmID = {$ID} AND cmExpire > 0");
        if (!mysqli_num_rows($query)) {
            $data['error'] = 'NO_ITEM';
        } else {
            $row = mysqli_fetch_assoc($query);
            $curr = 'respect';
            if ($row['cmCurrency'] == 'cash') {
                $curr = 'money';
            }

            if ($row['cmPrice'] > $this->application->user[$curr]) {
                $data['error'] = 'NOT_ENOUGH_CURRENCY';
            } else {
                itemAdd($row['cmItem'], $row['cmQuantity'], $row['cmDaysLeft'], $userId, 0);

                $this->application->db->query("UPDATE users SET {$curr} = {$curr} - {$row['cmPrice']} where userid = {$userId}");
                $this->application->db->query("UPDATE users SET {$curr} = {$curr} + {$row['cmPrice']} where userid = {$row['cmConsignor']}");
                $this->application->db->query("UPDATE conMarket SET cmExpire = 0, cmBuyer = {$userId}, cmBuyTime = unix_timestamp() WHERE cmID = {$ID}");

                $purchase = moneyFormatter($row['cmPrice'], "") . ' tokens';
                if ($row['cmCurrency'] == 'cash') {
                    $purchase = moneyFormatter($row['cmPrice']);
                }

                $data['item_name'] = $row['itmname'];
                $data['purchase'] = $purchase;

                logEvent($row['cmConsignor'], "Your {$row['itmname']} was sold on the Market for {$purchase}.");

                $qur = $this->application->db->query("SELECT userid, trackActionIP FROM users WHERE userid = {$row['cmConsignor']}");
                $ur = mysqli_fetch_assoc($qur);

                $this->application->db->query("INSERT INTO logsWealth (lwSender, lwSenderIP, lwReceiver, lwReceiverIP, lwAmount, lwTime, lwType, lwSource) VALUES ({$userId}, '{$this->application->user['trackActionIP']}', {$row['cmConsignor']}, '{$ur['trackActionIP']}', {$row['cmPrice']}, unix_timestamp(), '{$row['cmCurrency']}', 'market')");

                logItem($row['cmConsignor'], "{$ur['trackActionIP']}", $userId, "{$this->application->user['trackActionIP']}", "market", $row['itmid'], 1);
            }
        }

        $this->render('consignment/buy.html.twig', $data);
    }

    public function remove(int $userId, int $ID): void
    {
        $data = [
            'error' => '',
        ];

        $query = $this->application->db->query("SELECT cm.cmItem, cm.cmDaysLeft, i.itmname FROM conMarket cm LEFT JOIN items i ON cm.cmItem = i.itmid WHERE cmID = {$ID} AND cmConsignor = {$userId} AND cmExpire > 0");
        if (!mysqli_num_rows($query)) {
            $data['error'] = 'NO_ITEM';
        } else {
            $row = mysqli_fetch_assoc($query);

            itemAdd($row['cmItem'], 1, $row['cmDaysLeft'], $userId, 0);

            $this->application->db->query("UPDATE conMarket SET cmExpire = 0, cmBuyer = {$userId}, cmBuyTime = unix_timestamp() WHERE cmID = {$ID}");

            $data['item_name'] = $row['itmname'];
        }

        $this->render('consignment/remove.html.twig', $data);
    }
}