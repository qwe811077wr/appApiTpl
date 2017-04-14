<?php

require_once(dirname(__DIR__).DIRECTORY_SEPARATOR.'global-inc.php');

(new Yaf\Application(CFG . "application.ini"))->bootstrap()->run();
