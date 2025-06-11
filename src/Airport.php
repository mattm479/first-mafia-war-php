<?php

namespace Fmw;

use Exception;

class Airport extends BaseClass
{
    /**
     * @param Application $application
     */
    public function __construct(Application $application) {
        parent::__construct($application);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function index(): void {
        $this->render('airport.html.twig', [
            'locations' => locationDropdown($this->application->user['level'])
        ]);
    }

    /**
     * @param string $class
     * @param int $destination
     * @param int $falsePassport
     * @return void
     * @throws Exception
     */
    public function fly(string $class, int $destination, int $falsePassport): void {
        $data = $this->getData($class, $destination, $falsePassport);
        $this->render('airport/fly.html.twig', $data);
    }

    /**
     * @param string $class
     * @param int $destination
     * @param int $falsePassport
     * @return array
     * @throws Exception
     */
    private function getData(string $class, int $destination, int $falsePassport): array
    {
        $data = [
            'error' => ''
        ];

        $spMessage = '';
        $fee = 100000;
        $energyLoss = min(rand(round($this->application->user['level'] * 0.1), round($this->application->user['level'] * 0.5)), $this->application->user['energy']);
        $hpLoss = min(rand(round($this->application->user['level'] * 0.2), round($this->application->user['level'] * 2)), $this->application->user['hp']);

        if ($this->application->user['location'] == 0 || $this->application->user['location'] == 42) {
            $fee = 0;
            $energyLoss = 0;
            $hpLoss = 0;
        } else if ($class == 'Steerage') {
            $fee = 9000;
            $energyLoss = min(rand(($this->application->user['level']), ($this->application->user['level'] * 3)), $this->application->user['energy']);
            $hpLoss = min(rand(($this->application->user['level'] * 2), ($this->application->user['level'] * 6)), $this->application->user['hp']);
        } else if ($class == 'Coach') {
            $fee = 30000;
            $energyLoss = min(rand(($this->application->user['level'] * .2), ($this->application->user['level'] * 2)), $this->application->user['energy']);
            $hpLoss = min(rand(($this->application->user['level']), ($this->application->user['level'] * 4)), $this->application->user['hp']);
        }

        $data['formatted_money'] = moneyFormatter($fee - $this->application->user['money']);
        if ($this->application->user['money'] < $fee) {
            $data['error'] = 'NOT_ENOUGH_MONEY';
        } else {
            $ri = mysqli_fetch_assoc($this->application->db->query("SELECT iv.inv_itemid, iv.inv_id, iv.inv_userid, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid WHERE iv.inv_id = {$falsePassport} AND iv.inv_userid = {$this->application->user['userid']}"));
            if (isset($ri['inv_itemid']) && $ri['inv_itemid'] == 74) {
                $rnd = rand(1, 5);
                if ($rnd == 5) {
                    $this->application->db->query("UPDATE users SET money = money - {$fee}, jail = 120, hjReason='Busted by the border guards.' WHERE userid = {$this->application->user['userid']}");
                    itemDelete($ri['inv_id'], 1, $this->application->user['userid']);
                    $data['error'] = 'FALSE_PASSPORT_SPOTTED';

                    return $data;
                } else {
                    $data['error'] = 'FALSE_PASSPORT_CONFISCATED';
                    itemDelete($ri['inv_id'], 1, $this->application->user['userid']);
                }
            } elseif ($this->application->user['level'] < $destination) {
                $data['error'] = 'BAD_DESTINATION';

                return $data;
            } elseif ($this->application->user['location'] == $destination) {
                $data['error'] = 'ALREADY_HERE';
                $data['location'] = locationName($this->application->user['location']);
            }

            $rfly = mysqli_fetch_assoc($this->application->db->query("SELECT userid FROM coursesdone WHERE userid = {$this->application->user['userid']} AND courseid = 36"));
            if (isset($rfly['userid']) && $rfly['userid'] == $this->application->user['userid']) {
                $spMessage .= " Your personal pilot, Ted, didn't exactly fly straight, but he did fly fast, so you do not lose any energy on this flight.";
                $energyLoss = 0;
            }

            $this->application->db->query("UPDATE users SET money = money - {$fee}, location = {$destination}, energy = energy - {$energyLoss}, hp = hp - {$hpLoss} WHERE userid = {$this->application->user['userid']}");

            if (($this->application->user['hp'] - $hpLoss) < 1) {
                $this->application->db->query("UPDATE users SET hospital = 5, hjReason = 'Bad flight' WHERE userid={$this->application->user['userid']}");
                $spMessage .= ' Your flight was not good though and you ended up in the hospital for a few minutes.';
            }

            $qinv = $this->application->db->query("SELECT inv_id, inv_userid FROM inventory WHERE inv_userid = {$this->application->user['userid']} AND inv_itemid = 610");
            if (mysqli_num_rows($qinv)) {
                $this->application->db->query("UPDATE users SET will = will + level WHERE userid = {$this->application->user['userid']}");
                $spMessage .= " You gain {$this->application->user['level']} willpower visiting your estate and relaxing!";
            }

            $data['fee'] = moneyFormatter($fee);
            $data['class'] = $class;
            $data['destination'] = locationName($destination);
            $data['message'] = $spMessage;
        }

        return $data;
    }
}