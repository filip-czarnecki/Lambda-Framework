<?php

require_once(Config::read('lmbd.modules.dir').'/logger/config.php');

class Logger {

  public static function check_log_file() {
    if(Config::read('logger.enabled') == 1 && Config::read('logger.write') == 1) {
      if(file_exists(Config::read('lmbd.modules.dir').'/'.Config::read('logger.file'))) {
        if(Config::read('logger.autoremove.size') != 0) {
          if(filesize(Config::read('lmbd.modules.dir').'/'.Config::read('logger.file')) > Config::read('logger.autoremove.size')) {
            Config::write('logger.autoremove.set', 1);
          }
        }
      }
    }
  }
  public static function write($type, $value, $exit=false) {
    if(Config::read('logger.enabled') == 1) {
      if(Config::read('logger.level') != 'ALL') {
        $level = explode(',', Config::read('logger.level'));
        if(!in_array($type,$level)) {
          return false;
        }
      }
      $now = date("Y-m-d H:i:s");
      $log = $now.' | LMBD_'.$type.' | '.$value;
      if(Config::read('logger.write') == 1) {
        if(Config::read('logger.autoremove.set') == 1) {
          file_put_contents(Config::read('lmbd.modules.dir').'/'.Config::read('logger.file'), $log.PHP_EOL);
          Config::write('logger.autoremove.set', 0);
        }
        else {
          file_put_contents(Config::read('lmbd.modules.dir').'/'.Config::read('logger.file'), $log.PHP_EOL, FILE_APPEND | LOCK_EX);
        }
      }
      if($exit == true || $type == 'CRITICAL') {
        exit($log);
      }
      if(Config::read('logger.display') == 1) {
        echo($log.'<br />');
      }
    }
  }
}

?>