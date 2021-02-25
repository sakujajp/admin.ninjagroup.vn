<?php
set_time_limit(0);
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');
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
function getname($nodes)
{
    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $rq) {
        $curl_array[$i] = curl_init("https://graph.facebook.com/graphql?q=node(" . $rq['uid'] . ")%7Bname%7D&method=post&locale=en_US");
        $headers = [
            'authorization: OAuth ' . $rq["token"],
            'user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
        ];
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
    foreach ($nodes as $i => $url) {
        $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]));
    }
    foreach ($nodes as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}
function addvip($nodes)
{
    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $rq) {
        $curl_array[$i] = curl_init("https://graph.facebook.com/graphql?doc_id=1701966309910312&variables=%7B%220%22%3A%7B%22source%22%3A%22MEMBER_LIST%22,%22member%22%3A%22" . $rq['uid'] . "%22,%22group_id%22%3A%22" . $rq['id_group'] . "%22,%22client_mutation_id%22%3A%221%22,%22actor_id%22%3A%22" . $rq['actor_id'] . "%22%7D%7D&method=post");
        $headers = [
            'authorization: OAuth ' . $rq["token"],
            'user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
        ];

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
    foreach ($nodes as $i => $url) {
        $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]));
    }
    foreach ($nodes as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}
class request
{
    function __construct($token, $data, $id_group)
    {
        $this->token = $token;
        $this->data = $data;
        $this->id_group = $id_group;
    }
}
function graph_multi($nodes)
{
    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $rq) {
        $curl_array[$i] = curl_init("https://graph.facebook.com/graphql");
        $headers = [
            'authorization: OAuth ' . $rq->token,
            'user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
        ];
        curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_array[$i], CURLOPT_POST, 1);
        curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $rq->data);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }
    $running = null;
    do {
        usleep(100);
        curl_multi_exec($mh, $running);
    } while ($running > 0);

    $res = array();
    foreach ($nodes as $i => $url) {
        $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]));
    }
    foreach ($nodes as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}
function delete_post($nodes)
{
    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $rq) {
        $curl_array[$i] = curl_init('https://graph.facebook.com/graphql?doc_id=1207752162665320&method=post&variables={"0":{"story_location":"GROUP","story_id":"' . $rq["id"] . '","client_mutation_id":"' . generateUUID(true) . '","actor_id":"' . $rq["tokenid"] . '"}}&access_token=' . $rq["token"]);
        $headers = [
            'user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
        ];
        curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($curl_array[$i], CURLOPT_POST ,1);
        //curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $rq->data);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }
    $running = null;
    do {
        usleep(100);
        curl_multi_exec($mh, $running);
    } while ($running > 0);

    $res = array();
    foreach ($nodes as $i => $url) {
        $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]));
    }
    foreach ($nodes as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}
function checkmember($nodes, $id_group)
{
    $mh = curl_multi_init();
    $curl_array = array();
    foreach ($nodes as $i => $token) {
        $curl_array[$i] = curl_init('https://graph.facebook.com/graphql?q=node(' . $id_group . ')%7Bviewer_join_state%7D&method=get&locale=en_US&access_token=' . $token);
        $headers = [
            'user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]'
        ];
        curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $headers);
        //	curl_setopt($curl_array[$i], CURLOPT_POST ,1);
        //	curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $vars);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }
    $running = null;
    do {
        usleep(100);
        curl_multi_exec($mh, $running);
    } while ($running > 0);
    $res = array();
    foreach ($nodes as $i => $url) {
        $res[$i] = json_decode(curl_multi_getcontent($curl_array[$i]));
    }
    foreach ($nodes as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    return $res;
}
function build_data_files($boundary, $fields, $files)
{
    $data = '';
    $eol = "\r\n";
    $delimiter = '-------------' . $boundary;
    foreach ($fields as $name => $content) {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol
            . $content . $eol;
    }
    foreach ($files as $name => $content) {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
            //. 'Content-Type: image/png'.$eol
            . 'Content-Transfer-Encoding: binary' . $eol;
        $data .= $eol;
        $data .= $content . $eol;
    }
    $data .= "--" . $delimiter . "--" . $eol;
    return $data;
}
$servername = 'localhost';
$username = 'admingroup';
$password = 'Cn4pSicpbrCYgLpI';
$dbname = 'admingroup';
$login = (isset($_SESSION['data']) && !empty($_SESSION['data'])) ? true : false;
$data = json_decode(file_get_contents('php://input'), true);
if(isset($data)){
    extract($data);
}
if(isset($_GET)){
    extract($_GET);
}
if(isset($_POST)){
    extract($_POST);
}

if (!isset($action)) {
    echo json_encode(false);
    exit;
}
$own = $_SESSION['data']->email;
//$own = 'dev1@ninjateam.vn';
if (!$login) {
    switch ($action) {
        case 'dangnhap':
            if ($email && $password) {
                $email = addslashes($email);
                $password = md5(addslashes($password));
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://unlock.ninjateam.vn/api/groupmanager/loginweb",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "email=" . $email . "&password=" . $password,
                    CURLOPT_HTTPHEADER => array(
                        "content-type: application/x-www-form-urlencoded"
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if (json_decode($response)->status == false) {
                    echo json_encode(['status' => false, 'message' => 'Đăng nhập thất bại']);
                } else {
                    $_SESSION['data'] = json_decode($response);
                    echo json_encode(['status' => true, 'message' => 'Xin chào ' . json_decode($response)->name]);
                }
                break;
            }
        default:
            echo json_encode(['status' => false, 'message' => 'Bạn chưa đăng nhập']);
    }
} else {
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_set_charset($conn,"utf8mb4");
    switch ($action) {
        case 'info':
            echo json_encode(['status' => true, 'data' => $_SESSION['data']]);
            break;
        case 'logout':
            session_unset();
            session_destroy();
            echo json_encode(['status' => true, 'message' => 'Đăng xuất thành công']);
            break;
        case 'autopost':
            if (isset($uid_nguon, $id_group, $num_like, $num_cmt, $token_scan_save)) {
                $uid_nguon = addslashes($uid_nguon);
                $id_group = addslashes($id_group);
                $num_like = addslashes($num_like);
                $num_cmt = addslashes($num_cmt);
                $token_scan_save = addslashes($token_scan_save);
                $check = $conn->query('SELECT * FROM `data_group` WHERE `id_group`="' . $id_group . '" AND `own`="' . $own . '"');
                if ($check->num_rows <= 0) {
                    echo json_encode(['status' => false, 'message' => 'Bạn không sở hữu group này']);
                } else {
                    $row = $check->fetch_array();
                    $array_type = ["Group", "Page", "User"];
                    $check_exist = json_decode(file_get_contents('https://graph.facebook.com/graphql?q=node(' . $uid_nguon . ')%7B__typename%7D&method=get&locale=en_US&access_token=' . $token_scan_save));
                    if ($check_exist->error) {
                        echo json_encode(['status' => false, 'message' => $check_exist->error->message]);
                    } else if ($check_exist->$uid_nguon == null) {
                        echo json_encode(['status' => false, 'message' => 'Không tồn tại id nguồn']);
                    } else {
                        if (in_array($check_exist->$uid_nguon->__typename, $array_type)) {
                            if ($check_exist->$uid_nguon->__typename == "Group") {
                                $check_mem = json_decode(file_get_contents('https://graph.facebook.com/graphql?q=node(' . $uid_nguon . ')%7Bviewer_join_state%7D&method=get&locale=en_US&access_token=' . $token_scan_save));
                                if ($check_mem->$uid_nguon->viewer_join_state != "MEMBER") {
                                    echo json_encode(['status' => false, 'message' => 'Token quét bài chưa gia nhóm nảy']);
                                } else {
                                    $update =  $conn->query('UPDATE `data_group` SET `nuoidata`="' . $uid_nguon . '|' . $check_exist->$uid_nguon->__typename . '|' . $num_like . '|' . $num_cmt . '",`token_scan_post`="' . $token_scan_save . '" WHERE `id_group`="' . $id_group . '" AND `own`="' . $own . '"');
                                }
                            } else {
                                $update =  $conn->query('UPDATE `data_group` SET `nuoidata`="' . $uid_nguon . '|' . $check_exist->$uid_nguon->__typename . '|' . $num_like . '|' . $num_cmt . '",`token_scan_post`="' . $token_scan_save . '" WHERE `id_group`="' . $id_group . '" AND `own`="' . $own . '"');
                            }
                            if ($update) {
                                echo json_encode(['status' => true, 'message' => 'Cài đặt thành công']);
                            } else {
                                echo json_encode(['status' => false, 'message' => 'Lỗi hệ thống #1']);
                            }
                        } else {
                            echo json_encode(['status' => false, 'message' => 'Chỉ hỗ trợ lấy bài post của trang cá nhân, profile hoặc group mà admin đã tham gia']);
                        }
                    }
                }
            }
            break;
        case 'xoauutien':
            if (isset($id)) {
                $id = addslashes($id);
                $check = $conn->query('SELECT * FROM `vip_member` WHERE `id`="' . $id . '" AND `own`="' . $own . '"');
                if ($check->num_rows > 0) {
                    $row = $check->fetch_array();
                    $check2 = $conn->query('SELECT * FROM `data_group` WHERE `id_group`="' . $row["id_group"] . '" AND `own`="' . $own . '"');
                    $row2 = $check2->fetch_array();
                    $variables = array(
                        "input" => array(
                            "source" => "MEMBER_LIST",
                            "member" => $row["uid"],
                            "group_id" => $row["id_group"],
                            "actor_id" => $row2["moduid"],
                            "client_mutation_id" => generateUUID(true)
                        )
                    );
                    $xoauutien = json_decode(file_get_contents('https://graph.facebook.com/graphql?doc_id=1445318515595623&variables=' . json_encode($variables) . '&method=post&access_token=' . $row2["modtoken"]));
                    // if ($xoauutien->data->group_untrust_member != null) {
                        $del = $conn->query('DELETE FROM `vip_member` WHERE `id`="' . $id . '" AND `own`="' . $own . '"');

                        if ($del) {
                            echo json_encode(['status' => true, 'message' => 'Xóa thành công', 'suc' => $xoauutien]);
                        } else {
                            echo json_encode(['status' => false, 'message' => 'Lỗi #2', 'data' => $xoauutien]);
                        }
                    // } else {
                        // echo json_encode(['status' => false, 'message' =>  'Lỗi #1', 'data' => $xoauutien]);
                    // }
                } else {
                    echo json_encode(['status' => false, 'message' => 'Bạn không sở hữu id này']);
                }
            }
            break;
        case 'postbai':
            if (isset($id_post, $id_group, $token_scan)) {
                try {
                    $check = $conn->query('SELECT * FROM `data_group` WHERE `id_group`="' . $id_group . '" AND `own`="' . $own . '"');
                    $row = $check->fetch_array();
                    if ($row["token_post"] == null) {
                        $tokenpost = $row["modtoken"];
                        $idtokenpost = $row["moduid"];
                    } else {
                        $rdtk = explode("|", $row["token_post"]);
                        $tokenpost = $rdtk[mt_rand(0, count($rdtk) - 1)];
                        $idtokenpost = json_decode(file_get_contents('https://graph.facebook.com/me?fields=id&access_token=' . $tokenpost))->id;
                    }
                    if (!$idtokenpost) {
                        echo json_encode(['status' => false, 'message' => 'Lỗi thử lại']);
                        return;
                    }
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://graph.facebook.com/graphql?q=node(" . $id_post . ")%7B__typename%2Cimage%2Cmessage%7Btext%7D%7D&method=post",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_POSTFIELDS => "",
                        CURLOPT_HTTPHEADER => array(
                            "authorization: OAuth " . $token_scan
                        ),
                    ));

                    $response = json_decode(curl_exec($curl));
                    curl_close($curl);
                    $attachments = [];
                    $text = '<3';
                    if ($response->$id_post != null) {
                        if ($response->$id_post->__typename == "Photo") {
                            $fields = array("published" => "false");
                            $files = array();
                            $files["source"] = file_get_contents($response->$id_post->image->uri);
                            $curl2 = curl_init();
                            $boundary = uniqid();
                            $delimiter = '-------------' . $boundary;
                            $post_data = build_data_files($boundary, $fields, $files);
                            curl_setopt_array($curl2, array(
                                CURLOPT_URL => "https://graph.facebook.com/me/photos",
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POST => 1,
                                CURLOPT_POSTFIELDS => $post_data,
                                CURLOPT_HTTPHEADER => array(
                                    "authorization: OAuth " . $tokenpost,
                                    "Content-Type: multipart/form-data; boundary=" . $delimiter,
                                    "Content-Length: " . strlen($post_data)
                                ),
                            ));
                            $response2 = json_decode(curl_exec($curl2));
                            curl_close($curl2);
                            $attachments = [array("photo" => array("unified_stories_media_source" => "CAMERA_ROLL", "id" => $response2->id))];
                            $text = $response->$id_post->message->text;
                        } else {
                            echo json_encode(['status' => false, 'message' => 'Chỉ hỗ trợ post bài viết hoặc ảnh']);
                            return;
                        }
                    } else {
                        $hihi = json_decode(file_get_contents('https://graph.facebook.com/' . $id_post . '?fields=message&access_token=' . $tokenpost));
                        $text = $hihi->message;
                    }
                    $input = array(
                        "source" => "MOBILE",
                        "client_mutation_id" => "1",
                        "message" => array("text" => $text, "ranges" => []),
                        "actor_id" => $idtokenpost,
                        "is_group_linking_post" => false,
                        "reshare_original_post" => "SHARE_LINK_ONLY",
                        "attachments" => $attachments,
                        "is_tags_user_selected" => false,
                        "is_boost_intended" => false,
                        "is_throwback_post" => "NOT_THROWBACK_POST",
                        "audiences" => [array("wall" => array("to_id" => $id_group))]
                    );
                    $post = file_get_contents('https://graph.facebook.com/graphql?doc_id=1952867644831570&method=post&variables=' . rawurlencode(json_encode(array('4' => $input))) . '&method=post&access_token=' . $tokenpost);
                    $done = json_decode($post);
                    if ($done->data->story_create != null) {
                        echo json_encode(['status' => true, 'message' => 'POST thành công']);
                    } else {

                        echo json_encode(['status' => false, 'message' => 'POST thất bại']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
                }
            }
            break;
        case 'update':
            $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
            $sqlupdate = '';
            $data = [];
            $nodes = [];
            while ($vip = $getall->fetch_object()) {
                $vip->nuoidata = explode("|", $vip->nuoidata)[0];
                $data[] = $vip;
                $nodes[] = new request(trim($vip->modtoken), "q=node(" . $vip->id_group . ")%20%7Bid,name,visibility,viewer_admin_type,group_member_profiles%7Bcount%7D,group_admin_profiles%7Bcount%7D,group_pending_member_profiles%7Bcount%7D,group_pending_stories%20%7Bcount%7D,group_stories%7Bcount%7D%7D&method=post&locale=en_US", $vip->id_group);
            }
            $update = graph_multi($nodes);
            foreach ($update as $stt => $item) {
                $idgroup = $nodes[$stt]->id_group;
                $response = $item->$idgroup;
                if ($item->error) {
                    $sqlupdate .= 'UPDATE `data_group` SET `status`=-1 WHERE `id_group` = "' . $idgroup . '";';
                } else {
                    $sqlupdate .= 'UPDATE `data_group` SET `name_group`="' . $response->name . '",`group_pending_members`="' . $response->group_pending_member_profiles->count . '",`group_pending_stories`="' . $response->group_pending_stories->count . '",`member_count`="' . $response->group_member_profiles->count . '",`group_admin_profiles`="' . $response->group_admin_profiles->count . '",`group_stories`="' . $response->group_stories->count . '" WHERE `id_group` = "' . $idgroup . '";';
                }
            }

            if (mysqli_multi_query($conn, $sqlupdate)) {
                echo json_encode(['status' => true, 'data' => $data]);
            } else {
                echo json_encode(['status' => false]);
            }
            break;
        case 'delpost':
            if ($id_group && $data) {
                $data = json_decode($data);
                $id_group =  addslashes($id_group);
                $hihi = '';
                $nodes = [];
                if (isset($next)) $hihi = '.after(' . urlencode($next) . ')';
                $getgroup = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '" AND `id_group`="' . $id_group . '"')->fetch_object();
                $getpost = json_decode(file_get_contents('https://graph.facebook.com/graphql?q=node(' . $id_group . ')%7Bgroup_feed.group_feed_ranking_setting(CHRONOLOGICAL).first(' . $data->max_post_del . ')' . $hihi . '%7Bpage_info%7Bhas_next_page,end_cursor%7D,nodes%7Bid,creation_time,title,message,can_viewer_delete,attached_story,attachments%7Btitle%7D,actors%7Bid,name%7D,feedback%7Breactors%7Bcount%7D,top_level_comments%7Bcount%7D%7D%7D%7D%7D&method=post&access_token=' . $getgroup->modtoken));
                foreach ($getpost->$id_group->group_feed->nodes as $stt => $item) {
                    if ($item->can_viewer_delete) {
                        $xoa = false;
                        $xoatheouid = explode(",", $data->from_uid_del);
                        $checkdate = (($data->mindate * 86400) + $item->creation_time) <= time();
                        if ($data->livestream_del && $checkdate) {
                            if ($item->title) {
                                if (strrpos($item->title->text, "live") >= 0) {
                                    $xoa = true;
                                }
                            }
                        }
                        if ($data->share_del && $checkdate) {
                            if ($item->title) {
                                if (strrpos($item->title->text, "share") >= 0) {
                                    $xoa = true;
                                } else {
                                    if ($item->attached_story != null) {
                                        $xoa = true;
                                    }
                                }
                            }
                        }
                        if (count($xoatheouid) > 0 && $checkdate) {
                            if ($item->actors !== null) {
                                if ($item->actors[0]) {
                                    foreach ($xoatheouid as $st => $uid) {
                                        if ($item->actors[0]->id == $uid) $xoa = true;
                                    }
                                }
                            }
                        }
                        if (count($data->keywords_del) > 0 && $checkdate) {
                            if ($item->message != null) {
                                foreach ($data->keywords_del as $st => $key) {
                                    if (strrpos($item->message->text, $key) !== false) $xoa = true;
                                }
                            }
                        }
                        if ($item->feedback != null && $checkdate) {
                            if ($data->minlike_del > 0) {
                                if ($item->feedback->reactors->count < $data->minlike_del) $xoa = true;
                            }
                            if ($data->mincmt_del > 0) {
                                if ($item->feedback->top_level_comments->count < $data->minlike_del) $xoa = true;
                            }
                        }
                        if ($item->attachments != null && $checkdate) {
                            if (strrpos($item->attachments[0]->title, "available")) $xoa = true;
                        }
                        if ($data->all) $xoa = true;
                        if ($xoa) {
                            $nodes[] = array("id" => $item->id, "token" => $getgroup->modtoken, "tokenid" => $getgroup->moduid, "id_group" => $id_group);
                        }
                    }
                }
                $kq = delete_post($nodes);
                $done = 0;
                foreach ($kq as $stt => $item) {
                    if ($item->data->story_delete != null) $done++;
                }
                echo json_encode(array('test'=>$nodes,'done' => $done, 'num_post' => count($getpost->$id_group->group_feed->nodes), 'has_next_page' => $getpost->$id_group->group_feed->page_info->has_next_page, 'next' => $getpost->$id_group->group_feed->page_info->end_cursor));
            }
            break;
        case 'createtask':
            if ($id && $data) {
                $id = addslashes($id);
                $id_group =  addslashes($id_group);
                $update = $conn->query('UPDATE `data_group` SET `autodata`="' . mysqli_real_escape_string($conn, $data) . '" WHERE  `own`="' . $own . '" AND `id`="' . $id . '"');
                if ($update) {
                    $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                    $data = [];
                    while ($vip = $getall->fetch_object()) {
                        array_push($data, $vip);
                    }
                    echo json_encode(['status' => true, 'message' => 'Cài đặt thành công', 'data' => $data]);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Lỗi']);
                }
            }
            break;
            //Sync data
        case 'sync':
            $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
            $getall2 = $conn->query('SELECT * FROM `vip_member` WHERE `own`="' . $own . '"');
            $data = [];
            $data2 = [];
            while ($vip = $getall->fetch_object()) {
                $vip->nuoidata = explode("|", $vip->nuoidata)[0];
                array_push($data, $vip);
            }
            while ($vip2 = $getall2->fetch_object()) {
                $vip2->time_exp = date('d/m/Y', $vip2->time_exp);
                $vip2->time_add = date('d/m/Y', $vip2->time_add);
                array_push($data2, $vip2);
            }
            echo json_encode(['status' => true, 'data' => $data, 'vip' => $data2]);
            break;
        case 'xoanguon':
            if ($id_group) {
                $id_group =  addslashes($id_group);
                $update = $conn->query('UPDATE `data_group` SET `nuoidata`= NULL WHERE  `own`="' . $own . '" AND `id_group`="' . $id_group . '"');
                if ($update) {
                    $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                    $data = [];
                    while ($vip = $getall->fetch_object()) {
                        array_push($data, $vip);
                    }
                    echo json_encode(['status' => true, 'message' => 'Xóa thành công', 'data' => $data]);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Lỗi']);
                }
            }
            break;
        case 'themtokendang':
            if ($id_group && $list_token) {
                $id_group =  addslashes($id_group);
                $token_p = [];
                foreach ($list_token as $stt => $token) {
                    $token = preg_replace('/\s+/', '', $token);
                    if ($token != "") {
                        $token_p[] = $token;
                    }
                }
                $token_post2 = [];
                $checkmember = checkmember($token_p, $id_group);
                foreach ($checkmember as $stt => $kq) {
                    if ($kq->$id_group->viewer_join_state == "MEMBER") {
                        $token_post2[] = $token_p[$stt];
                    }
                }
                $update = $conn->query('UPDATE `data_group` SET `token_post`="' . join("|", $token_post2) . '" WHERE  `own`="' . $own . '" AND `id_group`="' . $id_group . '"');
                if ($update) {
                    $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                    $data = [];
                    while ($vip = $getall->fetch_object()) {
                        array_push($data, $vip);
                    }
                    echo json_encode(['status' => true, 'message' => 'Cài đặt thành công', 'data' => $data]);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Lỗi']);
                }
            }
            break;
        case 'themvip':
            if ($uid && $data) {
                $uid = addslashes($uid);
                $exp_vip = addslashes($exp_vip);
                $data = json_decode($data);
                if ($data . length < 0) {
                    echo json_encode(['status' => false, 'message' => 'Lỗi dữ liệu']);
                } else {
                    $nodes = [];
                    foreach ($data as $index => $item) {
                        if ($item->active) {
                            $nodes[] = array('actor_id' => $item->moduid, 'token' => $item->modtoken, 'uid' => $uid, 'id_group' => $item->id_group);
                        } else {
                            $nodes[] = null;
                        }
                    }
                    $addvip = addvip($nodes);
                    $getname = getname($nodes);
                    $done = 0;
                    $fail = 0;
                    $datares = [];
                    foreach ($addvip as $index => $item) {
                        if ($item->data->group_trust_member != null) {
                            $done++;
                            $result = $conn->query('INSERT INTO `vip_member`(`uid`, `name`, `id_group`, `name_group`, `time_exp`, `time_add`,`own`) VALUES ("' . $uid . '","' . $getname[$index]->$uid->name . '","' . $data[$index]->id_group . '","' . $data[$index]->name . '","' . (time() + $exp_vip * 86400) . '","' . time() . '","' . $own . '")');
                            array_push($datares, array('id_group' => $data[$index]->id_group, 'name_group' => $data[$index]->modname, 'success' => true));
                        } else {
                            $fail++;
                            array_push($datares, array('id_group' => $data[$index]->id_group, 'name_group' => $data[$index]->modname, 'success' => false));
                        }
                    }
                    echo json_encode(['status' => true, 'message' => 'Đã ưu tiên thành công  ' . $done . ' group.', 'data' => $datares]);
                }
            }
            break;
        case 'xoagroup':
            $id = addslashes($id);
            $xoa = $conn->query('DELETE FROM `data_group` WHERE `own`="' . $own . '" AND `id_group`="' . $id . '"');
            if ($xoa) {
                $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                $data = [];
                while ($vip = $getall->fetch_object()) {
                    array_push($data, $vip);
                }
                echo json_encode(['status' => true, 'message' => 'Xóa thành công', 'data' => $data]);
            } else {
                echo json_encode(['status' => false, 'message' => 'Lỗi']);
            }
            break;
        case 'getgroup':
            $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
            $getall2 = $conn->query('SELECT * FROM `vip_member` WHERE `own`="' . $own . '"');
            $data = [];
            $data2 = [];
            while ($vip = $getall->fetch_object()) {
                $vip->nuoidata = explode("|", $vip->nuoidata)[0];
                array_push($data, $vip);
            }
            while ($vip2 = $getall2->fetch_object()) {
                $vip2->time_exp = date('d/m/Y', $vip2->time_exp);
                $vip2->time_add = date('d/m/Y', $vip2->time_add);
                array_push($data2, $vip2);
            }
            echo json_encode(['status' => false, 'data' => $data, 'vip' => $data2]);
            break;
        case 'themgroup':
            if ($idgroup && $token) {
                $checknumgrop = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                $data = [];
                while ($vip = $checknumgrop->fetch_object()) {
                    array_push($data, $vip);
                }
                if ($checknumgrop->num_rows >= $_SESSION['data']->numbergroup && $own != "dev1@ninjateam.vn") {
                    echo json_encode(['status' => false, 'message' => 'Bạn đã vượt quá giới hạn gói của mình', 'data' => $data]);
                } else {
                    $idgroup = addslashes($idgroup);
                    $token = addslashes($token);
                    $tokenid = addslashes($tokenid);
                    $visibility = addslashes($visibility);
                    $tokenname = addslashes($tokenname);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://graph.facebook.com/graphql?q=node(" . $idgroup . ")%20%7Bid,name,visibility,viewer_admin_type,group_member_profiles%7Bcount%7D,group_admin_profiles%7Bcount%7D,group_pending_member_profiles%7Bcount%7D,group_pending_stories%20%7Bcount%7D,group_stories%7Bcount%7D%7D&method=post&locale=en_US",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_POSTFIELDS => "",
                        CURLOPT_HTTPHEADER => array(
                            "authorization: OAuth " . $token,
                            "user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]"
                        ),
                    ));
                    $response = json_decode(curl_exec($curl));
                    curl_close($curl);
                    if ($response->$idgroup->viewer_admin_type == 'ADMIN' || $response->$idgroup->viewer_admin_type == 'MODERATOR') {
                        $check = $conn->query('SELECT COUNT(*) FROM `data_group` WHERE `own`="' . $own . '" AND `id_group`="' . $idgroup . '"');
                        if (mysqli_fetch_array($check)[0] > 0) {
                            $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                            $data = [];
                            while ($vip = $getall->fetch_object()) {
                                array_push($data, $vip);
                            }
                            echo json_encode(['status' => false, 'message' => 'Đã tồn tại group trong hệ thống', 'data' => $data]);
                        } else {
                            $result = $conn->query('INSERT INTO `data_group`(`id_group`, `name_group`, `group_pending_members`, `group_pending_stories`, `member_count`, `group_admin_profiles`, `group_stories`, `own`, `privacy`, `modtoken`, `moduid`, `modname`) VALUES ("' . $idgroup . '","' . $response->$idgroup->name . '","' . $response->$idgroup->group_pending_member_profiles->count . '","' . $response->$idgroup->group_pending_stories->count . '","' . $response->$idgroup->group_member_profiles->count . '","' . $response->$idgroup->group_admin_profiles->count . '","' . $response->$idgroup->group_stories->count . '","' . $own . '","' . $visibility . '","' . $token . '","' . $tokenid . '","' . $tokenname . '")');
                            
                            $getall = $conn->query('SELECT * FROM `data_group` WHERE `own`="' . $own . '"');
                            $data = [];
                            while ($vip = $getall->fetch_object()) {
                                array_push($data, $vip);
                            }
                            if ($result && $getall) {
                                echo json_encode(['status' => true, 'message' => 'Thành công : ' . $response->$idgroup->name, 'data' => $data]);
                            } else {
                                echo json_encode(['status' => false, 'message' => 'Lỗi #1','insert'=>'INSERT INTO `data_group`(`id_group`, `name_group`, `group_pending_members`, `group_pending_stories`, `member_count`, `group_admin_profiles`, `group_stories`, `own`, `privacy`, `modtoken`, `moduid`, `modname`) VALUES ("' . $idgroup . '","' . $response->$idgroup->name . '","' . $response->$idgroup->group_pending_member_profiles->count . '","' . $response->$idgroup->group_pending_stories->count . '","' . $response->$idgroup->group_member_profiles->count . '","' . $response->$idgroup->group_admin_profiles->count . '","' . $response->$idgroup->group_stories->count . '","' . $own . '","' . $visibility . '","' . $token . '","' . $tokenid . '","' . $tokenname . '")','getall'=>$getall]);
                            }
                        }
                    } else {
                        echo json_encode(['status' => false, 'message' => 'Lỗi #2']);
                    }
                }
            }
            break;
        case 'checkgroup':
            if ($token) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://graph.facebook.com/graphql?q=viewer()%7Bgroups_tab%7Bbadged_group_list.first(100)%7Bnodes%7Bid%2Cname%2Cvisibility%2Cviewer_admin_type%2Cgroup_member_profiles%7Bcount%7D%2Cgroup_pending_member_profiles%7Bcount%7D%2Cgroup_pending_stories%20%7Bcount%7D%7D%7D%7D%7D&method=get&locale=en_US",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "",
                    CURLOPT_HTTPHEADER => array(
                        "authorization: OAuth " . addslashes($token),
                        "user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]"
                    ),
                ));
                $response = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                curl_close($curl);
                $data = [];
                foreach ($response->viewer->groups_tab->badged_group_list->nodes as $item) {
                    if ($item->viewer_admin_type == 'ADMIN' || $item->viewer_admin_type == 'MODERATOR') array_push($data, $item);
                }
                echo json_encode(['status' => true, 'data' => $data]);
            }
            break;
        case 'check':
            if (isset($token,$list_id)) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://graph.facebook.com/graphql?q=nodes(".join(",",$list_id).")%20%7Bid%2Cname%2Cvisibility%2Cviewer_admin_type%2Cgroup_member_profiles%7Bcount%7D%2Cgroup_admin_profiles%7Bcount%7D%2Cgroup_pending_member_profiles%7Bcount%7D%2Cgroup_pending_stories%20%7Bcount%7D%2Cgroup_stories%7Bcount%7D%7D&access_token=" . $token,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "",
                    CURLOPT_HTTPHEADER => array(
                        "user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]"
                    ),
                ));
                $response = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                curl_close($curl);
                $data = [];
                foreach ($response as $item) {
                    if ($item->viewer_admin_type == 'ADMIN' || $item->viewer_admin_type == 'MODERATOR') array_push($data, $item);
                }
                echo json_encode(['status' => true, 'data' => $data]);
            }
            break;
        case 'changetokenadmin':
            if (isset($tokennew,$uidnew,$namenew,$id_group)) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://graph.facebook.com/graphql?q=node(".$id_group.")%20%7Bid%2Cname%2Cvisibility%2Cviewer_admin_type%2Cgroup_member_profiles%7Bcount%7D%2Cgroup_admin_profiles%7Bcount%7D%2Cgroup_pending_member_profiles%7Bcount%7D%2Cgroup_pending_stories%20%7Bcount%7D%2Cgroup_stories%7Bcount%7D%7D&access_token=" . $tokennew,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_POSTFIELDS => "",
                    CURLOPT_HTTPHEADER => array(
                        "user-agent: [FBAN/FB4A;FBAV/227.0.0.43.158;FBBV/160467801;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_US;FBRV/161970156;FBCR/AT&amp-T;FBMF/google;FBBD/google;FBPN/com.facebook.katana;FBDV/google Pixel 2;FBSV/5.1.1;FBOP/1;FBCA/x86:armeabi-v7a;]"
                    ),
                ));
                $response = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                curl_close($curl);
                $status = false;
                foreach($response as $item){
                    if ($item->viewer_admin_type == 'ADMIN' || $item->viewer_admin_type == 'MODERATOR') $status = true;
                }
                if($status){
                    $result = $conn->query('UPDATE `data_group` SET `modtoken`="'.$tokennew.'",`moduid`="'.$uidnew.'",`modname`="'.$namenew.'",`status`=1 WHERE `id_group`="'.$id_group.'"');
                    if($result){
                        echo json_encode(['status' => true]);
                    } else {
                        echo json_encode(['status' => false,'message'=>'Lỗi DATABASE']);
                    }
                } else {
                    echo json_encode(['status' => false,'message'=>'Bạn không phải kiểm duyệt hoặc quản trị của nhóm này','test'=>$response]);
                }
            }
            break;
        case 'lichsu':
            if (isset($id_group,$type)) {
                $getall = $conn->query('SELECT * FROM `log_duyet` WHERE `id_group`="' . addslashes($id_group) . '" AND `chedo`="' . addslashes($type).'"');
                $data = [];
                while ($vip = $getall->fetch_object()) {
                    $vip->time_duyet = date('H:i d/m/Y', $vip->time_duyet);
                    array_push($data, $vip);
                }
                echo json_encode(['status' => true, 'data' => $data]);
            }
            break;
        default:
            echo json_encode(['status' => false, 'message' => 'No Action']);
    }
    $conn->close();
}
