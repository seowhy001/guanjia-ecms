<?php
//安装入口: http://www.xxxx.com/e/extend/guanjia/setup
//www.xxxx.com 为你的网站域名
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
@set_time_limit(3600);
@header('Content-Type: text/html; charset=utf-8');
define('EmpireCMSAdmin','1');

require("../../../class/connect.php");
require("../../../class/db_sql.php");
require("../../../class/functions.php");
?>
<?php
//根据不用操作类型执行逻辑
global $ecms_charset_config;
$charset = $ecms_config['db']['setchar'];
require_once ("../lang/{$charset}.php");
global $ecms_charset_config;

$link=db_connect();
$empire=new mysqlquery();
$editor=1;
if (!function_exists('curl_init')){
    exit($ecms_charset_config['msg']['fail_check_curl']);
}
if (file_exists("install.off")){
    printerror2($ecms_charset_config['msg']['fail_install_locked'],'history.go(-1)',0,1);
}

$showType=$_GET["showType"];
if (empty($showType)){//进入选择操作页面
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<title><?=$ecms_charset_config['configLabel']['title']?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="renderer" content="webkit"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="keywords" content=""/>
<meta name="description" content=""/>
<style>
h3 {
	margin: 10px 0;
	text-align: center;
}

h4 {
	margin: 0;
}

p {
	margin: 10px 0;
}

.clearfix::after {
	content: "";
	display: block;
	height: 0;
	visibility: hidden;
	clear: both;
}

.modal {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 3;
	overflow-x: hidden;
	overflow-y: auto;
}

.modal-content {
	position: relative;
	width: 640px;
	margin: 80px auto 0;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 5px 15px rgba(0,0,0,.5);
}

.modal-header {
	padding: 15px;
	border-bottom: 1px solid #e5e5e5;
	background: rgba(51,122,183,.7);
	color:#FFFFFF;
}

.modal-header button {
	float: right;
	border: 0;
	background: transparent;
	margin-top: -2px;
	padding: 0;
	cursor: pointer;
}
.modal-header span {
	font-size: 21px;
	font-weight: 700;
	text-shadow: 0 1px 0 #fff;
}

.modal-body {
	padding: 30px 15px 15px;
	min-height: 150px;
}

label {
	display: inline-block;
	padding: 0 10px;
}

.modal label:last-child {
	border-left: 2px solid #000;
}

.modal-footer {
	padding: 15px;
	border-top: 1px solid #e5e5e5;
	text-align: right;
}

.modal-footer button {
	color: #000;
	font-size: 14px;
	border: 1px solid #ccc;
	border-radius: 4px;
	background: #fff;
	margin-right: 15px;
	padding: 6px 20px;
	text-align: center;
	vertical-align: middle;
	cursor: pointer; 
}

.modal-footer button:hover {
	color: #fff;
	background: #337ab7 !important; 
}

.modal-back {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 2;
	background: #000; opacity: .5;
}

</style>
</head>
<body>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header clearfix">
      <button type="button"> <span>x</span> </button>
      <h4><?=$ecms_charset_config['configLabel']['title']?></h4>
    </div>
    <div class="modal-body clearfix">
      <h3><?=$ecms_charset_config['configLabel']['install_title']?></h3>
      <p></p>
      <form method="get" action="index.php" name="operationForm" onSubmit="return confirmAction(document.operationForm);">
        <span><?=$ecms_charset_config['configLabel']['operation_select']?>：</span>
        <label>
        <input type="radio" checked="checked" name="operation" value="install"/>
        <?=$ecms_charset_config['configLabel']['install']?> </label>
	  <input type="hidden" name="showType" value="operationPage">
	  <p><font color="red"><?=$ecms_charset_config['configLabel']['install_tip']?></font></p>
    </div>
    <div class="modal-footer clearfix">
      <button type="submit"><?=$ecms_charset_config['configLabel']['submit']?></button>
    </div>
	</form>
  </div>
</div>
<div class="modal-back"></div>
<script>
function confirmAction(formObj){
	if(confirm('<?=$ecms_charset_config['configLabel']['install_confirm']?>')){
		return true;
	}
	return false;
}
</script>
</body>
</html>
<?php
	exit;
} elseif($showType=="operationPage"){
	if($_GET['operation']=="install"){
		$phome_db_dbchar=file_exists('../../../config/config.php')?$ecms_config['db']['dbchar']:$phome_db_dbchar;
		$menuClassRow=$empire->fetch1("select classid from {$dbtbpre}enewsmenuclass where classname='{$ecms_charset_config['guanjia_name']}' limit 1");
		if(!$menuClassRow){
			//插件右侧按钮
			$empire->query("insert into `{$dbtbpre}enewsmenuclass` values(NULL,'{$ecms_charset_config['guanjia_name']}','0','0','2','');");
			$menuClassid=$empire->lastid();
			
			$empire->query("insert into `{$dbtbpre}enewsmenu` values(NULL,'{$ecms_charset_config['plugins_name']}','../extend/guanjia/index.php','0','$menuClassid','1');");
			
			$empire->query("insert into `{$dbtbpre}enewsmenu` values(NULL,'{$ecms_charset_config['plugins_setup']}','../extend/guanjia/setup/setup.php','0','$menuClassid','1');");
			}
		$lock = @fopen("install.off", "w");
		@fclose($lock);
		$word = $ecms_charset_config['configLabel']['install_finished'];
		printerror2("{$ecms_charset_config['configLabel']['title']} $word","/");
	}
}else{
    exit($ecms_charset_config['msg']['fail_install_page']);
}
db_close();
$empire = null;
?>