<?php
namespace Neuron\Mvc;

use Zend\Mvc\Controller\AbstractActionController;

class Controller extends \Zend\Mvc\Controller\AbstractActionController {

  protected $_db = null;
  protected $_config = array();

  public function onDispatch(\Zend\Mvc\MvcEvent $e) {

    /* get config! */
    $this->_config = $this->getServiceLocator()->get('Config');

    /* apply php settings */
    if (isset($this->_config['php'])) {
      $php = $this->_config['php'];
      foreach ($php as $key => $value) {
        ini_set($key, $value);
        if ($key == 'error_reporting') {
          error_reporting($value);
        }
      }
    }

    /* go dispatch! */
    return parent::onDispatch($e);
  }

  public function getConfig() {

    return $this->_config;
  }

  public function getDb($module = __NAMESPACE__) {

    /* not already loaded? */
    if ($this->_db == null) {

      /* get current config */
      $temp = $this->_config;

      /* primary connection set? */
      if (isset($temp['databases']['primary'])) {

        /* get adapter config and schema list */
        $config = $temp['databases']['primary'];
        if (isset($config['schemas'])) {
          $schemas = $config['schemas'];
          unset($config['schemas']);
        } else {
          $schemas = array();
        }

        /* remove unnecessary part from module / namespace */
        $part = explode('\\', strtolower($module));
        if ($part !== false) {
          $min = 0;
          $max = count($part) - 1;
          if ($part[0] == 'neuron') { $min = 1; }
          if ($max > ($min + 1)) { $max = $min + 1; }
          $module = $part[$min] . '\\' . $part[$max];
        }

        /* module found */
        if (isset($schemas[$module])) {
          $config['schema'] = $schemas[$module];
        }

        /* debug */
        //echo($module);

        /* Oci8? */
        if ($config['driver'] == 'Oci8') {
          /* Oci8: generate TNS */
          $config['connection'] = '(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP) (HOST = '.$config['host'].') (PORT = '.(isset($config['port']) ? $config['port'] : '1521').')) (CONNECT_DATA = (SERVICE_NAME = '.$config['schema'].')))';
          $config['character_set'] = 'AL32UTF8';
        } else {
          /* MySQL: set dsn */
          $config['dsn'] = 'mysql:dbname=' . $config['schema'] . ';host=' . $config['host'];
        }
        //print_r($config);

        /* return adapter */
        return new \Zend\Db\Adapter\Adapter($config);

      } /* config set */

    } /* db null */

    return $this->_db;
  }
}