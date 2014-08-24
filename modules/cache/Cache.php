<?php

require_once(Config::read('lmbd.modules.dir').'/event/iObserver.php');

class Cache implements iObserver {
  private $view_cache = array();
  private $write_view = false;
  private $force_reload = false;
  
  public function __construct() {
  
    if(Config::read('cache.compression') == 1 && function_exists('gzcompress')) {
      $this->compression = true;
    }
    else {
      $this->compression = false;
    }
  
    if(Config::read('cache.view') == 1) {
      $this->view_default_update_period = Config::read('cache.view.default');
      if($handle = opendir(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir'))) {
        while (false !== ($file = readdir($handle))) {
          if(substr($file, -4) == '.php' && substr($file, 0,5) == 'cache') {
            include_once(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.$file);
            if(isset($name) && isset($last_modified)) {
              $this->view_cache[$name] = $last_modified;
              unset($name);
              unset($last_modified);
            }
            else if(isset($name) && isset($expires)) {
              if($expires > time()) {
                unlink(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.$file);
                unlink(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.md5($name).'.cache');
              }
              unset($name);
              unset($expires);
            }
          }
        }
        closedir($handle);
      }
    }
  }
  public function forceReload() {
    $this->force_reload = true;
  }
  private function getHeader($name,$expiration=null) {
    $header = '<?php'.PHP_EOL;
    $header .= '$name = \''.$name.'\';'.PHP_EOL;
    if($expiration == null) {
      $header .= '$last_modified = \''.time().'\';'.PHP_EOL;
    }
    else {
      $header .= '$expires = \''.$expiration.'\';'.PHP_EOL;
    }
    $header .= '?>';
    return $header;
  }
  public function update($type,$data=null) {
    if($type == 'view_startView' && $this->write_view == true) {
      $header = $this->getHeader($this->uniqid);
      if(is_writable(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir'))) {
        file_put_contents(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/cache'.md5($this->uniqid).'.php', $header);
        $this->cacheString($this->uniqid,$data);
      }
    }
    else if($type == 'template_startViewCache') {
      exit('caching');
    }
  }
  public function cacheString($name,$content,$expiration=null) {
    if($this->compression) {
      $content = gzcompress(json_encode($content));
    }
    else {
      $content = json_encode($content);
    }
    if(is_writable(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir'))) {
      if($expiration != null && is_numeric($expiration)) {
        $expiration = time() + $expiration;
        $header = $this->getHeader($name,$expiration);
        file_put_contents(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/cache'.md5($name).'.php', $header);
      }
      if(file_put_contents(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.md5($name).'.cache', $content) !== false) {
        return true;
      }
    }
    return false;
  }
  public function deleteCache($name) {
    if(file_exists(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.md5($name).'.cache')) {
      return unlink(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.md5($name).'.cache');
    }
    return false;
  }
  public function cacheView($uniqid,$update_period=null) {
    $this->uniqid = $uniqid;
    if(Config::read('cache.view') == 1) {
      if($update_period == null) {
        $update_period = $this->view_default_update_period;
      }
      if($this->force_reload) {
        $this->write_view = true;
      }
      else if(isset($this->view_cache[$this->uniqid])) {
        $now = time();
        if($now - $this->view_cache[$this->uniqid] > $update_period) {
          $this->write_view = true;
        }
        else {
          return $this->readCache($this->uniqid);
        }
      }
      else {
        $this->write_view = true;
      }
    }
    return null;
  }
  public function readCache($name) {
    if(file_exists(Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.md5($name).'.cache')) {
      $file = Config::read('lmbd.modules.dir').'/'.Config::read('cache.storage.dir').'/'.md5($name).'.cache';
      $cache = file_get_contents($file);
      if($this->compression) {
        $cache = json_decode(gzuncompress($cache),true);
      }
      else {
        $cache = json_decode($cache,true);
      }
      return $cache;
    }
    return null;
  }
}











?>