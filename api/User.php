﻿<?php
	//Just for User Module~	

	require_once('../include/functions.php');

	if( isset($_GET["action"]) &&  ("logout" == Str_filter($_GET['action'])) ){
		User_Logout();
		exit(0);
	}
	
	if(isset($_POST["action"]) &&  ("signup" == Str_filter($_POST['action'])) ){
		User_Signup();
		exit(0);
	}
	
	if(isset($_POST["action"]) && ("login" == Str_filter($_POST['action'])) ){
		User_Logon();
		exit(0);
	}
	
	if(isset($_POST["action"]) && ("changepass" == Str_filter($_POST['action'])) ){
		User_Changepass();
		exit(0);
	}
	if(isset($_GET["action"]) && ("getusername" == Str_filter($_GET['action'])) ){
			User_GetbyID();
		exit(0);
	}
	
	echo Return_Error(true,1000,"Para Error");
	
	//For User signup
	function User_Signup(){
		if(($username = isEmail(Str_filter($_POST['username']))) && ($password = Str_filter($_POST['password']))){
			if(null == Mongodb_Reader("todo_users",array("username" => $username),1)){
				$user =  array("username" => $username,"password" => md5($password),"user_id" => md5($username),"user_class" => 0,"signup_datetime" =>  Now(),"last_datetime" => "");
				try{
					Mongodb_Writter("todo_users",$user);
					
					$list_id = Create_Uid("默认列表");
					$list =  array("list_name" => "默认列表","list_id" => $list_id,"event_total" => 0,"list_class" => 0,"list_created_time" =>  Now(),"last_datetime" => Now());
					Add_relation_user_list($username,$list_id);
					Mongodb_Writter("todo_lists",$list);
					
					$res = Return_Error(false,0,"注册成功",array("user_id" => md5($username)));
				} catch(MongoException $e) {
					$res = Return_Error(true,2,"注册失败");
				}
			}else{
				$res = Return_Error(true,3,"用户名已经占用");
			}
		}else{
			$res = Return_Error(true,4,"提交的数据为空");
		}
		echo $res;
	}
	
	//For User logon
	function User_logon(){
		if(($username = Str_filter($_POST['username'])) && ($password = Str_filter($_POST['password'])) ){
			if($user = Mongodb_Reader("todo_users",array("username" => $username),1)){
				if(md5($password) == $user['password']){
					$accesstoken = AccessToken_Setter($username);
					Mongodb_Updater("todo_users",array("username" => $username),array("last_datetime" =>  Now()));
					$res = Return_Error(false,0,"登陆成功",array("token" => $accesstoken));
				}else{
					$res = Return_Error(true,6,"密码不正确");
				}
			}else{
				$res = Return_Error(true,5,"该用户不存在");
			}
		}else{
			$res = Return_Error(true,4,"提交的数据为空");
		}
		echo $res;
	}
	
	//For User logout
	function User_logout(){
		if($token = Str_filter($_GET['token'])){
			$res = AccessToken_Remover($token);
			if($res == 16){
				$res = Return_Error(true,8,"注销失败");
			}
			if($res == true){
				$res = Return_Error(false,0,"注销成功",array("username" => $res));
			}
			if($res == false){
				$res = Return_Error(true,7,"token无效或登录超时");
			}
		}else{
			$res = Return_Error(true,4,"提交的数据为空");
		}
		echo $res;
	}
	
	//For User Change Password
	function User_Changepass(){
		if( ($token = Str_filter($_POST['token'])) && ($old_password = Str_filter($_POST['old_password'])) && ($new_password = Str_filter($_POST['new_password'])) ){
			if($username = AccessToken_Getter($token)){
				if($user = Mongodb_Reader("todo_users",array("username" => $username),1)){				
					if(md5($old_password) == $user['password']){
						Mongodb_Updater("todo_users",array("username" => $username),array("password" => md5($new_password)));
						$res = Return_Error(false,0,"修改成功",array("username" => $username));
					}else{
						$res = Return_Error(true,6,"密码不正确");
					}
				}else{
					$res = Return_Error(true,5,"该用户不存在");
				}
			}else{
				$res = Return_Error(true,7,"token无效或登录超时");
			}
		}else{
			$res = Return_Error(true,4,"提交的数据为空");
		}
		echo $res;
	}
	
	function User_GetbyID(){
		if(($user_id = Str_filter($_GET['user_id'])) && ($token = Str_filter($_GET['token'])) ){
			if($username = AccessToken_Getter($token)){
				if($user = Mongodb_Reader("todo_users",array("user_id" => $user_id),1)){
					$res = Return_Error(false,0,"获取成功",array("username" => $user["username"]));
				}else{
					$res = Return_Error(true,5,"该用户不存在");
				}
			}else{
				$res = Return_Error(true,7,"token无效或登录超时");
			}
		}else{
			$res = Return_Error(true,4,"提交的数据为空");
		}
		echo $res;
	}
?>
