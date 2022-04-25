<?php
define('ERROR_INVALID_PWD', 1403);
define('ERROR_REQUERIY_FIELD', 1404);
define('ERROR_PARA', 1405);
define('ERROR_SYSTEM', 1500);

function guanjia_successRsp($data = "", $msg = "") {
    guanjia_rsp(1, $data, $msg);
}

function guanjia_failRsp($code = 0, $data = "", $msg = "") {
    guanjia_rsp($code, $data, guanjia_iconv($msg));
}

function guanjia_rsp($code = 0, $data = "", $msg = "") {
	die(json_encode(array( "code" => $code, "data" => $data, "msg" => urlencode($msg))));
}

/** */
function guanjia_iconv($str,$encode = "UTF-8"){
    global $cfg_db_language;
	//error_log('dedeTest2-cfg_db_language:'.$cfg_db_language, 3, '/var/log/dede_test.log');
    if(strtolower($cfg_db_language) == 'utf8'){
        return $str;
    }
	//error_log('dedeTest2-str:'.$str, 3, '/var/log/dede_test.log');
    return iconv($cfg_db_language,$encode,$str);
}

if(!function_exists("dede_htmlspecialchars")){
    function dede_htmlspecialchars($str) {
        global $cfg_soft_lang;
        if (version_compare(PHP_VERSION, '5.4.0', '<')) return htmlspecialchars($str);
        if ($cfg_soft_lang=='gb2312') return htmlspecialchars($str,ENT_COMPAT,'ISO-8859-1');
        else return htmlspecialchars($str);
    }
}

?>