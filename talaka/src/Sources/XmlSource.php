<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 17:44
 */

namespace Sources;

class XmlSource extends Source {

  const SECTION = 'xml';

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

  public function __construct($data) {

    $this->config = $data;
    $this->prepareFileName()->readNotes();
  }

  /**
   * @param $id
   * @return \Sources\Record
   */
  public function getRecord($id) {
    $node = $this->doc->getElementById($this->prepareId($id));
    $record = new Record($this->getNodeData($node));

    return $record;
  }

  public function getAllRecords() {
    $list = [];
    foreach ($this->doc->getElementsByTagName('note') as $node) {

      $list[] = $this->prepareRecord($node);
    }
    return $list;
  }

  /**
   * @param Record $data
   * @return $this
   */
  public function setRecord(Record $data) {
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
   * @param Record $data
   * @return $this
   */
  private function updateRecord(Record $data) {
    $noteNode = $this->doc->getElementById($this->prepareId($data->getId()));

    $noteNode->setAttribute('timeAdd', $data->getTimeAdd()->format(Record::DATE_FORMAT));
    $noteNode->setAttribute('timeEdit', (new \DateTime())->format(Record::DATE_FORMAT) );

    $noteNode->getElementsByTagName('title')[0]->nodeValue = $data->getTitle();
    $noteNode->getElementsByTagName('text')[0]->nodeValue = $data->getText();

    return $this;
  }

  /**
   * @param $data
   * @return $this
   */
  private function insertRecord($data) {
    /** @var \DOMNode $listNode */
    $listNode = $this->doc->getElementsByTagName('list')[0];

    $id = (int) $listNode->getAttribute('lastId');
    $listNode->setAttribute('lastId', ++$id);

    $noteNode = $this->doc->createElement('note');
    $noteNode->setAttribute('timeAdd', $data['timeAdd']);
    $noteNode->setAttribute('timeEdit', $data['timeEdit']);

    $titleNode = $this->doc->createElement('title', $data['title']);
    $textNode = $this->doc->createElement('text', $data['text']);
    $noteNode->appendChild($titleNode);
    $noteNode->appendChild($textNode);

    $idAttr = $this->doc->createAttribute('xml:id');
    $idTextNode = $this->doc->createTextNode($this->prepareId($id));
    $idAttr->appendChild($idTextNode);
    $noteNode->appendChild($idAttr);
    $noteNode->setIdAttribute('xml:id', TRUE);

    $listNode->appendChild($noteNode);

    $this->doc->appendChild($listNode);

    return $this;
  }

  /**
   * @param $id
   * @return string
   */
  private function prepareId($id) {
    return 'id_' . $id;
  }

  /**
   * @param $id
   * @return $this
   */
  public function deleteRecord($id) {
    $node = $this->doc->getElementById($id);
    $node->parentNode->removeChild($node);

    return $this;
  }

  /**
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
   * @return $this
   */
  private function createFile() {
    $this->doc = new \DOMDocument();
    $rootNode = $this->doc->createElement('list');
    $rootNode->setAttribute('lastId', 0);
    $this->doc->appendChild($rootNode);
    $this->doc->save($this->filename);
    return $this;
  }

  /**
   * @return $this
   */
  private function prepareFileName() {
    $this->filename = '..' . DIRECTORY_SEPARATOR . $this->config['folder'] . DIRECTORY_SEPARATOR . $this->config['file'];
    return $this;
  }

  /**
   * @param \DOMElement $node
   * @return array
   */
  private function getNodeData(\DOMElement $node) {
    $data = [];
    $data['id'] = substr($node->getAttribute('xml:id'), 3);
    $data['timeAdd'] = $node->getAttribute('timeAdd');
    $data['timeEdit'] = $node->getAttribute('timeEdit');
    $data['title'] = $node->getElementsByTagName('title')[0]->nodeValue;
    $data['text'] = $node->getElementsByTagName('text')[0]->nodeValue;

    return $data;
  }
}