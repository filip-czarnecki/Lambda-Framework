<?php

class Config {
  private static $confArray;

  public static function read($name) {
    if(isset(self::$confArray[$name])) {
      return self::$confArray[$name];
    }
    else {
      return null;
    }
  }
  public static function write($name,$value,$force=false) {
    if($force == false && isset(self::$confArray[$name])) {
      return false;
    }
    else {
      self::$confArray[$name] = $value;
      return true;
    }
  }
}

?>