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

/** @var \Sources\Source $source */
//$source = \Sources\Source::initial();

//$test_record = new Record();
//$test_record->setTitle('test title')->setText('test text')->setTimeAdd(new DateTime())->setTimeEdit(new DateTime());
//$source->setRecord($test_record);
//
//$test_record = $source->getRecord(4);
//$test_record->setTitle('updates 5 title');
//$test_record->setText('asrg dsgse ryghwerg gh fwaqehfgqkuwgef aquwsdefgbkjhsxvaesdhgfshdkjhdgfudsdmnvbajk sdkjh bsde fkjhbsa degadfk hd jhs jhzfkjhzsf jhsd fkjhsadfkjh zs  kbsdfkjhbsdfkj hs dkhjsdfkl jhasdfaksj dfakj uhg');
//$source->setRecord($test_record);

//var_dump($source->getAllRecords());
new Controller();