<?php
return array(
	//'配置项'=>'配置值'
	// 加载数据库驱动
	'DB_TYPE'=>'mysql',
	'DB_HOST'=>'localhost',
	'DB_NAME'=>'db',
	'DB_USER'=>'root',
	'DB_PWD'=>'123456',
	'DB_PORT'=>'3306',
	'DB_CHARSET'=>'utf8',
	'TOKEN_ON'=>true,  // 是否开启令牌验证 默认关闭
	'TOKEN_NAME'=>'__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
	'TOKEN_TYPE'=>'md5',  //令牌哈希验证规则 默认为MD5
	'TOKEN_RESET'=>true,  //令牌验证出错后是否重置令牌 默认为true
);