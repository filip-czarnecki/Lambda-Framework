<?php

require_once(Config::read('lmbd.modules.dir').'/event/iObserver.php');

class Event {
  private $observers = array();
  
  public function attach($observer_in) {
    if(gettype($observer_in) == 'object') {
      if(in_array('iObserver',class_implements($observer_in), false)) {
        $this->observers[] = $observer_in;
      }
    }
  }
  public function detach($observer_in) {
    foreach($this->observers as $okey => $oval) {
      if($oval == $observer_in) { 
        unset($this->observers[$okey]);
      }
    }
  }
  public function notify($type,$data=null) {
    foreach($this->observers as $obs) {
      $obs->update($type,$data);
    }
  }
}

?>