<?php

class Frontend implements iConstruct {
  public function init() {
    require_once(Config::read('lmbd.modules.dir').'/frontend/iFrontend.php');
    require_once(Config::read('lmbd.modules.dir').'/frontend/iFrontend2.php');
    require_once(Config::read('lmbd.modules.dir').'/frontend_'.Config::read('frontend.framework').'/frontend.php');
    $implements = class_implements('Frontend_framework', false);
    if(in_array('iFrontend',$implements) || in_array('iFrontend2',$implements)) {
      $this->frontend_framework = new Frontend_framework($this->view);
    }
  }
  public function __call($method, $args) {
    if(method_exists($this->frontend_framework,$method)) {
      return call_user_func_array(array($this->frontend_framework,$method),$args);
    }
  }
}

?>