<?
set_time_limit(0);
$servername = 'localhost';
$username = 'admingroup';
$password = 'Cn4pSicpbrCYgLpI';
$dbname = 'admingroup';
class group {
    function __construct($id,$id_group,$modtoken,$moduid,$nuoidata,$tokenpost)
        {
            $this->id = $id;
            $this->id_group = $id_group;
            $this->modtoken = $modtoken;
            $this->moduid = $moduid;
            $this->nuoidata = explode("|",$nuoidata);
            $this->tokenpost = explode("|",$tokenpost);
        }
}
$groups = [];
$conn = new mysqli($servername, $username, $password, $dbname);
$result = $conn->query('SELECT * FROM `data_group` WHERE `last_quet`+600 <= '.time().' AND `status`=1 AND `nuoidata` IS NOT NULL AND `token_scan_post` IS NOT NULL ORDER BY RAND() LIMIT 5');
if ($result->num_rows <= 0) { echo 'NO GROUPS'; return;}
while($row = $result->fetch_assoc()) {
    if($row["token_scan_post"] != null && $row["token_scan_post"] != '')
{	$groups[] = new group(trim($row["id"]),trim($row["id_group"]),trim($row["modtoken"]),trim($row["moduid"]),trim($row["nuoidata"]),trim($row["token_post"]));
}
}
$conn->close();
echo json_encode($groups);
$quet = quet($groups);
$sqlupdate = '';
foreach($quet as $index=>$item){
    $id_nguon = $groups[$index]->nuoidata[0];
	if (isset($item->$id_nguon)) {
		$idbaipost = '';
		$dapost = '';
		$checkfile = file_exists(__DIR__ . "/logquet/".$groups[$index]->id_group."_".$id_nguon.".txt");
		if($checkfile) $dapost = file_get_contents("logquet/".$groups[$index]->id_group."_".$id_nguon.".txt");
		if($groups[$index]->nuoidata[1] == "User" || $groups[$index]->nuoidata[1] == "Page"){
			foreach($item->$id_nguon->timeline_feed_units->nodes as $stt=>$a){
				if($a->feedback->reactors->count >= $groups[$index]->nuoidata[2] && $a->feedback->top_level_comments->count >= $groups[$index]->nuoidata[3] && strpos($dapost,$a->feedback->id)===false){
					$idbaipost = $a->feedback->id;
				}
			}
		}else{
			foreach($item->$id_nguon->group_feed->nodes as $stt=>$a){
				if($a->__typename == "Story" && $a->feedback->reactors->count >= $groups[$index]->nuoidata[2] && $a->feedback->top_level_comments->count >= $groups[$index]->nuoidata[3] && strpos($dapost,$a->feedback->id)===false){
					$idbaipost = $a->feedback->id;
				}
			}
		}
		if($idbaipost != ''){
			$f = fopen(__DIR__ . "/logquet/".$groups[$index]->id_group."_".$id_nguon.".txt",'a');
			$savetoken = $idbaipost."\r\n";
			fputs($f,$savetoken);
			fclose($f);
			$tokenposts =  $groups[$index]->tokenpost;
			$tknow = $tokenposts[mt_rand(0,(count($tokenposts) -1))];
			$tokenid = json_decode(file_get_contents('https://graph.facebook.com/me?&access_token='.$tknow));
			$sqlupdate .= 'INSERT INTO `autopost`(`data_group_id`, `id_group`, `modtoken`, `moduid`, `id_nguon`, `time_add`) VALUES ('.$groups[$index]->id.',"'.$groups[$index]->id_group.'","'.$tknow.'","'.$tokenid.'","'.explode(":",base64_decode($idbaipost))[1].'",'.time().');';
		}
		$sqlupdate .= 'UPDATE `data_group` SET `last_quet`='.time().' WHERE `id`='.$groups[$index]->id.';';
	}
}
//update sqlupdate token
if($sqlupdate!=""){
	/* sql */
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error){ die("Connection failed: " . $conn->connect_error); }
	if ($conn->multi_query($sqlupdate) === TRUE) {
		echo "<p class=\"notify_suc\">Update token records successfully</p>";
	} else {
		echo "<p class=\"notify_error\">Error: " . $sqlupdate . "<br>" . $conn->error."</p>";
	}
	$conn->close();
	/* sql */
}
function getid($nodes){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $token) 
        {
            $curl_array[$i] = curl_init('https://graph.facebook.com/me?access_token='.$token);
			$ch = curl_init();
			$headers = [
        		'user-agent: [FBAN/FB4A;FBAV/163.0.0.43.91;FBBV/96845997;FBDM/{density=3.0,width=1080,height=1920};FBLC/vi_VN;FBRV/96845997;FBCR/Viettel;FBMF/Xiaomi;FBBD/xiaomi;FBPN/com.facebook.katana;FBDV/Redmi Note 4x;FBSV/6.0.1;FBOP/1;FBCA/armeabi-v7a:armeabi;]'
        		];
		    curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl_array[$i], CURLOPT_POST ,1);
			// curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $param);
			curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true); 
			curl_multi_add_handle($mh, $curl_array[$i]);
        } 
		
		$running = null;
		do {
			usleep(100); 
			curl_multi_exec($mh, $running);
		} while ($running > 0);
		   
        $res = array(); 
        foreach($nodes as $i => $url)
        { 
            $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]))->id;
        } 
        foreach($nodes as $i => $url){
            curl_multi_remove_handle($mh, $curl_array[$i]); 
        }
        curl_multi_close($mh);        
        return $res; 
}
function quet($nodes){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $node) 
        {
            if($node->nuoidata[1] == "User" || $node->nuoidata[1] == "Page"){
            $curl_array[$i] = curl_init('https://graph.facebook.com/graphql?q=node('.$node->nuoidata[0].')%7Btimeline_feed_units.first(50)%7Bnodes%7Bfeedback%7Bid,reactors%7Bcount%7D,top_level_comments%7Bcount%7D%7D%7D%7D%7D&method=get&locale=en_US&access_token='.$node->modtoken);
            }else{
                $curl_array[$i] = curl_init('https://graph.facebook.com/graphql?q=node('.$node->nuoidata[0].')%7Bgroup_feed.first(50)%7Bnodes%7B__typename,feedback%7Bid,reactors%7Bcount%7D,top_level_comments%7Bcount%7D%7D%7D%7D%7D&method=get&locale=en_US&access_token='.$node->modtoken);
            }
			$ch = curl_init();
			$headers = [
        		'user-agent: [FBAN/FB4A;FBAV/163.0.0.43.91;FBBV/96845997;FBDM/{density=3.0,width=1080,height=1920};FBLC/vi_VN;FBRV/96845997;FBCR/Viettel;FBMF/Xiaomi;FBBD/xiaomi;FBPN/com.facebook.katana;FBDV/Redmi Note 4x;FBSV/6.0.1;FBOP/1;FBCA/armeabi-v7a:armeabi;]'
        		];
		    curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl_array[$i], CURLOPT_POST ,1);
			// curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $param);
			curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true); 
			curl_multi_add_handle($mh, $curl_array[$i]);
        } 
		
		$running = null;
		do {
			usleep(100); 
			curl_multi_exec($mh, $running);
		} while ($running > 0);
		   
        $res = array(); 
        foreach($nodes as $i => $url)
        { 
            $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]));
        } 
        foreach($nodes as $i => $url){
            curl_multi_remove_handle($mh, $curl_array[$i]); 
        }
        curl_multi_close($mh);        
        return $res; 
}