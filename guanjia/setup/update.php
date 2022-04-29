<?php
//执行更新或卸载
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
@header('Content-Type: text/html; charset=utf-8');
define('EmpireCMSAdmin','1');

require("../../../class/connect.php");
require("../../../class/db_sql.php");
require("../../../class/functions.php");
?>
<?php
global $ecms_charset_config;
$charset = $ecms_config['db']['setchar'];
require_once ("../lang/{$charset}.php");
global $ecms_charset_config;
//error_log('test:', 3, '/var/log/ecms_test.log');

$link=db_connect();
$empire=new mysqlquery();
/** */
//验证用户
$lur=is_login();
$logininid=$lur['userid'];
$loginin=$lur['username'];
$loginrnd=$lur['rnd'];
$loginlevel=$lur['groupid'];
$loginadminstyleid=$lur['adminstyleid'];
//ehash
$ecms_hashur=hReturnEcmsHashStrAll();

?>

<?php
$operation=$_POST["operation"];
 //error_log('operation:' .$operation, 3, '/var/log/ecms_test.log');
$uninstalled=false;
$updated=false;
$word='';
if (!empty($operation)){
	if($operation=="uninstall"){
		$menuClassRow=$empire->fetch1("select classid from {$dbtbpre}enewsmenuclass where classname='{$ecms_charset_config['guanjia_name']}' limit 1");
		
		$empire->query("delete from {$dbtbpre}enewsmenuclass where classid='$menuClassRow[classid]'");
		$empire->query("delete from {$dbtbpre}enewsmenu where classid='$menuClassRow[classid]'");
		//$uninstalled=true;
		$word = $ecms_charset_config['configLabel']['uninstall_finished'];
		printerror2($word,'history.go(-1)',0,1);
		
	}if($operation=="update"){//更新
		//$updated=true;
		$word = $ecms_charset_config['configLabel']['update_finished'];
		printerror2($word,'',0,1);//history.go(-1)
		//TODO
	}
}

?>
<?php
if($uninstalled||$updated){
?>
<script>
	alert('<?=$word?>');
</script>
<?php
exit;
}
db_close();
$empire = null;	
?>