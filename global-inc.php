<?php

//基本常量
define('_', DIRECTORY_SEPARATOR);
define('BASE', __DIR__._);
define('APP', BASE.'app'._);
define('CFG', BASE.'cfg'._);
define('LIB', APP. 'library'._);
define('VENDER', BASE. 'vender'._);

//设置默认时区
date_default_timezone_set('Asia/Shanghai');