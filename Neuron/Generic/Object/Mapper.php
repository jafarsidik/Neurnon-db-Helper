<?php

namespace Neuron\Generic\Object;

class Mapper extends \Neuron\Generic\Object {

  const MODE_EXCLUDE = 0;
  const MODE_INCLUDE = 1;

  const CASE_SENSITIVE = 0;
  const CASE_INSENSITIVE = 1;

  private $_case;
  private $_map = array();

  public function __construct($data = array(), $map = array(), $case = self::CASE_SENSITIVE) {

    $this->_case = $case;

    $properties = array();
    foreach ($map as $key => $item) {
      if ($item instanceof Map) {
        $this->_map[($case == self::CASE_INSENSITIVE) ? strtolower($item->real) : $item->real] = $item;
        $properties[] = $item->alias;
      } else {
        $this->_map[($case == self::CASE_INSENSITIVE) ? strtolower($key) : $key] = new Map($key, $item);
        $properties[] = $item;
      }
    }

    parent::__construct($data, $properties);
  }

  public function map($real, $alias = null, $pseudo = false, $data = null, $store = true) {

    $map = new Map($real, $alias, $pseudo);
    if ($store) {
      $this->_map[($this->_case == self::CASE_INSENSITIVE) ? strtolower($real) : $real] = $map;
      parent::extend($alias == null ? $real : $alias, $data);
    }
    return $map;
  }

  public function get($name) {

    return $this->__get($this->_map[$name]->alias);
  }

  public function set($name, $value) {

    return $this->__set($this->_map[$name]->alias, $value);
  }

  public function push($data, $map = true) {

    if ($map && $this->_hasProperties) {

      if (is_array($data)) {

        foreach ($data as $name => $value) {

          $name = ($this->_case == self::CASE_INSENSITIVE) ? strtolower($name) : $name;

          if (array_key_exists($name, $this->_map) === false) {
            parent::notice('push', $name);
          } else {
            $this->_data[$this->_map[$name]->alias] = $value;
          }
        }

      }

    } else {

      /* raw push */
      parent::push($data);

    }

  }

  public function pull($mode = self::MODE_EXCLUDE) {

    $properties = array();

    /* init output */
    $data = array();
    if ($mode == self::MODE_INCLUDE) {

      /* include only data in properties */
      foreach ($this->_map as $key => $value) {
        if (!$value->pseudo) {
          if (array_search($key, $properties) !== false) {
            $data[$key] = $this->_data[$value->alias];
          }
        }
      }

    } else {

      /* exclude data in properties array */
      foreach ($this->_map as $key => $value) {
        if (!$value->pseudo) {
          if (array_search($key, $properties) === false) {
            $data[$key] = $this->_data[$value->alias];
          }
        }
      }

    }

    return $data;
  }

}