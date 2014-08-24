<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());

if(!empty($this->module)) {
  #print_r($this->module);
  if(isset($this->module['icon']) && !empty($this->module['icon'])) {
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
  $this->view->display('<b>Dependencies:</b><br />');
  if(isset($this->module['dependency'])) {
    foreach($this->module['dependency'] as $dependency_module=>$dependency_version) {
      $this->view->display('- '.$dependency_module.' ('.$dependency_version.')<br />');
    }
  }
  $this->frontend->button('Add',$this->router->linkdown('Add',array($this->router->getparameter('0'),$this->router->getparameter('1'))),'btn-sm','glyphicon-plus');
  $this->frontend->endgrid();
  $this->view->setPreviousArea();
  $this->view->setArea('buttons');
  if(isset($this->module['type']) && $this->module['type'] == 'Application (Lambda MVC)') {
    $this->frontend->button('Run','index.php?m='.$this->module['module'],'btn-lg','glyphicon-flash');
    $this->view->display(' ');
  }
  $this->frontend->button('Uninstall',$this->router->linkdown('Uninstall',array($this->router->getparameter('0'),$this->router->getparameter('1'))),'btn-lg','glyphicon-remove-circle');
  $this->view->setPreviousArea();
}

?>