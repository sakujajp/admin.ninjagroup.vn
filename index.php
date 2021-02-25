<?php

session_start();
$login = (isset($_SESSION['data']) && !empty($_SESSION['data']))? true : false;
if($login){
    include_once 'index_login.php';
}else{
    extract($_POST);
        $email = addslashes($email);
	    $password = md5(addslashes($password));
	    if($email && $password){
	    	$ch = curl_init();
	    	//curl_setopt($ch, CURLOPT_URL,"http://unlock.ninjateam.vn/api/groupmanager/loginweb");
            curl_setopt($ch, CURLOPT_URL,"http://api.ninjateam.vn/api/groupmanager/loginweb");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,"email=".$email."&password=".$password);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close ($ch);
	        $hihi = json_decode($server_output);
	        if($hihi->status == true){
	            $_SESSION['data'] = $hihi;
	            header('Location: /');
	        }else{
	           echo '<div class="alert alert-danger"><strong>STOP!</strong>Sai tài khoản hoặc mật khẩu</div>';
	        }
	    }
?>
<!-- BẢO TRÌ HỆ THỐNG. VUI LÒNG QUAY LẠI SAU Latest compiled and minified CSS 
  -->

<html>
    <head>
        <title>Phần mềm quản lý group facebook - Ninja Group</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
    #logo:before{
        content:"";
        position:absolute;
        left: -665px;
        top: -460px;
        width:250px;
        height:15px;
        background-color:rgba(255,255,255,.5);
        -webkit-transform:rotate(-45deg);
        -moz-transform:rotate(-45deg);
        -ms-transform:rotate(-45deg);
        -o-transform:rotate(-45deg);
        transform:rotate(-45deg);
        -webkit-animation:searchLights 1.5s ease-out 1s infinite;
        -o-animation:searchLights 1.5s ease-out 1s infinite;
        animation:searchLights 1.5s ease-out 1s infinite
    }
    @-webkit-keyframes searchLights{0%{left:-100px;top:50px}to{left:250px;top:50px}}
    @-o-keyframes searchLights{0%{left:-100px;top:50px}to{left:250px;top:50px}}
    @-moz-keyframes searchLights{0%{left:-100px;top:50px}to{left:250px;top:50px}}
    @keyframes searchLights{0%{left:-100px;top:50px}to{left:250px;top:50px}}
</style>
    </head>
    <body style="background: #efefef;">
        
<div class="container">
    <div class="row text-center">
        <div id="logo" style="position: relative;width: 264px;height: 130px;margin: 0 auto;overflow: hidden;">
        <img src="https://www.phanmemninja.com/wp-content/uploads/2019/01/logo.png" height="130px"/>
        </div>
    </div>
<div class="row">
    <div class="col-lg-4"></div>
    <div class="col-lg-4" style="
    background: #FFF;
    padding: 20px;
    border-radius: 12px;
    box-shadow: #CCCC 2px 2px 10px;
"><form action="/" method="post">
    <div class="form-group text-center">
        <h4><b>ĐĂNG NHẬP HỆ THỐNG</b></h4>
        </div>
  <div class="form-group">
    <label for="email">Email:</label>
    <input type="email" name="email" class="form-control" id="email">
  </div>
  <div class="form-group">
    <label for="pwd">Mật khẩu:</label>
    <input type="password" name="password" class="form-control" id="pwd">
  </div>
   <div class="form-group text-center">
        <button type="submit" class="btn btn-success">Đăng nhập</button>
        </div>
  
</form></div>
    <div class="col-lg-4"></div>
</div>
</div>
    </body>
</html>

<? }?>