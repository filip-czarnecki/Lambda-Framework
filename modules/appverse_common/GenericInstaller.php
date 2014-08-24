<?php

#abstract?
class GenericInstaller {
  protected $files = array();
  protected $directories = array();
  
  public function __construct($module) {
    $this->module = $module;
  }
  public function install($result,$config,$config_file) {
    $content = '<?php'.PHP_EOL;
    foreach($config as $config_value) {
      $content .= "Config::write('".$config_value['description']."', '".$result[$config_value['formkey']]."');".PHP_EOL;
    }
    $content .= '?>';
    file_put_contents(Config::read('lmbd.modules.dir').'/'.$this->module.'/'.$config_file.'.php', $content);
  }
  public function uninstall($exclude=null) {
    if($exclude == null) {
      $remove_root = true;
    }
    else {
      $remove_root = false;
    }
    if(Util::remove_dir(Config::read('lmbd.modules.dir').'/'.$this->module,$remove_root,$exclude)) {
      return 1;
    }
    else {
      return 2;
    }
  }
  public function update($from_version,$to_version) {
  
  }
  public function configure() {
  
  }
}


?>