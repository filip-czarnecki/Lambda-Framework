<?php

class AppverseServerModel extends AppverseCommon {
  public function get_response($command) {
    switch($command) {
      case 'start':
        $response = 'APPVERSE_SERVER_READY';
        break;
      case 'welcome':
        $welcome = Config::read('appverse.server.welcome');
        $response = nl2br(Util::trim_str($welcome,160));
        break;
      case 'modules_dir':
        $response = Config::read('lmbd.modules.dir');
        break;
      case 'modules_available':
        $response = serialize($this->getmodules());
        break;
    }
    return base64_encode($response);
  }
  public function get_file_list($name) {
    if(!empty($name) && strpos($name, '/') === false) {
      $cache = $this->cache->readCache('appverse_server_'.$name );
      if($cache == null) {
        $dir = Config::read('lmbd.modules.dir').'/'.$name;
        $scan = Util::scan_dir($dir);
        $exclude = array();
        if(file_exists(Config::read('lmbd.modules.dir').'/'.$name.'/install.ini')) {
          $install = parse_ini_file(Config::read('lmbd.modules.dir').'/'.$name.'/install.ini');
          if(isset($install['exclude'])) {
            $exclude = $install['exclude'];
          }
        }
        $scan['files'] = $this->strip_path($scan['files'],$name,$exclude);
        $scan['directories'] = $this->strip_path($scan['directories'],$name);
        $this->cache->cacheString('appverse_server_'.$name,serialize($scan),1800);
      }
      else {
        $scan = unserialize($cache);
      }
      return $scan;
    }
  }
  private function strip_path($files,$module,$exclude=array()) {
    $return_array = array();
    foreach($files as $path=>$filename) {
      $len1 = strlen(Config::read('lmbd.modules.dir'))+1;
      $len2 = strlen($module)+1;
      $path = substr($path,$len1+$len2);
      if(!in_array($path,$exclude)) {
        $return_array[$path] = $filename;
      }
    }
    return $return_array;
  }
  public function get_config($module,$file) {
    $config = array();
    $config_content = file_get_contents(Config::read('lmbd.modules.dir').'/'.$module.'/'.$file.'.php');
    $config_lines = explode("\n", $config_content);
    foreach($config_lines as $count=>$line) {
      if(isset($line[0]) && $line[0] != '#') {
        $key = Util::get_string_between($line,"Config::write('","',");
        $val = Util::get_string_between($line,"',",");");
        if(!empty($key)) {
          $default = '';
          $description = '';
          if(isset($config_lines[$count-1])) {
            $prev_line = $config_lines[$count-1];
            if(isset($prev_line[0]) && $prev_line[0] == '#') {
              $default = Util::get_string_between($prev_line,"default:",";");
              $description = Util::get_string_between($prev_line,"description:",";");
            }
          }
          if(empty($description)) {
            $description = $key;
          }
                              
          $val = trim($val);
          if(!empty($default)) {
            $default = trim($default);
            if(is_numeric($default) && $default == 0) {
              $default = 'N';
            }
            else if(is_numeric($default) && $default == 1) {
              $default = 'Y';
            }
            $val = trim($default);
          }
          else if(is_numeric($val) && ($val == 0 || $val == 1)) {
            $val = 'YN';
          }
          else {
            $val = 'STR';
          }
          
          $config[$key] = array('description'=>$description,'formkey'=>str_replace('.','',$key),'value'=>$val);
        }
      }
    }
    return base64_encode(serialize($config));
  }
  public function get_file($module,$file) {
    if(!empty($module) && !empty($file)) {
      $files = $this->cache->readCache('appverse_server_'.$module);
      if($files == null) {
        $files = $this->get_file_list($module);
      }
      $files=unserialize($files);
      if(isset($files['files'][$file])) {
        return base64_encode(file_get_contents(Config::read('lmbd.modules.dir').'/'.$module.'/'.$file));
      }
    }
  }

}

?>