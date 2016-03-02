<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 17:44
 */

namespace Sources;

/**
 * Class XmlSource
 * @package Sources
 */
class XmlSource extends Source {

  /**
   * Секция файла конфигурации
   */
  const SECTION = 'xml';

  const ATTR_LAST_ID = 'lastId';
  const ATTR_TIME_ADD = 'timeAdd';
  const ATTR_TIME_EDIT = 'timeEdit';
  const ATTR_ID = 'xml:id';

  const NODE_LIST = 'list';
  const NODE_NOTE = 'note';
  const NODE_TITLE = 'title';
  const NODE_TEXT = 'text';

  /**
   * @var array
   */
  private $config;

  /**
   * @var string
   */
  private $filename;

  /**
   * @var \DOMDocument
   */
  private $doc;

  /**
   * @var integer
   */
  private $lastId = null;

  /**
   * XmlSource constructor.
   * @param $data
   */
  public function __construct($data) {

    $this->config = $data;
    $this->prepareFileName()->readNotes();
  }

  /**
   * Возвращает заметку по id
   * @param $id
   * @return \Record
   */
  public function getRecord($id) {
    $node = $this->doc->getElementById($this->prepareId($id));
    $record = new \Record($this->getNodeData($node));

    return $record;
  }

  /**
   * Возвращает массив из всех заметок
   * @return array
   */
  public function getAllRecords() {
    $list = [];
    foreach ($this->doc->getElementsByTagName(self::NODE_NOTE) as $node) {
      $list[] = new \Record($this->getNodeData($node));
    }
    return $list;
  }

  /**
   * Сохраняет заметку
   * @param \Record $data
   * @return $this
   */
  public function setRecord(\Record $data) {
    if ($data->isNew()) {
      $this->insertRecord($data->getDataArray());
    }
    else {
      $this->updateRecord($data);
    }

    $this->doc->save($this->filename);

    return $this;
  }

  /**
   * @param \Record $data
   * @return $this
   */
  private function updateRecord(\Record $data) {
    $noteNode = $this->doc->getElementById($this->prepareId($data->getId()));

    $noteNode->setAttribute(self::ATTR_TIME_ADD, $data->getTimeAdd()
      ->format(\Config::getParam('date_format')));
    $noteNode->setAttribute(self::ATTR_TIME_EDIT, (new \DateTime())->format(\Config::getParam('date_format')));

    $noteNode->getElementsByTagName(self::NODE_TITLE)[0]->nodeValue = $data->getTitle();
    $noteNode->getElementsByTagName(self::NODE_TEXT)[0]->nodeValue = $data->getText();

    return $this;
  }

  /**
   * @param $data
   * @return $this
   */
  private function insertRecord($data) {

    $listNode = $this->getNodeList();

    $this->lastId = $this->getNextId();
    $this->setLastId($this->lastId);

    $noteNode = $this->doc->createElement(self::NODE_NOTE);
    $noteNode->setAttribute(self::ATTR_TIME_ADD, $data[self::ATTR_TIME_ADD]);
    $noteNode->setAttribute(self::ATTR_TIME_EDIT, $data[self::ATTR_TIME_EDIT]);

    $titleNode = $this->doc->createElement(self::NODE_TITLE, $data[self::NODE_TITLE]);
    $textNode = $this->doc->createElement(self::NODE_TEXT, $data[self::NODE_TEXT]);
    $noteNode->appendChild($titleNode);
    $noteNode->appendChild($textNode);

    $idAttr = $this->doc->createAttribute(self::ATTR_ID);
    $idTextNode = $this->doc->createTextNode($this->prepareId($this->lastId));
    $idAttr->appendChild($idTextNode);
    $noteNode->appendChild($idAttr);
    $noteNode->setIdAttribute(self::ATTR_ID, TRUE);

    $listNode->appendChild($noteNode);

    $this->doc->appendChild($listNode);

    return $this;
  }

  /**
   * Форматирует id заметки для xml ноды
   * @param $id
   * @return string
   */
  private function prepareId($id) {
    return 'id_' . $id;
  }

  /**
   * Удаляет заметку
   * @param $id
   * @return bool
   */
  public function deleteRecord($id) {
    $noteNode = $this->doc->getElementById($this->prepareId($id));
    if ($noteNode instanceof \DOMElement) {
      $noteNode->parentNode->removeChild($noteNode);
      $this->doc->save($this->filename);
      return true;
    }
    return false;
  }

  /**
   * Читает заметки из файла
   * @return $this
   */
  private function readNotes() {
    if (!file_exists($this->filename)) {
      $this->createFile();
    }
    else {
      $this->doc = new \DOMDocument();
      $this->doc->load($this->filename);
    }
    return $this;
  }

  /**
   * Создает файл хранилища
   * @return $this
   */
  private function createFile() {
    $this->doc = new \DOMDocument();
    $rootNode = $this->doc->createElement(self::NODE_LIST);
    $rootNode->setAttribute(self::ATTR_LAST_ID, 1);
    $this->doc->appendChild($rootNode);
    $this->doc->save($this->filename);
    return $this;
  }

  /**
   * Формирует относительный путь к файлу хранилища в соответствии с настройками
   * @return $this
   */
  private function prepareFileName() {
    $this->filename = '..' . DIRECTORY_SEPARATOR . $this->config['folder'] . DIRECTORY_SEPARATOR . $this->config['file'];
    return $this;
  }

  /**
   * Возвращает данные из заметки в хранилище в виде массива
   * @param \DOMElement $node
   * @return array
   */
  private function getNodeData(\DOMElement $node) {
    $data = array();
    $data['id'] = substr($node->getAttribute(self::ATTR_ID), 3);
    $data[self::ATTR_TIME_ADD] = $node->getAttribute(self::ATTR_TIME_ADD);
    $data[self::ATTR_TIME_EDIT] = $node->getAttribute(self::ATTR_TIME_EDIT);
    $data[self::NODE_TITLE] = $node->getElementsByTagName(self::NODE_TITLE)[0]->nodeValue;
    $data[self::NODE_TEXT] = $node->getElementsByTagName(self::NODE_TEXT)[0]->nodeValue;

    return $data;
  }

  /**
   * Возвращает id последней добавленной записи
   * @return int
   */
  public function getLastId() {
    return $this->lastId;
  }

  /**
   * Возвращает id на единицу больше, чем у последней добавленной записи
   * @return int
   */
  public function getNextId() {
    return (int) $this->getNodeList()->getAttribute(self::ATTR_LAST_ID) + 1;
  }

  /**
   * Сохраняет id последней добавленной записи в хранилище
   * @param $id
   * @return $this
   */
  private function setLastId($id) {
    $nodeList = $this->getNodeList();
    $nodeList->setAttribute(self::ATTR_LAST_ID, $id);
    $this->doc->appendChild($nodeList);
    return $this;
  }

  /**
   * @return \DOMElement
   */
  private function getNodeList() {
    return $this->doc->getElementsByTagName(self::NODE_LIST)[0];
  }
}