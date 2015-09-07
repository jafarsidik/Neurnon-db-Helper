<?php
namespace Neuron\Db;

use Neuron\Db\Storage\Helper;

class Storage {

  const DRIVER_MYSQL = 0;
  const DRIVER_OCI8 = 1;

  protected $_db;
  protected $_sql;
  protected $_driver;
  protected $_helper;

  protected $_now;

  protected function _($string, $prefix = '') {

    if ($this->_driver == self::DRIVER_OCI8) {
      return $prefix . strtoupper($string);
    }
    return $prefix . $string;
  }

  protected function __($list) {

    if ($this->_driver == self::DRIVER_OCI8) {
      return array_change_key_case($list, CASE_UPPER);
    }
    return $list;
  }

  protected function ___($list) {

    if ($this->_driver == self::DRIVER_OCI8) {
      foreach ($list as $key => $item) {
        $list[$key] = strtoupper($item);
      }
    }
    return $list;
  }

  public function __construct(\Zend\Db\Adapter\Adapter $adapter) {

    $this->_db = $adapter;
    $this->_sql = new \Zend\Db\Sql\Sql($this->_db);

    if ($this->_db->getDriver() instanceof \Zend\Db\Adapter\Driver\Oci8\Oci8) {
      $this->_driver = self::DRIVER_OCI8;
      $this->_now = new \Zend\Db\Sql\Expression("SYSDATE");
    } else {
      $this->_driver = self::DRIVER_MYSQL;
      $this->_now = new \Zend\Db\Sql\Expression("now()");
    }

    $this->_helper = new Helper($this);
  }

  public function getAdapter() {

    return $this->_db;
  }

  public function getDriver() {

    return $this->_driver;
  }

  public function getHelper() {

    return $this->_helper;
  }

  public function select() {

    return $this->_sql->select();
  }

  public function insert() {

    return new Sql\Insert();
    return $this->_sql->insert();
  }

  public function update() {

    return $this->_sql->update();
  }

  public function delete() {

    return $this->_sql->delete();
  }

  public function store() {

    return new Sql\Store($this->_db);
  }

  public function recycle() {

    return new Sql\Recycle($this->_db);
  }

  public function fetchAll(\Zend\Db\Sql\Select $select, $raw = true) {

    $statement = $this->_sql->prepareStatementForSqlObject($select);
    //var_dump($statement); echo '<hr>';

    if ($result = $statement->execute()) {

      if (($this->_driver != self::DRIVER_OCI8) && (count($result) == 0)) { return false; }

      if ((!$raw) && ($result instanceof ResultInterface)) {
        $resultset = new \Zend\Db\ResultSet\ResultSet();
        $resultset->initialize($result);
        return $resultset;
      }

      return $result;
    }

    return false;
  }

  public function fetchRow(\Zend\Db\Sql\Select $select) {

    if ($this->_driver != self::DRIVER_OCI8) {
      $select->limit(1);
    }

    if ($result = $this->fetchAll($select)) {
      foreach ($result as $row) {
        return $row;
      }
    }
    return null;
  }

  public function fetchValue(\Zend\Db\Sql\Select $select, $field = null) {

    if ($row = $this->fetchRow($select)) {
      if ($field) {
        if (array_key_exists($field, $row)) {
          return $row[$field];
        }
      } else {
        foreach ($row as $data) {
          return $data;
        }
      }
    }
    return null;
  }

  public function execute(\Zend\Db\Sql\SqlInterface $query) {

    /* recycle? */
    if ($query instanceof Sql\Recycle) {

      /* get recycle target data */
      if ($target = $query->getSqlString()) {

        /* init */
        $count = $target->set->count();
        $saved = 0;

        /* table, primary, flag field exists? */
        if (($target->table) && ($target->primary) && ($target->flag)) {

          /* any data to save? */
          if ($count > 0) {

            /* select existing */
            $select = $this->select()
                      ->from($target->table, array($target->primary, $target->flag))
                      ->where($target->where);
            if ($existing = $this->fetchAll($select)) {

              /* loop existing and update */
              foreach ($existing as $row) {

                /* all new record saved? */
                if ($saved >= $count) {

                  /* flag remaining records to unused */
                  $update = $this->update()
                            ->table($target->table)
                            ->set(array($target->flag => $query::FLAG_UNUSED))
                            ->where(array($target->primary => $row[$target->primary]));
                  $this->execute($update);

                } else {

                  /* overwrite existing record with new data */
                  $update = $this->update()
                            ->table($target->table)
                            ->set(array_merge(
                              $target->set->item($saved)->pull(array_merge(
                                array(
                                  $target->primary,
                                  $target->flag,
                                ),
                                $target->exclude
                              )),
                              array(
                                $target->flag => $query::FLAG_USED,
                              )
                            ))
                            ->where(array($target->primary => $row[$target->primary]));
                  if ($this->execute($update)) {

                    /* assign new id to updated item */
                    $target->set->item($saved)->push(array(
                      $target->primary => $row[$target->primary],
                    ));

                  }

                  /* increment saved */
                  $saved++;

                } //all saved

              } //each existing

            } //fetch

            /* any remaining unsaved records? */
            if ($saved < $count) {
              for ($index = $saved; $index < $count; $index++) {

                /* insert remaining record */
                $insert = $this->insert()
                          ->into($target->table)
                          ->values(array_merge(
                            $target->set->item($index)->pull(array_merge(
                              array(
                                $target->primary,
                                $target->flag,
                              ),
                              $target->exclude
                            )),
                            array(
                              $target->flag => $query::FLAG_USED,
                            )
                          ));
                if ($id = $this->execute($insert)) {

                    /* assign new id to set item */
                    $target->set->item($index)->push(array(
                      $target->primary => $id,
                    ));

                }

              } //each remaining record
            } //remaining?

            /* return! */
            return ($saved > 0);

          } else {

            /* flag related existing record to unused */
            $update = $this->update()
                      ->table($target->table)
                      ->set(array($target->flag => $query::FLAG_UNUSED))
                      ->where($target->where);
            return $this->execute($update);

          } //any data to save

        } //target primary check

      } //get data

      /* default return false */
      return false;

    /* store statement */
    } elseif ($query instanceof Sql\Store) {

      /* get store data */
      $target = $query->getSqlString();
      $values = $target->values;

      /* get id if any */
      $id = null;
      if (isset($values[$target->primary])) {
        $id = $values[$target->primary];
      }

      /* have id? update */
      if ($id) {

        /* unset id */
        unset($values[$target->primary]);

        /* generate update statement */
        $update = $this->update()
                  ->table($target->into)
                  ->set($values)
                  ->where(array($target->primary => $id));

        /* execute update! */
        if ($this->execute($update)) {
          return $id;
        }

      /* no id, insert */
      } else {

        /* unset id if its an autoincrement */
        if ($target->auto == true) { unset($values[$target->primary]); }

        /* generate insert */
        $insert = $this->insert()
                  ->into($target->into)
                  ->values($values);

        /* execute insert */
        return $this->execute($insert);
      }

      /* return false as default */
      return false;

    /* other normal statement */
    } else {

      /* prepare & execute */
      $statement = $this->_sql->prepareStatementForSqlObject($query);
      $result = $statement->execute();

      /* insert, return last generated value (auto increment) */
      if ($result && ($query instanceof Sql\Insert)) {

        /* oci8? */
        if ($this->_driver == self::DRIVER_OCI8) {
          /* has ai field? */
          if (($field = $query->hasAutoincrement()) !== false) {
            if ($id = $query->{$field}) {
              return $id;
            }
          }
        } else {
          /* others - mysql */
          if ($id = $this->_db->getDriver()->getLastGeneratedValue()) {
            return $id;
          }
        }
      }

      /* return result */
      return $result;

    } //recycle or not
  }

  public function now() {

    return $this->_now;
  }

  public function expression($expression) {

    return new \Zend\Db\Sql\Expression($expression);
  }
}