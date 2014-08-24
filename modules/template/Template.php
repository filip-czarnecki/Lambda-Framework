<?php

class Template {
  private $templatedir;
  private $preview;
  private $abort;
  
  public function cache($update_period=null) {
    if(isset($this->cache) && isset($this->event)) {
      $this->event->attach($this->cache);
      $cache = $this->cache->cacheView($this->router->get_unique_id(),$update_period);
      if($cache != null) {
        $this->view->setDisplay($cache[0]);
        $this->view->startView();
        $this->event->notify('template_startViewCache');
      }
    }
  }
  public function getCurrent() {
    return $this->router->getcurrent();
  }
  public function getParameter($key=null) {
    return $this->router->getparameter($key);
  }
  public function setTemplateDir($dir=null) {
    if($dir != null) {
      $this->templatedir = $dir;
    }
    else {
      $this->templatedir = Config::read('module.templates.dir');
    }
  }
  public function setTheme($theme) {
    $this->view->setTheme($theme);
  }
  public function getTheme() {
    return $this->view->getTheme();
  }
  public function getTemplateDir() {
    return $this->templatedir;
  }
  public function view($template=null) {
    if($template == null && isset($this->preview)) {
      if(isset($this->abort)) {
        unset($this->abort);
        unset($this->preview);
      }
      else {
        $this->view->renderPreview();
        unset($this->preview);
      }
    }
    else if($template == null && isset($this->render)) {
      if(isset($this->abort)) {
        unset($this->abort);
        unset($this->render);
      }
      else {
        $render = $this->render;
        unset($this->render);
        $this->view($render);
      }
    }
    else if(!empty($template)) {
      if(!isset($this->templatedir)) {
        $this->setTemplateDir();
      }
      if(file_exists(Config::read('lmbd.modules.dir').'/'.$this->templatedir.'/'.$template.'.php')) {
        include(Config::read('lmbd.modules.dir').'/'.$this->templatedir.'/'.$template.'.php');
      }
      else {
        Logger::write('NOTICE','Template file '.$this->templatedir.'/'.$template.'.php does not exist.');
      }
    }
  }
  public function preview($template) {
    $this->preview = $template;
    $this->view->startPreview();
    $this->view($template);
    $this->view->endPreview();
  }
  public function prerender($template) {
    $this->render = $template;
    $this->view->startVoid();
    $this->view($template);
    $this->view->endVoid();
  }
  public function abort() {
    $this->view->abortView();
    $this->abort = true;
  }
  private function setresult($result) {
    $this->result = $result;
  }
  public function getresult() {
    if(isset($this->result)) {
      return $this->result;
    }
    return null;
  }
  /*
  public function abortview() {
    $this->view->abortView();
  }
  */
}

?>