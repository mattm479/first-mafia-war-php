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
        $this->render('airport/fly.html.twig', $this->getData($class, $destination, $falsePassport));
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
        $data = [];

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

        if ($this->application->user['money'] < $fee) {
            throw new Exception("<p>You do not have enough cash to make the trip. You need an additional " . moneyFormatter($fee - $this->application->user['money']) . ". Airport security escorts you out of the building.</p><p><a href='bank.php'>Head to the bank</a> or <a href='airport.php'>pick a cheaper flight</a>.</p>", 500);
        }

        $ri = mysqli_fetch_assoc($this->application->db->query("SELECT iv.inv_itemid, iv.inv_id, iv.inv_userid, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid=i.itmid WHERE iv.inv_id = {$falsePassport} AND iv.inv_userid = {$this->application->user['userid']}"));
        if (isset($ri['inv_itemid']) && $ri['inv_itemid'] == 74) {
            $rnd = rand(1, 5);
            if ($rnd == 5) {
                $this->application->db->query("UPDATE users SET money = money - {$fee}, jail = 120, hjReason='Busted by the border guards.' WHERE userid = {$this->application->user['userid']}");
                itemDelete($ri['inv_id'], 1, $this->application->user['userid']);
                throw new Exception("<p>Your False Passport was spotted by the border guards and you are taken in for questioning. Like most things at the airport, you spend a <strong>lot</strong> of time waiting around. They confiscate your passport, take your ticket, and throw you in jail. Damn.</p>", 500);
            } else {
                print '<p>Your False Passport got you there, but the boarder guards do not like the look of you and confiscate your passport for further examination. Be careful - they could pick you up at any time for questioning and confiscate your property!</p>';
                itemDelete($ri['inv_id'], 1, $this->application->user['userid']);
            }
        } elseif ($this->application->user['level'] < $destination) {
            throw new Exception("<p>You cannot travel there. They are far too cool for you. Airport security escorts you out of the building.</p><p><a href='airport.php'>Back to the main terminal.</a></p>", 500);
        } elseif ($this->application->user['location'] == $destination) {
            print '<p>You buy a ticket to the city you are in and proceed to the security station.  The guard there is not the brightest, but even he knows that it is suspicious to try and fly into the city you are in now.  He takes your ticket and escorts you to the <em>VIP Lounge</em> which looks a lot like a small white room.</p><p>In time, a very nice lady comes and tells you, very politely, that your plane has landed and escorts you to the luggage claim area.  Welcome to ' . locationName($this->application->user['location']) . ', your final destination!</p>';
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

        print '<p>Congratulations, you paid ' . moneyFormatter($fee) . ' to fly in ' . $class . ' to ' . locationName($destination) . '. ' . $spMessage . '</p><p><a href=\'explore.php\'>Visit the city</a> or <a href=\'home.php\'>head on home</a>.</p>';

        return $data;
    }
}