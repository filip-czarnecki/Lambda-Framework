<?php

interface iDriver {
  public function __construct($host,$port,$name,$user,$pass,$pref);
  public function setTable($table);
  public function selectData($data=null,$fields=null,$selectone=false,$options=null);
  public function insertData($data);
  public function updateData($data,$criteria="",$options=null);
  public function deleteData($data=null,$options=null);
}

?>