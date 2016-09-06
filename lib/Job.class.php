<?php

class Job {
  static private $_instances = array();
  static public  $statuses = array(
    'ready'    => 'Возможен запуск',
    'start'    => 'Ожидает запуска',
    'started'  => 'Запущен',
    'finished' => 'Завершен',
    'failed'   => 'Возникла ошибка',
    'stopped'  => 'Остановлен',
  );
  private
    $_name     = null,
    $_file     = null;

  /**
   * @param string $name
   * @return Job
   * @throws Exception
   */
  public static function get($name)
  {
    if (empty($name)) {
      throw new Exception('Passing an empty Job name');
    }

    if (!isset(self::$_instances[$name])) {
      self::$_instances[$name] = new self($name);
    }
    return self::$_instances[$name];
  }

  /**
   * Private constructor
   * @param string $name status file name
   * @return Job
   * @throws Exception
   */
  private function __construct($name)
  {
    $this->_name = $name;
    $this->_file = sfConfig::get('sf_data_dir') . '/job/' . $name;

    $dir = dirname($this->_file);
    if (!is_dir($dir)) {
      if (!mkdir($dir, 0777, true)) {
        throw new Exception(sprintf('Directory "%s" is not exists', $dir));
      }
    }
    if (!is_writable($dir)) {
      throw new Exception(sprintf('Directory "%s" is not not available for writing', $dir));
    }

    return $this;
  }

  /**
   * 'Magic' getter
   * @param string $param
   * @return mixed
   */
  public function __get($param)
  {
    $method = 'get' . sfInflector::camelize($param);

    if (is_callable(array($this, $method))) {
      return $this->$method();
    }

    return null;
  }

  /**
   * Returns the job's current status
   * @return string
   */
  public function getStatus()
  {
    return $this->readFile();
  }

  /**
   * Returns the job's current status text representation
   * @return string
   */
  public function getStatusText()
  {
    return self::$statuses[$this->readFile()];
  }


  /**
   * Can Job run or not
   * @return boolean
   */
  public function canRun()
  {
    return $this->status == 'ready' || $this->status == 'finished';
  }

  /**
   * Should Job start or not
   * @return boolean
   */
  public function canStart()
  {
    return $this->status == 'start';
  }


  /**
   * Run the Job!
   * Actually nothings gonna be run right now.
   * @todo rename to smth more representative
   * @return void
   * @throws Exception
   */
  public function run()
  {
    $this->writeFile('start');
  }

  /**
   * Set Job as started
   * @return void
   * @throws Exception
   */
  public function start()
  {
    $this->writeFile('started');
  }

  /**
   * Set Job as finished
   * @return void
   * @throws Exception
   */
  public function finish()
  {
    $this->writeFile('finished');
  }

  /**
   * Set Job as failed
   * @return void
   * @throws Exception
   */
  public function fail()
  {
    $this->writeFile('failed');
  }

  /**
   * Mark Job as stopped. After this the only available 'write' action will be 'unstop'
   * Can be usefull for debug or maintance
   * @return void
   * @throws Exception
   */
  public function stop()
  {
    $this->writeFile('stopped');
  }

  /**
   * Set Job as no longer stopped.
   * @see self::stop
   * @return void
   * @throws Exception
   */
  public function unstop()
  {
    $this->writeFile('ready');
  }


  /**
   * Returns file contents or default if no file exists
   * @param string $default
   * @return string
   */
  private function readFile($default = 'ready')
  {
    if (file_exists($this->_file) && is_readable($this->_file)) {
      return trim(file_get_contents($this->_file));
    }

    return $default;
  }

  /**
   * Write current status to file. If no file exists, it will be created
   * @param string $status
   * @return boolean
   * @throws Exception
   */
  private function writeFile($status)
  {
    if ($this->getStatus() == 'stopped' && $status != 'ready') {
      throw new Exception(sprintf('Failed to set status "%s" to Job "%s"', $status, $this->_name));
    }

    return (boolean) file_put_contents($this->_file, $status);
  }
}