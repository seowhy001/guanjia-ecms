<?php
require('../../class/connect.php');
require('../../class/db_sql.php');
require("../../class/functions.php");
global $ecms_charset_config;
$charset = $ecms_config['db']['setchar'];
require_once ("./lang/{$charset}.php");
global $ecms_charset_config;
if('utf8' == strtolower($charset)){
    $charset = "utf-8";
}
$link=db_connect();
$empire=new mysqlquery();

//验证用户

$lur=is_login();
$logininid=(int)$lur['userid'];
$loginin=$lur['username'];
$loginrnd=$lur['rnd'];
$loginlevel=(int)$lur['groupid'];
$loginadminstyleid=$lur['adminstyleid'];
//ehash
$ecms_hashur=hReturnEcmsHashStrAll();
//hCheckEcmsRHash();

//配置表单保存
if(isset($_POST["guanjia_token_id"]) && isset($_POST['guanjia_title_unique_id'])){

	$guanjia_token_id = intval($_POST["guanjia_token_id"]);
	$guanjia_token = $_POST["guanjia_token"];

	$guanjia_title_unique_id = intval($_POST["guanjia_title_unique_id"]);
	$guanjia_title_unique = $_POST['guanjia_title_unique']?$_POST['guanjia_title_unique']:0;
	
	//操作扩展变量表phome_enewspubvar
	if($guanjia_token_id > 0){
		$empire->query("update {$dbtbpre}enewspubvar set varvalue='{$guanjia_token}' where varid={$guanjia_token_id}");
	}else{
		$empire->query("insert into {$dbtbpre}enewspubvar(myvar,varname,varvalue) values('guanjia_token','guanjia_token','{$guanjia_token}')");
	}

	if($guanjia_title_unique_id > 0){
		$empire->query("update {$dbtbpre}enewspubvar set varvalue='{$guanjia_title_unique}' where varid={$guanjia_title_unique_id}");
	}else{
		$empire->query("insert into {$dbtbpre}enewspubvar(myvar,varname,varvalue) values('guanjia_title_unique','guanjia_title_unique','{$guanjia_title_unique}')");
	}
}

include('template/publish-setting.php');
db_close();
$empire=null;
?>
