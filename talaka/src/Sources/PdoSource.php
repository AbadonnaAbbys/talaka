<?php
/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 08.02.2016
 * Time: 16:19
 */

namespace Sources;

/**
 * Class PdoSource
 * @package Sources
 */
class PdoSource extends Source {

  /**
   * Секция файла конфигурации
   */
  const SECTION = 'pdo';

  /**
   * Строка шаблона инициализации соединения с базой данных
   */
  const DSN = '%s:host=%s;dbname=%s;charset=%s';

  /**
   * Опции инициализации соединения с базой данных
   */
  const OPTIONS = array(
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
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
   * Возвращает заметку по id
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
   * Возвращает массив из всех заметок
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
   * Сохраняет заметку
   * @param \Record $data
   * @return bool
   */
  public function setRecord(\Record $data) {
    if (!empty($data->getId())) {
      $stmt = $this->pdo->prepare('UPDATE `note` SET `data` = :data WHERE `id` = :id');
      return $stmt->execute([
        'id' => $data->getId(),
        'data' => serialize($data)
      ]);
    }
    else {
      $stmt = $this->pdo->prepare('INSERT INTO `note` SET `data` = :data');
      $res = $stmt->execute(['data' => serialize($data)]);
      if ($res) {
        $data->setId($this->pdo->lastInsertId());
        $res = $this->setRecord($data);
      }
      return $res;
    }
  }

  /**
   * Удаляет заметку
   * @param $id
   * @return bool
   */
  public function deleteRecord($id) {
    $stmt = $this->pdo->prepare('DELETE FROM `note` WHERE `id` = :id');
    return $stmt->execute(['id' => $id]);
  }

  /**
   * Возвращает id последней добавленной записи
   * @return int
   */
  public function getLastId() {
    return (int) $this->pdo->lastInsertId();
  }

  /**
   * Возвращает id на единицу больше, чем у последней добавленной записи
   * @return int
   */
  public function getNextId() {
    $stmt = $this->pdo->query('SELECT MAX(`id`) + 1 AS next_id LIMIT 0,1');
    $stmt->execute();
    return (int) $stmt->fetch()['next_id'];
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