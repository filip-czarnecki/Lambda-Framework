<?php

require_once(Config::read('lmbd.modules.dir').'/appverse_common/GenericInstaller.php');

abstract class AppverseCommon {
  protected $applications = array('MVC'=>array(),'custom'=>array());
  
  public function getmodules($parameter=null) {
    if($parameter != 'refresh') {
      $cache = $this->cache->readCache('appverse_cache');
      if($cache != null) {
        return $cache;
      }
    }
    $discover = $this->discover();
    $this->cache->cacheString('appverse_cache',$discover);
    return $discover;
  }
  public function discover() {
    $modules = array('Application (Lambda MVC)'=>array(),'Application (custom)'=>array(),'Core'=>array(),'DB drivers'=>array(),'Front-end'=>array(),'Icon packs'=>array(),'Themes'=>array(),'Utility (Lambda MC)'=>array(),'Utilities'=>array(),'Uncategorized'=>array());
    if($handle = opendir(Config::read('lmbd.modules.dir'))) {
      $exclude = array('.', '..');
      while (false !== ($dir = readdir($handle))) {
        if(is_dir(Config::read('lmbd.modules.dir').'/'.$dir)) {
          if(file_exists(Config::read('lmbd.modules.dir').'/'.$dir.'/install.ini')) {
            $install = parse_ini_file(Config::read('lmbd.modules.dir').'/'.$dir.'/install.ini');
            $module = parse_ini_file(Config::read('lmbd.modules.dir').'/'.$dir.'/module.ini');
            $module['module'] = $dir;
            if(isset($install['name']) && isset($install['type'])) {
              if(isset($install['package']) && is_array($install['package'])) {
                foreach($install['package'] as $element) {
                  $exclude[] = $element;
                }
              }
              $modules[$install['type']][$install['name']] = $install + $module;
            }
          }
          else if(file_exists(Config::read('lmbd.modules.dir').'/'.$dir.'/module.ini') || $dir == 'controller') {
            if($dir == 'controller') {
              $module = array('icon'=>'controller/lmbdv1.png','version'=>Config::read('lmbd.core.version'));
            }
            else {
              $module = parse_ini_file(Config::read('lmbd.modules.dir').'/'.$dir.'/module.ini');
            }
            $module['module'] = $dir;
            $check = $this->check_unknown_module($dir);
            $modules[$check['type']][$check['name']] = $module;
          }
        }
      }
      closedir($handle);
    }
    $modules = $this->exclude($modules,$exclude);
    return $modules;
  }
  private function exclude($modules,$exclude) {
    foreach($modules as &$type) {
      foreach($type as $name => $module) {
        if(in_array($name, $exclude)) {
          unset($type[$name]);
        }
      }
    }
    return $modules;
  }
  protected function check_unknown_module($name) {
    $type = 'Uncategorized';
    if($name == 'controller') {
      $name = Config::read('lmbd.core.type').' core';
      $type = 'Core';
    }
    if($name == 'logger') {
      $type = 'Core';
    }
    if(substr($name, 0,7) == 'driver_') {
      $name = substr($name,7);
      $type = 'DB drivers';
    }
    if(substr($name, 0,9) == 'frontend_') {
      $name = substr($name,9);
      $type = 'Front-end';
    }
    if(substr($name, 0,6) == 'icons_') {
      $name = substr($name,6);
      $type = 'Icon packs';
    }
    if(substr($name, 0,6) == 'theme_') {
      $name = substr($name,6);
      $type = 'Themes';
    }
    /*
    if(substr($name, -5) == 'model' && strlen($name) > 5) {
      $name = substr($name,0,-6);
      $type = 'Application (Lambda MVC)';
    }
    */
    return array('name'=>$name,'type'=>$type);
  }
}





?>