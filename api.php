<?php
define('EmpireCMSAdmin', '1');
require('./common/guanjia.util.php'); //
require('../../class/connect.php'); //
require('../../class/db_sql.php'); //
require("../../class/functions.php");//ReturnAddF
require("../../data/dbcache/class.php");
require("../../member/class/user.php");
require("../../class/hinfofun.php");
require("../../class/t_functions.php");
require('./common/constant.php');
ini_set("display_errors", "On");
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));//必须去掉警告

$charset = $ecms_config['db']['setchar'];
require_once ("./lang/{$charset}.php");
global $ecms_charset_config;

$link = db_connect();
$empire = new mysqlquery();

$rowOne = $empire->fetch1("select * from {$dbtbpre}enewspubvar where myvar='guanjia_token'");
$guanjia_token = "guanjia.seowhy.com";
if ($rowOne) {
    $guanjia_token = $rowOne["varvalue"];
}
if(strtolower($charset) == 'utf8'){
    $charset = "utf-8";
}
foreach($_REQUEST as $key => $value) {
    $res = iconv('UTF-8',$charset,urldecode($value));
    if($res === false && strtolower($charset) == "gbk"){
        $res = iconv('UTF-8','GB18030',urldecode($value));
    }
    $_REQUEST[$key] = $res;
}//... for iconv


$guanjia_time = intval($_REQUEST['guanjia_time']);
if(!$guanjia_time){
    guanjia_failRsp(1008, "password error", "time不存在");
}
if (time()-$guanjia_time > 600) {
    guanjia_failRsp(1009, "password error", "该token已超时！");
}
//发布密码校验
if (empty($_REQUEST['guanjia_token']) || md5($guanjia_time.$guanjia_token) != $_REQUEST['guanjia_token']) {
    guanjia_failRsp(ERROR_INVALID_PWD, "password error", $ecms_charset_config['msg']['fail_password_wrong']);
}


if($_REQUEST["action"] == "articleAdd"){
    try{

        //系统模型id检查  enewsmodId ，目前只支持：新闻系统模型和文章系统模型
//        if (empty($_REQUEST['enewsmodId']) || !$_REQUEST['enewsmodId']) {
//            guanjia_failRsp(ERROR_PARA, "enewsmodId is empty", $ecms_charset_config['msg']['fail_enewsmod_mid_empty']);
//        }

        //系统模型  表usemod  是否启用 	0为开启，1为不使用
        $_REQUEST['enewsmodId'] = $_REQUEST['enewsmodId']?$_REQUEST['enewsmodId']:'1,7';
        $modSql = $empire->query(sprintf("select tbname from {$dbtbpre}enewsmod WHERE usemod=0 and mid in (%s) " ,$_REQUEST['enewsmodId']));
        $enewsmodData = $empire->fetch($modSql);
        if (!$enewsmodData) {
            guanjia_failRsp(ERROR_PARA, "enewsmod mid not found", $ecms_charset_config['msg']['fail_enewsmod_mid_not_found']);
        }
        $tbname=$enewsmodData['tbname'];


//获取文档主要数据
        $extDataArr = array();
//
        list($title,$content,$newstime,$thumbnail,$category,$userid,$username,$views,$keyboard,$extDataArr,$dokey)=genDocData($_REQUEST,$tbname);

        //判断栏目，栏目主表，栏目可以是ID或者名称  islast 1为终极栏目，0为非终极栏目
        $enewsclassRow = $empire->fetch1("select * from {$dbtbpre}enewsclass where islast=1 and (classid='{$category}' or classname='{$category}') limit 1");
        if (!$enewsclassRow['classid']) {
            db_close();
            $empire = null;
            guanjia_failRsp(ERROR_PARA, "class name or id:{$category} not found",'栏目ID:'.$category.','.$ecms_charset_config['msg']['fail_enewsclass_class_id']);
        }
        $classid = $enewsclassRow['classid'];
        if(!isset($class_r[$classid])){
            $class_r[$classid] = $enewsclassRow;
        }
        //自定义内容页存放目录
        if(!empty($enewsclassRow['ipath'])){
            //2020-7-20 兼容 自定义 内容页存放目录
            if (in_array($enewsclassRow['newspath'],array('Y-m-d','Y/m/d','Ymd','Y/m-d','Y/m','Y-m'))){
                $datepath = date($enewsclassRow['newspath'], $newstime);
            }else{
                $datepath = $enewsclassRow['newspath'];
            }
        }else if(in_array($enewsclassRow['newspath'],array('Y-m-d','Y/m/d','Ymd','Y/m-d','Y/m','Y-m'))){
            //内容页日期目录形式
            $datepath = date($enewsclassRow['newspath'], $newstime);
        }else{
            $datepath = $enewsclassRow['newspath'];
        }
//是否支持动态内容页
        if($enewsclassRow["showdt"] != 2){
            if (PHP_OS === 'WINNT') {
                $infopath = ReturnSaveInfoPath($classid, null);
                $newspath = _FormatPath($classid, $datepath, 0);
            } else {
                $newspath = _FormatPath($classid, $datepath, 1);
            }
            //增加了eles 防止$newspath 为空的情况20201016
        }else{
            $newspath = _FormatPath($classid, $datepath, 1);
        }



        try {
            //数据库操作,返回自定义字段 funcitons.php
            $ret_field=ReturnAddF($extDataArr,$class_r[$classid][modid],$userid,$username,0,0,0);
            //error_log("\r\n json_encode-ReturnAddF:" .json_encode($ret_field), 3, '/var/log/ecms_test.log');
        } catch (Exception $e) {
            // error_log('Exception ReturnAddF:' .$e->getMessage(), 3, '/var/log/ecms_test.log');
        }
        //发布时间、修改时间
        $truetime = $newstime;
        $lastdotime = $newstime;
        //生成HTML，1为已生成，0为未生成
        $havehtml = 0;
        //addreinfo 发布信息生成内容页   1为生成，0为不生成 (后台)；showdt 内容页模式 0为静态页面，1为动态生成，2为动态页面
        if ($enewsclassRow['addreinfo'] && $enewsclassRow["showdt"] != 2) {
            $havehtml = 1;
        }

        $checked = isset($_REQUEST["checked"]) ? intval($_REQUEST["checked"]) : 1;
        //插入信息表索引
        $indexSql = $empire->query("insert into {$dbtbpre}ecms_" . $class_r[$classid][tbname] . "_index(classid,checked,newstime,truetime,lastdotime,havehtml) values('$classid','$checked','$newstime','$truetime','$lastdotime','$havehtml');");
        $id = $empire->lastid();

        $ret_tb = $emod_r[$class_r[$classid][modid]]['deftb'];
        ReturnInfoPubid($classid, $id);
        $infotbr = ReturnInfoTbname($class_r[$classid][tbname], 1, $ret_tb);

        $httpScheme='http://';
        if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
            $httpScheme="https://";
        }
        $homeUrl=$httpScheme. $_SERVER['HTTP_HOST'];

        //标题唯一性校验
        if(false && $title){
            $guanjia_title_uniqueRow = $empire->fetch1("select varvalue from {$dbtbpre}enewspubvar where myvar='guanjia_title_unique'");
            if($guanjia_title_uniqueRow && $guanjia_title_uniqueRow['varvalue'] == '1'){
                $titleDataRow = $empire->fetch1("select titleurl from {$infotbr['tbname']} where title='{$title}'");
                if($titleDataRow){
                    if(stripos($titleDataRow['titleurl'],$homeUrl) === 0){
                        guanjia_successRsp(array("url" => $titleDataRow['titleurl']));
                    }else {
                        guanjia_successRsp(array("url" => $homeUrl.$titleDataRow['titleurl']));
                    }
                }
            }
        }
        //
        $infotags=isset($_REQUEST["infotags"]) && $_REQUEST["infotags"] ? $_REQUEST["infotags"] : '';
        $diggtop = isset($_REQUEST["diggtop"]) && $_REQUEST["diggtop"] ? intval($_REQUEST["diggtop"]) : 0;

        $firsttitle = isset($_REQUEST["firsttitle"]) && $_REQUEST["firsttitle"] ? intval($_REQUEST["firsttitle"]) : 0;
        $isgood = isset($_REQUEST["isgood"]) && $_REQUEST["isgood"] ? intval($_REQUEST["isgood"]) : 0;


        //error_log('ret_field:'. $ret_field['fields'], 3, '/var/log/ecms_test.log');
        //error_log('infotbr_tbname:'. $infotbr['tbname'], 3, '/var/log/ecms_test.log');
        //$ret_field['fields'] =  ,title,ftitle,newstime,titlepic,smalltext
        //标题分类ID，默认0
        $ttid = isset($_REQUEST["ttid"]) && $_REQUEST["ttid"] ? intval($_REQUEST["ttid"]) : 0;

        //20201016 frank 把$filename赋值为$id,解决待审核记录前台审核点详情页面打不开的问题
        $filename = $id ;
        $titleurl=GotoGetTitleUrl($classid,$id,$newspath,$filename,0,0,'');
        //插入
        $empire->query("insert into " . $infotbr['tbname'] . "(id,classid,ttid,onclick,plnum,totaldown,newspath,filename,userid,username,firsttitle,isgood,ispic,istop,isqf,ismember,isurl,truetime,lastdotime,havehtml,groupid,userfen,titlefont,titleurl,stb,fstb,restb,keyboard".$ret_field['fields'].") values('$id','$classid','$ttid','$views',0,'0','$newspath','$filename','$userid','" . addslashes($username) . "','$firsttitle','$isgood','0','0','0',0,'0','$truetime','$lastdotime','$havehtml','0','0','','$titleurl','$ret_field[tb]','$public_r[filedeftb]','$public_r[pldeftb]','$keyboard'".$ret_field['values'].");");

        //error_log("\r\n===datafields:". $ret_field['datafields'], 3, '/var/log/ecms_test.log');
        //phome_ecms_news_data_1 dokey 关键词替换为0 closepl为1   datafields: writer,befrom,newstext
        $empire->query("insert into ".$infotbr['datatbname'] . "(id,classid,keyid,dokey,newstempid,closepl,haveaddfen,infotags".$ret_field['datafields'].") values('$id','$classid','','$dokey','0','1',0,'" . addslashes($infotags) . "'".$ret_field['datavalues'].");");

        AddClassInfos($classid, '+1', '+1', 1);
        DoUpdateAddDataNum('info', $class_r[$classid]['tid'], 1);
        //frank 2020-10-20
        //搜外内容管家发布插件不做附件处理。
        //UpdateTheFile($id,$thumbnail,$enewsclassRow['classid'],$public_r['filedeftb']);

        $filename = ReturnInfoFilename($classid, $id, '');
        //error_log('havehtml:'. $havehtml, 3, '/var/log/ecms_test.log');
        ///////参考  /e/admin/AddNews.php
        //$infor=$empire->fetch1("select newspath,filename,groupid,isurl,titleurl from ".$infotbr['tbname']." where id='$id' limit 1");
        //把这一句移到前面去
        //$titleurl=GotoGetTitleUrl($classid,$id,$newspath,$filename,0,0,'');

        // 增加待审核记录 20200707
        //待审核记录无法返回url，当前返回的是错误的，但无法修正
        if($checked==0){
            MoveCheckInfoData($class_r[$classid][tbname],1,"1","id='$id'");
            //更新栏目信息数
            AddClassInfos($classid,'','-1');
        }

        if (!empty($thumbnail)) {
            //更新sql
            $updateTitlepic = ",titlepic='".addslashes($thumbnail)."',ispic=1";
        }
        //20201016 frank   filename与id是一样的，把filename更新为i,这个地方的update好像不起作用。


        $usql = $empire->query("update " . $infotbr['tbname'] . " set filename='$id'" . $updateTitlepic . ", titleurl='" .$titleurl. "' where id='{$id}'");


        //标签处理 phome_enewstags
        if (!empty($infotags)) {
            eInsertTags($infotags,$classid,$id,$newstime);
        }

        //是否生成HTML 1
        //待审核记录不生成html 20200707
        if($checked==1){
            if ($havehtml) {
                _GetHtml($classid, $id, '', 0, 1);
            }
        }
        //好像不起作用

        /////图片http下载
        downloadImages($_REQUEST);
        $rspUrl=$titleurl;
        if(stripos($titleurl,$homeUrl) === 0){
        }else{
            $rspUrl=$homeUrl.$titleurl;
        }
        guanjia_successRsp(array("url" =>$rspUrl));


    } catch (Exception $eall) {
        error_log('Exception:' .$eall->getMessage(), 3, '/var/log/ecms_test.log');
        guanjia_failRsp(ERROR_SYSTEM, "post error", $ecms_charset_config['msg']['fail_post'].$eall->getMessage());
    }
// action=post
}elseif($_REQUEST["action"] == "categoryLists"){
    $_REQUEST['enewsmodId'] = $_REQUEST['enewsmodId']?$_REQUEST['enewsmodId']:'1,7';
   # var_dump(sprintf("select * from %senewsclass where islast=1 and modid in (%s)",$dbtbpre,$_REQUEST['enewsmodId']));

    $arr = array();
    $l_sql=$empire->query(sprintf("select classid,classname from %senewsclass where islast=1 and modid in (%s)",$dbtbpre,$_REQUEST['enewsmodId']));
    while($l_r=$empire->fetch($l_sql))
    {
        array_push($arr,array('id'=>intval($l_r['classid']),'title'=>$l_r['classname']));
    }
    guanjia_successRsp($arr);
}elseif($_REQUEST["action"] == "version") {//获取版本
    guanjia_successRsp($guanjia_sys_config);
}
db_close();
$empire = null;


//图片http下载
function  downloadImages($post){
    try{
        $downloadFlag = isset($post['__guanjia_download_imgs_flag']) ? $post['__guanjia_download_imgs_flag'] : '';
        if (!empty($downloadFlag) && $downloadFlag== "true") {
            $docImgsStr = isset($post['__guanjia_docImgs']) ? $post['__guanjia_docImgs'] : '';
            if (!empty($docImgsStr)) {
                $docImgs = explode(',',$docImgsStr);
                if (is_array($docImgs)) {
                    //
                    $upload_dir = getFilePath();
                    foreach ($docImgs as $imgUrl) {
                        $urlItemArr = explode('/',$imgUrl);
                        $itemLen=count($urlItemArr);
                        if($itemLen>=3){
                            //最后的相对路径,如  2018/06
                            $fileRelaPath=$urlItemArr[$itemLen-3].'/'.$urlItemArr[$itemLen-2];
                            $imgName=$urlItemArr[$itemLen-1];
                            $finalPath=$upload_dir. '/'.$fileRelaPath;
                            if (create_folders($finalPath)) {
                                $file = $finalPath . '/' . $imgName;
                                if(!file_exists($file)){
                                    $doc_image_data = file_get_contents($imgUrl);
                                    file_put_contents($file, $doc_image_data);
                                }
                            }
                        }
                    }//.for
                }//..is_array
            }
        }
    } catch (Exception $ex) {
        //error_log('image download error:'. $e->getMessage(), 3, '/var/log/ecms_test.log');
    }
}

/**
 * 获取文件完整路径
 * @return string
 */
function getFilePath(){
    return $basepath=eReturnEcmsMainPortPath()."d/file/p";//moreport
    //error_log('basepath:'. $basepath, 3, '/var/log/ecms_test.log');
}
function create_folders($dir){
    return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
}
function genDocData($post,$tbname) {
    global $empire,$dbtbpre,$ecms_charset_config;

    $extDataArr = array();
    $title = RepPostStr($post["title"]);
    $content = $post["content"];
    //标题和内容必填
    if (empty($title)) {
        db_close();
        $empire = null;
        guanjia_failRsp(ERROR_PARA, "title is empty", $ecms_charset_config['msg']['fail_title_empty']);
    }

    if (empty($content)) {
        db_close();
        $empire = null;
        guanjia_failRsp(ERROR_PARA, "content is empty", $ecms_charset_config['msg']['fail_content_empty']);
    }
    //栏目ID或名称
    $classid = isset($post["category_id"]) && $post["category_id"] ? $post["category_id"]: '';
    //栏目检查
    if (empty($classid)) {
        db_close();
        $empire = null;
        guanjia_failRsp(ERROR_PARA, "classid is empty", $ecms_charset_config['msg']['fail_classid_empty']);
    }
    $cates =explode(',',stripslashes($classid));
    if (!is_array($cates) || count($cates) == 0) {
        db_close();
        $empire = null;
        guanjia_failRsp(ERROR_PARA, "classid is empty", $ecms_charset_config['msg']['fail_classid_empty']);
    }
    //frank 20210319,替换关键字,e/class/functions.php  function ReplaceKey($newstext,$classid=0){
    $dokey = isset($post['dokey']) ? $post['dokey'] :1 ;
    if ($dokey == 1) {
        $content= ReplaceKey($content,$classid);
    }

    ////20210319 结束
    $classidOrName=$cates[0];

    $newstime = empty($post['newstime']) ? time() : intval($post['newstime']);
    $ftitle = isset($post["ftitle"]) && $post["ftitle"] ? $post["ftitle"] : '';
    $titlepic = isset($post["titlepic"]) && $post["titlepic"] ? $post["titlepic"] : '';
    $keyboard = isset($post["keyboard"]) && $post["keyboard"] ? $post["keyboard"] : '';
    //error_log('keyboard:'. $keyboard, 3, '/var/log/ecms_test.log');
    $befrom = isset($post["befrom"]) && $post["befrom"] ? $post["befrom"] : '';
    $onclick = isset($post["onclick"]) && $post["onclick"] ? intval($post["onclick"]) : 0;
    $smalltext = isset($post["smalltext"]) && $post["smalltext"] ? strip_tags($post["smalltext"]) : substr(strip_tags($content), 0, 220).'...';
    $diggtop = isset($post["diggtop"]) && $post["diggtop"] ? intval($post["diggtop"]) : 0;

    //作者处理
    $userid=0;
    $username='';
    if($post["writer"] == "rand_users"){//随机管理员
        $row = $empire->fetch1("select username from {$dbtbpre}enewsuser limit 1");
        $username = $row["username"];
    }else if($post["writer"] == "rand_members"){//随机会员
        $row = $empire->fetch1("select username from {$dbtbpre}enewsmember order by rand() limit 1");
        $username = $row["username"];
    }else{
        $username = $post["writer"];
    }

    list($userid, $username) = getUserInfo($username, $newstime);
    //用户信息处理
    if (empty($userid)|| empty($username)) {
        db_close();
        $empire = null;
        guanjia_failRsp(ERROR_PARA, "invalid user：" . $_REQUEST["writer"], $ecms_charset_config['msg']['fail_writer']);
    }

//phome_enewsmod
//,title,ftitle,newstime,titlepic,smalltext,writer,befrom,newstext,

//,title,ftitle,newstime,titlepic,smalltext
    //新闻系统模型 存数据库
    if('news'==$tbname){
        $extDataArr = array(
            'title' => $title,
            'newstext' => $content,//内容
            'ftitle' => $ftitle,//副标题
            'newstime' => date('Y-m-d H:i:s', $newstime),
            'titlepic' => $titlepic,//标题图片
            'smalltext' => $smalltext,//简介
            'writer' => $username,//作者  副表
            'befrom' => $befrom,//信息来源 副表
            'diggtop' => $diggtop,
        );
    }elseif('article'==$tbname){
        //文章系统模型，存为文件
        $extDataArr = array(
            'title' => $title,
            'newstext' => $content,
            'ftitle' => $ftitle,
            'newstime' => date('Y-m-d H:i:s', $newstime),
            'titlepic' => $titlepic,
            'smalltext' => $smalltext,
            'writer' => $username,
            'befrom' => $befrom,
        );
    }elseif('movie'==$tbname){//暂不支持
        $movietype = isset($post["movietype"]) && $post["movietype"] ? $post["movietype"] : '';
        $company = isset($post["company"]) && $post["company"] ? $post["keyboard"] : '';
        $movietime = isset($post["movietime"]) && $post["movietime"] ? $post["movietime"] : '';
        $player = isset($post["player"]) && $post["player"] ? $post["player"] : '';
        $playadmin = isset($post["playadmin"]) && $post["playadmin"] ? $post["playadmin"] : '';
        $filetype = isset($post["filetype"]) && $post["filetype"] ? $post["filetype"] : '';
        $filesize = isset($post["filesize"]) && $post["filesize"] ? $post["filesize"] : '';
        $star = isset($post["star"]) && $post["star"] ? $post["star"] : '';
        $playdk = isset($post["playdk"]) && $post["playdk"] ? $post["playdk"] : '';
        $playtime = isset($post["playtime"]) && $post["playtime"] ? $post["playtime"] : '';
        $moviefen = isset($post["moviefen"]) && $post["moviefen"] ? $post["moviefen"] : '';
        $downpath = isset($post["downpath"]) && $post["downpath"] ? $post["downpath"] : '';
        $playerid = isset($post["playerid"]) && $post["playerid"] ? $post["playerid"] : 0;//播放器
        $moviefen = isset($post["moviefen"]) && $post["moviefen"] ? $post["moviefen"] : '';
        $onlinepath = isset($post["onlinepath"]) && $post["onlinepath"] ? $post["onlinepath"] : '';
        //$moviesay = isset($post["moviesay"]) && $post["moviesay"] ? $post["moviesay"] : '';
        $extDataArr = array(
            'title' => $title,
            'titlepic' => $titlepic,
            'newstime' => date('Y-m-d H:i:s', $newstime),
            'movietype' => $movietype,
            'company' => $company,
            'movietime' => $movietime,
            'player' => $player,
            'playadmin' => $playadmin,
            'filetype' => $filetype,
            'filesize' => $filesize,
            'star' => $star,
            'playdk' => $playdk,
            'playtime' => $playtime,
            'moviefen' => $moviefen,
            'downpath' => $downpath,
            'playerid' => $playerid,
            'onlinepath' => $onlinepath,
            'moviesay' => $content,
        );
    }else{
        //尝试使用默认
        if (isset($_REQUEST['guanjia_use_mod_default'])&&!empty($_REQUEST['guanjia_use_mod_default'])) {
            $extDataArr = array(
                'title' => $title,
                'newstext' => $content,
                'ftitle' => $ftitle,
                'newstime' => date('Y-m-d H:i:s', $newstime),
                'titlepic' => $titlepic,
                'smalltext' => $smalltext,
                'writer' => $username,
                'befrom' => $befrom,
            );
        }else{
            db_close();
            $empire = null;
            guanjia_failRsp(ERROR_PARA, "no supported sysmod：" . $tbname, $ecms_charset_config['msg']['fail_enewsmod_not_supported']);
        }
    }//.. if
    //添加扩展字段
    foreach ($post as $key => $value) {
        // error_log("参数:".$key."\r\n", 3, '/var/log/ecms_test.log');
        if (strpos($key, '__kdsExt_') === 0) {
            $real_name=substr($key,9);
            if (!empty($real_name)) {
                $extDataArr[$real_name]=$value;
                //error_log('\r\n扩展字段:'.$real_name.',value:'.$value.'\r\n', 3, '/var/log/ecms_test.log');
            }
        }
    }

    return array($title,$content,$newstime,$titlepic,$classidOrName,$userid,$username,$onclick,$keyboard,$extDataArr,$dokey);
}

/**
用户为空时，先从管理用户表中获取一个（一般都有）
 */
function getUserInfo($username, $lastdotime) {
    global $empire,$dbtbpre, $ecms_charset_config;
    $userid = 0;
    try {
        ob_start();
        //会员用户
        if (!empty($username)){
            $userRow = $empire->fetch1("select userid,username from {$dbtbpre}enewsmember where username='".  RepPostStr($username)."' limit 1");
            if ($userRow) {
                $userid = $userRow["userid"];
                $username = $userRow["username"];
            } else {
                //新增会员
                $password = md5("123456");
                $rnd = "654321";
                //lastdotime时间戳减去2天的时间戳
                $registtime = strtotime("-2 day", $lastdotime);
                //插入新会员，groupid默认为1（普通会员）
                $sql = $empire->query("insert into {$dbtbpre}enewsmember(username,password,rnd,email,registertime,groupid,userfen,checked,salt) values('".RepPostStr($username)."','$password','$rnd','','$registtime',1,1,1,'$rnd');");
                $userid = $empire->lastid();
                $username = $username;
            }
        } else {//如果提交的没有填写
            //网站管理用户
            $userRow = $empire->fetch1("select userid,username from {$dbtbpre}enewsuser order by userid limit 1");
            if ($userRow) {
                $userid = $userRow["userid"];
                $username = $userRow["username"];
            }
        }

        $result = ob_get_contents();
        if($result){
            guanjia_failRsp(ERROR_SYSTEM, $result, $ecms_charset_config['msg']['fail_username_insert']);
        }
        ob_end_clean();
    } catch (Exception $e) {
        guanjia_failRsp(ERROR_SYSTEM, "getUserInfo error", $ecms_charset_config['msg']['fail_username_insert'].$e->getMessage());
    }

    return array($userid,$username);
}

//生成内容文件
function _GetHtml($classid,$id,$add,$ecms=0,$doall=0){
    global $public_r,$class_r,$class_zr,$fun_r,$empire,$dbtbpre,$emod_r,$class_tr,$level_r,$etable_r;
    $mid=$class_r[$classid]['modid'];
    $tbname=$class_r[$classid][tbname];
    if(InfoIsInTable($tbname))//内部表
    {
        return '';
    }
    if($ecms==0)//主表
    {
        $add=$empire->fetch1("select ".ReturnSqlTextF($mid,1)." from {$dbtbpre}ecms_".$tbname." where id='$id' limit 1");
    }
    $add['id']=$id;
    $add['classid']=$classid;
    if($add['isurl'])
    {
        return '';
    }
    if(empty($doall))
    {
        if(!$add['stb']||$class_r[$add[classid]][showdt]==2||strstr($public_r['nreinfo'],','.$add['classid'].','))//不生成
        {
            return '';
        }
    }
    //副表
    $addr=$empire->fetch1("select ".ReturnSqlFtextF($mid)." from {$dbtbpre}ecms_".$tbname."_data_".$add[stb]." where id='$add[id]' limit 1");
    $add=array_merge($add,$addr);
    //路径
    $iclasspath=ReturnSaveInfoPath($add[classid],$add[id]);
    $doclasspath=eReturnTrueEcmsPath().$iclasspath;//moreport
    $createinfopath=$doclasspath;
    //建立日期目录
    $newspath='';
    if($add[newspath])
    {
        $createpath=$doclasspath.$add[newspath];
        if(!file_exists($createpath))
        {
            $r[newspath]=_FormatPath($add[classid],$add[newspath],1);
        }
        $createinfopath.=$add[newspath].'/';
        $newspath=$add[newspath].'/';
    }
    //新建存放目录
    if($class_r[$add[classid]][filename]==3)
    {
        $createinfopath.=ReturnInfoSPath($add['filename']);
        _DoMkdir($createinfopath);
        $fn3=1;
    }
    //存文本
    if($emod_r[$mid]['savetxtf'])
    {
        $stf=$emod_r[$mid]['savetxtf'];
        if($add[$stf])
        {
            $add[$stf]=GetTxtFieldText($add[$stf]);
        }
    }
    eAutodo_AddDo('ReNewsHtml',$classid,$id,0,0,0);//moreportdo
    $GLOBALS['navclassid']=$add[classid];
    $GLOBALS['navinfor']=$add;
    //取得内容模板
    $add[newstempid]=$add[newstempid]?$add[newstempid]:$class_r[$add[classid]][newstempid];
    $newstemp_r=$empire->fetch1("select temptext,showdate from ".GetTemptb("enewsnewstemp")." where tempid='$add[newstempid]' limit 1");
    $newstemp_r['tempid']=$add['newstempid'];
    if($public_r['opennotcj'])//启用反采集
    {
        $newstemp_r['temptext']=ReturnNotcj($newstemp_r['temptext']);
    }
    $newstemptext=$newstemp_r[temptext];
    $formatdate=$newstemp_r[showdate];
    //文件类型/权限
    if($add[groupid]||$class_r[$add[classid]]['cgtoinfo'])
    {
        if(empty($add[newspath]))
        {
            $include='';
        }
        else
        {
            $pr=explode('/',$add[newspath]);
            for($i=0;$i<count($pr);$i++)
            {
                $include.='../';
            }
        }
        if($fn3==1)
        {
            $include.='../';
        }
        $pr=explode('/',$iclasspath);
        $pcount=count($pr);
        for($i=0;$i<$pcount-1;$i++)
        {
            $include.='../';
        }
        $include1=$include;
        $include.='e/class/CheckLevel.php';
        $filetype='.php';
        $addlevel="<?php
		define('empirecms','wm_chief');
		\$check_tbname='".$class_r[$add[classid]][tbname]."';
		\$check_infoid=".$add[id].";
		\$check_classid=".$add[classid].";
		\$check_path=\"".$include1."\";
		require(\"".$include."\");
		?>";
    }
    else
    {
        $filetype=$class_r[$add[classid]][filetype];
        $addlevel='';
    }
    //取得本目录链接
    if($class_r[$add[classid]][classurl]&&$class_r[$add[classid]][ipath]=='')//域名
    {
        $dolink=$class_r[$add[classid]][classurl].'/'.$newspath;
    }
    else
    {
        $dolink=$public_r[newsurl].$iclasspath.$newspath;
    }
    //返回替换验证字符
    $docheckrep=ReturnCheckDoRepStr();
    if($add[newstext])
    {
        if(empty($public_r['dorepword'])&&$docheckrep[3])
        {
            $add[newstext]=ReplaceWord($add[newstext]);//过滤字符
        }
        if(empty($public_r['dorepkey'])&&$docheckrep[4]&&!empty($add[dokey]))//替换关键字
        {
            $add[newstext]=ReplaceKey($add['newstext'],$add['classid']);
        }
        if($public_r['opencopytext'])
        {
            $add[newstext]=AddNotCopyRndStr($add[newstext]);//随机复制字符
        }
    }
    //返回编译
    $newstemptext=GetInfoNewsBq($classid,$newstemp_r,$add,$docheckrep);
    //分页字段
    $expage='[!--empirenews.page--]';//分页符
    $pf=$emod_r[$mid]['pagef'];
    //变量替换
    $newstempstr=$newstemptext;//模板
    //分页
    if($pf&&strstr($add[$pf],$expage))//有分页
    {
        $n_r=explode($expage,$add[$pf]);
        $thispagenum=count($n_r);
        //取得分页
        $thefun=$public_r['textpagefun']?$public_r['textpagefun']:'sys_ShowTextPage';
        //下拉式分页
        if(strstr($newstemptext,'[!--title.select--]'))
        {
            $dotitleselect=sys_ShowTextPageSelect($thispagenum,$dolink,$add,$filetype,$n_r);
        }
        for($j=1;$j<=$thispagenum;$j++)
        {
            $string=$newstempstr;//模板
            $truepage='';
            $titleselect='';
            //下一页链接
            if($thispagenum==$j)
            {
                $thisnextlink=$dolink.$add[filename].$filetype;
            }
            else
            {
                $thisj=$j+1;
                $thisnextlink=$dolink.$add[filename].'_'.$thisj.$filetype;
            }
            $k=$j-1;
            if($j==1)
            {
                $file=$doclasspath.$newspath.$add[filename].$filetype;
                $ptitle=$add[title];
            }
            else
            {
                $file=$doclasspath.$newspath.$add[filename].'_'.$j.$filetype;
                $ti_r=explode('[/!--empirenews.page--]',$n_r[$k]);
                if(count($ti_r)>=2)
                {
                    $ptitle=$ti_r[0];
                    $n_r[$k]=$ti_r[1];
                }
                else
                {
                    $ptitle=$add[title].'('.$j.')';
                }
            }
            //取得当前页
            if($thispagenum!=1)
            {
                $truepage=$thefun($thispagenum,$j,$dolink,$add,$filetype,'');
                $titleselect=str_replace("?".$j."\">","?".$j."\" selected>",$dotitleselect);
            }
            //替换变量
            $newstext=$n_r[$k];
            if(!strstr($emod_r[$mid]['editorf'],','.$pf.','))
            {
                if(strstr($emod_r[$mid]['tobrf'],','.$pf.','))//加br
                {
                    $newstext=nl2br($newstext);
                }
                if(!strstr($emod_r[$mid]['dohtmlf'],','.$pf.','))//去除html
                {
                    $newstext=ehtmlspecialchars($newstext);
                    $newstext=RepFieldtextNbsp($newstext);
                }
            }
            $string=str_replace('[!--'.$pf.'--]',$newstext,$string);
            $string=str_replace('[!--p.title--]',strip_tags($ptitle),$string);
            $string=str_replace('[!--next.page--]',$thisnextlink,$string);
            $string=str_replace('[!--page.url--]',$truepage,$string);
            $string=str_replace('[!--title.select--]',$titleselect,$string);
            //写文件
            WriteFiletext($file,$addlevel.$string);
        }
    }
    else
    {
        $file=$doclasspath.$newspath.$add[filename].$filetype;
        $string=$newstempstr;//模板
        //替换变量
        $string=str_replace('[!--p.title--]',$add[title],$string);
        $string=str_replace('[!--next.page--]','',$string);
        $string=str_replace('[!--page.url--]','',$string);
        $string=str_replace('[!--title.select--]','',$string);
        //写文件
        WriteFiletext($file,$addlevel.$string);
    }
    //设为已生成
    if(empty($doall)&&empty($add['havehtml']))
    {
        $empire->query("update {$dbtbpre}ecms_".$class_r[$add[classid]][tbname]."_index set havehtml=1 where id='$add[id]' limit 1");
        $empire->query("update {$dbtbpre}ecms_".$class_r[$add[classid]][tbname]." set havehtml=1 where id='$add[id]' limit 1");
    }
}


//格式化信息目录
function _FormatPath($classid,$mynewspath,$enews=0){
    global $class_r;
    if($enews)
    {
        $newspath=$mynewspath;
    }
    else
    {
        $newspath=date($class_r[$classid][newspath]);
    }
    if(empty($newspath))
    {
        return "";
    }
    $path=eReturnTrueEcmsPath().ReturnSaveInfoPath($classid,$id);
    if(file_exists($path.$newspath))
    {
        return $newspath;
    }
    $returnpath="";
    $r=explode("/",$newspath);
    $count=count($r);
    for($i=0;$i<$count;$i++){
        if($i>0)
        {
            $returnpath.="/".$r[$i];
        }
        else
        {
            $returnpath.=$r[$i];
        }
        $createpath=$path.$returnpath;

        $mk=_DoMkdir($createpath);
        if(empty($mk))
        {
            printerror("CreatePathFail","");
        }
    }
    return $returnpath;
}

function _DoMkdir($path){
    $mk=@mkdir($path,0777);
    @chmod($path,0777);
    return true;
}
?>