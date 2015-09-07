<?php
/*
 * Author Jeff
 * jeff@neuronworks.co.id
 * DB Helpers
 */
namespace Neuron\Db\Storage;

use Neuron\Db\Storage;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Sql\Sql;

class Helper {

    protected $_storage;

    public function __construct(Storage $storage) {

        $this->_storage = $storage;

    }

    public function selectAll($tableName, $order = null) {

        $sql        = new Sql($this->_storage->getAdapter());

        $result     = array();

        $query      = $sql->select()->from($tableName);

        if ($order) {
            $query->order($order);
        }

        $statement  = $sql->prepareStatementForSqlObject($query);

        $response   =  $statement->execute();

        return $response;
    
    }

    public function selectWhere($tableName, $where, $order = null) {
        $sql        = new Sql($this->_storage->getAdapter());

        $result     = array();

        $query      = $sql->select()
                            ->from($tableName)
                            ->where($where);
        if ($order) {
            $query->order($order);
        }

        $statement  = $sql->prepareStatementForSqlObject($query);

        $response   =  $statement->execute();

        return $response;

    }

    
    public function selectRow($tableName, $where, $order = null){

        $sql        = new Sql($this->_storage->getAdapter());

        $result     = array();

        $query      = $sql->select()
                            ->from($tableName)
                            ->where($where)
                            ->limit(1); //ambil 1 row

        $statement  = $sql->prepareStatementForSqlObject($query);

        $response   =  $statement->execute();

        return $response;

    }

    /*
    selectVar
    */
    public function selectVar($tableName, $where, $order = null, $fieldName = null) {

        //select name from table where id = 1;
        //ambil isi field di row 1 col 1
        $sql        = new Sql($this->_storage->getAdapter());
        
        $metadata   = new \Zend\Db\Metadata\Metadata($this->_storage->getAdapter());
        $field      = $metadata->getColumns($tableName);
        $result     = array();

        
        $query      = $sql->select()
                            ->from($tableName)
                            ->columns(array($field[0]->getName()))
                            ->where($where)
                            ->limit(1); 
        
        if ($order) {
            $query->order($order);
        }              
        
        if ($fieldName) {
            $query->columns(array($fieldName));
        }

        $statement  = $sql->prepareStatementForSqlObject($query);

        $response   =  $statement->execute();
        
        foreach ($response as $key => $value) {
            $result[$key] = $value;
        }

        return $result[$key];
    
    }

    /*
    selectPage
    */
    public function selectPage($tableName, $where =  null, $order = null, $page = 1, $count = 10) {
        
        $sql        = new Sql($this->_storage->getAdapter());

        $result     = array();

        $query      = $sql->select()
                            ->from($tableName);
                            //->limit($count)
                            //->offset($page);
        
        if ($where) {
            $query->where($where);
        }

        if ($order) {
            $query->order($order);
        }

        $statement  = $sql->prepareStatementForSqlObject($query);

        $response   =  $statement->execute();

        foreach ($response as $key => $value) {
            $result[$key] = $value;
        }
        
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($result));
 
        $paginator->setCurrentPageNumber($page);

        $paginator->setItemCountPerPage($count);   

        return $paginator;

    }

    public function insert($tableName, $field =array()) {
        $sql        = new Sql($this->_storage->getAdapter());

        $insert     = $sql->insert()
                        ->into($tableName)
                        ->values($field);

        return $this->_storage->execute($insert);

    }

    public function update($tableName, $field = array(), $where){
        $sql        = new Sql($this->_storage->getAdapter());

        $update     = $sql->update($tableName)
                        ->set($field)
                        ->where($where);

        return $this->_storage->execute($update);
    }

    public function delete($tableName, $where = null){
        $sql        = new Sql($this->_storage->getAdapter());

        $query     = $sql->delete()->from($tableName);
        if($where){
            $query->where($where);
        }
        return $this->_storage->execute($delete);
    }


    

    /*
     * Utility
     */
    public function like(){

    }

    public function join(){

    }

    public function getusername(){

    }
    public function check_array($value){
        echo "<pre>";
        print_r($value);
        echo "</pre>";
    }

    public function check_dump($value){
        echo "<pre>";
        var_dump($value);
        echo "</pre>";
    }

}