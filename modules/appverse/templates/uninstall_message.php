<?php

$this->view->setarea('message');

$message = 'Unknown error.<br />(NO ERRID)';

if(isset($this->message)) {
  switch($this->message) {
    case 1:
      $this->frontend->message('alert-success','<i class="glyphicon glyphicon-ok-circle"></i> Success','Module uninstalled successfully.');
      break;
    case 2:
      $this->frontend->message('alert-danger','<i class="glyphicon glyphicon-remove-circle"></i> Error','Cannot uninstall module. Uninstaller was unable to delete one or more files/directories. Please check permissions.');
      break;
    default:
      $this->frontend->message('alert-danger','<i class="glyphicon glyphicon-remove-circle"></i> Error','Cannot uninstall module. Uninstaller experienced an unexpected error.');
  }
}

?>