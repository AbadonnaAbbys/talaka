<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 16:13
 */

/**
 * Class Record
 */
class Record {

  /**
   * @var integer
   */
  private $id;

  /**
   * @var string
   */
  private $title;

  /**
   * @var string
   */
  private $text;

  /**
   * @var \DateTime
   */
  private $timeEdit;

  /**
   * @var \DateTime
   */
  private $timeAdd;

  /**
   * Record constructor.
   * @param null $data
   */
  public function __construct($data = null) {
    if (is_array($data)) {
      if (isset($data['id'])) {
        $this->setId($data['id']);
      }
      if (isset($data['title'])) {
        $this->setTitle($data['title']);
      }
      if (isset($data['text'])) {
        $this->setText($data['text']);
      }
      if (isset($data['timeAdd'])) {
        $this->setTimeAdd($data['timeAdd']);
      }
      if (isset($data['timeEdit'])) {
        $this->setTimeEdit($data['timeEdit']);
      }
    }
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param int $id
   * @return $this
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param string $title
   * @return $this
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * @return string
   */
  public function getText() {
    return $this->text;
  }

  /**
   * @param string $text
   * @return $this
   */
  public function setText($text) {
    $this->text = $text;
    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getTimeEdit() {
    return $this->timeEdit;
  }

  /**
   * @param \DateTime $timeEdit
   * @return $this
   */
  public function setTimeEdit($timeEdit) {
    if ($timeEdit instanceof \DateTime) {
      $this->timeEdit = $timeEdit;
    } else {
      $this->timeEdit = new \DateTime($timeEdit);
    }
    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getTimeAdd() {
    return $this->timeAdd;
  }

  /**
   * @param \DateTime $timeAdd
   * @return $this
   */
  public function setTimeAdd($timeAdd) {
    if ($timeAdd instanceof \DateTime) {
      $this->timeAdd = $timeAdd;
    } else {
      $this->timeAdd = new \DateTime($timeAdd);
    }
    return $this;
  }

  /**
   * @param bool $isJson
   * @return array
   * @throws \Exception
   */
  public function getDataArray($isJson = false) {
    $data = [
      'id' => $this->id,
      'title' => $this->title,
      'text' => $this->text,
      'timeAdd' => $isJson ? $this->timeAdd->format(DATE_W3C) : $this->timeAdd->format(Config::getParam('date_format')),
      'timeEdit' => $isJson ? $this->timeEdit->format(DATE_W3C) : $this->timeEdit->format(Config::getParam('date_format')),
    ];
    return $data;
  }

  /**
   * @return bool
   */
  public function isNew() {
    return empty($this->id);
  }

  /**
   * @return string
   */
  public function getJSON() {
    return json_encode($this->getDataArray(true));
  }
}