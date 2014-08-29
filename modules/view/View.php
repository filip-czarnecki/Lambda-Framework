<?php

class View {
  private $void;
  private $abort;
  private $preview;
  private $theme;
  private $theme_file = 'index.html';
  private $preview_flag = '<!-- LMBD_PREVIEW_FLAG -->';
  public $input;
  public $previous_area;
  private $display_array=array();
  private $preview_flags=array();
  private $abstract_areas=array();
  
  public function setTheme($theme) {
    $this->theme = $theme;
  }
  public function setThemeFile($file) {
    $this->theme_file = $file;
  }
  public function getTheme($theme) {
    return $this->theme;
  }
  public function setArea($area) {
    if(isset($this->current_area)) {
      $this->previous_area = $this->current_area;
    }
    else {
      $this->previous_area = 'body';
    }
    $this->current_area = $area;
  }
  public function setPreviousArea() {
    if(isset($this->previous_area)) {
      $this->setArea($this->previous_area);
    }
  }
  public function createArea($abstract_area) {
    $this->abstract_areas[$abstract_area] = $this->current_area;
    $this->render('<!-- '.$abstract_area.' -->');
  }
  public function createAreaPush($abstract_area) {
    $this->abstract_areas[$abstract_area] = $this->current_area;
    return ('<!-- '.$abstract_area.' -->');
  }
  public function cloneArea($source,$destination) {
    if(isset($this->display_array[$source]) && isset($this->display_array[$destination])) {
      $this->display_array[$source] = $this->display_array[$destination];
      return true;
    }
    return false;
  }
  public function destroyArea($area) {
    unset($this->display_array[$area]);
    if(isset($abstract_areas[$area])) {
      unset($abstract_areas[$area]);
    }
  }
  public function clearArea($area) {
    $this->display_array[$area] = '';
  }
  public function display($what) {
    if(!isset($this->current_area)) {
      $this->current_area = 'body';
    }
    if(!isset($this->void)) {
      if(isset($this->preview)) {
        if(!in_array($this->current_area,$this->preview_flags)) {
          $this->render($this->preview_flag);
          $this->preview_flags[] = $this->current_area;
        }
        if(isset($this->display_array['preview'][$this->current_area])) {
          $this->display_array['preview'][$this->current_area] .= $what;
        }
        else {
          $this->display_array['preview'][$this->current_area] = $what;
        }
      }
      else {
        if(!isset($this->input)) {
          $this->input = true;
        }
        $this->render($what);
      }
    }
  }
  private function render($what) {
    if(isset($this->display_array[$this->current_area])) {
      $this->display_array[$this->current_area] .= $what;
    }
    else {
      $this->display_array[$this->current_area] = $what;
    }
  }
  public function redirect($url='',$time=0) {
    #may become unsupported in future releases
    if($time == 0) {
      exit('<meta http-equiv="refresh" content="0; url='.$url.'">');
    }
    else {
      $this->display_array['head'] = '<meta http-equiv="refresh" content="'.$time.'; '.$url.'">';
    }
  }
  public function startVoid() {
    $this->void = true;
  }
  public function endVoid() {
    unset($this->void);
  }
  public function getVoid() {
    if(isset($this->void)) {
      return true;
    }
    return false;
  }
  public function startPreview() {
    $this->preview = true;
  }
  public function endPreview() {
    unset($this->preview);
  }
  public function renderPreview() {
    if(isset($this->display_array['preview'])) {
      if(!isset($this->input)) {
        $this->input = true;
      }
      foreach($this->display_array['preview'] as $area => $display) {
        $this->display_array[$area] = str_replace($this->preview_flag,$display,$this->display_array[$area]);
      }
      unset($this->display_array['preview']);
    }
  }
  public function abortView() {
    $this->abort = true;
  }
  public function getDisplay() {
    return array('display_array'=>$this->display_array,'abstract_areas'=>$this->abstract_areas);
  }
  public function setDisplay($data) {
    if(isset($data['display_array']) && isset($data['abstract_areas'])) {
      $this->display_array = $data['display_array'];
      $this->abstract_areas = $data['abstract_areas'];
    }
    else {
      Logger::write('NOTICE','Provided empty display data in view->setDisplay() function.');
    }
  }
  public function setDisplayArea($data,$area=null) {
    if($area == null) {
      $area = $this->current_area;
    }
    $this->display_array[$area] = $data;
  }
  public function getDisplayArea($area=null) {
    if($area == null) {
      $area = $this->current_area;
    }
    return $this->display_array[$area];
  }
  public function startView() {
    if(!isset($this->abort)) {
      if(isset($this->event)) {
        $this->event->notify('view_startView',array($this->getDisplay()));
      }
      if(!empty($this->abstract_areas)) {
        foreach($this->abstract_areas as $abstract_area => $parent_area) {
          if(!empty($this->display_array[$abstract_area])) {
            $this->display_array[$parent_area] = str_replace('<!-- '.$abstract_area.' -->',$this->display_array[$abstract_area],$this->display_array[$parent_area]);
          }
        }
      }
      if(!isset($this->theme)) {
        $this->theme = Config::read('view.default.theme');
      }
      if(file_exists(Config::read('lmbd.modules.dir').'/theme_'.$this->theme.'/'.$this->theme_file)) {
        $theme = file_get_contents(Config::read('lmbd.modules.dir').'/theme_'.$this->theme.'/'.$this->theme_file);
        $theme = str_replace('{{lmbd.modules.dir}}', Config::read('lmbd.modules.dir'), $theme);
        foreach($this->display_array as $area=>$content) {
          if(strpos($theme, '<!-- lmbd:'.$area.' -->') === false) {
            Logger::write('NOTICE','Area '.$area.' has been not defined in theme '.$this->theme.'.');
          }
          else {
            $theme = str_replace('<!-- lmbd:'.$area.' -->', $content, $theme);
          }
        }
        $theme = str_replace('<!-- lmbd:title -->', '', $theme);
        echo $theme;
      }
      else {
        Logger::write('ERROR','Theme file ('.$this->theme_file.') for theme '.$this->theme.' does not exist.');
      }
      $this->abortView();
    }
  }
  public function __destruct() {
    $this->startView();
  }
}

?>