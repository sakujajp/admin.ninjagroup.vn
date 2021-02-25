<?php
set_time_limit(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$servername = 'localhost';
$username = 'admingroup';
$password = 'Cn4pSicpbrCYgLpI';
$dbname = 'admingroup';
class VipMem {
    function __construct($id, $uid, $id_group, $own)
        {
            $this->id = $id;
            $this->uid = $uid;
            $this->id_group = $id_group;
            $this->own = $own;
        }
}

$vipmems = [];
$conn = new mysqli($servername, $username, $password, $dbname);
$result = $conn->query('SELECT * FROM `vip_member` WHERE `time_exp`<"' . strtotime('00:00:00') . '"');
if ($result->num_rows <= 0) { echo 'NO VIP MEM'; return;}
while ($row = $result->fetch_assoc()) {
	$vipmems[] = new VipMem(trim($row["id"]), trim($row["uid"]), trim($row["id_group"]), trim($row["own"]));
}
// echo json_encode($vipmems);
// echo '<pre>'; print_r($vipmems); die;
foreach ($vipmems as $k_vipmems => $v_vipmems) {
	// if ($v_vipmems->own == 'dev8@ninjateam.vn') {
		$check2 = $conn->query('SELECT * FROM `data_group` WHERE `id_group`="' . $v_vipmems->id_group . '" AND `own`="' . $v_vipmems->own . '"');
		$row2 = $check2->fetch_array();
		$variables = array(
			"input" => array(
				"source" => "MEMBER_LIST",
				"member" => $v_vipmems->uid,
				"group_id" => $v_vipmems->id_group,
				"actor_id" => $row2["moduid"],
				"client_mutation_id" => generateUUID(true)
			)
		);
		$xoauutien = json_decode(file_get_contents('https://graph.facebook.com/graphql?doc_id=1445318515595623&variables=' . json_encode($variables) . '&method=post&access_token=' . $row2["modtoken"]));
		$del = $conn->query('DELETE FROM `vip_member` WHERE `id`="' . $v_vipmems->id . '" AND `own`="' . $v_vipmems->own . '"');
	// }
}
$conn->close();

function generateUUID($type)
{
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
    return $type ? $uuid : str_replace('-', '', $uuid);
}