<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);
//
if(!(isset($_GET["resource"])&&$_GET["resource"]=="accesslevels")){
	authorize();
}
function authorize(){
	$token=json_decode(base64_decode(base64_decode($_GET['token'])));
// die("SELECT * FROM `accesslevels` WHERE (`username` = '$token->username' AND `password` = '$token->password')");
	$result= generete_data("SELECT * FROM `accesslevels` WHERE (`username` = '$token->username' AND `password` = '$token->password')");
	
	if(!$result['success']){
		$result=array("success"=>false,"message"=>"Access Denied");
		die( json_encode($result));	
		//array("success"=>true,""=>));
	}else{
		return true;
	}
}
if(!isset($_GET['resource'])){
	$result=array("success"=>false,"message"=>"Access Denied");
	die( json_encode($result));
	
}
else{
	$resource=$_GET['resource'];
	$prop_id=$_GET['property_id'];
	$username=$_GET['username'];
	$password=$_GET['password'];

}

function generete_data($sql){
	$conn=mysqli_connect("localhost","silvano","access","tech_savana");
	$result=mysqli_query($conn,$sql) ;
	$number=mysqli_num_rows($result);
	$data=[];
	if($number>0){
		while ($res=mysqli_fetch_assoc($result)) {
				$data[]=$res;
		}
			$result=array("success"=>true,"data"=>$data);
	}
	else{
	$result=array("success"=>false,"message"=>"No data available at the moment");
	}
return $result;
}



if($resource=="properties"){
	echo json_encode(generete_data("SELECT * from agentproperty where agent_id=4"));
}


else if($resource=="tenants"&&isset($_GET['property_id']))
{	

	echo json_encode(generete_data("SELECT * FROM `tenants` WHERE `property_id`=$prop_id"));
}

// nnnn
else if($resource=="properties"&&isset($_GET['agentid']))
{	
	// display property list per agent id provided. ---->>
	echo json_encode(generete_data("SELECT * FROM `properties` WHERE `agentid`=$prop_id"));
	
}

else if($resource=="accesslevels"&&isset($_GET['username'])&&isset($_GET['password']))
{	
	// display tenant statement provided tenant id
	
	$result= generete_data("SELECT * FROM `accesslevels` WHERE (`username` = '$username' AND `password` = '$password')");
	if($result['success']){
		$token=base64_encode(json_encode(array("username"=>$username,"password"=>"$password")));
		echo base64_encode($token);
		//array("success"=>true,""=>));
	}
}

else{
		$result=array("success"=>false,"message"=>"Access Denied");
	echo json_encode($result);
}



function login(){

}

//tail -n3 /var/log/apache2/error.log

?>

