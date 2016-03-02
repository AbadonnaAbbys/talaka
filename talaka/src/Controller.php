<?php

/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 19.02.2016
 * Time: 12:18
 */
class Controller {
  /**
   * @var array
   */
  private $input;

  /**
   * @var array
   */
  private $output;

  /**
   * @var \Sources\Source
   */
  private $source;

  /**
   * @var array
   */
  private $actions = ['getRecord', 'setRecord', 'deleteRecord', 'getAllRecords', 'setRecords'];

  /**
   * Controller constructor.
   */
  public function __construct() {
    $this->input = $_POST;
    $this->output = ['code' => 200, 'message' => '', 'data' => []];
    $this->source = \Sources\Source::initial();
    if (!isset($this->input['action']) || !in_array($this->input['action'], $this->actions)) {
      $this->output['code'] = 404;
      $this->output['message'] =  'Неизветное действие';
      $this->response();
    } else {
      $method = $this->input['action'];
      $this->$method();
    }
  }

  /**
   * Возвращает одну запись по ID
   */
  private function getRecord() {
    if (isset($this->input['id'])) {
      $record = $this->source->getRecord($this->input['id']);
      if (!is_null($record->getId())) {
        $this->output['data'] = array($record->getJSON());
      } else {
        $this->output['code'] = 404;
        $this->output['message'] = 'Не найдена запись с ID:'.$this->input['id'];
      }
    } else {
      $this->output['code'] = 400;
      $this->output['message'] = 'Не задан ID записи';
    }
    $this->response();
  }

  /**
   * Возвращает все записи
   */
  private function getAllRecords() {
    $records = $this->source->getAllRecords();
    /**
     * @var Record $record
     */
    foreach ($records as $record) {
      $this->output['data'][] = $record->getJSON();
    }
    $this->response();
  }

  /**
   * Сохраняет запись в хранилище
   */
  private function setRecord() {
    $record = new Record($this->input);
    $this->source->setRecord($record);
    if ($record->isNew()) {
      $record->setId($this->source->getLastId());
    }
    $this->output['data'] = $record->getJSON();
    $this->response();
  }

  /**
   * Удаляет одну запись
   */
  private function deleteRecord() {
    $this->output['data'] = FALSE;
    if (!empty($this->input['id'])) {
      if ($this->source->deleteRecord($this->input['id'])) {
        $this->output['data'] = TRUE;
      }
    }
    $this->response();
  }

  /**
   * Выводит текущее значение $this->output в виде JSON документа и прерывает выполнение скрипта
   */
  private function response() {
    header('Content-type: application/json');
    echo json_encode($this->output);
    exit();
  }

}