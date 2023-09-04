<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/config.php');

$pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname, $username, $password);

if (!isset($_GET['code'])) {
  http_response_code(400);
  echo json_encode(
    array(
      "status" => "error",
      "message" => "No code provided"
    )
  );
  exit();
}

$code = $_GET['code'];

$payload = [
  'code' => $code,
  'client_id' => '1111955711665127535',
  'client_secret' => 'fhHNfcMuDa4VaItocUqC7ipqN_CAOKPB',
  'grant_type' => 'authorization_code',
  'redirect_uri' => 'https://gp-prognostics.fr/login/',
  'scope' => 'identify'
];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://discordapp.com/api/oauth2/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => http_build_query($payload),
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded',
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response, true);

if (!isset($response['access_token']) ){
  http_response_code(400);
  echo json_encode(
    array(
      "status" => "error",
      "message" => "Code has expired or is invalid",
    )
  );
  exit();
}

$token = $response['access_token'];

$discord_user_url = 'https://discordapp.com/api/users/@me';
$header = array('Authorization: Bearer ' . $token, 'Content-Type: application/x-www-form-urlencoded');

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $discord_user_url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => $header,
));

$response = curl_exec($curl);
curl_close($curl);

$response = json_decode($response, true);

$discord_id = $response['id'];
$discord_username = isSet($response['global_name']) ? $response['global_name'] : $response['username'];
$avatarUrl = "https://cdn.discordapp.com/avatars/" . $discord_id . "/" . $response['avatar'] . ".png";

if (empty($response['avatar'])) {
  $random = rand(1, 5);
  $avatarUrl = "https://cdn.discordapp.com/embed/avatars/" . $random . ".png";
}

$sql = 'SELECT * FROM users WHERE id = :id';
$query = $pdo->prepare($sql);
$query->execute([
    'id' => $discord_id
]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!isset($user['token'])){
  $token = hash('sha256', $discord_id . time() . bin2hex(random_bytes(64)));
  $sql = 'INSERT INTO users (id, token, username, avatarUrl) VALUES (:id, :token, :username, :avatarUrl)';
  $query = $pdo->prepare($sql);
  $query->execute([
      'id' => $discord_id,
      'token' => $token,
      'username' => $discord_username,
      'avatarUrl' => $avatarUrl
  ]);
  $new = true;
}
else {
  $token = $user['token'];
  $sql = 'UPDATE users SET username = :username, avatarUrl = :avatarUrl WHERE id = :id';
  $query = $pdo->prepare($sql);
  $query->execute([
      'id' => $discord_id,
      'username' => $discord_username,
      'avatarUrl' => $avatarUrl
  ]);
  $new = false;
}

echo json_encode(
  array(
    "status" => "success",
    "token" => $token,
    "new" => $new
  )
);
?>