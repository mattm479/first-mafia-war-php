<?php

namespace Fmw;

class Visit extends BaseClass
{
    private int $total_visits;
    private bool $visit_valid = true;
    private int $item_chance;
    private array $data = [
        'error' => '',
        'reward' => 0,
        'option' => 0,
    ];
    private float $mods;
    private int|float $low;
    private int|float $medi;
    private int|float $high;
    private int|float $tops;
    private int|float $tin;
    private int|float $lit;
    private int|float $few;
    private int|float $dec;
    private int|float $man;
    private int|float $lot;
    private int|float $ton;
    /**
     * @var float|int|mixed
     */
    private mixed $cashonhand;
    private mixed $stole;

    public function __construct(Application $application, int $userId, string $action)
    {
        parent::__construct($application);

        $this->total_visits = self::calculate_visits($userId);
        $this->item_chance = self::calculate_item_chance($userId);

        self::validate($action);
        self::calculate_rewards();

        $this->data['reward'] = $this->item_chance;
        $this->data['user'] = $this->application->user;
    }

    public function casino(int $userId, int $visit_num): void
    {
        if ($this->application->user['location'] != 25) {
            $this->data['error'] = 'WRONG_LOCATION';
        } elseif ($visit_num) {
            if ($this->application->user['money'] < $this->man) {
                $this->data['error'] = 'NOT_ENOUGH_MONEY';
            } else {
                $this->application->db->query("UPDATE users SET visits = visits + 1, respect = respect - 1, money = money - {$this->man} WHERE userid = {$userId}");
                switch ($this->item_chance) {
                    case 1:
                        $this->data['stole'] = $this->stole;
                        if ($this->application->user['money'] < $this->stole) {
                            $this->application->db->query("UPDATE users SET money = 1, respect = respect - 1 WHERE userid = {$userId}");
                        } else {
                            $this->data['formatted_stole'] = number_format($this->stole);
                            $this->application->db->query("UPDATE users SET money = money - {$this->stole}, respect = respect - 1 WHERE userid = {$userId}");
                        }
                        break;
                    case 2:
                        $this->data['formatted_lot'] = number_format($this->lot);
                        $this->application->db->query("UPDATE users SET money = money + {$this->lot}, respect = respect - 1 WHERE userid = {$userId}");
                        break;
                    case 3:
                        break;
                    case 4:
                        $this->data['formatted_dec'] = number_format($this->dec);
                        $this->application->db->query("UPDATE users SET money = money + {$this->dec} WHERE userid = {$userId}");
                        break;
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                        $this->data['formatted_man'] = number_format($this->man);
                        $this->application->db->query("UPDATE users SET money = money + {$this->man} WHERE userid = {$userId}");
                        break;
                    case 9:
                    case 10:
                    case 11:
                        $this->data['formatted_lot'] = number_format($this->lot);
                        $this->application->db->query("UPDATE users SET money = money + {$this->lot} WHERE userid = {$userId}");
                        break;
                    case 12:
                        $this->data['formatted_ton'] = number_format($this->ton);
                        $this->application->db->query("UPDATE users SET money = money + {$this->ton} WHERE userid = {$userId}");
                        break;
                    case 13:
                        $this->data['formatted_ton'] = number_format($this->ton);
                        $this->application->db->query("UPDATE users SET moneyChecking = moneyChecking + {$this->ton} WHERE userid = {$userId}");
                        break;
                    case 14:
                    case 15:
                        $rnd = rand(1, 2);
                        $this->data['option'] = $rnd;
                        if ($rnd == 1) {
                            $this->data['item_info'] = itemInfo(66);
                            itemAdd(66, 1, 0, $userId, 0);
                        } else {
                            $this->data['item_info'] = itemInfo(67);
                            itemAdd(67, 1, 0, $userId, 0);
                        }
                        break;
                    case 16:
                        $rnd = rand(1, 2);
                        $this->data['option'] = $rnd;
                        if ($rnd == 1) {
                            $this->data['item_info'] = itemInfo(24);
                            itemAdd(24, 1, 0, $userId, 0);
                        } else {
                            $this->data['item_info'] = itemInfo(25);
                            itemAdd(25, 1, 0, $userId, 0);
                        }
                        break;
                    case 17:
                        $this->data['item_info'] = itemInfo(71);
                        itemAdd(71, 1, 0, $userId, 0);
                        break;
                    case 18:
                        $this->data['item_info'] = itemInfo(5);
                        itemAdd(5, 1, 0, $userId, 0);
                        break;
                    case 19:
                    case 20:
                        $rnd = rand(1, 7);
                        $this->data['option'] = $rnd;
                        if (in_array($rnd, [1, 2, 3])) {
                            $this->data['item_info'] = itemInfo(5);
                            itemAdd(5, 1, 0, $userId, 0);
                        } else if (in_array($rnd, [4, 5, 6])) {
                            $this->data['item_info'] = itemInfo(626);
                            itemAdd(626, 1, 2, $userId, 0);
                        } else {
                            $this->data['item_info'] = itemInfo(10);
                            itemAdd(10, 1, 0, $userId, 0);
                        }
                        break;
                }
            }
        } else {
            $this->data['bribe'] = number_format($this->man);
        }

        $this->render('visit/casino.html.twig', $this->data);
    }

    public function distillery(int $userId, int $visit_num): void
    {
        if ($this->application->user['location'] != 250) {
            $this->data['error'] = 'WRONG_LOCATION';
        } elseif ($this->application->user['money'] < $this->few) {
            $this->data['error'] = 'NOT_ENOUGH_MONEY';
            $this->data['formatted_dec'] = number_format($this->dec);
        } else {
            if ($visit_num) {
                $this->application->db->query("UPDATE users SET visits = visits + 1, money = money - {$this->few}, respect = respect - 1 WHERE userid = {$userId}");
                switch ($this->item_chance) {
                    case 1:
                        print '
                    <p>The room continues to spin though you have been lying in this ditch for at least an hour now based on the water seeping into your pores.</p>
                    <p>You lost an extra Respect and an extra Visit.</p>
                ';
                        $this->application->db->query("UPDATE users SET visits = visits + 1, respect = respect - 1 WHERE userid = {$userId}");
                        break;
                    case 2:
                        print '<p>You are starting to wonder if maybe a nice addiction to water might be better than this enjoyment of fine whiskey. Though ' . $this->medi . ' braver, your bleary eyes have annoyed the Don and you lose a point of Respect.</p>';
                        $this->application->db->query("UPDATE users SET respect = respect - 1, brave = brave + {$this->medi} WHERE userid = {$userId}");
                        break;
                    case 3:
                        print '
                    <p>You spend a wonderful afternoon examining the sour mash and enjoying the weather. Unfortunately, the whiskey is thin and they run out by the time you get back to the main building.</p>
                    <p>You gain nothing but the pleasant warm feeling in your belly.</p>
                ';
                        break;
                    case 4:
                        print '
                    <p>You had a few drinks, but also supper - and so it was largely a wash. You get a little gain, but not much.</p>
                    <p>You receive ' . number_format($this->mods) . ' Bravery.</p>
                ';
                        $this->application->db->query("UPDATE users SET brave = brave + {$this->mods} WHERE userid = {$userId}");
                        break;
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                        print '
                    <p>You learn a bit about the distilling process - and a bit about how to enjoy a good whiskey. Time to get back to the business of running a Family.</p>
                    <p>You pick up ' . number_format($this->low) . ' Bravery to help you engage your enemies.</p>
                ';
                        $this->application->db->query("UPDATE users SET brave = brave + {$this->low} WHERE userid = {$userId}");
                        break;
                    case 9:
                    case 10:
                    case 11:
                        print '
                    <p>You shiver, fleeing the cold arctic weather and into the warm embrace of cigars and whiskey samples. A few hours later you head back into the cold fortified and ready.</p>
                    <p>You gain ' . number_format($this->medi) . ' Bravery.</p>
                ';
                        $this->application->db->query("UPDATE users SET brave = brave + {$this->medi} WHERE userid = {$userId}");
                        break;
                    case 12:
                    case 13:
                        print '
                    <p>You carefully examine the entire distillery and learn much. A few vats here and there and you think you could probably do this yourself. Well, at least as a good Chicago bootlegger.</p>
                    <p>You gain ' . number_format($this->high) . ' Bravery with the plan.</p>
                ';
                        $this->application->db->query("UPDATE users SET brave = brave + {$this->high} WHERE userid = {$userId}");
                        break;
                    case 14:
                    case 15:
                    case 16:
                        print '
                    <p>You have a wonderful couple drinks with owner. Over cigars you discuss the future of fine distilleries and sneak a little into your flask.</p>
                    <p>You gain ' . itemInfo(70) . '.</p>
                ';
                        itemAdd(70, 1, 0, $userId, 0);
                        break;
                    case 17:
                    case 18:
                        print '
                    <p>Distilleries are wonderful places to fill ones flask. You have a little more luck than last time, and get away with more than before.</p>
                    <p>You gain a ' . itemInfo(17) . '.</p>
                ';
                        itemAdd(17, 1, 0, $userId, 0);
                        break;
                    case 19:
                    case 20:
                        $rnd = rand(1, 3);
                        if ($rnd == 1) {
                            print '
                        <p>Distilleries are wonderful places to fill ones flask. You have a little more luck than last time, and get away with more than before.</p>
                        <p>You gain a ' . itemInfo(17) . '.</p>
                    ';
                            itemAdd(17, 1, 0, $userId, 0);
                        } else if ($rnd == 2) {
                            print '
                        <p>You are in with the owners - good folks all around. In celebration of something you cannot now remember (who needs a reason?) they give you a flask bottle of their personal Rye.</p>
                        <p>You get ' . itemInfo(62) . '.
                    ';
                            itemAdd(62, 1, 0, $userId, 0);
                        } else {
                            print '
                        <p><em>I love you man!</em><br>Turning you look into the very bleary-eyes of an extreme drunk. The only way you can get them to go away and quit stinking up your clothes is to promise to use them for something later.</p>
                        <p>You gain access to a ' . itemInfo(54) . '.</p>
                    ';
                            itemAdd(54, 1, 0, $userId, 0);
                        }
                        break;
                }

                $this->application->db->query("UPDATE users SET brave = maxbrave WHERE brave > maxbrave");
            } else {
                $this->data['bribe'] = true;
            }
        }

        $this->render('visit/distillery.html.twig', $this->data);
    }

    public function don(int $userId, int $visit_num): void
    {
        if ($visit_num) {
            $used_energy = (round($this->application->user['maxenergy'] / 10) + 1);
            if ($this->application->user['energy'] < $used_energy) {
                $this->data['error'] = 'NOT ENOUGH ENERGY';
            } elseif ($this->application->user['money'] < $this->tin) {
                $this->data['error'] = 'NOT ENOUGH MONEY';
                $this->data['formatted_money'] = number_format($this->tin);
            } else {
                $this->application->db->query("UPDATE users SET visits = visits + 1, money = money - {$this->tin}, energy = energy - {$used_energy} WHERE userid = {$userId}");
                $this->data['reward'] = $this->item_chance;
                switch ($this->item_chance) {
                    case 1:
                        $this->data['stole'] = number_format($this->stole);
                        $this->application->db->query("UPDATE users SET respect = respect - 1, money = money - {$this->stole} WHERE userid = {$userId}");
                        break;
                    case 2:
                        $this->data['stole'] = number_format($this->stole);
                        $this->application->db->query("UPDATE users SET money = money - {$this->stole} WHERE userid = {$userId}");
                        itemAdd(105, 1, 0, $userId, 0);
                        break;
                    case 3:
                        break;
                    case 4:
                    case 5:
                    case 6:
                        if ($this->application->user['level'] < 100) {
                            itemAdd(11, 1, 0, $userId, 0);
                        }
                        $this->application->db->query("UPDATE users SET respect = respect + 1 WHERE userid = {$userId}");
                        break;
                    case 7:
                    case 8:
                    case 9:
                        $this->application->db->query("UPDATE users SET respect = respect + 1 WHERE userid = {$userId}");
                        break;
                    case 10:
                        $this->application->db->query("UPDATE users SET respect = respect + 2 WHERE userid = {$userId}");
                        break;
                    case 11:
                        $this->application->db->query("UPDATE users SET respect = respect + 3 WHERE userid = {$userId}");
                        break;
                    case 12:
                        $this->data['item_info'] = itemInfo(69);
                        itemAdd(69, 1, 0, $userId, 0);
                        break;
                    case 13:
                        $this->data['item_info'] = itemInfo(70);
                        itemAdd(70, 1, 0, $userId, 0);
                        break;
                    case 14:
                        $rnd = rand(1, 2);
                        $this->data['option'] = $rnd;
                        if ($rnd == 1) {
                            $this->data['item_info'] = itemInfo(12);
                            itemAdd(12, 1, 0, $userId, 0);
                        } else {
                            $this->data['item_info'] = itemInfo(27);
                            itemAdd(27, 1, 0, $userId, 0);
                        }
                        break;
                    case 15:
                        $rnd = rand(1, 2);
                        $this->data['option'] = $rnd;
                        if ($rnd == 1) {
                            $this->data['item_info'] = itemInfo(68);
                            itemAdd(68, 2, 0, $userId, 0);
                        } else {
                            $this->data['item_info'] = itemInfo(56);
                            itemAdd(56, 1, 0, $userId, 0);
                        }
                        break;
                    case 16:
                        itemAdd(15, 1, 0, $userId, 0);
                        break;
                    case 17:
                        itemAdd(17, 1, 0, $userId, 0);
                        break;
                    case 18:
                        $this->data['item_info'] = itemInfo(57);
                        itemAdd(57, 1, 0, $userId, 0);
                        break;
                    case 19:
                    case 20:
                        $rnd = rand(1, 3);
                        $this->data['option'] = $rnd;
                        if ($rnd == 1) {
                            $this->data['item_info'] = itemInfo(57);
                            itemAdd(57, 1, 0, $userId, 0);
                        } else if ($rnd == 2) {
                            $this->data['item_info'] = itemInfo(74);
                            itemAdd(74, 1, 0, $userId, 0);
                        } else {
                            $this->data['item_info'] = itemInfo(28);
                            itemAdd(28, 1, 0, $userId, 0);
                        }
                        break;
                }
            }
        } else {
            $this->data['bribes'] = number_format($this->tin);
        }

        $this->render('visit.html.twig', $this->data);
    }

    public function football(int $userId, int $visit_num): void
    {
        print '
        <h3>Visit the Lazio Stadio</h3>
        <div class=floatright> <img src=\'assets/images/photos/football.jpg\' height=190 width=300 alt=Stadium></div>
    ';

        if ($user['location'] != 10) {
            print '
            <p>You may only visit the Lazio Stadio while in Rome.</p>
            <p><a href=\'explore.php\'>Go to town</a> or <a href=\'home.php\'>home</a>.</p>
        ';

            $headers->endpage();
            exit;
        }

        if ($visit) {
            $usedbrave = (round($user['maxbrave'] / 10) + 1);
            if ($user['brave'] < $usedbrave) {
                print '
                <p>You are not Brave enough to properly enjoy the game.</p>
                <p><a href=\'explore.php\'>Return home</a></p>
            ';

                $headers->endpage();
                exit;
            }

            if ($user['money'] < $lit) {
                print '
                <p>Sorry, you must provide about $' . number_format($lit) . ' to get a seat worth your standing.</p>
                <p><a href=\'bank.php\'>Head to the bank</a> or <a href=\'crime.php\'>Do a little crime</a>.</p>
            ';

                $headers->endpage();
                exit;
            }

            $db->query("UPDATE users SET visits = visits + 1, money = money - {$lit}, brave = brave - {$usedbrave} WHERE userid = {$userId}");
            switch ($chance) {
                case 1:
                    print '
                    <p>The game was excellent! Unfortunately, you were not only on the losing side, but you were also rounded up with the other hooligans in the arrests.</p>
                    <p>You are dumped into prison to cool off after a sound beating.</p>
                ';
                    $db->query("UPDATE users SET jail = 30, hp = level, hjReason = 'Too much fun at the Lazio Stadio.' WHERE userid = {$userId}");
                    print '<p><a href=\'jail.php\'>Go to Jail</a></p>';

                    $headers->endpage();
                    exit;
                case 2:
                    print '
                    <p>Your team won! You manage to beat down a few supporters of the opposition and, like any good fight, you gain some experience by doing so though you also pick up some jail time.</p>
                    <p>You gain a little experience.</p>
                ';
                    $db->query("UPDATE users SET exp = exp + {$ton} WHERE userid = {$userId}");
                    $db->query("UPDATE users SET jail = 10, hp = hp - level, hjReason = 'Too much fun at the Lazio Stadio.' WHERE userid = {$userId}");
                    print '<p><a href=\'jail.php\'>Go to Jail</a></p>';

                    $headers->endpage();
                    exit;
                case 3:
                    print '
                    <p>The game ends in a tie against a mediocre team. Feeling unsatisfied, you head home early and manage to gain nothing but a wasted afternoon.</p>
                    <p>Blinking in the sunshine you gain nothing.</p>
                ';
                    break;
                case 4:
                case 5:
                    print '
                    <p>You manage to get in the owner\'s box for the game. Very nice. It turns out that this guy just runs the place. It is owned by various political factions. You plan for the future...</p>
                    <p>You grab a bottle of Asian Beer on your way out.</p>
                ';
                    itemAdd(9, 1, 0, $userId, 0);
                    break;
                case 6:
                case 7:
                    print '
                    <p>The win is incredible, and so is the beer you drink. You wake up hung over, but you have a little hair of the dog to keep you company.</p>
                    <p>You have a bottle of Stout Beer.</p>
                ';
                    itemAdd(18, 1, 0, $userId, 0);
                    break;
                case 8:
                case 9:
                    print '
                    <p>The win is incredible, and so is the beer you drink. You wake up hung over, but you have a little hair of the dog to keep you company.</p>
                    <p>You have a bottle of High Alcohol Beer.</p>
                ';
                    itemAdd(16, 1, 0, $userId, 0);
                    break;
                case 10:
                case 11:
                    $rnd = rand(1, 2);
                    if ($rnd == 1) {
                        print '
                        <p>You won, and bloodied a few noses in the celebration. As if that was not enough - you also met a rather nice Nurse who offers to help you later!</p>
                        <p>You meet a ' . itemInfo(12) . '.</p>
                    ';
                        itemAdd(12, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>You won, and the celebration in your area was intense! Your enjoyment of the day almost knows no bounds! You meet a few people at the bar afterward, and hit it off with a Police Officer.</p>
                        <p>You gain a contact, ' . itemInfo(27) . '.</p>
                    ';
                        itemAdd(27, 1, 0, $userId, 0);
                    }
                    break;
                case 12:
                case 13:
                case 14:
                    $rnd = rand(1, 2);
                    if ($rnd == 1) {
                        print '
                        <p>Your injuries landed you in the infirmary briefly. Luckily enough though, the Doctor likes the same team and offers to help you out of a jam next time you are in town.</p>
                        <p>You have met a ' . itemInfo(13) . ' who offers to help you out later.</p>
                    ';
                        itemAdd(13, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>Incredible! Your knowledge, and a little luck, have attracted the attention of the owners. You have been spending a lot of time in Rome and enjoying each game in various high-end boxes. One of the security guards is an off-duty Police Sergeant</p>
                        <p>The ' . itemInfo(26) . ' offers to help you out later.</p>
                    ';
                        itemAdd(26, 1, 0, $userId, 0);
                    }
                    break;
                case 15:
                case 16:
                    $rnd = rand(1, 2);
                    if ($rnd == 1) {
                        print '
                        <p>The fighting was getting a little out of hand. You found yourself standing over a dead body answering questions that you would rather not deal with. In the end, your story impressed the Detective and you were let off.</p>
                        <p>The ' . itemInfo(66) . ' even offers to help you out later.</p>
                    ';
                        itemAdd(66, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>The bone! That was the last thing you thought before passing out looking at your broken leg. The skin was split, blood was pouring and the bright white bone was shining in the sun the very way it should not. The surgeon who patched you up was imporessed by your other scars as well as this injury.</p>
                        <p>The ' . itemInfo(67) . ' offers to help you out later.</p>
                    ';
                        itemAdd(67, 1, 0, $userId, 0);
                    }
                    break;
                case 17:
                    print '
                    <p>The game was a going slowly and the rioting in your section was becoming boring, so you took advantage of that fancy box pass you managed. It was not much better there - still too quiet. However, on your way out, you do see a Consiglieri you know and in a spot of bother.</p>
                    <p>For helping out, the ' . itemInfo(25) . ' offers to help you later.</p>
                ';
                    itemAdd(25, 1, 0, $userId, 0);
                    break;
                case 18:
                    print '
                    <p>The game was a going slowly and the rioting in your section was becoming boring, so you took advantage of that fancy box pass you managed. It was not much better there - still too quiet. However, on your way out, you do see a Chief of Medicine you know and in a spot of bother.</p>
                    <p>For helping out, the ' . itemInfo(24) . ' offers to help you later.</p>
                ';
                    itemAdd(24, 1, 0, $userId, 0);
                    break;
                case 19:
                case 20:
                    $rnd = rand(1, 2);
                    if ($rnd == 1) {
                        print '
                        <p>The game ended in a tie - but what a game! You head to a party after instead of the pub. You get there and the owner isn\'t serving food! However, there is a bowl of Beer Cheese Soup in the corner with some dry crackers. You try it... and wow.  Just wow.</p>
                        <p>You steal a little ' . itemInfo(65) . ' for the road.</p>
                    ';
                        itemAdd(65, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>Hanging around the bars you meet a guy down on his luck. He clearly supports your team and so you buy him a few rounds. As you walk away, you realize he has picked your pocket! You manage to grab him before he escapes (all the liquor in him helped) and he teaches you how to pull it off.</p>
                        <p>You gain a ' . itemInfo(92) . '.</p>
                    ';
                        itemAdd(92, 1, 0, $userId, 0);
                    }
                    break;
            }

            print '<p><a href=\'visit.php?action=football&visit=1\'>Go to another game</a> or <a href=\'explore.php\'>head back to town</a></p>';
        } else {
            print '
            <p>European Football. Nothing outside mass genocide is more exhausting.</p>
            <p>Even watching it takes bravery. If watched properly. If you go, you\'ll stand the entire time, shout, yell, and gesture in a variety of entertaining ways at the opposition. If it is a close game (and what football game is not?) a little extra curricular activity typically occurs.</p>
            <p>Are you Brave enough to go to a game? You will also need about $' . number_format($lit) . ' in cash for seating and snacks.</p>
            <p><a href=\'visit.php?action=football&visit=1\'>Head to a game</a> &nbsp;&middot;&nbsp; <a href=\'explore.php\'>No thanks, I\'ll head back to town.</a></p>
        ';
        }
    }

    public function plantation(int $userId, int $visit_num): void
    {
        print '
        <h3>Visit the Hacienda El Carmen Coffee Plantation</h3>
        <div class=floatright> <img src=\'assets/images/photos/coffeePlantation.jpg\' height=326 width=300 alt=Plantation></div>
    ';

        if ($user['location'] != 500) {
            print '
            <p>You may only visit the Plantation while in Caracas.</p>
            <p><a href=\'explore.php\'>Go to town</a> or <a href=\'home.php\'>home</a>.</p>
        ';

            $headers->endpage();
            exit;
        }

        if ($user['money'] < $few) {
            print '
            <p>You must have a little Respect and about $' . number_format($few) . ' to visit the Plantation.</p>
            <p><a href=\'explore.php\'>Head to town</a></p>
        ';

            $headers->endpage();
            exit;
        }

        if ($visit) {
            $usedhealth = (round($user['maxhp'] / 15) + 1);
            if ($user['hp'] < $usedhealth) {
                print '
                <p>You are not Healthy enough to climb the mountains and experience the Plantation.</p>
                <p><a href=\'explore.php\'>Return home</a></p>
            ';

                $headers->endpage();
                exit;
            }

            $db->query("UPDATE users SET visits = visits + 1, money = money - {$few}, hp = hp - {$usedhealth} WHERE userid = {$userId}");
            switch ($chance) {
                case 1:
                    print '
                    <p>Sections of the path are rather steep and you misjudge your step. You are getting a bit old afterall.</p>
                    <p>You fall quite a ways and hospitalize yourself and lose an extra Visit.</p>
                ';
                    $db->query("UPDATE users SET visits = visits + 1, hp = 1, hospital = 90, hjReason = 'Fell off a cliff' WHERE userid = {$userId}");
                    break;
                case 2:
                    print '<p>You have the shakes and bad ones. Too much coffee leaves you unable to properly speak and weakens you, though ' . $high . ' more energetic.</p>';
                    $db->query("UPDATE users SET hp = hp - {$usedhealth}, energy = energy + {$high} WHERE userid = {$userId}");
                    break;
                case 3:
                    print '
                    <p>You spend a wonderful afternoon in the bright sun and thin air examining the ripening beans. Unfortunately the coffee is thin and just serving chircory get back to the main building.</p>
                    <p>You gain nothing but the pleasant warm feeling in your belly.</p>
                ';
                    break;
                case 4:
                    print '
                    <p>You had a few cups of coffee, but also a huge breakfast - and so it was largely a wash. You get a little gain, but not much.</p>
                    <p>You receive ' . number_format($vlow) . ' Energy.</p>
                ';
                    $db->query("UPDATE users SET energy = energy + {$vlow} WHERE userid = {$userId}");
                    break;
                case 5:
                case 6:
                case 7:
                case 8:
                    print '
                    <p>You learn a bit about the growing process - and a bit about how to enjoy a good cup in the morning. Time to get back to the business of running a Family.</p>
                    <p>You pick up ' . number_format($medi) . ' Energy to help you engage your enemies.</p>
                ';
                    $db->query("UPDATE users SET energy = energy + {$medi} WHERE userid = {$userId}");
                    break;
                case 9:
                case 10:
                case 11:
                    print '
                    <p>The thin air makes it hard to deal with all the Mafia around here. You get yourself a coffee press. More, more, more....</p>
                    <p>You gain ' . number_format($high) . ' Energy.</p>
                ';
                    $db->query("UPDATE users SET energy = energy + {$high} WHERE userid = {$userId}");
                    break;
                case 12:
                case 13:
                    print '
                    <p>You carefully examine every acre of the plantation and learn a great deal. A bit of land and you think you could probably do this yourself. The owners send you home with a book on growing tea and a couple cups of the stuff hoping you\'ll change your mind.</p>
                    <p>You gain two ' . itemInfo(68) . ' and a useless book.</p>
                ';
                    itemAdd(68, 2, 0, $userId, 0);
                    break;
                case 14:
                case 15:
                case 16:
                    print '
                    <p>You have a wonderful couple drinks with owner testing various flavours. Over biscotti you discuss the future of fine coffee.</p>
                    <p>You gain ' . itemInfo(56) . '.</p>
                ';
                    itemAdd(56, 1, 0, $userId, 0);
                    break;
                case 17:
                case 18:
                    print '
                    <p>Plantations are wonderful places to get coffee.  So fresh from the Family roasteries! You have a little more luck than last time, and get away with some great dark stuff.</p>
                    <p>You gain a ' . itemInfo(57) . '.</p>
                ';
                    itemAdd(57, 1, 0, $userId, 0);
                    break;
                case 19:
                case 20:
                    print '
                    <p>You are in with the owners - good folks all around. In celebration of something you cannot now remember (who needs a reason?) they teach you about a wonderful drink from the old country.</p>
                    <p>You get ' . itemInfo(64) . '.
                ';
                    itemAdd(64, 1, 0, $userId, 0);
                    break;
            }

            $db->query("UPDATE users SET energy = maxenergy WHERE energy > maxenergy");
            print '<p><a href=\'visit.php?action=plantation&visit=1\'>Get more coffee</a> or <a href=\'explore.php\'>head back to town</a></p>';
        } else {
            print '
            <p>Hacienda El Carmen Coffee Plantation is not huge but it is big enough. More importantly it is far from town and isolated. Just spending a little time in the thin air of the mountains is worth the trip.</p>
            <p>To visit the plantation you must hike up in to the mountains which does damage your constitution briefly. The time also reduces your visitations (as with the Don\'s Family) by one so plan your day carefully.</p>
            <p>The owners need some help to sell their product so they have occasional samples available for just a few bucks as well. Would you like to walk up to the Plantation?</p>
            <p><a href=\'visit.php?action=plantation&visit=1\'>Okay, give it a try</a> &nbsp;&middot;&nbsp; <a href=\'explore.php\'>No thanks, I\'ll head back to town.</a></p>
        ';
        }
    }

    public function track(int $userId, int $visit_num): void
    {
        print '
        <h3>Visit the New York Race Track</h3>
        <div class=floatright><img src=\'assets/images/photos/horseRacing.jpg\' height=319 width=250 alt=\'Horse Racing\'></div>
    ';

        if ($user['location'] != 50) {
            print '
            <p>You may only visit the Horse Track while in New York.</p>
            <p><a href=\'explore.php\'>Go to town</a> or <a href=\'home.php\'>home</a>.</p>
        ';

            $headers->endpage();
            exit;
        }

        if ($visit) {
            $usedenergy = (round($user['maxenergy'] / 10) + 1);
            if ($user['energy'] < $usedenergy) {
                print '
                <p>You do not have enough Energy to wander the stalls - and the bars.</p>
                <p><a href=\'explore.php\'>Return home and rest a while</a></p>
            ';

                $headers->endpage();
                exit;
            }

            if ($user['money'] < $few) {
                print '
                <p>You must have a little Respect and a bit more cash (not just the fee) to visit the Track in style.</p>
                <p><a href=\'explore.php\'>Head to town</a></p>
            ';

                $headers->endpage();
                exit;
            }

            $db->query("UPDATE users SET visits = visits + 1, money = money - {$few}, energy = energy - {$usedenergy} WHERE userid = {$userId}");
            switch ($chance) {
                case 1:
                    print '<p>The fix is in, but like most folks with too much information, you shared it with a close friend - but were overheard. The race was fixed against you, and you lost big. You didn\'t lose face but you did lose a lot of cash.</p>';
                    if ($user['money'] < $stole) {
                        print '<p>You lost your entire bankroll and you\'re down to nothing in your wallet.</p>';
                        $db->query("UPDATE users SET money = 15 WHERE userid = {$userId}");
                    } else {
                        print '<p>You didn\'t lose your entire bankroll but you did lose $' . number_format($stole) . '.</p>';
                        $db->query("UPDATE users SET money = money - {$stole} WHERE userid = {$userId}");
                    }
                    break;
                case 2:
                    print '
                    <p>You won on an extremely long shot! Unfortunately that is exactly the sort of thing you are supposed to do more carefully. You celebrated too loudly and end up getting busted for it.</p>
                    <p>You get to spend some time in Jail, but you do keep most of the cash.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$dec}, jail = 20, hjReason = 'Making too much on the horses.' WHERE userid = {$userId}");

                    $headers->endpage();
                    exit;
                case 3:
                    print '
                    <p>You spend a great day at the track drinking and gambling and having a generally good time. You don\'t really have much success at the post, and didn\'t really commit to any fix.</p>
                    <p>You gain a little redness from the sun, and the whiskey.</p>
                ';
                    break;
                case 4:
                case 5:
                case 6:
                    print '
                    <p>You had a good time and learned a bit about the horses, jockeys, and most importantly the track wardens.  You made a few bucks and while it\'s time to get back to the business of running a Family, you wonder about the gains to be made at the track.</p>
                    <p>You pick up $' . number_format($dec) . ' cash in your gambling.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$dec} WHERE userid = {$userId}");
                    break;
                case 7:
                case 8:
                    print '
                    <p>The day is glorious. You learned even more about the inner workings of a racetrack, and won even when you hadn\'t shaved the odds in your favor! The money is good - you kept your bets even and attracted no undue attention. The bookies know you now and you\'re getting preferential treatment.</p>
                    <p>You gain $' . number_format($man) . ' cash to continue your plans.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$man} WHERE userid = {$userId}");
                    break;
                case 9:
                    print '
                    <p>You know how to balance risk and reward. You have found a good combination of scams, fixes, and luck. The money is good, the horses are energetic, and the day is glorious.</p>
                    <p>You win $' . number_format($lot) . ' in cash.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$lot} WHERE userid = {$userId}");
                    break;
                case 10:
                case 11:
                    $rnd = rand(1, 2);
                    if ($rnd == 1) {
                        print '
                        <p>There are many subtleties to a race and many people involved. The number of people here is incredible! With your charisma you meet many who are eager to help you, including a Nurse.</p>
                        <p>You gain another contact ' . itemInfo(12) . '</p>
                    ';
                        itemAdd(12, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>There are many subtleties to a race and many people involved. The number of people here is incredible! With your charisma you meet many who are eager to help you, including a Police Officer.</p>
                        <p>You gain another contact ' . itemInfo(27) . '</p>
                    ';
                        itemAdd(27, 1, 0, $userId, 0);
                    }
                    break;
                case 12:
                    $rnd = rand(1, 2);
                    if ($rnd == 1) {
                        print '
                        <p>There are many subtleties to a race and many people involved. The number of people here is incredible! With your charisma you meet many who are eager to help you, including a Doctor.</p>
                        <p>You gain another contact ' . itemInfo(13) . '</p>
                    ';
                        itemAdd(13, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>There are many subtleties to a race and many people involved. The number of people here is incredible! With your charisma you meet many who are eager to help you, including a Police Sergeant.</p>
                        <p>You gain another contact ' . itemInfo(26) . '</p>
                    ';
                        itemAdd(26, 1, 0, $userId, 0);
                    }
                    break;
                case 13:
                    print '
                    <p>You are walking the stalls and chatting with the people when you bump into the stall drunk. Bleary eyed he curses you and stumbles away. After he departs you spot his flask in the grass where he fell. Does whisky kill germs?</p>
                    <p>You grab the ' . itemInfo(70) . '.</p>
                ';
                    itemAdd(70, 1, 0, $userId, 0);
                    break;
                case 14:
                case 15:
                    print '
                    <p>You spend your time placing a few bets, but you have begun to turn your attention to the people. There is much to learn here and many who are eager to help out with crime. This fellow, for a few bucks, will stand lookout for you.</p>
                    <p>You take the ' . itemInfo(51) . ' up on their offer.</p>
                ';
                    itemAdd(51, 1, 0, $userId, 0);
                    break;
                case 16:
                case 17:
                    print '
                    <p>An old friend! You two haven\'t been drinking in far too long. As the day wears on, you begin to plan many capers together...</p>
                    <p>You pick up a ' . itemInfo(52) . '.</p>
                ';
                    itemAdd(52, 1, 0, $userId, 0);
                    break;
                case 18:
                    print '
                    <p>Your knowledge of horse racing is gaining with every visit. The people you meet gambling here may not be in the same class as the Casino, but you meed a very smart kid who offers you a paper.</p>
                    <p>You gain a ' . itemInfo(636) . ' to help you out.</p>
                ';
                    itemAdd(636, 1, 0, $userId, 0);
                    break;
                case 19:
                case 20:
                    $rnd = rand(1, 3);
                    if ($rnd == 1) {
                        print '
                        <p>An old friend! You two haven\'t been drinking in far too long. As the day wears on, you begin to plan many capers together...</p>
                        <p>You pick up a ' . itemInfo(52) . '.</p>
                    ';
                        itemAdd(52, 1, 0, $userId, 0);
                    } else if ($rnd == 2) {
                        print '
                        <p>You have a wonderful day at the track. On your way home, you stop for some gas at a little filling station in \'jersey. The greasemonkey in the garage just stares at you the entire time he fills your tank. As you pay him, he asks if "you\'re the guy". He takes your silence as approval and hands you a brown package with a little oil leaking through.</p>
                        <p>As you drive away, you open the package and see it is a ' . itemInfo(90) . '. Yikes.</p>
                    ';
                        itemAdd(90, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>You are on your way out but your car has a flat tire. As you\'re working the jack, a friendly face stops by and helps out. You put a couple bucks in his hand for his help and he looks at you. "I\'ve been looking for work a long time. Thank you. Anything you need, let me know."</p>
                        <p>The ' . itemInfo(632) . ' is yours for a couple days.</p>
                    ';
                        itemAdd(632, 1, 0, $userId, 0);
                    }
                    break;
            }

            print '<p><a href=\'visit.php?action=track&visit=1\'>Visit the Track again</a> or <a href=\'explore.php\'>head back to town</a></p>';
        } else {
            print '
            <p>Few places in the world are as chaotically beautiful as a racetrack. The smells, the excitement, the money - everything makes for a beautiful day even in the rainiest weather.</p>
            <p>Getting in the door is fairly cheap considering what you could win. You will need about $' . number_format($few) . ' to make the day worthwhile, a little cash for betting and entertaining and a tiny bit of energy. Are you ready?</p>
            <p><a href=\'visit.php?action=track&visit=1\'>Head on in!</a> &nbsp;&middot;&nbsp; <a href=\'explore.php\'>No thanks, I\'ll head back to town.</a></p>
        ';
        }
    }

    public function meigs_field(int $userId, int $visit_num): void
    {
        print '<h3>Visit Merrill C. Meigs Field</h3>';

        if ($user['location'] != 100) {
            print '
            <p>You may only visit Meigs Field while in Chicago.</p>
            <p><a href=\'explore.php\'>Go to town</a> or <a href=\'home.php\'>home</a>.</p>
        ';

            $headers->endpage();
            exit;
        }

        if ($visit) {
            if ($user['money'] < $dec) {
                print '
                <p>Sorry, you must have a little Respect as well as the bribe money to visit the Field.</p>
                <p><a href=\'explore.php\'>Head to town</a></p>
            ';

                $headers->endpage();
                exit;
            }

            $db->query("UPDATE users SET visits = visits + 1, money = money - {$dec}, respect = respect - 1 WHERE userid = {$userId}");
            switch ($chance) {
                case 1:
                    print '
                    <p>There are no police here - at least none that aren\'t well paid - but there are other mafia families. Your shipment has been bought out from under you. You lose a substantial amount of cash - and respect.</p>
                    <p>You lose a token of respect
                ';

                    if ($user['money'] < $stole) {
                        print ' and you lost your entire bankroll and you\'re down to nothing in your wallet.</p>';
                        $db->query("UPDATE users SET money = 22, respect = respect - 1 WHERE userid = {$userId}");
                    } else {
                        print ' you didn\'t lose your entire bankroll but you did lose $' . number_format($stole) . '.</p>';
                        $db->query("UPDATE users SET money = money - {$stole}, respect = respect - 1 WHERE userid = {$userId}");
                    }
                    break;
                case 2:
                    print '<p>Your shipment was stolen from under you and while you managed to cover it up, you still lost a ton of cash. You did gain two Tokens of Respect though from the ground crew for not killing them all in the cover up.</p>';
                    if ($user['money'] < $stole) {
                        print '<p>You lost your entire bankroll and you\'re down to nothing in your wallet.</p>';
                        $db->query("UPDATE users SET money = 31, respect = respect + 2 WHERE userid = {$userId}");
                    } else {
                        print '<p>You didn\'t lose your entire bankroll but you did lose $' . number_format($stole) . '.</p>';
                        $db->query("UPDATE users SET money = money - {$stole}, respect = respect + 2 WHERE userid = {$userId}");
                    }
                    break;
                case 3:
                    print '<p>You charter a private jet to fly around the city of Chicago. You and your guests have a GREAT time, but other than a warm pleasant feeling, you don\'t gain much.</p>';
                    break;
                case 4:
                case 5:
                case 6:
                    print '
                    <p>You have a good run and your smuggling was a success. You get in and out quickly and cleanly.</p>
                    <p>You pick up $' . number_format($man) . ' cash in your smuggling.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$man} WHERE userid = {$userId}");
                    break;
                case 7:
                case 8:
                    print '
                    <p>You have been learning a bit about the smuggling business, and making some decent money at the same time.</p>
                    <p>You gain $' . number_format($lot) . ' cash to continue your plans.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$lot} WHERE userid = {$userId}");
                    break;
                case 9:
                    print '
                    <p>Nicely done. Your charter picked up some questionable cargo and brought it into Chicago without a hitch. You unload it to the local Families and walk away with a nice bankroll.</p>
                    <p>You gain $' . number_format($ton) . ' cash.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$ton} WHERE userid = {$userId}");
                    break;
                case 10:
                    print '
                    <p>You are working with the top people in the organization now. They still don\'t know your name, but you are learning theirs - and their habits.</p>
                    <p>Your smuggling brings in $' . number_format($man) . ' and you get an extra visit.</p>
                ';
                    $db->query("UPDATE users SET money = money + {$man}, visits = visits - 1 WHERE userid = {$userId}");
                    break;
                case 11:
                case 12:
                    print '
                    <p>The cash comes and the cash goes. Nothing major, but but this time someone left a couple cases of grenades. Finders keepers.</p>
                    <p>You gain ' . itemInfo(105) . ', or rather two of them.</p>
                ';
                    itemAdd(105, 2, 0, $userId, 0);
                    break;
                case 13:
                    print '
                    <p>The cash comes and the cash goes. Nothing major, but but this time someone left a fruit cup. Oh boy.</p>
                    <p>You gain a ' . itemInfo(69) . '.</p>
                ';
                    itemAdd(69, 1, 0, $userId, 0);
                    break;
                case 14:
                    print '
                    <p>The cash comes and the cash goes. Nice, this time someone left a bottle of wine behind. Cool!  Oh, well, it\'s just Vino da Tavola.</p>
                    <p>You gain a ' . itemInfo(63) . '.</p>
                ';
                    itemAdd(63, 1, 0, $userId, 0);
                    break;
                case 15:
                    print '
                    <p>The cash comes and the cash goes. Nice, this time someone left a small flask of whiskey behind. Cool.</p>
                    <p>You gain a ' . itemInfo(17) . '.</p>
                ';
                    itemAdd(17, 1, 0, $userId, 0);
                    break;
                case 16:
                    print '
                    <p>Now you are getting somewhere. While learning the process of basic smuggling (and the money that goes along with it), you pick up a few leftovers.</p>
                    <p>You gain a ' . itemInfo(15) . '.</p>
                ';
                    itemAdd(15, 1, 0, $userId, 0);
                    break;
                case 17:
                case 18:
                    print '
                    <p>You are getting better and better at moving through various official places undetected. You make some decent money on this haul but you quickly dump it for a smugglers pass when it is offered.</p>
                    <p>Get a ' . itemInfo(601) . ' and pick up a little cash.</p>
                ';
                    itemAdd(601, 1, 2, $userId, 0);
                    break;
                case 19:
                case 20:
                    $rnd = rand(1, 8);
                    if ($rnd == 1) {
                        $rnd = rand(1, 5);
                        print '<p>WOW. You manage to get some stuff in from over the border and through into Chicago without issue. Untouched, you\'re able to grab a case for yourself and no one cares.</p>';
                        switch ($rnd) {
                            case  1:
                                print '<p>Get a ' . itemInfo(314) . '!</p>';
                                itemAdd(314, 1, 0, $userId, 0);
                                break;
                            case 2:
                                print '<p>Get a ' . itemInfo(322) . '!</p>';
                                itemAdd(322, 1, 0, $userId, 0);
                                break;
                            case 3:
                                print '<p>Get a ' . itemInfo(330) . '!</p>';
                                itemAdd(330, 1, 0, $userId, 0);
                                break;
                            case 4:
                                print '<p>Get a ' . itemInfo(334) . '!</p>';
                                itemAdd(334, 1, 0, $userId, 0);
                                break;
                            case 5:
                                print '<p>Get a ' . itemInfo(310) . '!</p>';
                                itemAdd(310, 1, 0, $userId, 0);
                                break;
                        }
                    } else {
                        print '
                        <p>You are extremely pleased. You have managed to get close to the entire operation and made a new contact. You have met an Illinois Politician.</p>
                        <p>You obtain a ' . itemInfo(23) . '!!</p>
                    ';
                        itemAdd(23, 1, 0, $userId, 0);
                    }
                    break;
            }

            print '
            <p><a href=\'visit.php?action=meigsfield&visit=1\'>Visit Meigs Field again</a> or <a href=\'explore.php\'>head back to town</a></p><br>
            <div align=center><img src=\'assets/images/photos/meigsField.jpg\' height=267 width=500 alt=\'Meigs Field\'></div>
        ';
        } else {
            print '
            <div class=floatright><img src=\'assets/images/photos/mayorDaley.jpg\' height=213 width=167 alt=\'Mayor Daley\'></div>
            <p>Merrill C. Meigs Field is an airfield nearly in the lake right in Downtown Chicago. The main terminal was rebuilt in 1961 to help take care of the heavier traffic.</p>
            <p>Mayor Richard Daley dedicated the new passenger terminal building - though not likely while wearing this hat, but you never know.</p>
            <p>Meigs Field is an excellent place to bring goods in and out of Chicago. It\'s small, run by the politicians, and convenient. When you\'re in a hurry, there is no better place than Meigs Field.</p>
            <p>To use the airfield you will need to show some respect and make the appropriate bribes to various public officials. So in addition to about $' . moneyFormatter($dec) . ' it is going to cost you 1 token of Respect.</p>
            <p><a href=\'visit.php?action=meigsfield&visit=1\'>Fly Meigs Field</a> &nbsp;&middot;&nbsp; <a href=\'explore.php\'>No thanks, I\'ll head back to town.</a></p><br>
            <div align=center><img src=\'assets/images/photos/meigsField.jpg\' height=267 width=500 alt=\'Meigs Field\'></div>
        ';
        }
    }

    public function winery(int $userId, int $visit_num): void
    {
        print '
        <h3>Visit the Marsala Winery</h3>
        <div class=floatright><img src=\'assets/images/photos/winery.jpg\' height=454 width=300 alt=Winery></div>
    ';

        if ($user['location'] != 1) {
            print '
            <p>You may only visit the Winery while in Palermo.</p>
            <p><a href=\'explore.php\'>Go to town</a> or <a href=\'home.php\'>home</a>.</p>
        ';

            $headers->endpage();
            exit;
        }

        if ($visit) {
            $db->query("UPDATE users SET visits = visits + 1, respect = respect - 1 WHERE userid = {$userId}");
            switch ($chance) {
                case 1:
                    print '<p>You have been drunk before, but this was a new low. You broke many things that day. You also broke many fine wines.</p>';
                    $row = mysqli_fetch_assoc($db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 14"));
                    if ($row['inv_id']) {
                        itemDelete($row['inv_id'], 1, $userId);
                        print '<p>You replace some of the wine you broke with one of your own bottles - and the good stuff too!</p>';
                    } else {
                        $row = mysqli_fetch_assoc($db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 63"));
                        if ($row['inv_id']) {
                            itemDelete($row['inv_id'], 1, $userId);
                            print '<p>You replace some of the wine you broke with one of your own bottles - at least you sneak them the cheap stuff.</p>';
                        } else {
                            $db->query("UPDATE users SET respect = respect - 3 WHERE userid = {$userId}");
                            print '<p>You lose three Tokens of Respect.</p>';
                        }
                    }
                    break;
                case 2:
                    print '
                    <p>It started so well, and then, well, some say they have never seen you so drunk. Other say you were drunker at your cousins wedding. Whomever you believe, one thing is certain - that sting on your cheek was from the Dons Daughter, and the loss of Respect is yours.</p>
                    <p>You lose two Tokens of Respect, but you gain ' . number_format($lit) . ' Willpower to try again.</p>
                ';
                    $db->query("UPDATE users SET respect = respect - 2, will = will + {$lit} WHERE userid = {$userId}");
                    break;
                case 3:
                    print '
                    <p>You spend a wonderful afternoon examining the grapes and enjoying the weather. Unfortunately the wine is thin and they run out of the free stuff by the time you get back to the main building.</p>
                    <p>You gain nothing but a pleasant warm feeling.</p>
                ';
                    break;
                case 4:
                    print '
                    <p>Having spent a lazy sunny afternoon sipping wine and eating cheese you decide it is time to get back to the business of running a Family. You spent a little too much time relaxing.</p>
                    <p>You only pick up ' . number_format($tops) . ' Willpower to help you set things right.</p>
                ';
                    $db->query("UPDATE users SET will = will + {$tops} WHERE userid = {$userId}");
                    break;
                case 5:
                case 6:
                case 7:
                case 8:
                    print '
                    <p>Wonderful day. As the sun sets lightly over the vines, you feel ready to continue your plans.</p>
                    <p>You drink ' . number_format($tin) . ' Willpower.</p>
                ';
                    $db->query("UPDATE users SET will = will + {$tin} WHERE userid = {$userId}");
                    break;
                case 9:
                case 10:
                case 11:
                    print '
                    <p>There is something about the warmth coming up from the grapes as you tour the vineyard. The bright green of the leaves and the mist settling around the vines in the shadows brings many wonderful thoughts to the surface. All this and wine too.</p>
                    <p>You gain ' . number_format($lit) . ' Willpower.</p>
                ';
                    $db->query("UPDATE users SET will = will + {$lit} WHERE userid = {$userId}");
                    break;
                case 12:
                case 13:
                    print '
                    <p>The cool breeze blowing through the open door smells of smooth wines and assorted hordeorves. A few well enjoyed hours later and you stumble back into the sun ready to lead your Family to victory!</p>
                    <p>You gain ' . number_format($few) . ' Willpower to continue your day.</p>
                ';
                    $db->query("UPDATE users SET will = will + {$few} WHERE userid = {$userId}");
                    break;
                case 14:
                case 15:
                case 16:
                    print '
                    <p>You walk the grounds and inspect the vines. A few grapes here and a few grapes there and you think you could probably do this yourself. The Vino da Tavola Wine is not very good - but it is near the door.</p>
                    <p>You steal a bottle of ' . itemInfo(63) . ' on your way out.</p>
                ';
                    itemAdd(63, 1, 0, $userId, 0);
                    break;
                case 17:
                    print '
                    <p>Having spent a lovely morning touring the winery, you catch the owner and invite them to lunch! You discuss wine over a light lunch and discover that the actual owner is in Rome and Marsala is just the caretaker. The Sangiovese is a superior wine.</p>
                    <p>You gain one bottle of ' . itemInfo(14) . ' as a gift.</p>
                ';
                    itemAdd(14, 1, 0, $userId, 0);
                    break;
                case 18:
                    print '
                    <p>You have been studying wine and carrying around a little wine book to keep track of your favorites. Your knowledge on the tours is beginning to make even the tour guides impressed with your knowledge of the Sangiovese Wines.</p>
                    <p>The guides give you a bottle of ' . itemInfo(14) . '.</p>
                ';
                    itemAdd(14, 1, 0, $userId, 0);
                    break;
                case 19:
                case 20:
                    $rnd = rand(1, 3);
                    if ($rnd == 1) {
                        print '
                        <p>You have been studying wine and carrying around a little wine book to keep track of your favorites. Your knowledge on the tours is beginning to make even the tour guides impressed with your knowledge of the Sangiovese Wines.</p>
                        <p>The guides give you a bottle of ' . itemInfo(14) . '.</p>
                    ';
                        itemAdd(14, 1, 0, $userId, 0);
                    } else if ($rnd == 2) {
                        print '
                        <p>Your knowledge, and a little luck, have attracted the attention of the owner in Rome. They fly you to Rome and back (first class!) to get your thoughts on the business and to share some Grappa. You have a lovely time and are flushed the whole way back to Palermo.</p>
                        <p>On your return there is a ' . itemInfo(55) . ' gift wrapped in your home.</p>
                    ';
                        itemAdd(55, 1, 0, $userId, 0);
                    } else {
                        print '
                        <p>You have a wonderful drink with owner from Rome. Over dinner, you meet an Olympic Coach from the 1960 Olympiad. Very cool.</p>
                        <p>The ' . itemInfo(627) . ' offers to help you today and tomorrow.</p>
                    ';
                        itemAdd(627, 1, 2, $userId, 0);
                    }
                    break;
            }

            $db->query("UPDATE users SET will = maxwill WHERE will > maxwill");
            print '<p><a href=\'visit.php?action=winery&visit=1\'>Get more wine for another Token of Respect</a> or <a href=\'explore.php\'>head back to town</a></p>';
        } else {
            print '
            <p>The wines of Palermo Sicily are world renown for their mediocrity. However, they are very drinkable, and the Willpower they provide is quite valuable!</p>
            <p>To visit the Marsala Winery you must sacrifice a Token of Respect. It also takes time, and so reduces your visitations (as with the Don\'s Family) by one so plan your day carefully.</p>
            <p>Here at the Winery you will sample a few wines and increasing your Willpower slightly.  With a little luck you may even gain a bottle or two to take home!</p>
            <p>Would you like to visit the Winery?</p>
            <p><a href=\'visit.php?action=winery&visit=1\'>Okay, ask for permission</a> &nbsp;&middot;&nbsp; <a href=\'explore.php\'>No thanks, I\'ll head back to town.</a></p>
        ';
        }
    }

    private function validate(string $action): void
    {
        // Have enough Respect for the trip?
        if ($this->application->user['respect'] < 2) {
            $data['error'] = 'NOT_ENOUGH_RESPECT';
            $this->visit_valid = false;
        } elseif ($this->application->user['visits'] >= $this->total_visits) {
            $data['error'] = 'NO_MORE_VISITS';
            $this->visit_valid = false;
        }

        if (!$this->visit_valid) {
            $template = ($action == '' || $action == 'don') ? 'visit' : $action . '.html.twig';
            $this->render($template, $data);;
        }
    }

    private function calculate_visits(int $userId): int
    {
        $visits = 7;

        $qmat = $this->application->db->query("SELECT userid FROM coursesdone WHERE userid = {$userId} AND courseid = 24");
        if (mysqli_num_rows($qmat)) {
            $visits += 1;
        }

        $qinv = $this->application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 629");
        if (mysqli_num_rows($qinv)) {
            $visits += 1;
        }

        return $visits;
    }

    private function calculate_item_chance(int $userId): int
    {
        $base = 1;
        $bonus = 0;

        if ($this->application->user['level'] < 5) {
            $base = 4;
        } else if ($this->application->user['level'] < 10) {
            $base = 2;
        }

        $qdon = $this->application->db->query("SELECT userid FROM coursesdone WHERE userid = {$userId} AND courseid = 27");
        if (mysqli_num_rows($qdon)) {
            $base = 2;
        }

        $qinv = $this->application->db->query("SELECT inv_id FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 626");
        if (mysqli_num_rows($qinv)) {
            $bonus = 1;
            $base += 1;
        }

        return rand($base, 19) + $bonus;
    }

    private function calculate_rewards(): void
    {
        $this->mods = round(rand(1, 5) + ($this->application->user['level'] / 3));
        $this->low = rand(1, 2) * $this->mods;
        $this->medi = rand(1, 3) * $this->mods;
        $this->high = rand(2, 4) * $this->mods;
        $this->tops = rand(3, 6) * $this->mods;
        $this->tin = rand(16, 36) * $this->mods;
        $this->lit = rand(21, 63) * $this->mods;
        $this->few = rand(30, 90) * $this->mods;
        $this->dec = rand(150, 450) * $this->mods;
        $this->man = rand(300, 900) * $this->mods;
        $this->lot = rand(600, 1800) * $this->mods;
        $this->ton = rand(1200, 3600) * $this->mods;
        $this->cashonhand = $this->application->user['money'] - $this->few;
        $this->stole = max($this->dec, $this->man);
    }
}