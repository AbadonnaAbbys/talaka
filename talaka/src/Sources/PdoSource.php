<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 16:19
 */

namespace Sources;

class PdoSource extends Source {

  const SECTION = 'pdo';

  const DSN = '%s:host=%s;dbname=%s;charset=%s';

  const OPTIONS = array(
    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
  );

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * @var array
   */
  private $config;

  /**
   * PdoSource constructor.
   * @param $data
   */
  public function __construct($data) {
    $this->config = $data;
    $this->setConnection();
  }

  /**
   * @param $id
   * @return \Record
   */
  public function getRecord($id) {
    $stmt = $this->pdo->prepare('SELECT `data` FROM `note` WHERE `id` = :id LIMIT 0,1');
    $stmt->execute(['id' => $id]);
    $data = json_decode($stmt->fetch()['data']);
    return new \Record($data);
  }

  /**
   * @return array
   */
  public function getAllRecords() {
    $res = [];
    $stmt = $this->pdo->query('SELECT `data` FROM `note` ORDER BY `id` DESC');
    foreach ($stmt as $row) {
      $data = unserialize($row['data']);
      $res[] = $data;
    }
    return $res;
  }

  /**
   * @param \Record $data
   * @return bool
   */
  public function setRecord(\Record $data) {
    if (!empty($data->getId())) {
      $stmt = $this->pdo->prepare('UPDATE `note` SET `data` = :data WHERE `id` = :id');
      return $stmt->execute(['id' => $data->getId(), 'data' => serialize($data)]);
    } else {
      $stmt = $this->pdo->prepare('INSERT INTO `note` SET `data` = :data');
      return $stmt->execute(['data' => serialize($data)]);
    }
  }

  /**
   * @param $id
   * @return bool
   */
  public function deleteRecord($id) {
    $stmt = $this->pdo->prepare('DELETE FROM `note` WHERE `id` = :id');
    return $stmt->execute(['id' => $id]);
  }

  /**
   * @return string
   */
  public function getLastId() {
    return $this->pdo->lastInsertId();
  }

  /**
   * @return mixed
   */
  public function getNextId() {
    $stmt = $this->pdo->query('SELECT MAX(`id`) + 1 AS next_id LIMIT 0,1');
    $stmt->execute();
    return $stmt->fetch()['next_id'];
  }

  /**
   * Создает подключение к базе данных
   */
  private function setConnection() {
    try {
      $dsn = sprintf(self::DSN, $this->config['driver'], $this->config['host'], $this->config['name'], $this->config['charset']);
      $this->pdo = new \PDO($dsn, $this->config['user'], $this->config['password'], self::OPTIONS);
    } catch (\Exception $e) {
      die('Подключение не удалось: ' . $e->getMessage());
    }
  }
}