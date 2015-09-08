<?php

namespace Neuron\Generic;

class Object {

  protected $_properties = array();
  protected $_hasProperties = false;
  protected $_data = array();

  protected function notice($function, $property) {

    /* trace! */
    $traces = debug_backtrace();
    if (count($traces) > 0) {

      /* build error message */
      $count = 0;
      $message = "Not allowed property '{$property}' via {$function}() {<pre>\r\n";
      foreach ($traces as $trace) {
        if (isset($trace['file'])) {
          $message .= " In {$trace['file']} at line {$trace['line']}\r\n";
          $count++;
          if ($count >= 5) { break; }
        }
      }
      $message .= "</pre>}";

      /* trigger! */
      trigger_error($message, E_USER_NOTICE);
    }
  }

  public function __construct($data = array(), $properties = array()) {

    /* set properties as set/get constraint */
    $count = 0;
    foreach ($properties as $name) {
      $name = trim($name);
      if ((!is_numeric($name)) && (strlen($name) > 0)) {
        $this->_properties[] = $name;
        $this->_data[$name] = null;
        $count++;
      }
    }
    if ($count > 0) { $this->_hasProperties = true; }

    $this->push($data);
  }

  public function __set($name, $value) {

    if ($this->_hasProperties) {
      if (array_search($name, $this->_properties) === false) {
        $this->notice('__set', $name);
      } else {
        $this->_data[$name] = $value;
      }
    } else {
      $this->_data[$name] = $value;
    }
  }

  public function __get($name) {

    if ($this->_hasProperties && ($name == 'properties')) {
      return $this->_properties;
    }

    if (array_key_exists($name, $this->_data)) {
      return $this->_data[$name];
    }

    $this->notice('__get', $name);

    return null;
  }

  public function extend($property, $data = null) {

    if (!array_search($property, $this->_properties)) {
      $this->_properties[] = $property;
      $this->_data[$property] = $data;
      $this->_hasProperties = true;
    }
  }

  public function push($data) {

    if (is_array($data)) {
      if ($this->_hasProperties) {
        foreach ($data as $name => $value) {
          if (array_search($name, $this->_properties) === false) {
            $this->notice('push', $name);
          } else {
            $this->_data[$name] = $value;
          }
        }
      } else {
        $this->_data = array_merge($this->_data, $data);
      }
    }
  }

  public function pull() {

    return $this->_data;
  }
}