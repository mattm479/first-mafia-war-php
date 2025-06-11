<?php

namespace Fmw;

class Explore extends BaseClass
{
    public function __construct(Application $application) {
        parent::__construct($application);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function index(): void
    {
        $this->render('explore.html.twig', $this->getData());
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $data = [
            'user' => $this->application->user,
            'location_name' => locationName($this->application->user['location'])
        ];

        if ($this->application->user['autoOwned'] != 0) {
            $auto_businesses = [];

            $query = $this->application->db->query("SELECT busID, busName FROM business WHERE busLocation = {$this->application->user['location']} AND busAuto = 'yes'");
            while ($row = mysqli_fetch_assoc($query)) {
                $auto_businesses[] = $row;
            }

            $data[] = [ 'auto_businesses' => $auto_businesses ];
        }

        $businesses = [];
        $query = $this->application->db->query("SELECT busID, busName FROM business WHERE busLocation = {$this->application->user['location']} AND busAuto = 'no'");
        while ($row = mysqli_fetch_assoc($query)) {
            $businesses[] = $row;
        }
        $data[] = [ 'businesses' => $businesses ];

        $onedayago = time() - (24 * 60 * 60);
        $sevendaysago = $onedayago * 7;

        $top_fighters_today = [];
        $qd = $this->application->db->query("SELECT laAttacker, count(laAttacker) AS countValue FROM logsAttacks WHERE laTime > {$onedayago} AND laResult = 'won' GROUP BY laAttacker ORDER BY countValue DESC LIMIT 4");
        while ($rd = mysqli_fetch_assoc($qd)) {
            $top_fighters_today[] = [ 'countValue' => $rd['countValue'], 'attacker' => mafiosoLight($rd['laAttacker']) ];
        }
        $data[] = [ 'top_fighters_today' => $top_fighters_today ];

        $top_fighters_weekly = [];
        $qw = $this->application->db->query("SELECT laAttacker, count(laAttacker) AS countValue FROM logsAttacks WHERE laTime > {$sevendaysago} AND laResult = 'won' GROUP BY laAttacker ORDER BY countValue DESC LIMIT 4");
        while ($rw = mysqli_fetch_assoc($qw)) {
            $top_fighters_weekly[] = [ 'countValue' => $rw['countValue'], 'attacker' => mafiosoLight($rw['laAttacker']) ];
        }
        $data[] = [ 'top_fighters_weekly' => $top_fighters_weekly ];

        $qm2 = $this->application->db->query("SELECT magID, magText FROM newsMagazine WHERE magLocation = {$this->application->user['location']} AND magColumn = '2' AND magVisible = 'yes'");
        $count = mysqli_num_rows($qm2);
        if ($count) {
            while ($rm2 = mysqli_fetch_assoc($qm2)) {
                $news[] = mysql_tex_out($rm2['magText']);
            }

            $news = [];
            $data[] = [ 'magazine_column_two' => [ 'count' => $count, 'news' => $news ]];
        }

        $news = [];
        $qm1 = $this->application->db->query("SELECT magID, magText FROM newsMagazine WHERE magLocation = {$this->application->user['location']} AND magColumn = '1' AND magVisible = 'yes'");
        while ($rm1 = mysqli_fetch_assoc($qm1)) {
            $news[] = mysql_tex_out($rm1['magText']);
        }

        $data[] = [ 'magazine_column_one' => [ 'news' => $news ] ];

        return $data;
    }
}