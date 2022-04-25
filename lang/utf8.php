<?php
$ecms_charset_config = array(
     "guanjia_name"=>"搜外内容管家平台",
     "plugins_name"=>"采集发布配置",	 
	 "plugins_setup"=>"更新或卸载",	 
	 "author_network"=>"网络",
    "configLabel" => array(
        "title"      => "搜外内容管家平台—帝国发布插件",
        "page_title"   => "搜外内容管家平台—帝国发布插件",
		"install_title"     => "帝国CMS发布插件安装",
		"install_tip"     => "为了安全，安装后请手动删除或重命名文件/e/extend/guanjia/setup/index.php",
		"update_title"     => "帝国CMS发布插件更新|卸载",
		"operation_select"     => "执行的操作",
		"install"   => "安装",
		"update"   => "更新",
		"uninstall" => "卸载",
		"submit"    => "提交",
		"close"     => "关闭",
		"install_finished"  => "恭喜，安装完成!",
    	"uninstall_finished"    => "已成功卸载!请重新刷新页面。请手动删除安装文件夹/e/extend/guanjia",	
		"update_finished"   => "更新执行完成！请重新刷新页面。",
		"install_confirm"   => "确定要安装搜外内容管家发布插件吗？ 安装后请手动删除安装文件/e/extend/tend/guanjia/setup/index.php",
		"update_confirm"   => "确定要更新吗？注意更新前请先上传覆盖插件文件。",
		"uninstall_confirm"   => "确定要卸载吗？",
		"version_name_curr"   => "现使用插件版本",
		"version_name_lastest"   => "服务器最新版本",
		"view_version"   => "查看对比",
		"plus_desc"   => "",
		"plus_config_title"   => "内容发布设置",
        "plus_config_home_url_lb"    =>  "网站发布地址为",
        "plus_config_home_url_tip" => "（采集和发布数据请到 <a href='https://guanjia.seowhy.com' target='_blank' style='color:#3ca5f6;'>搜外内容管家控制台</a>）",
        "plus_config_password" => "发布密码",
        "plus_config_password_tip"   =>"（请勿使用特殊字符或汉字,并注意修改和保管好）",
        "plus_config_title_unique"   =>"根据标题去重",
        "plus_config_title_unique_yes" => "存在相同标题，则不插入",
		"plus_config_save_btn"   =>"保存更改",
		"plus_help_title"   =>"简介和使用教程",
		"plus_help_home_lb"   =>"搜外内容管家官网",
		"plus_help_qq"   =>"<a href='https://guanjia.seowhy.com' target='_blank' style='color:#3ca5f6;'>guanjia.seowhy.com</a>",
        "plus_help_link_lb"=>"客服微信",
        "plus_help_link"=>"<img src='https://static.seowhy.com/www/didi/static/images/didi-service-weixin-1.jpg' width='150px'>",
		"plus_help_feature_lb"   =>"平台主要功能特性",
		"plus_help_feature_1"   =>" 1.不要配置任何采集规则，直接选择文章<br>2.在线选择文章进行伪原创之后即可发布<br>3.全程操作一分钟即可获得文章",
    ),
    "msg"   => array(
        "success_config_save"=>"保存成功",
		"fail_config_save"=>"保存失败",
        "fail_auth"     => "搜外内容管家平台发布插件，需要管理员才能够使用",
        "fail_password_wrong"=>"发布密码错误,请检查发布插件密码与搜外内容管家发布目标密码是否一致。",
        "fail_title_empty"  => "标题为空，请检查数据或映射配置",
		"fail_content_empty"  => "内容为空，请检查数据或映射配置",
        "fail_enewsmod_mid_empty"    => "系统模型ID为空",
		"fail_enewsmod_mid_not_found"    => "找不到该系统模型ID",
		"fail_enewsmod_not_supported"    => "系统模型暂不支持",
		"fail_classid_empty"  => "请填写栏目ID或名称",
		"fail_enewsclass_class_id"     => "找不到栏目ID,请检查目标映射配置或创建该栏目或栏目是否为终极栏目！",
		"fail_writer"   => "文章作者异常，请检查帝国后台用户是否有权限",
		"fail_channelid_empty"    => "请在发布目标映射中填写频道模型ID！",
		"fail_channelid_typeid"    => "请检查发布目标映射中的文章主栏目ID和频道模型ID与织梦当前模型是否相符！",
		"fail_channelid_nofound"    => "没找到当前模型的主表信息，无法完成操作！",
        "fail_arcID"    => "无法获得主键，因此无法进行后续操作！",
		"fail_username_insert"     => "用户名有误，插入异常, 请检查用户名是否含特殊字符！",
		"fail_username_noexist"   => "发布用户登录名不存在,请到DEDE‘系统用户管理’获取登录ID！",
        "fail_db_save"  => "把数据保存到数据库主表出错",
		"fail_db_save_ext"  => "把数据保存到数据库附加表时出错！",
		"fail_insert_user"    => "插入用户失败,请联系管理员",
		"fail_post"    => "文章提交异常,请联系管理员",
		"fail_check_curl"    => "用户服务器不支持curl，无法使用本插件！",
	    "fail_install_locked"   => "安装程序被锁定，如果要重新安装，请删除文件：/e/install/install.off",
		"fail_install_page"    => "安装程序页面错误",
		"error_gen_litpic" =>"生成缩略图异常",		
    ),
);