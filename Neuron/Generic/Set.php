<?php

namespace Neuron\Generic;

class Set implements \Countable, \Iterator {

  protected $_list = array();
  protected $_index = 0;
  protected $_count = 0;

  public function __construct($list = array()) {

    foreach ($list as $item) {
      $this->_list[] = $item;
    }
    $this->_count = count($this->_list);
  }

  public function find($property, $value) {

    foreach ($this->_list as $item) {
      //if (isset($item->{$property})) {
        if ($item->{$property} == $value) {
          return $item;
        }
      //}
    }
    return false;
  }

  public function set($property, $value) {

    foreach ($this->_list as $item) {
      $item->{$property} = $value;
    }
    return true;
  }

  /* countable */
  public function count() {

    return $this->_count;
  }

  /* iterator */
  public function rewind() {

    $this->_index = 0;
  }

  public function next() {

    $this->_index++;
  }

  public function current() {

    return $this->_list[$this->_index];
  }

  public function key() {

    return $this->_key;
  }

  public function valid() {

    return ($this->_index < $this->_count);
  }

  public function clear() {

    unset($this->_list);
    $this->_list = array();
    $this->_count = 0;
  }

  public function add(\Neuron\Generic\Object $object) {

    if ($object) {
      $this->_list[] = $object;
      $this->_count = count($this->_list);
      return $this->_count - 1;
    }
    return false;
  }

  public function remove($index) {

    if (array_key_exists($index, $this->_list)) {
      array_splice($this->_list, $index, 1);
      $this->_count = count($this->_list);
      return true;
    }
    return false;
  }

  public function item($index) {

    return $this->_list[$index];
  }

  public function items() {

    return $this->_list;
  }
}