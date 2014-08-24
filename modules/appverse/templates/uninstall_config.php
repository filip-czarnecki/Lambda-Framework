<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());

$this->view->createArea('message');

$this->frontend->panel('<h4>Uninstall</h4>',$this->view->createAreaPush('form'));
$this->view->setarea('form');

$this->frontend->newform();
$this->frontend->formselect('preserve_config','Do you want to keep configuration?','Y',array('N'=>'No','Y'=>'Yes'),1);
$this->frontend->formsubmit('lmbd_form','Proceed');
$this->setresult($this->frontend->endform());

?>