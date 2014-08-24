<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());

$this->view->createArea('message');

$this->frontend->panel('<h4>Configuration settings</h4>',$this->view->createAreaPush('form'));
$this->view->setarea('form');

$this->frontend->newform();
$answers = array('N'=>'No','Y'=>'Yes');

foreach($this->content as $module=>$config) {
  foreach($config as $key=>$value) {
    if($value['value'] == 'YN') {
      $this->frontend->formselect($value['formkey'],$value['description'],'Y',$answers,1);
    }
    else if($value['value'] == 'STR') {
      $this->frontend->forminput($value['formkey'],$value['description'],'',1);
    }
    else if($value['value'] == 'Y' || $value['value'] == 'N') {
      $this->frontend->formselect($value['formkey'],$value['description'].' (default: '.$answers[$value['value']].')',$value['value'],$answers,1);
    }
    else {
      $this->frontend->forminput($value['formkey'],$value['description'],'',1,null,"","default","md-9",$value['value']);
    }
  }
}
$this->frontend->formsubmit('lmbd_form','Install');
$this->setresult($this->frontend->endform());

?>