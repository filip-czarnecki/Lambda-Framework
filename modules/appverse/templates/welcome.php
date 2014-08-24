<?php
$this->view->setarea('body');

$this->frontend->panel('<h4>Welcome</h4>',$this->view->createAreaPush('welcome'));

$this->view->setarea('welcome');

$this->frontend->newgrid();
$this->frontend->grid(2);
$this->view->display('
<img src="'.Config::read('lmbd.modules.dir').'/appverse/templates/lambda.png">');
$this->frontend->grid(10);
$this->view->display('Thank you for choosing Lambda Framework!
<br /><br />If you see this page, it means that the Appverse secret phrase has been not set. To use Appverse, you must set the secret phrase in '.Config::read('lmbd.modules.dir').'/appverse/config.php file. You can also set different language or change default repository there. If you have any questions, please visit Lambda Framework official site.
<br /><br />Have fun!
');
$this->frontend->endgrid();


?>