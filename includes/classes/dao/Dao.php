<?php
if (!defined('ABSPATH')) exit;

class IPFM_Dao {
    var $Utils;

    public function __construct() {
        $this->Utils=new IPFM_DaoUtils();
    }

    //object relational map
    public function orms($class, $values, $options=array()) {
        if($values===FALSE || is_null($values)) {
            return array();
        }
        $result=array();
        foreach($values as $v) {
            $v=$this->orm($class, $v, $options);
            if($v!==FALSE) {
                $result[]=$v;
            }
        }
        return $result;
    }
    //object relational map
    public function orm($class, $value, $options=array()) {
        global $ipfm;
        if($value===FALSE || is_null($value)) {
            return FALSE;
        }

        $tableClass=$ipfm->Dao->Utils->getTableClass($class);
        if($tableClass=='') {
            throw new Exception('NO TABLE CLASS FOUND FOR CLASS='.$class);
        }

        //database iscase-insensitive
        $columns=$ipfm->Dao->Utils->getColumns($tableClass);
        foreach($columns as $k=>$v) {
            $k=strtolower($k);
            $columns[$k]=$v;
        }

        $result=new $tableClass();
        foreach($value as $k=>$v) {
            if(isset($columns[$k])) {
                $c=$columns[$k];
                $k=$c['field'];
                $result->$k=$ipfm->Dao->Utils->decode($tableClass, $k, $v);
            }
        }
        if(isset($options['loadPointers']) && $options['loadPointers']) {
            $columns=$ipfm->Dao->Utils->getPointersColumns($class);
            foreach($columns as $k=>$v) {
                $class=$ipfm->Dao->Utils->getClass($v['rel']);
                $id=$result->$k;
                if($id!=='' && intval($id)>0) {
                    $instance=$this->queryForId($class, $id);
                    if($instance!==FALSE) {
                        $property=$v['instance'];
                        $result->$property=$instance;
                    }
                }

            }
        }
        return $result;
    }

    public function queryForId($class, $id) {
        global $wpdb, $ipfm;
        $table=$ipfm->Dao->Utils->getTableName($class);
        $primary=$ipfm->Dao->Utils->getPrimary($class);

        $sql='SELECT * FROM '.$table.' WHERE '.$primary.'='.$id.' LIMIT 1;';
        $result=$wpdb->get_row($sql);

        $options=array('loadPointers'=>TRUE);
        $result=$this->orm($class, $result, $options);
        return $result;
    }
    public function queryForIds($search, $id='id') {
        $array=$this->queryForList($search);
        $result=array();
        foreach($array as $v) {
            $result[]=$v->$id;
        }
        return $result;
    }
    public function queryForMap($search, $options=array()) {
        global $wpdb, $ipfm;
        $array=$this->queryForList($search, $options);
        $table=$ipfm->Dao->Utils->getTableName($search);
        $primary=$ipfm->Dao->Utils->getPrimary($table);

        $result=array();
        foreach($array as $v) {
            $k=$v->$primary;
            $result[$k]=$v;
        }
        return $result;
    }
    private function executeIntQuery($sql, $default=FALSE) {
        global $wpdb;
        $data=$wpdb->get_row($sql);
        $result=$default;
        if($data!==FALSE) {
            //associative array stdClass
            foreach($data as $k=>$v) {
                $result=intval($v);
                break;
            }
        }
        return $result;
    }
    public function queryForCount($search, $options=array()) {
        if(is_array($options)) {
            $options=array();
        }
        $options['count']=TRUE;
        $sql=$this->getQuerySql($search, $options);
        $result=$this->executeIntQuery($sql, 0);
        return $result;
    }
    public function encodeColumn($class, $column, $alias='') {
        return '`'.strtolower($column).'`';
    }
    public function getSearchPatterns() {
        $patterns=array('Equals', 'Like', 'Ids', 'From', 'To', 's', '');
        return $patterns;
    }
    protected function getQuerySql($search, $options=array()) {
        global $ipfm;
        if(is_string($options)) {
            $options=array(
                'queryClass'=>$options
                , 'resultClass'=>$options
            );
        }
        $defaults=array(
            'queryClass'=>$search
            , 'resultClass'=>$search
            , 'count'=>FALSE
            , 'distinct'=>FALSE
            , 'columns'=>''
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $queryClass=$options['queryClass'];

        $table=$ipfm->Dao->Utils->getTableName($queryClass);
        if($table=='') {
            throw new Exception('TABLE NOT FOUND FOR CLASS='.$queryClass);
        }

        $where=array();
        $columns=$ipfm->Dao->Utils->getColumns($queryClass);
        $patterns=$this->getSearchPatterns();
        foreach($columns as $name=>$column) {
            if(!isset($column['type']) || $column['type']=='') {
                continue;
            }

            foreach($patterns as $pattern) {
                $property=$name.$pattern;
                if(isset($search->$property) && !is_null($search->$property)) {
                    $value=$search->$property;
                    $value=$ipfm->Utils->trim($value);
                    //if($value==='' || is_null($value)) {
                    if(is_null($value)) {
                        continue;
                    }

                    $operator='';
                    switch ($pattern) {
                        case 'Equals':
                            $value=$ipfm->Dao->Utils->encodeQuote($queryClass, $name, $value);
                            $operator='=';
                            break;
                        case 'Like':
                            $value=trim($value);
                            while(strpos($value, '  ')!==FALSE) {
                                $value=str_replace('  ', ' ', $value);
                            }
                            if($value!='') {
                                $value='%'.str_replace(' ', '%', $value).'%';
                                $value=$ipfm->Dao->Utils->encodeQuote($queryClass, $name, $value);
                                $operator='LIKE';
                            }
                            break;
                        case 'Ids':
                        case 's':
                        case '':
                            if($pattern=='' && !$ipfm->Utils->endsWith($name, 'Ids')) {
                                $value=$ipfm->Dao->Utils->encodeQuote($queryClass, $name, $value);
                                $operator='=';
                            } else {
                                if($ipfm->Dao->Utils->isColumnNumeric($queryClass, $name)) {
                                    $value=$ipfm->Utils->iuarray($value, FALSE);
                                    if(count($value)>0) {
                                        $value='('.implode(',', $value).')';
                                        $operator="IN";
                                    }
                                } elseif($ipfm->Dao->Utils->isColumnArray($queryClass, $name)) {
                                    switch (strtolower($column['type'])) {
                                        case 'array':
                                            $value=$ipfm->Utils->dbarray($value, FALSE);
                                            $array=array();
                                            foreach($value as $v) {
                                                $array[]=$this->encodeColumn($queryClass, $name).' LIKE "%,'.$v.',%"';
                                            }
                                            if(count($patterns)>0) {
                                                $where[]=$array;
                                            }
                                            $operator='';
                                            break;
                                        /*
                                         * //UNUSED
                                        case 'iuarray':
                                            $value=$ec->Utils->iuarray($value, FALSE);
                                            if(count($value)>0) {
                                                $value=$ec->Dao->Utils->encodeQuote($table, $name, $value);
                                                $value=str_replace(',,', ',%,', $value);
                                                $operator="LIKE";
                                            }
                                            break;
                                        */
                                    }
                                }
                            }
                            break;
                        case 'From':
                            if($value!==0) {
                                $value=$ipfm->Dao->Utils->encodeQuote($queryClass, $name, $value);
                                $operator='>=';
                            }
                            break;
                        case 'To':
                            if($value!==0) {
                                $value=$ipfm->Dao->Utils->encodeQuote($queryClass, $name, $value);
                                $operator='<=';
                            }
                            break;
                    }

                    if($operator!='') {
                        $annotation=$ipfm->Dao->Utils->getColumn($search, $property);
                        if(isset($annotation['query']) && $annotation['query']!='') {
                            $array=$ipfm->Utils->toArray($annotation['query']);
                        } else {
                            $array=array($name);
                        }
                        if(count($array)>0) {
                            if(count($array)==1) {
                                $where[]=$this->encodeColumn($queryClass, $array[0]).' '.$operator.' '.$value;
                            } else {
                                $clause=array();
                                foreach($array as $v) {
                                    $clause[]=$this->encodeColumn($queryClass, $v).' '.$operator.' '.$value;
                                }
                                $where[]=$clause;
                            }
                        }
                    }
                }
            }
        }

        $select='*';
        if($options['count']) {
            $select='COUNT(*)';
        } elseif($options['columns']!='') {
            $select=$options['columns'];
        }

        if($options['distinct']) {
            $select='DISTINCT '.$select;
        }
        $sql='SELECT '.$select.' FROM '.$table.' ';
        if(count($where)>0) {
            $buffer='';
            foreach($where as $clause) {
                if($buffer!='') {
                    $buffer.=" \n AND ";
                }
                if(is_array($clause)) {
                    $clause=$ipfm->Utils->implode(" ( ", " ) ", "OR \n", $clause);
                    if($clause!='') {
                        $buffer.=" ( \n ".$clause." \n ) ";
                    }
                } elseif(is_string($clause)) {
                    if($clause!='') {
                        $buffer.=" ( ".$clause." ) ";
                    }
                }
            }

            if($buffer!='') {
                $sql.="\n WHERE ".$buffer;
            }
        }

        $orderBy=$ipfm->Utils->get($search, 'orderBy', '');
        if($orderBy!='' && $orderBy!='orderBy') {
            $array=$ipfm->Dao->Utils->getColumn($search, 'orderBy');
            if(isset($array['query']) && $array['query']!='') {
                $array=$ipfm->Utils->toArray($array['query']);
                if (is_numeric($orderBy)) {
                    $orderBy=intval($orderBy);
                    if (isset($array[$orderBy])) {
                        $orderBy=$array[$orderBy];
                    } else {
                        $orderBy='';
                    }
                }
            }

            $buffer='';
            $orderBy=explode(',', $orderBy);
            foreach($orderBy as $clause) {
                $clause=str_replace(' ', '#', $clause);
                $clause=explode('#', $clause);
                if(count($clause)==1) {
                    $clause[]='ASC';
                }
                if($buffer!='') {
                    $buffer.=" \n , ";
                }
                $buffer.=$this->encodeColumn($table, $clause[0]).' '.$clause[1];
            }
            if($buffer!='') {
                $sql.="\n ORDER BY ".$buffer;
            }
        }

        $limit=$ipfm->Utils->iget($search, 'limit', 0);
        if($limit>0) {
            $sql.="\n LIMIT ".$limit;
            $offset=$ipfm->Utils->iget($search, 'offset', 0);
            if($offset>0) {
                $sql.=",".$offset;
            }
        }
        $ipfm->Log->debug('QUERY: %s', $sql);
        return $sql;
    }
    public function queryForNew($instance) {
        if(!is_object($instance)) {
            return FALSE;
        }

        $result=$this->queryForFirst($instance);
        if($result===FALSE) {
            $instance->id=0;
            $result=$instance;
        }
        return $result;
    }
    public function queryForFirst($search, $options=array()) {
        $search->limit=1;
        if(!is_array($options)) {
            $options=array();
        }
        $options['loadPointers']=TRUE;
        $search->limit=1;
        $array=$this->queryForList($search, $options);
        $result=FALSE;
        if($array!==FALSE && count($array)>0) {
            $result=$array[0];
        }
        return $result;
    }
    public function queryForList($search, $options=array()) {
        global $wpdb, $ipfm;
        if(is_string($options)) {
            $options=array(
                'queryClass'=>$options
                , 'resultClass'=>$options
            );
        }
        $defaults=array(
            'queryClass'=>$search
            , 'resultClass'=>$search
        );
        $options=$ipfm->Utils->parseArgs($options, $defaults);
        $resultClass=$options['resultClass'];

        if(!is_string($search) || !$ipfm->Utils->startsWith($search, 'SELECT ')) {
            $sql=$this->getQuerySql($search, $options);
        } else {
            $sql=$search;
        }
        $result=$wpdb->get_results($sql);
        $result=$this->orms($resultClass, $result, $options);
        return $result;
    }

    public function store(&$instance, $class='') {
        global $ipfm;
        if($instance==FALSE || is_null($instance)) {
            return FALSE;
        }

        if($class=='') {
            $class=get_class($instance);
        }
        $primary=$ipfm->Dao->Utils->getPrimary($class);
        if($primary=='') {
            return FALSE;
        }

        //check for pointers
        $columns=$ipfm->Dao->Utils->getPointersColumns($class);
        foreach($columns as $propertyId=>$property) {
            $property=$property['instance'];
            $v=$instance->$property;
            if($ipfm->Utils->isObject($v)) {
                $id=intval($instance->$propertyId);
                $v->id=$id;
                $this->store($v);
                //can also occurs that $v is empty due to is not passed during store
                $id=$this->Utils->getId($v);
                $instance->$propertyId=$id;
            }
        }

        $ipfm->Utils->set($instance, 'editDate', time());
        //$ec->Utils->set($instance, 'editUserId', $ec->Session->getUserId());
        $id=$ipfm->Utils->iget($instance, $primary);
        $result=FALSE;
        if($id==0) {
            $ipfm->Utils->set($instance, 'creationDate', time());
            //$ec->Utils->set($instance, 'creationUserId', $ec->Session->getUserId());
            $id=$this->insert($instance, $class);
            if($id>0) {
                $instance->$primary=$id;
                $result=TRUE;
            }
        } else {
            $result=$this->update($instance, $class);
        }
        return $result;
    }

    private function prepare($class, $instance, &$output) {
        global $ipfm;

        if($class=='') {
            $class=get_class($instance);
        }
        $table=$ipfm->Dao->Utils->getTableName($class);
        $defaults=$ipfm->Dao->Utils->getColumnsDefaults($class);
        $formats=$ipfm->Dao->Utils->getColumnsFormats($class);
        //transform instance to array
        $array=$ipfm->Utils->parseArgs($instance, $defaults);

        $columns=$ipfm->Dao->Utils->getColumns($instance);
        $instance=array();
        foreach($columns as $k=>$v) {
            if(isset($v['type']) && $v['type']!='') {
                if(!isset($v['primary']) || !$v['primary']) {
                    $v=$array[$k];
                    if (is_null($v)) {
                        //unset($instance[$k]);
                    } elseif ($ipfm->Dao->Utils->isColumnNumeric($class, $k) && $v === '') {
                        //unset($instance[$k]);
                    } else {
                        $instance[$k]=$ipfm->Dao->Utils->encodeQuote($class, $k, $v, TRUE);
                    }
                }
            }
        }
        // Force fields to lower case
        $instance=array_change_key_case($instance);
        $formats=array_change_key_case($formats);
        // White list columns
        $instance=array_intersect_key($instance, $formats);

        // Reorder $column_formats to match the order of columns given in $data
        $array=array();
        foreach($instance as $k=>$v) {
            $format='%s';
            if(isset($formats[$k])) {
                $format=$formats[$k];
            }
            $array[]=$format;
        }
        $formats=$array;

        $output['table']=$table;
        $output['formats']=$formats;
        $output['defaults']=$defaults;
        $output['instance']=$instance;
        return TRUE;
    }

    public function insert($instance, $class='') {
        global $wpdb;

        if($class=='') {
            $class=get_class($instance);
        }
        $output=array();
        $this->prepare($class, $instance, $output);

        $table=$output['table'];
        $instance=$output['instance'];

        //WP mode...sometimes fail but I dont know why :(
        //$formats=$output['formats'];
        //$wpdb->insert($table, $instance, $formats);
        //$id=$wpdb->insert_id;

        $fields="";
        $values="";
        foreach($instance as $k=>$v) {
            if($fields!='') {
                $fields.=" \n, ";
            }
            $fields.=$this->encodeColumn($class, $k);

            if($values!='') {
                $values.=" \n, ";
            }
            $values.=$v;
        }
        if($fields=='' || $values=='') {
            return 0;
        }

        $sql="INSERT INTO ".$table." ( \n ".$fields." \n ) VALUES ( \n ".$values." \n )";
        $result=$wpdb->query($sql);
        $id=0;
        if($result!==FALSE) {
            $sql='SELECT LAST_INSERT_ID()';
            $id=$this->executeIntQuery($sql, 0);
        } else {
            throw new Exception('INSERT EXCEPTION FOR QUERY='.$sql);
        }
        return $id;
    }

    public function update($instance, $class='') {
        global $wpdb, $ipfm;

        if($class=='') {
            $class=get_class($instance);
        }
        $primary=$ipfm->Dao->Utils->getPrimary($class);
        $id=$ipfm->Dao->Utils->getId($instance);
        if($id<=0) {
            return FALSE;
        }

        $output=array();
        $this->prepare($class, $instance, $output);

        $table=$output['table'];
        $instance=$output['instance'];

        //$formats=$output['formats'];
        //$result=$wpdb->update($table, $instance, array($primary => $id), $formats);
        $buffer="";
        foreach($instance as $k=>$v) {
            if($buffer!='') {
                $buffer.=" \n, ";
            }
            $buffer.=$this->encodeColumn($class, $k)."=".$v;
        }
        if($buffer=='') {
            return FALSE;
        }
        $sql="UPDATE ".$table." SET \n ".$buffer." \n WHERE ";
        $sql.=$this->encodeColumn($class, $primary)."=".$id;
        $result=$wpdb->query($sql);
        $result=($result!==FALSE);
        if(!$result) {
            throw new Exception('UPDATE EXCEPTION FOR QUERY='.$sql);
        }
        return $result;
    }

    public function delete($class, $ids=0) {
        global $wpdb, $ipfm;
        $table=$ipfm->Dao->Utils->getTableName($class);
        $primary=$ipfm->Dao->Utils->getPrimary($class);

        $ids=$ipfm->Utils->iarray($ids);
        if(count($ids)==0) {
            return FALSE;
        }

        $args=array();
        $sql='DELETE FROM '.$table.' WHERE '.$primary.' IN('.implode(',', $ids).')';
        $result=$wpdb->query($wpdb->prepare($sql, $args));
        return ($result!==FALSE);
    }
}