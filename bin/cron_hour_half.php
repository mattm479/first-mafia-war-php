<?php
require_once "../vendor/autoload.php";

use Fmw\Application;

$application = new Application();
$db = $application->db;

require_once "../public/global_func.php";

// Random Associate Activity 
$rndAssociate = rand(1, 12);
$db->query("UPDATE inventory SET inv_userid = 965 WHERE inv_userid = 896");
$rgear = mysqli_fetch_assoc($db->query("SELECT inv_itemid, inv_id FROM inventory WHERE inv_equip = 'no' ORDER BY RAND() LIMIT 1"));
$rtar = mysqli_fetch_assoc($db->query("SELECT userid FROM users WHERE rankCat = 'Player' AND level > 1 AND level < 100 ORDER BY RAND() LIMIT 1"));

if (isset($rtar['userid'])) {
    logEvent($rtar['userid'], "Caesar Perez stopped by and left you one " . itemInfo($rgear['inv_itemid']) . ".");
    itemAdd($rgear['inv_itemid'], 1, 0, $rtar['userid'], 0);
    itemDelete($rgear['inv_id'], 1, 965, 0);
}

// Random Walter quotes
if ($rndAssociate == 1 || $rndAssociate == 2) {
    $rnd = rand(1, 25);
    switch ($rnd) {
        case 1:
            $post = "I just did an hour on the gym machine. I'm sweaty and I have to shit. Where's my bag, this workout is over.";
            break;
        case 2:
            $post = "What I'm trying to say is, I'm not a drinker - I'm a drunk.";
            break;
        case 3:
            $post = "Friendship is like peeing on yourself: everyone can see it, but only you get the warm feeling that it brings.";
            break;
        case 4:
            $post = "I turn the kitchen faucet on and the shower burns you, yes, I get it...No, I'm not gonna stop, I'm just saying yes, I get that concept.";
            break;
        case 5:
            $post = "You worry too much. Eat some bacon... What? No, I got no idea if it will make you feel better, I just made too much bacon.";
            break;
        case 6:
            $post = "Kefern will talk when he talks, relax. It ain't like he knows the cure for cancer and he just ain't spitting it out.";
            break;
        case 7:
            $post = "Here's a strawberry, sorry for farting near you...Hey! Either take the strawberry and stop bitching, or no strawberry, that's the deal.";
            break;
        case 8:
            $post = "Tennessee is nice. The first time I vomited was in Tennessee, I think.";
            break;
        case 9:
            $post = "You're not drunk if you can lie on the floor without holding on.";
            break;
        case 10:
            $post = "The worst thing you can be is a liar....Okay fine, yes, the worst thing you can be is a Nazi, but THEN, number two is liar. Nazi 1, Liar 2";
            break;
        case 11:
            $post = "They serve Jim Beam on airplanes. Tastes like piss. You wouldn't be able to tell the difference, because you drink shit. I don't.";
            break;
        case 12:
            $post = "The dog is not bored, it's a dog. It's not like he's waiting for me to give him a damn rubix cube. He's a damned dog.";
            break;
        case 13:
            $post = "Just pay the parking ticket. Don't be so outraged. You're not a freedom fighter in the civil rights movement. You double parked.";
            break;
        case 14:
            $post = "Your mother made a batch of meatballs last night. Some are for you, some are for me, but more are for me. Remember that. More. Me.";
            break;
        case 15:
            $post = "Don't touch the bacon, it's not done yet. You let me handle the bacon, and I'll let you handle... what ever it is you do. I guess nothing.";
            break;
        case 16:
            $post = "You need to flush the toilet more than once... No, YOU. YOU specifically need to. You know what, use a different toilet. This is my toilet.";
            break;
        case 17:
            $post = "The dog don't like you planting stuff there. It's his backyard. If you're the only one who shits in something, you own it. Remember that.";
            break;
        case 18:
            $post = "You know, sometimes it's nice having you around. But now ain't one of those times. Now gimmie the remote we're not watching this crap.";
            break;
        case 19:
            $post = "You don't know shit, and you're not shit. Don't take that the wrong way, that was meant to cheer you up.";
            break;
        case 20:
            $post = "Anytime someone sells you food in a sack, it's not a sack of food, it's a sack of shit.";
            break;
        case 21:
            $post = "A scar ain't 13 stitches. I'll introduce you to men with REAL scars, then we'll all laugh at your damn 13 stitches together.";
            break;
        case 22:
            $post = "You're like a tornado of crap right now. We'll talk again after your crap dies out over someone else's house.";
            break;
        case 23:
            $post = "Does anyone your age know how to comb their hair? It looks like two squirrels crawled on their head and started screwing.";
            break;
        case 24:
            $post = "I like the dog. If he can't eat it, or screw it, he pisses on it. I can get behind that.";
            break;
        case 25:
            $post = "That woman was sexy... Out of your league? Son. Let women figure out why they won't screw you, don't do it for them.";
            break;
    }

    newsPost(42, "{$post}");
    $db->query("UPDATE users SET trackActionTime = unix_timestamp() WHERE userid = 42");
    $rtar = mysqli_fetch_assoc($db->query("SELECT userid FROM users WHERE rankCat = 'Player' ORDER BY RAND() LIMIT 1"));
    logEvent($rtar['userid'], 'Walter got a little drunk at your place and left some beer behind.');
    $rad = rand(1, 3);
    switch ($rad) {
        case 1:
            itemAdd(18, 0, $rtar['userid'], 0, 1);
            break;
        case 2:
            itemAdd(16, 0, $rtar['userid'], 0, 1);
            break;
        case 3:
            itemAdd(9, 0, $rtar['userid'], 0, 1);
            break;
    }
}

// The Evil Midnight Bomber What Bombs at Midnight
if ($rndAssociate == 3 || $rndAssociate == 4) {
    $rnd = rand(1, 20);
    switch ($rnd) {
        case 1:
        case 20:
        case 10:
            $post = "Oh, heh-heh, that's just, I-BOOM, BABY, BOOM! I'm the Evil Midnight Bomber What Bombs at Midnight!";
            break;
        case 2:
            $post = "And so he says to me, 'You got legs, baby, you're everywhere. YOU'RE ALL OVER THE PLACE!'";
            break;
        case 3:
            $post = "And so he says, 'I don't like the cut of your jib.' And I go I says, IT'S THE ONLY JIB I GOT, BABY!";
            break;
        case 4:
            $post = "AN OBJECT AT REST, CANNOT BE STOPPED!";
            break;
        case 5:
            $post = "EAT MY SMOKE, COPPER! Aaaaaa-hahahahaha!";
            break;
        case 6:
            $post = "Excuse me, excuse me...and then I says, tell me I'm wrong! and he says, 'I can't, baby, 'CAUSE YOU'RE NOT!'";
            break;
        case 7:
            $post = "Hahahahaha! sixty seconds to midnight, sixty seconds to nowhere, baby!";
            break;
        case 8:
            $post = "He says to me, he says to me, 'Baby, I'm tired of workin' for the MAN!' I says, I says, WHY DON'T YOU BLOW HIM TO BITS?";
            break;
        case 9:
            $post = "He says to me, he says to me, 'You got STYLE, baby. But if you're going to be a real villain, you gotta get a gimmick.' And so I go I says YEAH, baby. A gimmick, that's it. High explosives. Aaaaaa-hahahahaha!";
            break;
        case 11:
            $post = "One of these days, milkshake! BOOM!";
            break;
        case 12:
            $post = "So he says to me, 'You gotta do something smart, baby. Something BIG! He says, 'You wanna be a super villain, right?' And I go yeah, baby, YEAH! YEAH! WHAT DO I GOTTA DO? He says, 'You got bombs, blow up the club, it's packed with mafioso, you'll go down in SUPER VILLAIN HISTORY!' And I go yeah, baby, 'cause I'm the Evil Midnight Bomber What Bombs at Midnight! Aaaaaa-hahahahaha!'";
            break;
        case 13:
            $post = "So he says to me, 'You wanna be a baaaaad guy?' And I say yeah, baby! I wanna be bad! I SAYS, SURF'S UP, SPACE PONIES! I'M MAKING GRAVY WITHOUT THE LUMPS! Aaaaaa-hahahahaha!";
            break;
        case 14:
            $post = "This could happen to you, baby. This could happen TO ANYBODY!";
            break;
        case 15:
            $post = "Yeah? Keep playing with fire, superpants! You don't know how much fire you're playing with! Aaaaaa-hahahahaha!";
            break;
        case 16:
            $post = "Yeah, baby! And you've only got twenty seconds before you all EAT CEILING!";
            break;
        case 17:
            $post = "You have all become victims of the Evil Midnight Bomber What Bombs... Hey! PAY ATTENTION!";
            break;
        case 18:
            $post = "You'll never prove a thing copper, I'm just a part time electrician. I... I... I... BAD IS GOOD, BABY! DOWN WITH GOVERNMENT!";
            break;
        case 19:
            $post = "I-I-I just, uh, I just uh, wanted to use the uh, heh, ah-AND SO HE SAYS, EVIL'S OKAY IN MY BOOK, WHAT ABOUT YOURS? AND I GO YEAH BABY YEAH! YEAH! I... I... uh, just wanted to, uh, wash my hands.";
            break;
    }

    newsPost(860, "{$post}");
    $db->query("UPDATE users SET trackActionTime = unix_timestamp() WHERE userid = 860");
    $rtar = mysqli_fetch_assoc($db->query("SELECT userid FROM users WHERE rankCat = 'Player' ORDER BY RAND() LIMIT 1"));

    logEvent($rtar['userid'], 'The Midnight Bomber blew you up and left something behind. I hope it does not go off early.');

    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (104, 0, {$rtar['userid']}, 0, 1, 'yes')");
    $db->query("UPDATE users SET hp = 1, hospital = hospital + 90 + jail, hjReason = 'You are a victim of the Evil Midnight Bomber What Bombs...<br>Hey! PAY ATTENTION!', jail = 0 WHERE userid = {$rtar['userid']}");
}

if ($rndAssociate == 5 || $rndAssociate == 6 || $rndAssociate == 7) {
    $rnd = rand(1, 7);
    switch ($rnd) {
        case 1:
            $post = "The measure of a life, after all, is not its duration, but its donation.";
            break;
        case 2:
            $post = "The taxpayer is required to substantiate on their own both legally and through documentation that they actually incurred the expenses they claimed they did. If you told your tax preparer you gave a large donation to new Mafioso and just pulled that number out of the air, the tax preparer isn't going to be able to help you. You have to be responsible for your claims. Remember Capone.";
            break;
        case 3:
            $post = "We were supposed to give a donation today, but none of the stuff is in here.";
            break;
        case 4:
            $post = "There is no such thing as a free lunch. What did this donation buy?";
            break;
        case 5:
            $post = "We get no staff money; we rely on volunteer donation.";
            break;
        case 6:
            $post = "Some people donate money, some gear. I donate time. There's never enough gear, but time I have.";
            break;
        case 7:
            $post = "I'm just so grateful to anyone who has donated items and everyone who has given support.";
            break;
    }

    newsPost(965, "{$post}");
    $db->query("UPDATE users SET trackActionTime = unix_timestamp() WHERE userid = 965");
}

if ($rndAssociate == 8 || $rndAssociate == 9) {
    // Green Grocer
    $rnd = rand(1, 2);
    switch ($rnd) {
        case 1:
            $post = "If you remember to vote, fruit may be your reward.";
            break;
        case 2:
            $post = "Would you like a bit of fruit?";
            break;
        case 3:
            $post = "I like fruit.";
            break;
    }

    newsPost(817, "{$post}");
    $db->query("UPDATE users SET trackActionTime=unix_timestamp() WHERE userid=817");
}

if ($rndAssociate == 10) {
    $rnd = rand(1, 7);
    switch ($rnd) {
        case 1:
            $post = "Be brave. Take risks. Nothing can substitute experience.";
            break;
        case 2:
            $post = "When we least expect it, life sets us a challenge to test our courage and willingness to change; at such a moment, there is no point in pretending that nothing has happened or in saying that we are not ready. The challenge will not wait. Life does not look back. A moment is more than enough time for us to decide whether or not to accept our destiny.";
            break;
        case 3:
            $post = "You drown not by falling into a river, but by staying submerged in it.";
            break;
        case 4:
            $post = "The two worst strategic mistakes to make are acting prematurely and letting an opportunity slip; to avoid this, the warrior treats each situation as if it were unique and never resorts to formulae, recipes or other people's opinions.";
            break;
        case 5:
            $post = "It's a family that's loaded with grudges and passion. We come from a long line of robbers and highwaymen in Italy, you know. Killers, even.";
            break;
        case 6:
            $post = "Don't listen to the wussy side of you when you make a decision. People gravitate towards being a wimp. Remove the wimp and you will do well.";
            break;
        case 7:
            $post = "Some people will always try and screw you. Don't waste your life planning for it. ...just be alert when your pants are down.";
            break;
    }

    newsPost(19, "{$post}");
    $db->query("UPDATE users SET trackActionTime = unix_timestamp() WHERE userid = 19");
}
