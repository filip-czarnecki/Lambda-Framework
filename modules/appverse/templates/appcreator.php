<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());

if(isset($this->message) && $this->message != null) {
  $type = 'alert-warning';
  $header = '<i class="glyphicon glyphicon-warning-sign"></i> Warning';
  $message = 'Unknown';
  switch($this->message) {
    case 1:
      $type = 'alert-success';
      $header = '<i class="glyphicon glyphicon-ok-circle"></i> Success';
      $message = 'Module created.';
      break;
    case 2:
      $message = 'No DB host provided.';
      break;
    case 3:
      $message = 'No DB name provided.';
      break;
    case 4:
      $message = 'No DB username provided.';
      break;
    case 5:
      $message = 'No DB password provided.';
      break;
  }
  $this->frontend->message($type,$header,$message);
}

$this->frontend->panel('<h4>New Lambda MVC application</h4>',$this->view->createAreaPush('form'));
$this->view->setarea('form');

$this->frontend->newform();
$this->frontend->forminput('name','* Application name','Only alphanumeric characters. Anything else will be removed.',1);
$this->frontend->formselect('theme','Theme','',$this->themes,1);
$this->frontend->formselect('database','Use database?','Y',array('N'=>'No','Y'=>'Yes'),1);
$this->frontend->formselect('datasource','Create new data source?','N',array('N'=>'No','Y'=>'Yes'),1);
$this->frontend->formselect('dbdriver','DB Driver','',$this->drivers);
$this->frontend->forminput('dbhost','* DB Host');
$this->frontend->forminput('dbport','DB Port');
$this->frontend->forminput('dbname','* DB Name');
$this->frontend->forminput('dbuser','* DB Username');
$this->frontend->forminput('dbpass','* DB Password');
$this->frontend->forminput('dbpref','DB Prefix');

$this->frontend->dependency('database','datasource',array('Y'),'show');
$this->frontend->dependency('database','datasource',array('N'),'hide');
$this->frontend->dependency('datasource','dbdriver',array('Y'),'show');
$this->frontend->dependency('datasource','dbdriver',array('N'),'hide');
$this->frontend->dependency('datasource','dbhost',array('Y'),'show');
$this->frontend->dependency('datasource','dbhost',array('N'),'hide');
$this->frontend->dependency('datasource','dbport',array('Y'),'show');
$this->frontend->dependency('datasource','dbport',array('N'),'hide');
$this->frontend->dependency('datasource','dbname',array('Y'),'show');
$this->frontend->dependency('datasource','dbname',array('N'),'hide');
$this->frontend->dependency('datasource','dbuser',array('Y'),'show');
$this->frontend->dependency('datasource','dbuser',array('N'),'hide');
$this->frontend->dependency('datasource','dbpass',array('Y'),'show');
$this->frontend->dependency('datasource','dbpass',array('N'),'hide');
$this->frontend->dependency('datasource','dbpref',array('Y'),'show');
$this->frontend->dependency('datasource','dbpref',array('N'),'hide');

$this->frontend->formsubmit('lmbd_form','Create');
$this->result = $this->frontend->endform();

?>