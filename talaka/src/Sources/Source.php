<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 16:07
 */

namespace Sources;


abstract class Source {
  const SECTION = '';

  /**
   * @return Source
   * @throws \Exception
   */
  public static function initial() {
    $class = __NAMESPACE__ . '\\' . \Config::getParam('class');
    $instance = new $class(\Config::getParam($class::SECTION));
    return $instance;
  }

  /**
   * @param $id
   * @return \Record
   */
  abstract public function getRecord($id);

  /**
   * @return array
   */
  abstract public function getAllRecords();

  /**
   * @param \Record $data
   * @return bool
   */
  abstract public function setRecord(\Record $data);

  /**
   * @param $id
   * @return bool
   */
  abstract public function deleteRecord($id);

  abstract public function getLastId();

  abstract public function getNextId();
}