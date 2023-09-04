<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/config.php');

$pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname, $username, $password);

$headers = apache_request_headers();
$token = $headers['Authorization'];

$stmt = $pdo->prepare("SELECT username, avatarUrl, id FROM users WHERE token = :token");
$stmt->bindParam(':token', $token);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// go on votes tables, find userId and type "pilot", get the targetId in this row

$stmt = $pdo->prepare("SELECT targetId FROM votes WHERE userId = :userId AND type = 'pilot'");
$stmt->bindParam(':userId', $user['id']);
$stmt->execute();
$targetId = $stmt->fetch(PDO::FETCH_ASSOC);

$pilotVoteId = null;
if ($targetId) {
    $pilotVoteId = $targetId['targetId'];
}
 
if ($user) {
    header('Content-Type: application/json');
    echo json_encode(
        array(
            'username' => $user['username'],
            "avatarUrl" => $user['avatarUrl'],
            "pilotVoteId" => $pilotVoteId
            )
    );
} else {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'User not found'));
    exit();
}
?>