<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());

if(!empty($this->module)) {
  #print_r($this->module);
  if(isset($this->module['icon_ext']) && !empty($this->module['icon_ext'])) {
    $icon = $this->module['icon_ext'];
  }
  else if(isset($this->module['icon']) && !empty($this->module['icon'])) {
    $icon = Config::read('lmbd.modules.dir').'/'.$this->module['icon'];
  }
  else {
    $icon = Config::read('lmbd.modules.dir').'/icons_cutie/folder.png';
  }

  $this->frontend->panel('<h4>'.$this->router->getparameter('1').' <div class="pull-right">'.$this->view->createAreaPush('buttons').'</div></h4>',$this->view->createAreaPush('module'));
  $this->view->setArea('module');
  $this->frontend->newgrid();
  $this->frontend->grid(2);
  $this->view->display('<img src="'.$icon.'">');
  $this->frontend->grid(10);
  if(isset($this->module['version'])) {
    $this->view->display('<b>Version:</b><br />'.$this->module['version'].'<br /><br />');
  }
  if(isset($this->module['author'])) {
    $this->view->display('<b>Author:</b><br />'.$this->module['author'].'<br /><br />');
  }
  if(isset($this->module['description'])) {
    $this->view->display('<b>Description:</b><br />'.$this->module['description'].'<br /><br />');
  }
  if(isset($this->module['dependency'])) {
    $this->view->display('<b>Requires:</b><br />');
    foreach($this->module['dependency'] as $dependency_module=>$dependency_version) {
      $this->view->display('- '.$dependency_module.' ('.$dependency_version.')<br />');
    }
    $this->view->display('<br />');
  }
  $this->frontend->endgrid();
  $this->view->setPreviousArea();
  $this->view->setArea('buttons');
  if($this->module['status'] == 'new') {
    $this->frontend->button('Install',$this->router->linkdown('Install',array($this->router->getparameter('0'),$this->router->getparameter('1'))),'btn-lg','glyphicon-plus-sign');
    $this->view->display(' ');
  }
  else if($this->module['status'] == 'newer') {
    $this->frontend->button('Update',$this->router->linkdown('Update'),'btn-lg','glyphicon-upload');
    $this->view->display(' ');
  }
  $this->view->setPreviousArea();
}

?>