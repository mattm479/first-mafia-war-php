<?php

namespace Fmw;

use mysqli_result;

class Home
{
    /**
     * @var Application $_application
     */
    private readonly Application $_application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->_application = $application;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function render(): void
    {
        $headerData = $this->_application->header->getHeaderData();
        $sidebarData = $this->_application->header->getSidebarData();
        $data = $this->getData();

        $this->_application->template->render('home.html.twig', [
            'header' => $headerData,
            'sidebar' => $sidebarData,
            'data' => $data
        ]);
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $data = [
            'user' => $this->_application->user,
            'mafioso' => mafioso($this->_application->user['userid']),
            'left_pane' => $this->getLeftPaneData(),
            'right_pane' => $this->getRightPaneData()
        ];

        if ($this->_application->user['autoOwned'] > 1) {
            $autoOwned = mysqli_fetch_assoc($this->_application->db->query("SELECT auID, auName FROM autos WHERE auID = {$this->_application->user['autoOwned']}"));
            $data['auto_name'] = $autoOwned['auName'];
        }

        $birthday = unserialize($this->_application->user['birthday']);
        $data['combat_rank'] = $this->_application->user['comRank'];
        $data['strength_rank'] = getRank($this->_application->user['strength'], 'strength');
        $data['strength'] = number_format($this->_application->user['strength']);
        $data['agility_rank'] = getRank($this->_application->user['agility'], 'agility');
        $data['agility'] = number_format($this->_application->user['agility']);
        $data['guard_rank'] = getRank($this->_application->user['guard'], 'guard');
        $data['guard'] = number_format($this->_application->user['guard']);
        $data['iq_rank'] = getRank($this->_application->user['IQ'], 'IQ');
        $data['iq'] = number_format($this->_application->user['IQ']);
        $data['labour_rank'] = getRank($this->_application->user['labour'], 'labour');
        $data['labour'] = number_format($this->_application->user['labour']);
        $data['birthday']['month'] = $birthday['mth'];
        $data['birthday']['day'] = $birthday['day'];
        $data['age'] = daysOld($this->_application->user['trackSignupTime']);
        $data['anniversary'] = date('F, j', $this->_application->user['trackSignupTime']);

        $result = $this->_application->db->query("SELECT iv.inv_id, i.itmid, i.itmCombatType FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$this->_application->user['userid']} AND iv.inv_equip = 'yes' AND i.itmtype = 60 ORDER BY i.itmCombatType, i.itmCombat");
        while ($row = mysqli_fetch_assoc($result)) {
            $data['equipped_protection'][] = [ 'inv_id' => $row['inv_id'], 'item_info' => itemInfo($row['itmid']), 'item_combat_type' => itemCombatType($row['itmCombatType']) ];
        }

        $result = $this->_application->db->query("SELECT iv.inv_id, i.itmid, i.itmCombatType FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$this->_application->user['userid']} AND iv.inv_equip = 'yes' AND i.itmtype != 60 ORDER BY i.itmCombatType, i.itmCombat");
        while ($row = mysqli_fetch_assoc($result)) {
            $data['equipped_weapons'][] = [ 'inv_id' => $row['inv_id'], 'item_info' => itemInfo($row['itmid']), 'item_combat_type' => itemCombatType($row['itmCombatType']) ];
        }

        $result = $this->_application->db->query("SELECT u.level, u.userid FROM referals r LEFT JOIN users u ON r.refREFED = u.userid WHERE r.refREFER = {$this->_application->user['userid']}");
        $data['referrals']['count'] = mysqli_num_rows($result);
        while ($row = mysqli_fetch_assoc($result)) {
            $data['referrals']['mafioso'][] = [ 'mafioso_light' => mafiosoLight($row['userid']), 'level' => $row['level'] ];
        }

        $locations = [
            [ 'name' => 'palermo', 'dbValue' => 'residence_1' ],
            [ 'name' => 'rome', 'dbValue' => 'residence_10' ],
            [ 'name' => 'monte_carlo', 'dbValue' => 'residence_25' ],
            [ 'name' => 'new_york', 'dbValue' => 'residence_50' ],
            [ 'name' => 'chicago', 'dbValue' => 'residence_100' ],
            [ 'name' => 'montreal', 'dbValue' => 'residence_250' ],
            [ 'name' => 'caracas', 'dbValue' => 'residence_500' ]
        ];

        foreach ($locations as $location) {
            $data['houses'][$location['name']] = houseName($this->_application->user[$location['dbValue']]);
        }

        $data['user_notepad'] = mysql_tex_out($this->_application->user['user_notepad']);

        return $data;
    }

    /**
     * @return array
     */
    private function getLeftPaneData(): array
    {
        $leftPane = $this->_application->db->query("SELECT iv.inv_id, iv.inv_qty, iv.inv_itmexpire, i.itmid, i.itmtype FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$this->_application->user['userid']} AND iv.inv_equip = 'no' AND i.itmtype < 40 ORDER BY i.itmtype, i.itmname");

        return $this->arrangePaneData($leftPane);
    }

    /**
     * @return array
     */
    private function getRightPaneData(): array
    {
        $rightPane = $this->_application->db->query("SELECT iv.inv_id, iv.inv_qty, iv.inv_itmexpire, i.itmid, i.itmtype FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$this->_application->user['userid']} AND iv.inv_equip = 'no' AND i.itmtype > 39 ORDER BY i.itmtype, i.itmname");

        return $this->arrangePaneData($rightPane);
    }

    /**
     * @param bool|mysqli_result $queryResult
     * @return array
     */
    private function arrangePaneData(bool|mysqli_result $queryResult): array
    {
        if (gettype($queryResult) == 'boolean') return [];

        $lt = '';
        $items = [];
        $data = [];

        while ($row = mysqli_fetch_assoc($queryResult)) {
            if ($lt != itemType($row['itmtype'])) {
                if (count($items) > 0) {
                    $data[] = [ 'items' => [ 'type' => $lt, 'items' => $items ]];
                }

                $lt = itemType($row['itmtype']);
                $items = [];
            }

            $row['item_info'] = itemInfo($row['itmid']);
            $items[] = $row;
        }

        return $data;
    }
}