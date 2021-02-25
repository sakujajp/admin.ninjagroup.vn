<?php
set_time_limit(0);
$servername = 'localhost';
$username = 'admingroup';
$password = 'Cn4pSicpbrCYgLpI';
$dbname = 'admingroup';
$start_time = time();
$conn = new mysqli($servername, $username, $password, $dbname);
class groups {
    function __construct($id_group,$modtoken,$moduid,$autodata,$own)
	{
		$this->id_group = $id_group;
		$this->modtoken = $modtoken;
		$this->moduid = $moduid;
		$this->autodata = json_decode($autodata);
	   $this->own = $own;
	}
}
class nodes {
    function __construct($url,$modtoken,$moduid,$id_group,$type)
	{
		$this->url = $url;
		$this->modtoken = $modtoken;
		$this->moduid = $moduid;
		$this->id_group = $id_group;
		$this->type=$type;
	}
}
$groups = [];
$result = $conn->query('SELECT * FROM `data_group` WHERE `last_duyet` + 180 <= '.time().' AND `status` = 1 AND `autodata` IS NOT NULL ORDER BY RAND() LIMIT 40');
if ($result->num_rows <= 0) { echo 'NO GROUPS'; return;}
while($row = $result->fetch_assoc()) {
    $groups[] = new groups(trim($row["id_group"]),trim($row["modtoken"]),trim($row["moduid"]),trim($row["autodata"]),trim($row["own"]));
}
$conn->close();
$loaddata = loaddata($groups);
$nodes = [];
$sqlupdate = '';
$group_share = ["1847853468851310","2067950260197845","181974892456424","243122829942455"];
foreach($loaddata as $index => $items){
    $id = $groups[$index]->id_group;
	if(isset($items->$id)){
		$item = $items->$id;
		$sqlupdate .= 'UPDATE `data_group` SET `last_duyet`='.time().' WHERE `id_group`='.$id.';';
		if(isset($item->group_pending_member_profiles)){
			if($item->group_pending_member_profiles->count > 0){
				foreach($item->group_pending_member_profiles->edges as $index2 => $item2){
					$duyet = true;
					if($groups[$index]->autodata[1]->gender == "hide women" && $item2->node->gender != "MALE") {
						$duyet = false;
					}
					if($groups[$index]->autodata[1]->gender == "hide men" && $item2->node->gender != "FEMALE"){
						$duyet = false;
					}
					if((int)$item2->node->registration_time > (time() - (int)$groups[$index]->autodata[1]->date_create * 86400)){
						$duyet = false;
					}
					$traloi = [];
					foreach($item2->membership_criteria_answers as $i=>$ques){
						if($ques->answer != "") {
							$traloi[] = $ques->question . '-' . $ques->answer;
						}
					}
					if(count($traloi) > 0){
						$sqlupdate .= 'INSERT INTO `log_duyet`(`id_group`, `chedo`, `id_duyet`, `noidung`, `time_duyet`) VALUES ("'.$id.'","member","'.$item2->node->id.'","'.join(",",$traloi).'",'.time().');';
					}
					if($groups[$index]->autodata[1]->traloi){
						$datl = true;
						foreach($item2->membership_criteria_answers as $i=>$ques){
							
							if($ques->answer == "") $datl = false;
						}
						if($datl == false) $duyet = false;
					}
					if($duyet){
						$nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1867275129985053&variables={"0":{"user_id":"'.$item2->node->id.'","source":"requests_queue","group_id":"'.$id.'","client_mutation_id":1,"actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"group_approve_pending_member");
					}else{
						$nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=2067036920004036&variables={"0":{"user_id":"'.$item2->node->id.'","source":"requests_queue","group_id":"'.$id.'","client_mutation_id":1,"actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"group_reject_pending_member");
					}
				}
			}
		}
    }
    if(isset($item->group_pending_stories)){
        if($item->group_pending_stories->count > 0){
            foreach($item->group_pending_stories->nodes as $index3 => $item3){
                
                $duyet = true;
                $change = false;
                if($groups[$index]->autodata[2]->livestream){
                    if(strpos(strtolower(@$item3->title->text),'live')!==false || strpos(strtolower(@$item3->attached_story->title->text),'live')!==false){
                        $duyet = false;
                        $change = true;
                        echo $id.'-'.$item3->id . ' XOA LIVESTREAM<br/>';
                    }
                }
                foreach($groups[$index]->autodata[2]->keywordx as $stt2 => $key2){
                    if(preg_match("/".strtolower($key2)."/i", strtolower(@$item3->message->text)) || preg_match("/".strtolower($key2)."/i", strtolower(@$item3->attached_story->message->text))){
                        $duyet = false;
                        $change = true;
                    }
                }
                foreach($groups[$index]->autodata[2]->keyword as $stt => $key){
                   $key= strtolower(str_replace("/","\/",preg_quote($key)));
                    if(preg_match("/".$key."/i", strtolower(@$item3->message->text)) || preg_match("/".$key."/i", strtolower(@$item3->attached_story->message->text))){
                         echo $id.'-'.$item3->id.'-'.@$item3->message->text . '- DUYET TU KHOA ' . $key .'<br/>';
                        $duyet = true;
                        $change = true;
                    }
                }
                if(@$groups[$index]->autodata[2]->duyet != "no_action" && @$item3->attachments[0]->title_with_entities->text == "This content isn't available right now") {
                    $duyet = false; 
                    $change = true;
                }
                if(in_array($id, $group_share) && strpos(strtolower(@$item3->title->text),'share')!==false && strpos(strtolower(@$item3->title->text),'first post') === false) {
                    $duyet = false;
                    $change = true;
                    echo $id.'-'.$item3->id.'-'.@$item3->message->text . '- XOA share ' . @$item3->title->text .'<br/>';
                }
                if($groups[$index]->autodata[2]->duyet != "no_action"){
                    if($duyet && $change){
                        $nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1217150925028112&variables={"0":{"trust_author":false,"story_id":"'.$item3->id.'","group_id":"'.$id.'","client_mutation_id":"1","actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"group_approve_pending_story");
                    }else if($duyet == false && $change){
                        $nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1207752162665320&variables={"0":{"story_location":"GROUP","story_id":"'.$item3->id.'","client_mutation_id":"2","actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"story_delete"); 
                    } else if($change == false) {
                        if($groups[$index]->autodata[2]->duyet == "duyet_all") {
                            $nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1217150925028112&variables={"0":{"trust_author":false,"story_id":"'.$item3->id.'","group_id":"'.$id.'","client_mutation_id":"1","actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"group_approve_pending_story");
                        } else if ($groups[$index]->autodata[2]->duyet == "del_all") {
                            $nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1207752162665320&variables={"0":{"story_location":"GROUP","story_id":"'.$item3->id.'","client_mutation_id":"2","actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"story_delete"); 
                        }
                    }
                } else {
                    if($duyet && $change){
                        $nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1217150925028112&variables={"0":{"trust_author":false,"story_id":"'.$item3->id.'","group_id":"'.$id.'","client_mutation_id":"1","actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"group_approve_pending_story");
                    }else if($duyet == false && $change){
                        $nodes[] = new nodes('https://graph.facebook.com/graphql?doc_id=1207752162665320&variables={"0":{"story_location":"GROUP","story_id":"'.$item3->id.'","client_mutation_id":"2","actor_id":"'.$groups[$index]->moduid.'"}}&method=post',$groups[$index]->modtoken,$groups[$index]->moduid,$id,"story_delete"); 
                    }
                }
            }
        }
    }
}
echo '-------------------------------<br>';
echo '-- Total request : '.count($nodes).'<br>';
echo json_encode($nodes).'<br>';
echo '-------------------------------<br>';
if (count($nodes) <= 100){
    $done_like = duyet($nodes);
    echo json_encode($done_like);
    foreach($done_like as $stt=>$kq){
        $kqa = json_decode($kq);
        $type = $nodes[$stt]->type;
        if(strpos($kq,'Error validating access token')!==false || strpos($kq,'Action Blocked')!==false){
           $sqlupdate .= 'UPDATE `data_group` SET `status` = -1 WHERE `id_group`='.$nodes[$stt]->id_group.';';
        }else if($kqa->data->$type != null){
            if($type == "group_approve_pending_member"){
                $sqlupdate .= 'UPDATE `data_group` SET `memdaduyet`=`memdaduyet`+1 WHERE `id_group`='.$nodes[$stt]->id_group.';';
            }else if($type == "group_reject_pending_member"){
                $sqlupdate .= 'UPDATE `data_group` SET `memdaxoa`=`memdaxoa`+1 WHERE `id_group`='.$nodes[$stt]->id_group.';';
            }else if($type == "group_approve_pending_story"){
                $sqlupdate .= 'UPDATE `data_group` SET `postdaduyet`=`postdaduyet`+1 WHERE `id_group`='.$nodes[$stt]->id_group.';';
            }else if($type == "story_delete"){
                $sqlupdate .= 'UPDATE `data_group` SET `postdaxoa`=`postdaxoa`+1 WHERE `id_group`='.$nodes[$stt]->id_group.';';
            }
        }
    }
    sleep(1);
}else{
    $chiareq = array_chunk($nodes, 100);
    foreach($chiareq as $stt=>$req){
        $done_like = duyet($req);
        echo json_encode($done_like);
        foreach($done_like as $stt2=>$kq){
            $kqa = json_decode($kq);
            $type = $req[$stt2]->type;
            if(strpos($kq,'Error validating access token')!==false || strpos($kq,'Action Blocked')!==false){
               $sqlupdate .= 'UPDATE `data_group` SET `status` = -1 WHERE `id_group`='.$req[$stt2]->id_group.';';
            }else if($kqa->data->$type != null){
                if($type == "group_approve_pending_member"){
                    $sqlupdate .= 'UPDATE `data_group` SET `memdaduyet` = `memdaduyet` + 1 WHERE `id_group`='.$req[$stt2]->id_group.';';
                }else if($type == "group_reject_pending_member"){
                    $sqlupdate .= 'UPDATE `data_group` SET `memdaxoa` = `memdaxoa` + 1 WHERE `id_group`='.$req[$stt2]->id_group.';';
                }else if($type == "group_approve_pending_story"){
                    $sqlupdate .= 'UPDATE `data_group` SET `postdaduyet` = `postdaduyet` + 1 WHERE id_group='.$req[$stt2]->id_group.';';
                }else if($type == "story_delete"){
                    $sqlupdate .= 'UPDATE `data_group` SET `postdaxoa` = `postdaxoa` + 1 WHERE id_group='.$req[$stt2]->id_group.';';
                }
            }
        }
        sleep(1);
    }
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
$stop_time = time();
echo 'DONE IN: ' .($stop_time - $start_time).' s';
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
function duyet($nodes){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $node) 
        {
            $curl_array[$i] = curl_init($node->url ."&access_token=".$node->modtoken);
            $ch = curl_init();
            $headers = [
                'user-agent: [FBAN/FB4A;FBAV/225.0.0.47.118;FBBV/158425880;FBDM/{density=1.5,width=720,height=1280};FBLC/vi_VN;FBRV/0;FBCR/Viettel Telecom;FBMF/samsung;FBBD/samsung;FBPN/com.facebook.katana;FBDV/SM-G965N;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
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
            $res[$i] = curl_multi_getcontent($curl_array[$i]);
        } 
        foreach($nodes as $i => $url){
            curl_multi_remove_handle($mh, $curl_array[$i]); 
        }
        curl_multi_close($mh);        
        return $res; 
}

function loaddata($nodes){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $gr) 
        {
            $data = [];
            $token = $gr->modtoken;
            if($gr->autodata[0]->duyetmem){
                $data[] = 'group_pending_member_profiles.first(10)%7Bcount%2Cedges%7Binviter%2Cmembership_criteria_answers%7Bquestion%2Canswer%7D%2Crequest_time%2Cnode%7Bid%2Cname%2Cgender%2Cregistration_time%2Ctimeline_context_items%7Bnodes%7Btitle%7D%7D%7D%7D%7D';
            }
            if($gr->autodata[0]->duyetpost){
                $data[] = 'group_pending_stories.first(10)%7Bcount%2Cnodes%7Bid%2Cactors%7Bid%2Cname%7D%2Ctitle.location()%2Cattachments%2Cmessage%7Btext%7D%2Ccreation_time%2Cattached_story%20%7Bactors%7Bid%2Cname%7D%2Cmessage%7Btext%7D%2Ctitle.location()%2Cattachments%7Bmedia%7Bis_live_streaming%7D%7D%7D%7D%7D';
            }
            $param = "q=group(".$gr->id_group.")%7B".join(",",$data)."%7D&method=get&locale=en_US";
            $curl_array[$i] = curl_init();
            $ch = curl_init();
            $headers = [
                'user-agent: [FBAN/FB4A;FBAV/225.0.0.47.118;FBBV/158425880;FBDM/{density=1.5,width=720,height=1280};FBLC/vi_VN;FBRV/0;FBCR/Viettel Telecom;FBMF/samsung;FBBD/samsung;FBPN/com.facebook.katana;FBDV/SM-G965N;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
                ];
            curl_setopt($curl_array[$i], CURLOPT_URL, "https://graph.facebook.com/graphql?".$param."&access_token=".$token);
            curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
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