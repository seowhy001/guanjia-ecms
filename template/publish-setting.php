<?php
if(!defined('InEmpireCMS'))
{
	exit();
}
//验证用户
$lur=is_login();
$logininid=$lur['userid'];
$loginin=$lur['username'];
$loginrnd=$lur['rnd'];
$loginlevel=$lur['groupid'];
$loginadminstyleid=$lur['adminstyleid'];
//ehash
$ecms_hashur=hReturnEcmsHashStrAll();

global $ecms_charset_config;
require 'common/constant.php';
$guanjia_token_id = "0";
$guanjia_token = "guanjia.seowhy.com";

$guanjia_title_unique_id = "0";
$guanjia_title_unique="0";
$pubvarRow=$empire->fetch1("select * from {$dbtbpre}enewspubvar where myvar='guanjia_token' limit 1");
if($pubvarRow){
    $guanjia_token_id = $pubvarRow["varid"];
    $guanjia_token = $pubvarRow["varvalue"];
    $pubvarRowTitle = $empire->fetch1("select * from {$dbtbpre}enewspubvar where myvar='guanjia_title_unique' limit 1");
    if($pubvarRowTitle){
		$guanjia_title_unique_id = $pubvarRowTitle['varid'];
        $guanjia_title_unique = $pubvarRowTitle['varvalue'];
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $ecms_charset_config['configLabel']['title'];?></title>
<style>
body{ 
	font-size:14px; 
	padding:10px 10px;
}
.publish-config-box h3 {
	font-size: 16px;
	padding: 10px 10px;
	margin: 0;
	line-height: 1;
}
.config-table {
	background-color:#FFFFFF;
	font-size:14px;
	padding:15px 20px;
}
.config-table td{
	height:35px;
	padding-left:10px;
}
.config-input {
	width:320px;
}
.info-box h3 {
	font-size: 15px;
	padding: 10px 10px;
	margin: 0;
	line-height: 1;
}
.feature {
	padding-top:5px;
}
table{
	border-collapse: collapse;
	border: 1px solid #E1E1E1;
	background: #ffffff;
	background-color: rgb(255, 255, 255);
	line-height: 120%;
}
a {
	color: #3ca5f6;
}
a:link {
	color: #3ca5f6;
}
.link-blue {
    color: #3ca5f6;
}
</style>
</head>
<body topmargin="8" leftmargin="8" background="template/images/allbg.gif">
<div class="wrap" style="margin-bottom:100px;">
<div class="bodytitle">
	<div class="bodytitleleft"></div>
	<div class="bodytitletxt" style="padding-left:10px;"><?php echo $ecms_charset_config['configLabel']['page_title'];?>(<b>V<?php echo $guanjia_sys_config['version']; ?></b>)</div>
</div>
  <div style="margin-left:20px;padding-top:10px;padding-bottom:10px;"><?php echo $ecms_charset_config['configLabel']['plus_desc'];?>
</div>

    <div class="publish-config-box">
      <div>  
<form id="edit" name="edit" action="index.php?1=1<?=$ecms_hashur['ehref']?>" method="post" style="padding-top: 14px;">
<table width="98%"  align="center" cellpadding="4" cellspacing="1" class="tbtitle" style="background:#FFFFFF;">
  <tr>
    <td bgcolor="#F9FCEF" colspan="2" background='template/images/tbg.gif' style="padding-left:10px;">
    	<strong><?php echo $ecms_charset_config['configLabel']['plus_config_title'];?></strong>
    </td>
  </tr>
          <tr>
            <td width="15%"><?php echo $ecms_charset_config['configLabel']['plus_config_home_url_lb'];?>:</td>
            <td><input type="text" id="homeUrl"  name="homeUrl" class="config-input" readonly value="<?php
                                if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
                                    echo "https://";
                                } else {
                                    echo "http://";
                                }
                                $domain = str_replace('\\', '/', $_SERVER['HTTP_HOST']);
                                echo $domain.'/e/extend/guanjia/api.php'; ?>" /><?php echo $ecms_charset_config['configLabel']['plus_config_home_url_tip'];?>
            
            </td>
          </tr>
          <tr>
            <td><?php echo $ecms_charset_config['configLabel']['plus_config_password'];?>:</td>
            <td><input type="text" name="guanjia_token" class="config-input" value="<?php echo $guanjia_token; ?>" /><?php echo $ecms_charset_config['configLabel']['plus_config_password_tip'];?>
            </td>
          </tr>
		  <tr style="display: none">
			<td><?php echo $ecms_charset_config['configLabel']['plus_config_title_unique'];?>:</td>
			<td><input type="checkbox" name="guanjia_title_unique" value="1" <?php if($guanjia_title_unique == 1) echo "checked='checked'" ?> /><?php echo $ecms_charset_config['configLabel']['plus_config_title_unique_yes'];?>
			</td>
		</tr>					  
          <tr>
            <td></td>
            <td><input type="submit"  name="formSubmit"  value="<?php echo $ecms_charset_config['configLabel']['plus_config_save_btn'];?>" class="button-primary" /></td>
          </tr>
        </table>
    <input type="hidden" name="guanjia_token_id" value="<?php echo $guanjia_token_id;?>">
    <input type="hidden" name="guanjia_title_unique_id" value="<?php echo $guanjia_title_unique_id;?>">
	<input id="reset" name="reset" type="hidden" value="" />			
  </form>		
      </div>
    </div>
  <div class="info-box">
    <div>
<table width="98%"  border="1" align="center" cellpadding="4" cellspacing="1" class="tbtitle" style="background:#FFFFFF;">
    <td bgcolor="#F9FCEF" colspan="2" background='template/images/tbg.gif' style="padding-left:10px;">
    	<strong><?php echo $ecms_charset_config['configLabel']['plus_help_title'];?></strong>
    </td>
        <tr>
          <td width="15%"><?php echo $ecms_charset_config['configLabel']['plus_help_home_lb'];?>:</td>
          <td><?php echo $ecms_charset_config['configLabel']['plus_help_qq'];?></td>
        </tr>
        <tr>
          <td><?php echo $ecms_charset_config['configLabel']['plus_help_feature_lb'];?>：</td>
          <td>
		  <div class="feature"><?php echo $ecms_charset_config['configLabel']['plus_help_feature_1'];?></div>
		  </td>
        </tr>
    <tr>
        <td><?php echo $ecms_charset_config['configLabel']['plus_help_link_lb'];?>：</td>
        <td><?php echo $ecms_charset_config['configLabel']['plus_help_link'];?></td>
    </tr>
      </table>
    </div>
  </div>
</div><!-- wrap -->	
</body>
</html>