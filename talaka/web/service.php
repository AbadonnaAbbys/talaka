<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 14:57
 */

require_once '../src/Config.php';
$config = Config::getInstance('../config/config.ini');

require_once '../src/Sources/Source.php';
require_once '../src/Record.php';
require_once '../src/Sources/' . $config->getParam('class') . '.php';
require_once '../src/Controller.php';

new Controller();