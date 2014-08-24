<?php

class Appverse implements iConstruct {
  public function init() {  
    $page = $this->template->getCurrent();
    $secret = Config::read('appverse.secret');
    #$this->template->cache();
    if(isset($_SESSION['appverse'])) {
      switch($page) {
        case 'Main page':
          $this->main();
          break;
        case 'Appcreator':
          $this->appcreator();
          break;
        case 'Module':
          $this->module();
          break;
        case 'Repository':
          $this->respository();
          break;
        case 'Information':
          $this->information();
          break;
        case 'Explore':
          $this->explore();
          break;
        case 'Install':
          $this->install();
          break;
        case 'Uninstall':
          $this->uninstall();
          break;
        default:
          $this->template->errid = 2;
          $this->template->view('error');
      }
    }
    else if(!empty($secret)) {
      $this->template->prerender('start');
      $check = $this->appversemodel->checksecret($this->template->getresult(),$secret);
      if($check == 1) {
        $this->main();
      }
      else {
        if($check == 2) {
          $this->template->wrongsecret = true;
        }
        $this->template->view();
      }
    }
    else {
      $this->template->view('welcome');
    }
  }
  public function get_modules($modules) {
    #print_r($modules);
  }
  private function main() {
    $parameter = $this->template->getparameter();
    $this->appversemodel->createsession();
    if($parameter == 'disconnect') {
      $this->template->address = $this->appversemodel->getsession('repository');
      $this->appversemodel->disconnect_repository($this->template->address);
    }
    $this->template->modules = $this->appversemodel->getmodules($parameter);
    $this->template->view('modules');
  }
  private function appcreator() {
    $this->template->themes = $this->appversemodel->getthemes();
    $this->template->drivers = $this->appversemodel->getdrivers();
    $this->template->prerender('appcreator');
    $this->template->message = $this->appversemodel->createmodule($this->template->getresult());
    $this->template->view();
  }
  private function module() {
    $this->template->module = $this->appversemodel->getmodule($this->template->getparameter('0'),$this->template->getparameter('1'));
    $this->template->view('module');
  }
  private function respository() {
    if(isset($_SESSION['repository'])) {
      $this->template->address = $this->appversemodel->getsession('repository');
    }
    else {
      $this->template->firstconnect = true;
      $this->template->prerender('connect');
      $this->template->address = $this->appversemodel->check_address($this->template->getresult());
      $this->template->message =  $this->appversemodel->get_welcome_message($this->template->address);
    }
    $check = $this->appversemodel->check_repository($this->template->address);
    if($check == 1) {
      $this->template->modules_available = $this->appversemodel->get_repository($this->template->address,$this->template->getparameter());
      $this->appversemodel->createsession('repository',$this->template->address);
      $this->template->view('repository');
    }
    else {
      if($check == 3) {
        $this->template->invalidrepo = true;
      }
      $this->template->view('connect');
    }
  }
  private function information() {
    $this->template->address = $this->appversemodel->getsession('repository');
    $this->template->content = $this->appversemodel->getinformation($this->template->address);
    $this->template->view('information');
  }
  private function explore() {
    $this->template->module = $this->appversemodel->getmodule_ext($this->appversemodel->getsession('repository'),$this->template->getparameter('0'),$this->template->getparameter('1'));
    $this->template->view('explore');
  }
  private function install() {
    $this->appversemodel->setmodule_ext($this->appversemodel->getsession('repository'),$this->template->getparameter('0'),$this->template->getparameter('1'));
    $this->appversemodel->resolveAll();
    $template_dir = $this->appversemodel->gettemplatedir();
    $this->template->setTemplateDir($template_dir);
    $template = $this->appversemodel->gettemplate('install');
    $this->template->content = $this->appversemodel->getInstallContent();
    $this->template->view($template);
    $this->template->message = $this->appversemodel->install($this->template->getresult(),$this->template->content);
    $this->template->view('install_message');

    #$this->appversemodel->getfilelist();
  }
  private function uninstall() {
    $this->appversemodel->setmodule($this->template->getparameter('0'),$this->template->getparameter('1'));
    #$this->appversemodel->resolve();
    $template_dir = $this->appversemodel->gettemplatedir();
    $this->template->setTemplateDir($template_dir);
    $template = $this->appversemodel->gettemplate('uninstall');
    $this->template->view($template);
    $this->template->message = $this->appversemodel->uninstall($this->template->getresult());
    $this->template->view('uninstall_message');
  }
}













?>