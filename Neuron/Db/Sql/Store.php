<?php
/*
  Store->into('table')
       ->primary('field')
       ->values('data');
*/

namespace Neuron\Db\Sql;

class Store implements \Zend\Db\Sql\SqlInterface {

  protected $_into = null;
  protected $_primary = null;
  protected $_auto = true;
  protected $_values = array();

  public function into($into) {

    $this->_into = $into;
    return $this;
  }

  public function primary($primary, $auto = true) {

    $this->_primary = $primary;
    $this->_auto = $auto;
    return $this;
  }

  public function values($values = array()) {

    $this->_values = $values;
    return $this;
  }

  public function getSqlString(\Zend\Db\Adapter\Platform\PlatformInterface $adapterPlatform = null) {

    $data = new \stdClass();
    $data->into = $this->_into;
    $data->primary = $this->_primary;
    $data->auto = $this->_auto;
    $data->values = $this->_values;

    return $data;
  }
}