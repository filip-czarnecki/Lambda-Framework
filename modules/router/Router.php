<?php

class Router implements iConstruct {
  private $parameters = array();
  private $previous = array();
  private $current = null;
  private $id = null;
  private $parameter = null;
  private $parents = array();
  
  public function init() {
    if(Config::read('router.use.auth') == 1 && Config::read('auth.pages.table') == null) {
      require_once(Config::read('lmbd.modules.dir').'/auth/config.php');
    }
    if(isset($_GET['id'])) {
      $this->id = $_GET['id'];
    }
    $this->setparameter();
    if(isset($_GET['m'])) {
      $this->module = $_GET['m'];
    }
    if(isset($_GET['p'])) {
      $this->parameters = explode(",", $_GET['p']);
      $this->previous = $this->parameters;
      foreach ($this->previous as &$parameter) {
        $parameter = $this->decode_page($parameter);
      }
      $this->setcurrent($this->decode_page(array_pop($this->previous)));
      $this->setparameters();
    }
    else {
      $this->setcurrent(Config::read('router.default.page'),true);
    }
    if(Config::read('router.use.auth') == 1) {
      $this->setpage();
    }
    else {
      $this->setpagename($this->current);
    }
  }
  public function setcurrent($page,$default=false) {
    if(!empty($page)) {
      if($default) {
        $this->parameters = array($this->encode_page($page));
        $this->previous = array();
      }
      $this->current = $this->checkcurrent($page);
    }
  }
  private function setparameters() {
    $parameters = array();
    if(Config::read('router.use.auth') == 1) {
      $current = $this->getpagename($this->current);
    }
    else {
      $current = $this->current;
    }
    $current_key = array_search($current,$this->parameters);
    if(is_numeric($current_key) && $this->parameter != null) {
      if(is_array($this->parameter)) {
        foreach($this->parameter as &$id) {
          $check = $this->checkparametertmp($id);
          if($check == null) {
            $parameters[] = $id;
          }
          else {
            $id = $check;
          }
        }
        if(!empty($parameters)) {
          $parameter = implode("@", $parameters);
        }
      }
      else {
        $check = $this->checkparametertmp($this->parameter);
        if($check == null) {
          $parameter = $this->parameter;
        }
        else {
          $this->parameter = $check;
        }
      }
      if(isset($parameter)) {
        $this->parameters[$current_key] = $current.$this->encode(' (id '.$parameter.')');
      }
    }
  }
  private function checkparametertmp($parameter) {
    $tmp = strstr($parameter,'tmp_');
    if($tmp == false) {
      return null;
    }
    else {
      return substr($parameter, 4);
    }
  }
  private function checkcurrent($current) {
    $id = strstr($current,'(id ');
    if($id != false) {
      $current = rtrim(strstr($current,'(id ',true));
      if(Config::read('router.use.auth') == 1) {
        $current = $this->decode_page($current);
      }
      $this->id = substr(substr($id, 4), 0, -1);
      $this->setparameter();
    }
    return $current;
  }
  private function setparameter() {
    if($this->id != null) {
      $parameter = explode("@", $this->id);
      if(count($parameter) == 1) {
        $this->parameter = array_shift($parameter);
      }
      else {
        $this->parameter = $parameter;
      }
    }
  }
  private function setpage() {
    $this->pagename = $this->getpagename($this->current);
  }
  public function setpagename($pagename) {
    $this->pagename = $pagename;
  }
  public function getcurrent() {
    return $this->current;
  }
  public function getpagename($page_page=null) {
    if(Config::read('router.use.auth') == 1) {
      if($page_page == null) {
        return $this->pagename;
      }
      else {
        $this->model->dbh->setTable(Config::read('auth.pages.table'));
        $page = $this->model->dbh->selectData(array(Config::read('auth.page.field') => $page_page),array(Config::read('auth.name.field')),true);
        if(!empty($page)) {
          return $page[Config::read('auth.name.field')];
        }
      }
    }
    else {
      return $this->pagename;
    }
  }
  public function getparameter($key=null) {
    if($key != null) {
      if(isset($this->parameter) && is_array($this->parameter)) {
        if(isset($this->parameter[$key])) {
          return $this->parameter[$key];
        }
      }
      return null;
    }
    else if(isset($this->parameter)) {
      return $this->parameter;
    }
    else {
      return null;
    }
  }
  private function checkparameter($page) {
    $id = strstr($page,'(id ');
    if($id != false) {
      $p1 = rtrim(strstr($page,'(id ',true));
      $p2 = substr(substr($id, 4), 0, -1);
      $parameter = $p1.$this->encode(' (id '.$p2.')');
      return array('address'=>$p1,'parameter'=>$parameter);
    }
    return null;
  }
  public function previouslinks($previous = null) {
    $links = array();
    if($previous == null) {
      $previous = $this->previous;
    }
    if(!empty($previous)) {
      foreach ($previous as $key => $parameter) {
        if(Config::read('router.use.auth') == 1) {
          $check = $this->checkparameter($parameter);
          if($check == null) {
            $address = $this->getpagename($parameter);
          }
          else {
            $address = $check['address'];
            $parameter = $check['parameter'];
          }
        }
        else {
          $address = $parameter;
        }
        $links[$this->linkup($parameter)] = $address;
      }
    }
    return $links;
  }
  public function checkparents($parent_id) {    
    if($parent_id != 0) {
      $this->model->dbh->setTable(Config::read('auth.pages.table'));
      $parent = $this->model->dbh->selectData(array(Config::read('auth.id.field') => $parent_id),array(Config::read('auth.page.field'),Config::read('auth.parent.field')),true);
      if(!empty($parent)) {
        $this->parents[] = $parent[Config::read('auth.page.field')];
        $this->checkparents($parent[Config::read('auth.parent.field')]);
      }
    }
  }
  public function link($destination=null,$id='') {
    if($destination == null) {
      return $this->linkdown($destination,$id);
    }
    else {
      $previous = array();
      return $this->generate_link($destination,$previous,$id);
    }
  }
  public function linkup($destination=null,$id='') {
    if(!empty($this->previous)) {
      $tmparray = $this->previous;
      foreach ($tmparray as &$parameter) {
        if(Config::read('router.use.auth') == 1) {
          $check = $this->checkparameter($parameter);
          if($check == null) {
            $parameter = $this->decode_page($parameter);
          }
          else {
            $parameter = $check['parameter'];
          }
        }
        else {
          $parameter = $this->decode_page($parameter);
        }
      }
      $count = count($tmparray);
      if($destination == null) {
        $destination = $tmparray[$count-1];
      }
      else if(is_numeric($destination)) {
        $destination = $tmparray[$count-$destination];
      }
      $destination_key = array_search($destination,$tmparray);
      $number = $count - $destination_key;
      for ($i = 1; $i <= $number; $i++) {
        array_pop($tmparray);
      }
      return $this->generate_link($destination,implode(",", $tmparray),$id);
    }
    else {
      return $this->generate_link($destination,array(),$id);
    }
  }
  public function linkdown($destination=null,$id='') {
    if($destination == null) {
      return $this->generate_link($this->current,implode(",", $this->previous),$id);
    }
    if(!empty($this->parameters)) {
      return $this->generate_link($destination,implode(",", $this->parameters),$id);
    }
    else {
      return $this->generate_link($destination,array(),$id);
    }
  }
  public function decode_page($string) {
    if(Config::read('router.use.auth') == 1) {
      $this->model->dbh->setTable(Config::read('auth.pages.table'));
      $page = $this->model->dbh->selectData(array(Config::read('auth.address.field') => $string),array(Config::read('auth.page.field')),true);
      if(!empty($page)) {
        return $page[Config::read('auth.page.field')];
      }
      else {
        return $string;
      }
    }
    else {
      return $this->decode($string);
    }
  }
  public function decode($string) {
    return urldecode($string);
  }
  public function encode($string) {
    return urlencode($string);
  }
  public function encode_page($string) {
    if(Config::read('router.use.auth') == 1) {
      $this->model->dbh->setTable(Config::read('auth.pages.table'));
      $page = $this->model->dbh->selectData(array(Config::read('auth.page.field') => $string),array(Config::read('auth.address.field')),true);
      if(!empty($page)) {
        return $page[Config::read('auth.address.field')];
      }
      else {
        return $string;
      }
    }
    else {
      return $this->encode($string);
    }
  }
  public function generate_link($destination,$previous,$id='') {
    $index = '';
    $html = '.html';
    if(is_array($id)) {
      $id = implode("@", $id);
    }
    if(Config::read('router.url.rewrite') == 0) {
      $html = '';
      if(isset($this->module)) {
        $index = 'index.php?m='.$this->module.'&p=';
      }
      else {
        $index = 'index.php?p=';
      }
      if(!empty($id)) {
        $id = '&id='.$id;
      }
    }
    else {
      if(isset($this->module)) {
        $index = 'mod-'.$this->module.'-';
      }
      if(!empty($id)) {
        $id = '-id'.$id;
      }
    }
    
    
    if(empty($previous)) {
      $link = $index.$this->encode_page($destination).$id.$html;
    }
    else {
      $link = $index.$previous.','.$this->encode_page($destination).$id.$html;
    }
    return $link;
  }
  public function get_unique_id() {
    $current_module = Config::read('lmbd.appcontroller');
    $current_page_with_id = end($this->parameters);
    $post = md5(serialize($_POST));
    $sess = md5(serialize($_SESSION));
    return base64_encode($current_module.'_'.$current_page_with_id.'_'.$post.'_'.$sess);
  }
}

?>