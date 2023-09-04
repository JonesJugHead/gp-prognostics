<?php
// Api : vote for a pilot or a team
// Method : post
// Token : Yes (from localStorage to header)
// Path : /api/vote/?voteType=pilot&targetId=1

// Api : get number of votes for a pilot or a team
// Method : get
// Token : No
// Path : /api/vote/?voteType=pilot&targetId=1


// Check method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    postMethod();
    die();
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    getMethod();
    die();
} else {
    http_response_code(405); // method not allowed
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Method not allowed'));
}

function postMethod() {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/api/config.php');
    $pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname, $username, $password);

    // check if user is logged in
    $headers = apache_request_headers();
    $token = $headers['Authorization'];

    // check if user exists with this token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        http_response_code(401); // unauthorized
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'User not found'));
        exit();
    }

    // check if voteType is valid
    $voteType = $_GET['voteType'];
    if ($voteType != 'pilot' && $voteType != 'team') {
        http_response_code(400); // bad request
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Invalid voteType'));
        exit();
    }

    $targetId = $_GET['targetId'];

    // check if targetId exists in database
    $table = $voteType . 's';
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->bindParam(':id', $targetId);
    $stmt->execute();
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target) {
        http_response_code(400); // bad request
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Unknown targetId'));
        exit();
    }

    // add vote, or update vote if already exists
    $stmt = $pdo->prepare("SELECT * FROM votes WHERE userId = :userId AND type = :type");
    $stmt->bindParam(':userId', $user['id']);
    $stmt->bindParam(':type', $voteType);
    $stmt->execute();
    $vote = $stmt->fetch(PDO::FETCH_ASSOC);

    $voteStatus = 'created';

    // if vote exists, delete it
    if ($vote) {
        $stmt = $pdo->prepare("DELETE FROM votes WHERE id = :id");
        $stmt->bindParam(':id', $vote['id']);
        $stmt->execute();
        $voteStatus = 'updated';
    }
    
    // Create the vote, generate an id of 6 random char
    $voteId = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
    $stmt = $pdo->prepare("INSERT INTO votes (id, userId, targetId, type) VALUES (:id, :userId, :targetId, :type)");
    $stmt->bindParam(':id', $voteId);
    $stmt->bindParam(':userId', $user['id']);
    $stmt->bindParam(':targetId', $targetId);
    $stmt->bindParam(':type', $voteType);
    $stmt->execute();

    header('Content-Type: application/json');
    echo json_encode(
        array(
            'success' => true,
            'status' => $voteStatus,
            'voteId' => $voteId,
            'targetId' => $targetId,
            'voteType' => $voteType
            )
    );
    exit();
}

function getMethod(){
    require_once($_SERVER['DOCUMENT_ROOT'] . '/api/config.php');
    $pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname, $username, $password);

    // check if voteType is valid
    $voteType = $_GET['voteType'];
    if ($voteType != 'pilot' && $voteType != 'team') {
        http_response_code(400); // bad request
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Invalid voteType'));
        exit();
    }

    // check if targetId exists in database
    $targetId = $_GET['targetId'];

    $table = $voteType . 's';
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
    $stmt->bindParam(':id', $targetId);
    $stmt->execute();
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target) {
        http_response_code(400); // bad request
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Unknown targetId'));
        exit();
    }

    // get number of votes in votes table
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE targetId = :targetId AND type = :type");
    $stmt->bindParam(':targetId', $targetId);
    $stmt->bindParam(':type', $voteType);
    $stmt->execute();
    $votes = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(
        array(
            'success' => true,
            'targetId' => $targetId,
            'voteType' => $voteType,
            'votes' => $votes['COUNT(*)']
            )
    );
    exit();
}


?>