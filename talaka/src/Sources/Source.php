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
   * Инициализирует и возвращает экземпляр класса хранилища в соответствии с заданными настройками
   * @return Source
   * @throws \Exception
   */
  public static function initial() {
    $class = __NAMESPACE__ . '\\' . \Config::getParam('class');
    $instance = new $class(\Config::getParam($class::SECTION));
    return $instance;
  }

  /**
   * Возвращает заметку по id
   * @param $id
   * @return \Record
   */
  abstract public function getRecord($id);

  /**
   * Возвращает массив из всех заметок
   * @return array
   */
  abstract public function getAllRecords();

  /**
   * Сохраняет заметку
   * @param \Record $data
   * @return bool
   */
  abstract public function setRecord(\Record $data);

  /**
   * Удаляет заметку
   * @param $id
   * @return bool
   */
  abstract public function deleteRecord($id);

  /**
   * Возвращает id последней добавленной записи
   * @return int
   */
  abstract public function getLastId();

  /**
   * Возвращает id на единицу больше, чем у последней добавленной записи
   * @return int
   */
  abstract public function getNextId();
}