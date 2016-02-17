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

  public static function initial(Array $config) {
    $class = get_called_class();
    $instance = new $class($config[static::SECTION]);

    return $instance;
  }
  /**
   * @param $id
   * @return Record
   */
  abstract public function getRecord($id);

  /**
   * @return array
   */
  abstract public function getAllRecords();

  /**
   * @param Record $data
   * @return bool
   */
  abstract public function setRecord(Record $data);

  /**
   * @param $id
   * @return bool
   */
  abstract public function deleteRecord($id);
}