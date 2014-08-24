<?php

class DBHMagic {
  private $previousdriver = null;
  
  public function __construct($name,$driver,$event=null) {
    $this->name = $name;
    $this->driver = $driver;
    if($event != null) {
      $this->event = $event;
    }
  }
  public function __call($method, $args) {
    if(method_exists($this->driver,$method)) {
      if(isset($this->event)) {
        $this->event->notify('model_qcall_'.$method,$args);
      }
      $result = call_user_func_array(array($this->driver,$method),$args);
      if(isset($this->event)) {
        $this->event->notify('model_qresult_'.$method,array('call'=>$args,'result'=>$result));
      }
      return $result;
    }
  }
  public function changeDriver($driver) {
    $this->previousdriver = $this->driver;
    $this->driver = $driver;
  }
  public function driverRollback() {
    $this->driver = $this->previousdriver;
  }
}

?>