<?php

class AppverseModel extends AppverseCommon {
  
  public function checksecret($result,$secret) {
    if(empty($result)) {
      return null;
    }
    else if($result['secret'] == $secret) {
      return 1;
    }
    else {
      return 2;
    }
  }
  public function createsession($name='appverse',$val=null,$serialize=false) {
    if(!isset($_SESSION[$name])) {
      if($val == null) {
        $val = microtime();
      }
      if($serialize) {
        $val = serialize($val);
      }
      $_SESSION[$name] = $val;
    }
  }
  public function getsession($name='appverse',$unserialize=false) {
    if(isset($_SESSION[$name])) {
      if($unserialize) {
        return unserialize($_SESSION[$name]);
      }
      else {
        return $_SESSION[$name];
      }
    }
  }
  public function check_repository($address) {
    if(empty($address)) {
      return 2;
    }
    else {
      $response = $this->send_request($address,'&p=start');
      if($response == 'APPVERSE_SERVER_READY') {
        return 1;
      }
    }
    if(isset($_SESSION['repository'])) {
      unset($_SESSION['repository']);
    }
    return 3;
  }
  private function send_request($address,$request='') {
    try {
      $ch = curl_init();
      $timeout = 5;
      curl_setopt($ch, CURLOPT_URL, $address.'/index.php?m=appverse_server'.$request);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      $data = curl_exec($ch);
      curl_close($ch);
      return base64_decode($data);
    }
    catch (Exception $e) {
      Logger::write('WARNING',$e);
      return null;
    }
  }
  public function getinformation($address) {
    return $this->send_request($address,'&p=information');
  }
  public function check_address($address) {
    if(!empty($address)) {
      if(is_array($address)) {
        $address = $address['address'];
      }
      if((substr($address, 0, 7) != 'http://') && (substr($address, 0, 8) != 'https://')) {
        $address = 'http://'.$address;
      }
      return rtrim($address,'/');
    }
    return null;
  }
  public function get_repository($address,$parameter=null) {
    if($parameter == 'refresh') {
      $this->cache->deleteCache('appverse_cache');
    }
    else {
      $cache = $this->cache->readCache('appverse_'.$address);
      if($cache != null) {
        return $cache;
      }
    }
    $this->modules = $this->getmodules($parameter);
    $this->address = $address;
    $this->modules_dir = $this->send_request($this->address,'&p=modules_dir');
    
    $modules = $this->send_request($this->address,'&p=modules_available');
    if(empty($modules)) {
      return null;
    }
    else {
      $modules = unserialize($modules);
    }
    
    foreach($modules as $category_name => &$category) {
      $category = $this->compare_cat($category_name,$category);
    }
    
    $this->cache->cacheString('appverse_'.$address,$modules);
    return $modules;
  }
  public function disconnect_repository($address) {
    unset($_SESSION['repository']);
    $this->cache->deleteCache('appverse_'.$address);
  }
  private function compare_cat($category_name,$category) {

    foreach($category as $module_name=>&$module) {
      if(isset($this->modules[$category_name][$module_name])) {
        $module = $this->compare_module($module,$this->modules[$category_name][$module_name]);
      }
      else {
        if(isset($module['icon'])) {
          $module['icon_ext'] = $this->address.'/'.$this->modules_dir.'/'.$module['icon'];
        }
        $module['status'] = 'new';
      }
    }
    return $category;
  }
  private function compare_module($module,$current) {
    if($module['version'] == $current['version']) {
      $module['status'] = 'ok';
    }
    else if($module['version'] > $current['version']) {
      $module['status'] = 'newer';
    }
    else if($module['version'] < $current['version']) {
      $module['status'] = 'older';
    }
    return $module;
  }
  public function setmodule($category,$module) {
    $this->module = $this->getmodule($category,$module);
  }
  public function setmodule_ext($address,$category,$module) {
    #used by Appverse->install
    $this->address = $address;
    $this->module = $this->getmodule_ext($address,$category,$module);
  }
  public function getmodule($category,$module) {
    if(!isset($this->modules)) {
      $this->modules = $this->getmodules();
    }
    if(isset($this->modules[$category][$module])) {
      return $this->modules[$category][$module];
    }
    return null;
  }
  public function getmodule_ext($address,$category,$module) {
    if(!isset($this->modules_available)) {
      $this->modules_available = $this->get_repository($address);
    }
    if(isset($this->modules_available[$category][$module])) {
      return $this->modules_available[$category][$module];
    }
    return null;
  }
  public function module_search($name) {
    if(isset($this->modules)) {
      foreach($this->modules as $category) {
        foreach($category as $module) {
          if($module['module'] == $name) {
            return $module;
          }
        }
      }
    }
    return null;
  }
  public function module_search_ext($name) {
    if(isset($this->modules_available)) {
      foreach($this->modules_available as $category) {
        foreach($category as $module) {
          if($module['module'] == $name) {
            return $module;
          }
        }
      }
    }
    return null;
  }
  public function gettemplatedir() {
    #used by Appverse->install & uninstall
    if(isset($this->module['install_script'])) {
    
    }
    return null;
  }
  public function gettemplate($context) {
    #used by Appverse->install & uninstall
    if(isset($this->module['install_script'])) {
    
    }
    else if(isset($this->module['config'])) {
      return $context.'_config';
    }
    return $context.'_basic';
  }
  public function uninstall($result) {
    if($result != null) {
      if(isset($this->module['package'])) {
        foreach($this->module['package'] as $package) {
          $remove = $this->remove($package,$result);
          if($remove != 1) {
            return $remove;
          }
        }
      }
      return $this->remove($this->module['module'],$result);
    }
  }
  public function getfilelist($module) {
    return unserialize($this->send_request($this->address,'&p=file_list&id='.$module));
  }
  public function resolveAll() {
    #used by Appverse->install
    if(!isset($this->modules)) {
      $this->modules = $this->getmodules();
    }
    $this->resolve = $this->resolve(null,$this->module);
  }
  public function getInstallContent() {
    #used by Appverse->install
    $content = array();
    foreach($this->resolve as $module) {
      if(isset($module['status']) && $module['status'] == 'new') {
        $content = $content + $this->gettemplatecontent($module);
      }
    }
    return $content;
  }
  private function resolve($module_name,$module=null,$required_version=null) {
    $content = array();
    if($module == null) {
      $module = $this->module_search($module_name);
      if($module == null || $module['version'] != $required_version) {
        $module = $this->module_search_ext($module_name);
      }
    }
    $content[$module['module']] = $module;
    if(isset($module['dependency'])) {
      foreach($module['dependency'] as $dependency_name => $dependency_version) {
        $content = $content + $this->resolve($dependency_name,null,$dependency_version);
      }
    }
    if(isset($module['package'])) {
      foreach($module['package'] as $package) {
        $content = $content + $this->resolve($package);
      }
    }
    return $content;
  }
  private function resolve_old($module) {
    if(!isset($this->modules)) {
      $this->modules = $this->getmodules();
    }
    if(isset($module['dependency'])) {
      $dependencies = array();
      foreach($module['dependency'] as $dependency=>$version) {
        $module_found = $this->module_search($dependency);
        if($module_found == null) {
          $module_found_ext = $this->module_search_ext($dependency);
          if(isset($module_found_ext['config'])) {
            $dependencies[$dependency] = array('action'=>'update','config'=>$module_found_ext['config']);
          }
          else {
            $dependencies[$dependency] = array('action'=>'install');
          }
        }
        else if($module_found['version'] < $version) {
          /*
          if(isset($module['config'])) {
            $dependencies[$dependency] = array('action'=>'up','config'=>$module['config']);
          }
          */
          $dependencies[$dependency] = array('action'=>'up');
        }
      }
      $module['dependency'] = $dependencies;
    }
    return $module;
  }
  public function gettemplatecontent($module) {
    #used by Appverse->install
    $content = array();
    if(isset($module['config'])) {
      $content[$module['module']] = unserialize($this->send_request($this->address,'&p=get_config&id='.$module['module'].'@'.$module['config']));
    }
    return $content;
  }
  public function install($result,$content) {
    if($result != null) {
      foreach($this->resolve as $module) {
        if($module['status'] == 'new') {
          $download = $this->download($module['module']);
        }
        if(isset($content[$module['module']])) {
          $installer = new GenericInstaller($module['module']);
          $installer->install($result,$content[$module['module']],$module['config']);
        }
      }
      $this->cache->deleteCache('appverse_'.$this->address);
    }
  }
  public function install_old($result,$config,$module=null) {
    #used by Appverse->install
    if($module == null) {
      $module = $this->module;
    }
    else {
      $module = $this->module_search_ext($module);
    }
    $module = $this->resolve($module);
    if($result != null) {
      if(isset($module['package'])) {
        foreach($module['package'] as $package) {
          $this->install($result,$config,$package);
        }
      }
      if(isset($module['dependency'])) {
        foreach($module['dependency'] as $dependency=>$action) {
          $action = $this->$action['action']($result,$config,$dependency);
        }
      }
      /*
      $download = $this->download($module['module']);
      if($download != 1) {
        return $download;
      }
      */
      return $this->add($result,$config,$module);
    }
  
  }
  private function update($result,$config,$dependency) {
    #print_r($result);
    #print_r($config);
    #print_r($dependency);
  }
  private function create_directories($module,$directories) {
    #used by Appverse->install
    if(is_array($directories)) {
      if(!file_exists(Config::read('lmbd.modules.dir').'/'.$module)) {
        if(!mkdir(Config::read('lmbd.modules.dir').'/'.$module)) {
          return false;
        }
      }
      foreach($directories as $path => $directory) {
        if(!mkdir(Config::read('lmbd.modules.dir').'/'.$module.'/'.$path)) {
          return false;
        }
      }
      return true;
    }
    return false;
  }
  private function download($module) {
    #used by Appverse->install
    $files = $this->getfilelist($module);
    if($this->create_directories($module,$files['directories'])) {
      foreach($files['files'] as $path=>$file) {
        $content = $this->send_request($this->address,'&p=get_file&id='.$module.'@'.$path);
        if(!file_put_contents(Config::read('lmbd.modules.dir').'/'.$module.'/'.$path,$content)) {
          return 11;
        }
        unset($content);
      }
      return 1;
    }
    return 12;
  }
  private function up($module,$result) {
    echo 'update'.$module;
    return 1;
  }
  private function remove($module,$result) {
    $installer = new GenericInstaller($module);
    if(isset($this->module['config'])) {
      if($result['preserve_config'] == 'Y') {
        return $installer->uninstall($this->module['config']);
      }
      else {
        return $installer->uninstall();
      }
    }
    else {
    
    }
  }
  public function getthemes() {
    if(!isset($this->modules)) {
      $this->modules = $this->getmodules();
    }
    return $this->getcat('Themes');
  }
  public function getdrivers() {
    if(!isset($this->modules)) {
      $this->modules = $this->getmodules();
    }
    return $this->getcat('DB drivers');
  }
  private function getcat($cat) {
    $return_array = array();
    foreach($this->modules[$cat] as $module=>$content) {
      if(isset($content['name'])) {
        $return_array[$module] = $content['name'];
      }
      else {
        $return_array[$module] = $module;
      }
    }
    return $return_array;
  }
  public function createmodule($result) {
    if($result != null) {
      if($result['database'] == 'Y') {
        if(empty($result['dbhost'])) {
          return 2;
        }
        if(empty($result['dbname'])) {
          return 3;
        }
        if(empty($result['dbuser'])) {
          return 4;
        }
        if(empty($result['dbpass'])) {
          return 5;
        }
      }
      $this->write_driver_conf($result);
      
    }
    return null;
  }
  private function write_driver_conf($conf) {
  
  }
  public function get_welcome_message($address) {
    return $this->send_request($address,'&p=welcome');
  }
}















?>