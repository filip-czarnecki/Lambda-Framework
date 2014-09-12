<?php

class driver_sql implements iDriver {
  public $connected = false;
  
  public function __construct($host,$port,$name,$user,$pass,$pref,$options=array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',PDO::ATTR_PERSISTENT => true)) {
    if($port == 0 || empty($port)) {
      $port = '3306';
    }
    try {
      $dsn = "mysql:host=$host;port=$port;dbname=$name";
      $this->dbh = new PDO($dsn, $user, $pass, $options);
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->prefix = $pref;
      $this->connected = true;
    }
    catch(PDOException $e) {
      Logger::write('ERROR',$e->getMessage());
      $this->connected = false;
    }
  }
  public function query($query) {
    if($this->connected) {
      return $this->dbh->query($query);
    }
    return null;
  }
  public function setTable($table) {
    if($this->connected) {
      $this->table = ''.$this->prefix.''.$table.'';
      return true;
    }
    return false;
  }
  protected function getCursor($data,$options=null) {
    $cursor = array('where'=>'','values'=>array());
    if(!empty($data)) {
      $i = 1;
      foreach($data as $key => $value) {
        $check = $this->checkOptions($key,$value,$options);
        if($check['value'] != '*') {
          if(!is_array($check['value'])) {
            $check['value'] = array($check['value']);
          }
          foreach($check['value'] as $value) {
            if(!empty($value)) {
              $cursor['values'][] = $value;
            }
          }
          $where = $check['key'].' '.$check['sign'].' '.$check['placeholder'];
          if($i == 1) {
            $cursor['where'] .= "where ($where)";
          }
          else {
            $cursor['where'] .= " and ($where)";
          }
          $i++;
        }
      }
    }
    if(isset($options['orderby']) && isset($options['order'])) {
      $cursor['where'] .= ' order by '.$options['orderby'].' '.$options['order'];
    }
    if(isset($options['limit'])) {
      $cursor['where'] .= ' limit '.$options['limit'];
    }
    return $cursor;
  }
  protected function checkOptions($key,$value,$options) {
    $return_array = array('sign'=>'=','value'=>$value,'placeholder'=>'?','key'=>$key);
    if($options != null && is_array($options)) {
      if(array_key_exists($key,$options)) {
        if(!is_array($options[$key])) {
          $options[$key] = array($options[$key]);
        }
        foreach($options[$key] as $option) {
          if($option == '>' || $option == '<') {
            $return_array['sign'] = $option;
          }
          else if($option == '<>') {
            $return_array['sign'] = "between";
            $return_array['placeholder'] = "? and ?";
            $return_array['value'] = explode('-',$value);
          }
          else if($option == 'in') {
            $return_array['sign'] = $option;
            #$return_array['value'] = explode(',',$return_array['value']);
            $return_array['placeholder'] = '(';
            for($i=1;$i<=count($return_array['value']);$i++) {
              $return_array['placeholder'] .= '?,';
            }
            $return_array['placeholder'] = rtrim($return_array['placeholder'],',').')';
          }
          else if($option == 'is') {
            $return_array['sign'] = $option;
          }
          else if($option == 'like') {
            $return_array['sign'] = $option;
            $return_array['value'] = "%".$return_array['value']."%";
          }
          else if($option == 'noquotes') {
            $return_array['placeholder'] = $return_array['value'];
            $return_array['value'] = null;
          }
          else if($option == 'nosign') {
            $return_array['sign'] = '';
          }
          else if($option == 'or') {
            $keys = explode("|",$key);
            $return_array['key'] = $keys[0].' '.$return_array['sign'].' '.$return_array['placeholder'].' ';
            $return_array['placeholder'] = ' '.$keys[1].' '.$return_array['sign'].' '.$return_array['placeholder'];
            $return_array['sign'] = $option;
            $return_array['value'] = array($return_array['value'],$return_array['value']);
          }
        }
      }
    }
    return $return_array;
  }
  public function selectData($data=null,$fields=null,$selectone=false,$options=null,$fetch=PDO::FETCH_ASSOC) {
    if($this->connected) {
      $what = "";
      $cursor = $this->getCursor($data,$options);
      if($fields != null) {
        $what = "";
        $count = count($fields);
        $i = 1;
        foreach($fields as $field) {
          if($i == $count) {
            $what .= $field;
          }
          else {
            $what .= ''.$field.',';
          }
          $i++;
        }
      }
      else {
        $what = "*";
      }
      $query = 'select '.$what.' from '.$this->table.' '.$cursor['where'];
      try {
        $st = $this->dbh->prepare($query);
        $st->execute($cursor['values']);
        if($selectone == true) {
          return $st->fetch($fetch);
        }
        else {
          return $st->fetchAll($fetch);
        }
      }
      catch (Exception $e) {
        Logger::write('WARNING',$e->getMessage().'. Query: '.$query);
      }
    }
    return array();
  }
  public function insertData($data) {
    if($this->connected) {
      if(!empty($data)) {
        $keys = array_keys($data);
        foreach($keys as $key) {
          $keys_insert[] = $key;
          if(substr($data[$key], 0,9) != 'function_') {
            $data_insert[] = $data[$key];
            $placeholders_insert[] = '?';
          }
          else {
            $placeholders_insert[] = substr($data[$key], 9);
          }
        }
        $keys_comma_separated = implode(",", $keys_insert);
        $placeholders_comma_separated = implode(",", $placeholders_insert);
        $query = "insert into ".$this->table." ($keys_comma_separated) values ($placeholders_comma_separated)";
        $st = $this->dbh->prepare($query);
        try {
          if($st->execute($data_insert)) {
            return $this->dbh->lastInsertId();
          }
        }
        catch (Exception $e) {
          Logger::write("WARNING",$e->getMessage()." Query: insert into ".$this->table." ($keys_comma_separated) values ($data_comma_separated)");
        }
      }
    }
    return null;
  }
  public function updateData($data,$criteria="",$options=null) {
    if($this->connected) {
      $cursor = $this->getCursor($criteria,$options);
      $what = "";
      foreach($data as $key=>$value) {
        if(substr($value, 0,9) != 'function_') {
          $what .= " $key = '$value',";
        }
        else {
          $what .= " $key = ".substr($value, 9).",";
        }
      }
      $what = rtrim($what,',');
      $query = 'update '.$this->table.' set '.$what.' '.$cursor['where'];
      $st = $this->dbh->prepare($query);
      try {
        if($st->execute($cursor['values'])) {
          return true;
        }
      }
      catch (Exception $e) {
        Logger::write('WARNING',$e->getMessage().'. Query: '.$query);
      }
    }
    return false;
  }
  public function deleteData($data=null,$options=null) {
    if($this->connected) {
      $cursor = $this->getCursor($data,$options);
      $query = 'delete from '.$this->table.' '.$cursor['where'];
      $st = $this->dbh->prepare($query);
      try {
        if($st->execute($cursor['values'])) {
          return true;
        }
      }
      catch (Exception $e) {
        Logger::write('WARNING',$e->getMessage().'. Query: '.$query);
      }
    }
    return false;
  }
  public function relations($table1, $table2, $what, $rel1, $rel2, $where_key, $where_val) {
    if($this->connected) {
      if(is_array($where_val) && !empty($where_val)) {
        $where = " IN (";
        foreach($where_val as $value) {
          $where .= "$value,";
        }
        $where = rtrim($where,",");
        $where .= ")";
      }
      else {
        $where = " ='$where_val'";
      }
      $query = $this->dbh->query("
        SELECT ".$this->prefix."$table1.$what FROM ".$this->prefix."$table1 
        JOIN ".$this->prefix."$table2 ON ".$this->prefix."$table2.$rel1=".$this->prefix."$table1.$rel2
        WHERE ".$this->prefix."$table2.$where_key $where;
      ")->fetchAll();

      return $query;
    }
    return array();
  }
  public function startTransaction() {
    if($this->connected) {
      if($this->dbh->beginTransaction()) {
        return true;
      }
    }
    return false;
  }
  public function applyTransaction() {
    if($this->connected) {
      if($this->dbh->commit()) {
        return true;
      }
    }
    return false;
  }
}

?>