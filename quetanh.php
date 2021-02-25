<?php
extract($_POST);
if(isset($uids,$token)){
	$nodes = [];
	$arr_uid = explode(",",$uids);
	if(count($arr_uid)>50){
		$chiauid = array_chunk($arr_uid, 50);
		foreach($chiauid as $stt=>$luid){
			$list_uid = join(",",$luid);
			$nodes[] = array('list_uid'=>$list_uid,'token'=>$token);
		}
		
	} else {
		$nodes[] = array('list_uid'=>$uids,'token'=>$token);
	}
	$getanh = getanh($nodes);
	echo json_encode($getanh);
}
function getanh($nodes){ 
        $mh = curl_multi_init(); 
        $curl_array = array(); 
        foreach($nodes as $i => $rq) 
        {
            $curl_array[$i] = curl_init("https://graph.facebook.com/graphql?locale=vi_VN&access_token=" . $rq["token"]);
			$param = 'q=nodes('.$rq["list_uid"].'){id,name,tagged_mediaset{media.first(10){nodes{id,automatic_accessibility_caption,image as imageLow}}}}';
			    $headers = [
			        'user-agent: [FBAN/FB4A;FBAV/198.0.0.53.101;FBBV/131501790;FBDM/{density=3.0,width=1080,height=1920};FBLC/vi_VN;FBRV/0;FBCR/Viettel;FBMF/Xiaomi;FBBD/xiaomi;FBPN/com.facebook.katana;FBDV/Redmi Note 4x;FBSV/6.0.1;FBOP/1;FBCA/armeabi-v7a:armeabi;]]'
			    ];
    
				curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl_array[$i], CURLOPT_POST ,1);
			curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $param);
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