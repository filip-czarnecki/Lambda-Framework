<?php

$this->view->setarea('body');
$this->frontend->navigation($this->router->getpagename(),$this->router->previouslinks());
if(isset($this->firstconnect)) {
  $this->frontend->message('alert-success','<i class="glyphicon glyphicon-ok-sign"></i> Connected to '.$this->address,$this->message);
}

$this->frontend->button('Information',$this->router->linkdown('Information'),'btn-lg','glyphicon-info-sign');
$this->view->display(' ');
$this->frontend->button('Disconnect',$this->router->link('Main page','tmp_disconnect'),'btn-lg','glyphicon-off');
$this->view->display(' ');
$this->frontend->button('Refresh',$this->router->link(null,'tmp_refresh'),'btn-lg','glyphicon-refresh');
$this->view->display(' ');

$this->view->display('<br /><br /><div class="well">');
foreach($this->modules_available as $category_name=>$modules) {
  if(!empty($modules)) {
    $this->view->display('<br /><h4>'.$category_name.'</h4></hr>');
    $this->frontend->newicons();
    foreach($modules as $dir=>$module) {
      if(isset($module['icon_ext']) && !empty($module['icon_ext'])) {
        $icon = $module['icon_ext'];
      }
      else if(isset($module['icon']) && !empty($module['icon'])) {
        $icon = Config::read('lmbd.modules.dir').'/'.$module['icon'];
      }
      else {
        $icon = Config::read('lmbd.modules.dir').'/icons_cutie/folder.png';
      }
      if(isset($module['status'])) {
        switch($module['status']) {
          case 'new':
            $status = '<span class="label label-success"><i class="glyphicon glyphicon-star"></i> new</span>';
            break;
          case 'ok':
            $status = '<span class="label label-primary"><i class="glyphicon glyphicon-ok"></i> up to date</span>';
            break;
          case 'newer':
            $status = '<span class="label label-info"><i class="glyphicon glyphicon-arrow-up"></i> update available</span>';
            break;
          case 'older':
            $status = '<span class="label label-default"><i class="glyphicon glyphicon-remove"></i> older than current</span>';
            break;
        }
        $name = $dir.'<br /><br />'.$status.'';
      }
      else {
        $name = $dir;
      }
      $this->frontend->icon($name,$icon,$this->router->linkdown('Explore',array($category_name,$dir)));
    }
    $this->frontend->endicons();
  }
}
$this->view->display('</div>');

?>