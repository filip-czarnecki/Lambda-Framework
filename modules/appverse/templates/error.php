<?php
$this->view->setarea('body');

$message = 'Unknown error.<br />(NO ERRID)';

if(isset($this->errid)) {
  switch($this->errid) {
    case 2:
      $message = 'Page not found.<br />(ERRID: 2)';
      break;
    case 3:
      $message = 'No secret phrase found. Please set secret phrase in file appverse/secret.php<br />(ERRID: 3)';
      break;
  }
}

$this->frontend->message('alert-warning','<i class="glyphicon glyphicon-warning-sign"></i> Warning',$message);

?>