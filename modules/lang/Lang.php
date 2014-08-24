<?php

class Lang {
  protected $languages_enabled = array();
  protected $language = null;
    
  public function __construct() {
    require_once(Config::read('lmbd.modules.dir').'/'.Config::read('lang.configuration.source'));
    $this->languages_enabled = json_decode($languages_enabled,true);
  }
  public function setLanguage($lang) {
    $this->language = $lang;
  }
  public function __destruct() {
    if($this->language == null) {
      $this->language = $this->languages_enabled[0];
    }
    include_once(Config::read('lmbd.modules.dir').'/lang/'.$this->language['name'].'/stringtable.php');
    $display = $this->view->getDisplay();
    foreach($display['display_array'] as $area=>&$content) {
      $content = str_replace(array_keys($str),$str,$content);
    }
    $this->view->setDisplay($display);
  }
}
?>