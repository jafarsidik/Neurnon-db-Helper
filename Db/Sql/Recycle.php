<?php

namespace Neuron\Db\Sql;

class Recycle implements \Zend\Db\Sql\SqlInterface {

  const FLAG_USED = 0;
  const FLAG_UNUSED = 1;
  const FLAG_DEFAULT = 'unused';

  protected $_table = null;
  protected $_primary = null;
  protected $_set = null;
  protected $_exclude = array();
  protected $_flag = self::FLAG_DEFAULT;
  protected $_where = null;

  public function table($table) {

    $this->_table = $table;
    return $this;
  }

  public function primary($primary) {

    $this->_primary = $primary;
    return $this;
  }

  public function set(\Neuron\Generic\Set $set) {

    $this->_set = $set;
    return $this;
  }
  
  public function exclude($exclude = array()) {
    
    $this->_exclude = $exclude;
    return $this;
  }

  public function flag($flag) {

    $this->_flag = $flag;
    return $this;
  }

  public function where($where) {

    $this->_where = $where;
    return $this;
  }

  public function getSqlString(\Zend\Db\Adapter\Platform\PlatformInterface $adapterPlatform = null) {

    $data = new \stdClass();
    $data->table = $this->_table;
    $data->primary = $this->_primary;
    $data->set = $this->_set;
    $data->exclude = $this->_exclude;
    $data->flag = $this->_flag;
    $data->where = $this->_where;

    return $data;
  }
}