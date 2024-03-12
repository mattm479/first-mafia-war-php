<?php
require_once "globals.php";
global $db, $userId;

$attacking = $_SESSION['attacking'] ?? 0;

if ($attacking) {
    print '
        <br><br>
        <h2>You ran away from the fight!</h2>
        <p>Cowardice is not rewarded in the Mafia. You lost any gain you may have had from the fight, you lost Respect, and your own angry gang beats you to a bloody pulp and leaves you on the streets.</p>
    ';

    $db->query("UPDATE users SET respect = respect - 5, attacking = 0, hospital = 60, hjReason = 'Ran away from a fight.' WHERE userid = {$userId}");
}

session_unset();
session_destroy();
header('Location: index.php');
