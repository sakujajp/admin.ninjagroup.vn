<?
set_time_limit(0);
$servername = 'localhost';
$username = 'admingroup';
$password = 'Cn4pSicpbrCYgLpI';
$dbname = 'admingroup';
class post {
    function __construct($id,$data_group_id,$id_group,$modtoken,$moduid,$id_nguon)
        {
            $this->id =$id;
            $this->data_group_id = $data_group_id;
            $this->id_group = $id_group;
            $this->modtoken = $modtoken;
            $this->moduid = $moduid;
            $this->id_nguon = $id_nguon;
        }
}

$posts = [];
$conn = new mysqli($servername, $username, $password, $dbname);
$result = $conn->query('SELECT * FROM `autopost` ORDER BY `time_add` ASC LIMIT 5');
if ($result->num_rows <= 0) { echo 'NO POSTS'; return;}
while($row = $result->fetch_assoc()) {
	$posts[] = new post(trim($row["id"]),trim($row["data_group_id"]),trim($row["id_group"]),trim($row["modtoken"]),trim($row["moduid"]),trim($row["id_nguon"]));
}
$conn->close();
echo json_encode($posts);
$checkphoto = checkphoto($posts);
$sqlupdate = '';
foreach($checkphoto as $stt=>$item){
    $id_nguon = $posts[$stt]->id_nguon;
    $text = '';
    $attachments = [];
    if($item->$id_nguon == null){
        $hihi = json_decode(file_get_contents('https://graph.facebook.com/'.$id_nguon.'?fields=message&access_token='.$posts[$stt]->modtoken));
        $text = $hihi->message;
    }else{
        if($item->$id_nguon->__typename == "Photo"){
                            $fields = array("published"=>"false");
                            $files = array();
                            $files["source"] = file_get_contents($item->$id_nguon->image->uri);
                            $curl2 = curl_init();
                            $boundary = uniqid();
                            $delimiter = '-------------' . $boundary;
                            $post_data = build_data_files($boundary, $fields, $files);
                            curl_setopt_array($curl2, array(
                              CURLOPT_URL => "https://graph.facebook.com/me/photos",
                              CURLOPT_RETURNTRANSFER => 1,
                              CURLOPT_MAXREDIRS => 10,
                              CURLOPT_TIMEOUT => 30,
                              //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                              CURLOPT_CUSTOMREQUEST => "POST",
                              CURLOPT_POST => 1,
                              CURLOPT_POSTFIELDS => $post_data,
                              CURLOPT_HTTPHEADER => array(
                                "authorization: OAuth " . $posts[$stt]->modtoken,
                                "Content-Type: multipart/form-data; boundary=" . $delimiter,
                                "Content-Length: " . strlen($post_data)
                              ),
                            ));
                            $response2 = json_decode(curl_exec($curl2));
                            curl_close($curl2);
                            $attachments = [array("photo"=>array("unified_stories_media_source"=>"CAMERA_ROLL","id"=>$response2->id))];
                            $text = $item->$id_nguon->message->text;
                        }
    }
    $input = array(
                            "source"=>"MOBILE",
                            "client_mutation_id"=>"1",
                            "message" => array("text"=>$text,"ranges"=>[]),
                            "actor_id"=>$posts[$stt]->moduid,
                            "is_group_linking_post"=>false,
                            "reshare_original_post"=>"SHARE_LINK_ONLY",
                            "attachments" => $attachments,
                            "is_tags_user_selected"=>false,
                            "is_boost_intended"=>false,
                            "is_throwback_post"=>"NOT_THROWBACK_POST",
                            "audiences"=>[array("wall"=>array("to_id"=>$posts[$stt]->id_group))]
                        );
                    $post = file_get_contents('https://graph.facebook.com/graphql?doc_id=1952867644831570&method=post&variables='.rawurlencode(json_encode(array('4'=>$input))).'&method=post&access_token='.$posts[$stt]->modtoken);
                    echo $post;
                   	$sqlupdate .= 'DELETE FROM `autopost` WHERE `id`="'.$posts[$stt]->id.'";';
                   	$sqlupdate .= 'UPDATE `data_group` SET `last_post`='.time().',`dapost`=`dapost`+1 WHERE `id`='.$posts[$stt]->data_group_id.';';
}
echo $sqlupdate;
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
function checkphoto($nodes){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $node) 
        {
            $curl_array[$i] = curl_init("https://graph.facebook.com/graphql?q=node(".$node->id_nguon.")%7B__typename%2Cimage%2Cmessage%7Btext%7D%7D&method=post&access_token=".$node->modtoken);
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