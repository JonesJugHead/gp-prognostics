<?php
// Api : Get all pilots votes
// Method : GET
// Token : No
// Path : /api/votes/pilots/?top=3

// Include database connection  
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/config.php');
$pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname, $username, $password);

// Get all pilots from pilot table 
$stmt = $pdo->prepare("SELECT * FROM pilots");
$stmt->execute();
$pilots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Now get all pilots team badsed on teamId in pilot object
foreach ($pilots as $key => $pilot) {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = :id");
    $stmt->bindParam(':id', $pilot['teamId']);
    $stmt->execute();
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    $pilots[$key]['team'] = $team['teamName'];
}

// Now get all pilots votes based on pilotId in pilot object, its in a votes table, the targetId is the pilotId and  type need to be "pilot"
foreach ($pilots as $key => $pilot) {
    $stmt = $pdo->prepare("SELECT * FROM votes WHERE targetId = :targetId AND type = 'pilot'");
    $stmt->bindParam(':targetId', $pilot['id']);
    $stmt->execute();
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pilots[$key]['votes'] = count($votes);
}

usort($pilots, function($a, $b) {
    return $b['votes'] <=> $a['votes'];
});

// Add props "rank" to each pilot
foreach ($pilots as $key => $pilot) {
    $pilots[$key]['rank'] = $key + 1;
}

// Check if limit is set in query string
if (isset($_GET['top']) AND is_numeric($_GET['top']) AND $_GET['top'] > 0) {
    $top = $_GET['top'];
    $pilots = array_slice($pilots, 0, $top);
}

header('Content-Type: application/json');
echo json_encode($pilots);

?>