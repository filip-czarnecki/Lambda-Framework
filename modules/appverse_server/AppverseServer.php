<?php

class AppverseServer implements iConstruct {
  private $version = '0.46';
  
  public function init() {
    $page = $this->router->getcurrent();
    $this->template->abort();

    switch($page) {
      case 'start':
        $this->start();
        break;
      case 'welcome':
        $this->welcome();
        break;
      case 'modules_available':
        $this->modules_available();
        break;
      case 'modules_dir':
        $this->modules_dir();
        break;
      case 'file_list':
        $this->file_list();
        break;
      case 'get_config':
        $this->get_config();
        break;
      case 'get_file':
        $this->get_file();
        break;
    }
  }
  private function start() {
    $this->template->response = $this->appverseservermodel->get_response('start');
    $this->template->view('response');
  }
  private function welcome() {
    $this->template->response = $this->appverseservermodel->get_response('welcome');
    $this->template->view('response');
  }
  private function modules_available() {
    $this->template->response = $this->appverseservermodel->get_response('modules_available');
    $this->template->view('response');
  }
  private function modules_dir() {
    $this->template->response = $this->appverseservermodel->get_response('modules_dir');
    $this->template->view('response');
  }
  private function file_list() {
    $this->template->response = base64_encode(serialize($this->appverseservermodel->get_file_list($this->template->getparameter())));
    $this->template->view('response');
  }
  private function get_config() {
    $this->template->response = $this->appverseservermodel->get_config($this->template->getparameter('0'),$this->template->getparameter('1'));
    $this->template->view('response');
  }
  private function get_file() {
    $this->template->response = $this->appverseservermodel->get_file($this->template->getparameter('0'),$this->template->getparameter('1'));
    $this->template->view('response');
  }
}

?>