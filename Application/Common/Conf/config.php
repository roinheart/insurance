<?php
return array(
	//'配置项'=>'配置值'
	'TMPL_PARSE_STRING' => array(
		'__WEBNAME__' => '游客信息采集系统', //TITLE
		'__B__' => SITE_URL . '/Public/bower_components',
		'__D__' => SITE_URL . '/Public/dist',
		'__P__' => SITE_URL . '/Public/plugins',
		'__Public__' => SITE_URL . '/Public',
		'__URL__' => SITE_URL,
		'SESSION_AUTO_START' => true,
		'SESSION_OPTIONS' => array('expire' => 3600),
		'DEFAULT_MODULE' => 'Home',
		'URL_CASE_INSENSITIVE' => true, //URL不区分大小写
		'URL_MODEL' => 1, //INFOPATH模式
		'URL_HTML_SUFFIX' => 'html',
	),
);