<?php

/* resultset converter, untuk formatting data dari resultset sesuai interface yg digunakan */

namespace Neuron\Utility\Db\ResultSet;

class Converter {

  private $_resultset = null;

  function __construct(\Zend\Db\ResultSet\ResultSet $resultset) {

    if ($resultset) {
      $this->_resultset = $resultset;
    }
  }

  public function getResultSet() {

    return $this->_resultset;
  }

  public function toArray() {

    if ($this->_resultset != null) {
      return $this->_resultset->toArray();
    }
    return array();
  }

  public function toOptions($default = null) {

    $options = '';

    if ($this->_resultset != null) {

      $name = null;
      $value = null;
      $count = 0;

      foreach ($this->_resultset as $row) {

        /* get value and name field name */
        if ($count == 0) {
          foreach ($row as $field => $temp) {
            if ($count == 0) {
              $value = $field;
            } else {
              $name = $field;
              break;
            }
            $count++;
          }
        }

        /* value and name field ok? */
        if ($count > 0) {
          if ($row[$value] == $default) {
            $options .= '<option value="' . $row[$value] . '" selected="selected">' . $row[$name] . '</option>';
          } else {
            $options .= '<option value="' . $row[$value] . '">' . $row[$name] . '</option>';
          }
        }

      }
    }
    return $options;
  }

  public function toJqxGrid() {
  /* convert resultset ke bentuk data yang bisa diterima oleh jqxgrid */

    return $this->toArray();
  }
}