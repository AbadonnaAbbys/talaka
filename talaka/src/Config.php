<?php

/**
 * Created by PhpStorm.
 * User: Abadonna
 * Date: 18.02.2016
 * Time: 16:43
 */
class Config {

  /**
   * @var Config
   */
  private static $instance;

  /**
   * @var array
   */
  private $config;

  /**
   * Config constructor.
   * @param $path
   * @throws \Exception
   */
  private function __construct($path) {
    $this->config = parse_ini_file($path, TRUE);
    if (FALSE === $this->config) {
      throw new \Exception('Нечитабельный файл config.ini');
    }
  }

  /**
   * @param string $path
   * @return Config
   */
  public static function getInstance($path = NULL) {
    if (!(self::$instance instanceof Config)) {
      self::$instance = new Config($path);
    }
    return self::$instance;
  }

  /**
   * @param $name
   * @return mixed
   * @throws \Exception
   */
  public static function getParam($name) {
    $config = self::getInstance()->config;
    if (isset($config[$name])) {
      return $config[$name];
    }
    else {
      throw new \Exception('Неизветный параметр конфигурации "' . $name . '"');
    }
  }
}