<?php
namespace Neuron\Db\Sql;

use \Zend\Db\Adapter\AdapterInterface;
use \Zend\Db\Adapter\StatementContainerInterface;
use \Zend\Db\Adapter\ParameterContainer;

class Insert extends \Zend\Db\Sql\Insert {

  protected $autoincrement = null;
  protected $sequence = null;

  protected $hasAutoincrement = false;

  public function autoincrement($field, $sequence = null) {

    $this->autoincrement = $field;
    $this->sequence = $sequence;
    return $this;
  }

  public function getRawState($key = null) {

    $rawState = array(
      'table' => $this->table,
      'columns' => $this->columns,
      'values' => $this->values,
      'autoincrement' => $this->autoincrement,
      'sequence' => $this->sequence,
    );
    return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
  }

  public function hasAutoincrement() {

    if ($this->hasAutoincrement !== false) {
      return $this->autoincrement;
    }
    return false;
  }

  public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer) {

    /* init */
    $this->hasAutoincrement = false;

    /* parent prep */
    $result = parent::prepareStatement($adapter, $statementContainer);

    /* oci8 with autoincrement */
    if (($statementContainer instanceof \Zend\Db\Adapter\Driver\Oci8\Statement) && ($this->autoincrement !== null)) {

      /* get sequence */
      if ($this->sequence === null) { $this->sequence = 'SEQ_' . $this->table; }

      /* replace ai field with sequence & move ai field binding to the end with returning */
      $count = 0;
      $sql = preg_replace('/:'.$this->autoincrement.'\s*/', $this->sequence.'.NEXTVAL', $statementContainer->getSql(), 1, $count) . ' RETURNING "' . $this->autoincrement . '" INTO :' . $this->autoincrement;

      /* anything replaced? */
      if ($count > 0) {

        /* prep statement to prep resource */
        $statementContainer->setSql($sql);
        $statementContainer->prepare();

        /* unset ai field */
        $statementContainer->getParameterContainer()->offsetUnset($this->autoincrement);

        /* get ai field position on values */
        $position = array_search($this->autoincrement, $this->columns);
        $this->values[$position] = 0;
        $this->hasAutoincrement = true;
        oci_bind_by_name($statementContainer->getResource(), $this->autoincrement, $this->values[$position], -1, SQLT_INT);

      }

    } //oci8 AI

    return $result;
  }
}