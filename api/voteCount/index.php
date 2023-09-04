<?php
// Api : get number of total of vote for a team or a pilot
// Method : get
// Token : no
// Path : /api/voteCount/?voteType=pilot


// Check method
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    getMethod();
    die();
} else {
    http_response_code(405); // method not allowed
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Method not allowed'));
}

function getMethod() {
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

    // go on vote table, count all vote for the voteType,
    // in table is named "type" is pilot or team
    $query = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE type = :voteType');
    $query->execute(array(
        'voteType' => $voteType
    ));
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // return the result
    http_response_code(200); // ok
    header('Content-Type: application/json');
    echo json_encode(array('count' => $result['COUNT(*)']));
    exit();
    

}


?>