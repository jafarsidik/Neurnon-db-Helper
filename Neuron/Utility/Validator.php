<?php

namespace Neuron\Utility;

class Validator {

  protected $_items = array();
  protected $_values = array();

  protected $_valid = true;
  protected $_field = null;
  protected $_messages = array();

  public function __construct($items = array(), $values = array()) {

    /* run validator */
    foreach($items as $key => $item) {

      /* apply default for unset value, and get value */
      if (isset($values[$key])) {
        $value = $values[$key];
        $items[$key]['value'] = $value;
      } else {
        if (isset($item['value'])) {
          $value = $item['value'];
        } else {
          $value = null;
          $items[$key]['value'] = null;
        }
      }

      /* store values internally */
      $this->_values[$key] = $value;

      /* loop roles */
      foreach ($item as $role => $options) {

        /* exclude value */
        if ($role != 'value') {

          /* prepare validation chain */
          $chain = new \Zend\Validator\ValidatorChain();

          /* attach role to chain */
          switch ($role) {
            case 'notempty': $chain->attach(new \Zend\Validator\NotEmpty($options)); break;
            case 'alnum': $chain->attach(new \Zend\I18n\Validator\Alnum($options)); break;
            case 'digits': $chain->attach(new \Zend\Validator\Digits($options)); break;
            case 'greaterthan': $chain->attach(new \Zend\Validator\GreaterThan($options)); break;
          }

          /* validate, if invalid exit loop! */
          if (!($this->_valid = $chain->isValid($value))) {
            $this->_field = $key;
            $this->_messages[$key] = $chain->getMessages();
            break;
          }

        } //value check

      }

      /* invalid? just exit loop */
      if (!$this->_valid) { break; }

    }

    /* store items internally */
    $this->items = $items;
  }

  public function isValid() {

    return $this->_valid;
  }

  public function getField() {

    return $this->_field;
  }

  public function getMessages($string = false) {

    if ($string) {
      $messages = array();
      foreach ($this->_messages as $key => $list) {
        $messages[$key] = '';
        foreach ($list as $item) {
          if (strlen($messages[$key]) > 0) {
            $messages[$key] .= ', ' . $item;
          } else {
            $messages[$key] .= $item;
          }
        }
      }
      return $messages;

    } else {
      return $this->_messages;
    }
  }

  public function toArray() {

    return $this->_values;
  }

}