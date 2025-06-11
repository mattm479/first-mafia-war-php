<?php

namespace Fmw;

class StreetFight extends BaseClass
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    public function index(): void
    {
        $data = [];

        $qcsf = $this->application->db->query("SELECT sfID, sfLevelMin, sfLevelMax, sfTitle FROM streetFight WHERE sfStart = 0 AND sfEnd > 0 ORDER BY sfEnd ");
        while ($rcsf = mysqli_fetch_assoc($qcsf)) {
            $rcsf['join'] = ($this->application->user['attacksID'] == 0 && $this->application->user['level'] >= $rcsf['sfLevelMin'] && $this->application->user['level'] <= $rcsf['sfLevelMax'])
                ? "<a href='streetfight.php?action=join&do={$rcsf['sfID']}'>join&rang;</a>"
                : '';

            $qcf = $this->application->db->query("SELECT userid, attacks FROM users WHERE attacksID = {$rcsf['sfID']} ORDER BY attacks DESC");
            while ($rcf = mysqli_fetch_assoc($qcf)) {
                $rcf['mafioso'] = mafioso($rcf['userid']);
                $rcf['attacks'] = number_format($rcf['attacks']);
                $rcsf['fighters'][] = $rcf;
            }

            $data['fights'][] = $rcsf;
        }

        $qcsf = $this->application->db->query("SELECT sfTitle, sfEnd, sfPrize, sfGift, sfLevelMin, sfLevelMax FROM streetFight WHERE sfStart = 0 AND sfEnd > 0");
        while ($rcsf = mysqli_fetch_assoc($qcsf)) {
            $rcsf['grand_prize'] = itemInfo($rcsf['sfPrize']);
            $rcsf['gift'] = itemInfo($rcsf['sfGift']);
            $data['current_fights'][] = $rcsf;
        }

        $qnsf = $this->application->db->query("SELECT sfTitle, sfStart, sfEnd, sfPrize, sfGift, sfLevelMin, sfLevelMax FROM streetFight WHERE sfStart > 0 ORDER BY sfStart LIMIT 3");
        while ($rnsf = mysqli_fetch_assoc($qnsf)) {
            $rnsf['start'] = 'in ' . $rnsf['sfStart'] . ' hours';
            if ($rnsf['sfStart'] == 1) {
                $rnsf['start'] = 'at the top of the hour';
            }

            $rnsf['grand_prize'] = itemInfo($rnsf['sfPrize']);
            $rnsf['gift'] = itemInfo($rnsf['sfGift']);
            $data['upcoming_fights'][] = $rnsf;
        }

        $qpsf = $this->application->db->query("SELECT sfTitle, sfComment FROM streetFight WHERE sfEnd = 0 ORDER BY sfID DESC LIMIT 3");
        while ($rpsf = mysqli_fetch_assoc($qpsf)) {
            $data['recent_fights'][] = $rpsf;
        }

        $this->render('street_fight.html.twig', $data);
    }

    public function join_fight(int $userId, int $do): void
    {
        $data = [
            'joined' => false
        ];
        $query = $this->application->db->query("SELECT sfTitle, sfEnd, sfPrize, sfGift, sfLevelMin, sfLevelMax FROM streetFight WHERE sfID = {$do}");
        $row = mysqli_fetch_assoc($query);

        if ($row['sfLevelMin'] <= $this->application->user['level'] && $row['sfLevelMax'] >= $this->application->user['level']) {
            $this->application->db->query("UPDATE users SET attacks = 1, attacksID = {$do} WHERE userid = {$userId}");
            $data['joined'] = true;
        }

        $this->render('street_fight/join.html.twig', $data);
    }
}