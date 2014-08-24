<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());

if(isset($this->invalidrepo)) {
  $this->frontend->message('alert-warning','<i class="glyphicon glyphicon-warning-sign"></i> Warning','Failed to connect to '.$this->address.'<br />Please make sure that repository is online and is compatible with your Appverse.');
}

$this->frontend->panel('<h4>Connect to a repository</h4>',$this->view->createAreaPush('address'));
$this->view->setarea('address');

$this->frontend->newform();
$this->frontend->forminput('address','Repository address','',1,null,"","default","md-9",Config::read('appverse.default.repository'));
$this->frontend->formsubmit('lmbd_form','Connect');
$this->result = $this->frontend->endform();
  
?>