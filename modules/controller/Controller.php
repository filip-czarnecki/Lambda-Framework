<?php

/*LMBDV1 Controller 0.51
Lambda Framework copyright © 2013-2014 Filip Czarnecki*/

class Controller {
  private $ini_array = array();
  private $modules_autoload = array();
  private $injections = array();
    
  public function __construct($lmbd_conf) {
    #load core elements
    require_once($lmbd_conf['modules_dir'].'/config/Config.php');
    Config::write('lmbd.modules.dir', $lmbd_conf['modules_dir']);
    require_once(Config::read('lmbd.modules.dir').'/controller/config.php');
    require_once(Config::read('lmbd.modules.dir').'/controller/iConstruct.php');
    require_once(Config::read('lmbd.modules.dir').'/logger/Logger.php');
    Logger::check_log_file();
    Config::write('lmbd.core.type', 'LMBDV1');
    Config::write('lmbd.core.version', 0.51);
    $this->conf_file = ''.Config::read('lmbd.modules.dir').'/controller/modules_defined.php';
    $this->module_called = $this->user_input();
    $this->controller_start();
  }
  private function controller_start($update=false) {
    if(Config::read('lmbd.controller.workmode') == 'performance') {
      $this->start_defined_modules();
    }
    else if(Config::read('lmbd.controller.workmode') == 'discovery') {
      $this->discover_modules();
      if($update) {
        $this->update_modules_file();
      }
    }
    else if(Config::read('lmbd.controller.workmode') == 'automatic') {
      $result = $this->check_modules_file();
      if($result == 1) {
        Config::write('lmbd.controller.workmode', 'performance',true);
      }
      else {
        Config::write('lmbd.controller.workmode', 'discovery',true);
      }
      if($result == 2) {
        $update = true;
      }
      $this->controller_start($update);
    }
    else {
      Logger::write('CRITICAL','Invalid controller workmode');
    }
  }
  private function user_input() {
    if(isset($_GET['m']) && !empty($_GET['m']) && Config::read('lmbd.default.module.force') != 1) {
      Config::write('lmbd.module.called', $_GET['m']);
      return $_GET['m'];
    }
    else {
      $default = Config::read('lmbd.default.module');
      if(!empty($default) && $default != 'none') {
        return $default;
      }
    }
    return null;
  }
  private function update_modules_file() {
    if(defined('JSON_PRETTY_PRINT')) {
      $this->modules_defined = json_encode($this->ini_array, JSON_PRETTY_PRINT);
    }
    else {
      $this->modules_defined = json_encode($this->ini_array);
    }
    $content = '<?php'.PHP_EOL;
    $content .= '$modules_defined = (\''.PHP_EOL;
    $content .= $this->modules_defined;
    $content .= '\');'.PHP_EOL;
    $content .= '$last_modified = \''.time().'\';'.PHP_EOL;
    $content .= '?>';
    file_put_contents($this->conf_file, $content);
  }
  private function check_modules_file() {
    if(is_writable($this->conf_file)) {
      $last_modified = 0;
      include($this->conf_file);
      if(time() - $last_modified < Config::read('lmbd.controller.scan.interval')) {
        return 1;
      }
      else {
        return 2;
      }
    }
    else {
      Logger::write('NOTICE','Cannot write to modules file');
      return 3;
    }
  }
  private function start_defined_modules() {
    if(isset($this->modules_defined)) {
      $modules_defined = $this->modules_defined;
    }
    else {
      include(''.Config::read('lmbd.modules.dir').'/controller/modules_defined.php');
      if(isset($modules_defined)) {
        $modules_defined = json_decode($modules_defined, true);
      }
      else {
        $modules_defined = null;
      }
    }
    if(is_array($modules_defined)) {
      $this->ini_array = $modules_defined;
      foreach($modules_defined as $module => $ini_array) {
        $this->load_settings($module);
      }
      $this->lmbd_start();
      return true;
    }
    #fallback
    $this->discover_modules();
    return false;
  }
  private function discover_modules() {
    if(Config::read('lmbd.modules.loaded') == null) {
      Config::write('lmbd.modules.loaded', 1);
      if($handle = opendir(Config::read('lmbd.modules.dir'))) {
        $exclude = array('.', '..');
        while (false !== ($dir = readdir($handle))) {
          if(!in_array($dir, $exclude) && is_dir(Config::read('lmbd.modules.dir').'/'.$dir)) {
            if(file_exists(Config::read('lmbd.modules.dir').'/'.$dir.'/module.ini')) {
              $this->ini_array[$dir] = parse_ini_file(Config::read('lmbd.modules.dir').'/'.$dir.'/module.ini');
              $this->load_settings($dir);
            }
          }
        }
        closedir($handle);
        $this->lmbd_start();
      }
      else {
        Logger::write('CRITICAL','Cannot read modules directory');
      }
    }
  }
  private function lmbd_start() {
    $this->check_appcontroller();
    if(!empty($this->modules)) {
      $this->load_modules();
      $this->run_modules();
    }
    else {
      Logger::write('NOTICE','No modules found');
    }
  }
  private function check_appcontroller() {
    if(!isset($this->appcontroller)) {
      if(isset($this->appcontroller_possible)) {
        $this->load_settings($this->appcontroller_possible,false);
        $this->set_appcontroller($this->appcontroller_possible);
      }
      else {
        Logger::write('NOTICE','No appcontroller found');
      }
    }
  }
  private function set_appcontroller($module) {
    if(!isset($this->appcontroller)) {
      $this->appcontroller = $module;
      $this->modules_autoload[$module] = $this->ini_array[$module]['classname'];
      Config::write('lmbd.appcontroller', $module);
    }
  }
  private function load_settings($module,$check_appcontroller=true) {
    if(isset($this->ini_array[$module]['enabled']) && $this->ini_array[$module]['enabled'] == 1) {
      if(isset($this->ini_array[$module]['classname'])) {
        if($check_appcontroller) {
          if((isset($this->ini_array[$module]['appcontroller'])) && ($this->ini_array[$module]['appcontroller'] == 1)) {
            if($module == $this->module_called) {
              $this->set_appcontroller($module);
            }
            else if(!isset($this->appcontroller) && !isset($this->appcontroller_possible)) {
              $this->appcontroller_possible = $module;
              return false;
            }
            else {
              return false;
            }
          }
        }
        $this->modules[$module] = $this->ini_array[$module]['classname'];
        
        if(isset($this->ini_array[$module]['classname'])) {
          if(isset($this->ini_array[$module]['autoload'])) {
            if($this->ini_array[$module]['autoload'] == 1) {
              $this->modules_autoload[$module] = $this->ini_array[$module]['classname'];
            }
          }
          else {
            $this->ini_array[$module]['classname'] = null;
          }
          if(isset($this->ini_array[$module]['config'])) {
            include_once(Config::read('lmbd.modules.dir').'/'.$module.'/'.$this->ini_array[$module]['config'].'.php');
          }
        }
      }
      if(isset($this->ini_array[$module]['dependency'])) {
        $this->resolve($this->ini_array[$module]['classname'],$this->ini_array[$module]['dependency']);
      }
    }
  }
  private function resolve($parent_classname,$dependency_array) {
    if(is_array($dependency_array)) {
      foreach($dependency_array as $dependency_module => $dependency_version) {
        if(isset($this->ini_array[$dependency_module])) {
          $dependency_ini_array = $this->ini_array[$dependency_module];
        }
        else {
          $dependency_ini_array = parse_ini_file(Config::read('lmbd.modules.dir').'/'.$dependency_module.'/module.ini');
        }        
        if($this->is_compatible($dependency_ini_array['version'],$dependency_version)) {
          $resolve = true;
          if(isset($dependency_ini_array['config'])) {
            include_once(Config::read('lmbd.modules.dir').'/'.$dependency_module.'/'.$dependency_ini_array['config'].'.php');
          }
          if(isset($dependency_ini_array['classname']) && isset($dependency_ini_array['autoload'])) {
            if(isset($dependency_ini_array['inject']) && $dependency_ini_array['inject'] == 1 && $parent_classname != null) {
              $classl = strtolower($dependency_ini_array['classname']);
              if(isset($this->injections[$parent_classname][$classl])) {
                $resolve = false;
              }
              $this->injections[$parent_classname][$classl] = $dependency_ini_array['classname'];
            }
            if(isset($dependency_ini_array['dependency'])) {
              if($resolve) {
                $this->resolve($dependency_ini_array['classname'],$dependency_ini_array['dependency']);
              }
            }
            include_once(Config::read('lmbd.modules.dir').'/'.$dependency_module.'/'.$dependency_ini_array['classname'].'.php');
            if($dependency_ini_array['autoload'] == 1) {
              $this->run_module($dependency_module,$dependency_ini_array['classname']);
            }
          }
        }
        else {
          if(empty($dependency_ini_array['version'])) {
            $version = 'none';
          }
          else {
            $version = $dependency_ini_array['version'];
          }
          Logger::write('CRITICAL','Cannot load module '.$dependency_module.'<br />Minimal version required by dependency is '.$dependency_version.', version installed is '.$version);
        }
      }
    }
  }
  private function is_compatible($version_provided, $version_required) {
    if($version_provided >= $version_required) {
      return true;
    }
    return false;
  }
  private function load_modules() {
    foreach($this->modules as $dir => $module) {
      include_once(Config::read('lmbd.modules.dir').'/'.$dir.'/'.$module.'.php');
    }
  }
  private function run_modules() {

    foreach($this->modules_autoload as $key => $value) {
      $this->run_module($key,$value);
    }
  }
  private function run_module($id,$module) {
    if(!isset($this->$module)) {
      $this->$module = new $module();
      if(array_key_exists($module,$this->injections)) {
        foreach($this->injections[$module] as $inject => $classname) {
          if(!isset($this->$classname)) {
            $this->run_module(strtolower($classname),$classname);
          }
          $this->$module->$inject = $this->$classname;
        }
      }
      if(in_array('iConstruct',class_implements($this->$module), false)) {
        $this->$module->init();
      }
    }
  }
}

?>