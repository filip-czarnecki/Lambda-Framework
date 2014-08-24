<?php

/*Index (entry-point) file
Lambda Framework 0.5 series
Copyright © 2013-2014 Filip Czarnecki*/

session_start();

$lmbd_conf = array(
  'modules_dir'=>'modules'
);

require_once($lmbd_conf['modules_dir'].'/controller/Controller.php');

new Controller($lmbd_conf);

?>