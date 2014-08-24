<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());
if(isset($this->address)) {
  $this->frontend->message('alert-info','<i class="glyphicon glyphicon-info-sign"></i> Information','Disconnected from '.$this->address.' ');
}

$this->frontend->button('Appcreator',$this->router->linkdown('Appcreator'),'btn-lg','glyphicon-file');
$this->view->display(' ');
$this->frontend->button('Connect',$this->router->linkdown('Repository'),'btn-lg','glyphicon-link');
$this->view->display(' ');
$this->frontend->button('Refresh',$this->router->link(null,'tmp_refresh'),'btn-lg','glyphicon-refresh');
#$this->view->display(' ');
#$this->frontend->dropdown('<i class="glyphicon glyphicon-th-large"></i> View as',array('<i class="glyphicon glyphicon-th"></i> Grid'=>$this->router->link(null,'grid'),'<i class="glyphicon glyphicon-list"></i> List'=>$this->router->link(null,'list')),'btn-lg');

$this->view->display('<br /><br /><div class="well">');
foreach($this->modules as $category_name=>$modules) {
  if(!empty($modules)) {
    $this->view->display('<br /><h4>'.$category_name.'</h4></hr>');
    $this->frontend->newicons();
    foreach($modules as $dir=>$module) {
      if(isset($module['icon']) && !empty($module['icon'])) {
        $icon = Config::read('lmbd.modules.dir').'/'.$module['icon'];
      }
      else {
        $icon = Config::read('lmbd.modules.dir').'/icons_cutie/folder.png';
      }
      if(isset($module['version'])) {
        $name = $dir.' ('.$module['version'].')';
      }
      else {
        $name = $dir;
      }
      $this->frontend->icon($name,$icon,$this->router->linkdown('Module',array($category_name,$dir)));
    }
    $this->frontend->endicons();
  }
}
$this->view->display('</div>');

?>