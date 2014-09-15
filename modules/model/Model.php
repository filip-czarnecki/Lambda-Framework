<?php

class Model {
  public function __construct() {
    #require_once(Config::read('lmbd.modules.dir').'/model/DBHMagic.php');
    require_once(Config::read('lmbd.modules.dir').'/model/iDriver.php');
    require_once(Config::read('lmbd.modules.dir').'/'.Config::read('model.configuration.source'));
    $drivers_enabled = json_decode($drivers_enabled);
    if(!empty($drivers_enabled)) {
      foreach($drivers_enabled as $count => $driver) {
        if(isset($driver->bind)) {
          $bindto = explode(',', $driver->bind);
          if(!in_array(Config::read('lmbd.appcontroller'),$bindto)) {
            continue;
          }
        }
        $driver_name = 'driver_'.$driver->driver;
        if($count == 0) {
          $dbh = 'dbh';
        }
        else {
          $dbh = 'dbh'.$count;
        }
        require_once(Config::read('lmbd.modules.dir').'/'.$driver_name.'/driver.php');
        if(in_array('iDriver',class_implements($driver_name), false)) {
          if(isset($this->event)) {
            $event = $this->event;
          }
          else {
            $event = null;
          }
          $this->$dbh = new $driver_name($driver->host,$driver->port,$driver->name,$driver->user,$driver->pass,$driver->pref);
          #$this->$dbh = new DBHMagic($dbh,$driver,$event);
        }
      }
    }
    else {
      Logger::write('NOTICE','No drivers found.');
    }
  }
  public function __call($method, $args) {
    if(isset($this->dbh)) {
      return $this->dbh->$method($args);
    }
  }
}

?>