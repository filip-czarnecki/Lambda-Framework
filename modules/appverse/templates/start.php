<?php

$this->view->setarea('body');

if(isset($this->wrongsecret)) {
  $this->frontend->message('alert-warning','<i class="glyphicon glyphicon-warning-sign"></i> Warning','Invalid secret.');
}

$this->frontend->panel('<h4>Welcome to Appverse. To continue, please enter secret phrase from '.Config::read('lmbd.modules.dir').'/appverse/config.php</h4>',$this->view->createAreaPush('secret'));
$this->view->setarea('secret');

$this->frontend->newform();
$this->frontend->forminput('secret','Secret phrase','',1);
$this->frontend->formsubmit('lmbd_form','Start Appverse');
$this->result = $this->frontend->endform();
  
?>