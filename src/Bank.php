<?php

namespace Fmw;

class Bank extends BaseClass
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    public function clear(int $userId): void
    {
        $fee = $this->application->user['moneyInvest'] * 0.05;

        $this->application->db->query("UPDATE users SET moneyInvest = moneyInvest - {$fee}, moneyInvestFlag = 0 where userid = {$userId}");
        $this->render('bank/clear.html.twig', [ 'fee' => moneyFormatter($fee) ]);
    }

    public function transfer(int $userId, int $amount, string $from, string $to, string $invest): void
    {
        $data = [
            'error' => '',
            'free_transfer' => '',
        ];

        $message = self::validate_transfer($userId, $amount, $from, $to, $invest);
        if ($message != '') {
            $data['error'] = $message;
        } else {
            $ri = mysqli_fetch_assoc($this->application->db->query("SELECT inv_itemid FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 637"));
            $fee = 150;
            if ($ri != null && $ri['inv_itemid'] == 637) {
                $data['free_transfer'] = 'Free transfers while the banker is working for you.';
                $fee = 0;
            }

            $gain = $amount - $fee;
            $extra = '';
            if ($from == 'moneySavings') {
                $extra = "moneySavingsFlag = 1,";
            } elseif ($from == 'moneyTreasury') {
                $extra = "moneyTreasuryFlag = 3,";
            } elseif ($from == 'moneyInvest') {
                $extra = "moneyInvestFlag = 3,";
            }

            $this->application->db->query("UPDATE users SET {$extra} {$to} = {$to} + {$gain}, {$from} = {$from} - {$amount} where userid = {$userId}");
            $data['amount_transferred'] = moneyFormatter($amount - $fee);
        }

        $this->render('bank/transfer.html.twig', $data);
    }

    public function index(string $invest): void
    {
        $data = [
            'error' => '',
            'user' => $this->application->user,
            'invest' => $invest,
        ];

        $savingsFlag = "available";
        if ($this->application->user['moneySavingsFlag'] == 1) {
            $savingsFlag = '<span class=offline>tomorrow</span>';
        }

        $investmentFlag = 'available';
        if ($invest != 'yes') {
            $investmentFlag = '<span title=\'You must invest first\' class=offline>Unavailable</span>';
        } else {
            if ($this->application->user['moneyInvestFlag'] > 1) {
                $investmentFlag = '<span class=offline>' . $this->application->user['moneyInvestFlag'] . ' days</span>';
            } elseif ($this->application->user['moneyInvestFlag'] == 1) {
                $investmentFlag = '<span class=offline>tomorrow</span>';
            }
        }

        $treasuryFlag = 'available';
        if ($this->application->user['donatordays'] == 0) {
            $treasuryFlag = '<span title=\'Donators Only\' class=offline>Unavailable</span>';
        } else {
            if ($this->application->user['moneyTreasuryFlag'] > 1) {
                $treasuryFlag = '<span class=offline>' . $this->application->user['moneyTreasuryFlag'] . ' days</span>';
            } elseif ($this->application->user['moneyTreasuryFlag'] == 1) {
                $treasuryFlag = '<span class=offline>tomorrow</span>';
            }
        }

        $data['savings_flag'] = $savingsFlag;
        $data['investment_flag'] = $investmentFlag;
        $data['treasury_flag'] = $treasuryFlag;
        $data['accounts'] = moneyFormatter($this->application->user['moneyChecking'] + $this->application->user['moneySavings'] + $this->application->user['moneyInvest'] + $this->application->user['moneyTreasury']);
        $data['cash'] = moneyFormatter($this->application->user['money']);
        $data['checking'] = moneyFormatter($this->application->user['moneyChecking']);
        $data['savings'] = moneyFormatter($this->application->user['moneySavings']);
        $data['investment'] = moneyFormatter($this->application->user['moneyInvest']);
        $data['treasury'] = moneyFormatter($this->application->user['moneyTreasury']);

        $this->render('bank.html.twig', $data);
    }

    private function validate_transfer(int $userId, int $amount, string $from, string $to, string $invest): string {
        $message = '';

        if ($amount > $this->application->user[$from]) {
            $message = "
                <p>You need more money in that account to make the transfer.</p>
                <p><a href='bank.php'>Return to the bank</a></p>
            ";
        }

        if ($from == 'moneyInvest' && $this->application->user['moneyInvestFlag'] > 0) {
            $message = "<p>You cannot transfer money from your Investments until cash flow improves.</p>";
        }

        if ($from == 'moneyTreasury' && $this->application->user['moneyTreasuryFlag'] > 0) {
            $message = "<p>You cannot transfer money from that Account until the hold has been lifted.</p>";
        }

        if ($from == 'moneySavings' && $this->application->user['moneySavingsFlag'] > 0) {
            $message = "<p>You cannot transfer money from that Account until the hold has been lifted.</p>";
        }

        if ($amount < 0) {
            $message = "
                <p>The bank does not provide loans to the likes of you. For even trying they have berated you publicly and you lost Respect.</p>
                <p><a href='bank.php'>Return to the bank</a></p>
            ";

            $this->application->db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$userId}");
        }

        if ($amount < 151) {
            $message = "
                <p>That small an amount will not even cover the costs! Please transfer a larger amount.</p>
                <p><a href='bank.php'>Return to the bank</a></p>
            ";
        }

        if ($to == 'moneyInvest' && $invest != 'yes') {
            $message = "
                <p>You have not yet made your initial investment.</p>
                <p><a href='bank.php'>Return to the bank</a></p>
            ";
        }

        if ($to == 'moneyTreasury' && $this->application->user['donatordays'] == 0) {
            $message = "
                <p>You are not a Donator and therefore cannot invest in Treasury Bills.</p>
                <p><a href='bank.php'>Return to the bank</a></p>
            ";
        }

        return $message;
    }
}