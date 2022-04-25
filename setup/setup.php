<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
@header('Content-Type: text/html; charset=utf-8');
define('EmpireCMSAdmin','1');

require("../../../class/connect.php");
require("../../../class/db_sql.php");
require("../../../class/functions.php");
?>
<?php
global $ecms_charset_config;
require '../common/constant.php';
$charset = $ecms_config['db']['setchar'];
require_once ("../lang/{$charset}.php");
global $ecms_charset_config;

$link=db_connect();
$empire=new mysqlquery();
//验证用户
$lur=is_login();
$logininid=$lur['userid'];
$loginin=$lur['username'];
$loginrnd=$lur['rnd'];
$loginlevel=$lur['groupid'];
$loginadminstyleid=$lur['adminstyleid'];
//ehash
$ecms_hashur=hReturnEcmsHashStrAll();
db_close();
$empire = null;
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
	width: 600px;
	margin: 80px auto 0;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 5px 15px rgba(0,0,0,.5);
}
/** rgba(51,122,183,.7) */
.modal-header {
	padding: 15px;
	border-bottom: 1px solid #e5e5e5;
	background: url(../template/images/tbg.gif);
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
      <h3><?=$ecms_charset_config['configLabel']['update_title']?></h3>
      <p></p>
      <form method="post" action="update.php?1=1<?=$ecms_hashur['ehref']?>" name="operationForm" onSubmit="return confirmAction(document.operationForm);">
        <span><?=$ecms_charset_config['configLabel']['operation_select']?>：</span>
		<label>
        <input type="radio" name="operation" value="update" checked="checked"/>
        <?=$ecms_charset_config['configLabel']['update']?> </label>
        <label>
        <input type="radio"  name="operation" value="uninstall"/>
        <?=$ecms_charset_config['configLabel']['uninstall']?> </label>	 
		<p><?=$ecms_charset_config['configLabel']['version_name_curr']?>：<?php echo $guanjia_sys_config['version']; ?></p> 
		<p><?=$ecms_charset_config['configLabel']['version_name_lastest']?>：<a href="https://guanjia.seowhy.com/help#ecms" target="_blank"><?=$ecms_charset_config['configLabel']['view_version']?></a></p> 
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
	var optName='<?=$ecms_charset_config['configLabel']['update_confirm']?>';
	var operation=getRadioValue('operation');
	if(operation&&operation=='uninstall'){
		optName='<?=$ecms_charset_config['configLabel']['uninstall_confirm']?>';
	}
	if(confirm(optName)){
		return true;
	}
	return false;
}
function getRadioValue(name){
	var radio = document.getElementsByName(name);
	for (var i=0; i<radio.length; i++) {
		if (radio[i].checked) {
			return (radio[i].value);
		}
	}
	return '';
}
</script>
</body>
</html>