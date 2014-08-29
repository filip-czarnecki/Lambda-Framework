<?php

class Auth implements iConstruct {
  public $is_logged;
  public $has_access;
  public $first_login;
  public $failed_login;
  public $user;
  
  public function init() {
    require_once(Config::read('lmbd.modules.dir').'/auth/config.php');
    require_once(Config::read('lmbd.modules.dir').'/auth/User.php');
    $this->is_logged = false;
    $this->has_access = false;
    $this->failed_login = false;
    $this->user = null;
    
    $this->check_first_login();
    
    if(isset($_SESSION['lmbd_auth'])) {
      $data = unserialize($_SESSION['lmbd_auth']);
      $this->user = new User($this->model,$data);
      $this->is_logged = true;
    }
    else {
      $this->autologin();
    }
    if($this->is_logged && $this->check_access()) {
      $this->has_access = true;
    }
  }
  public function createUser($data) {
    $this->user = new User($this->model,$data);
  }
  public function check_access() {
    $page = $this->get_page();
    if($this->check_page($page)) {
      return true;
    }
    return false;
  }
  private function check_page($page) {
    $page_id = $this->get_page_id($page);
    if($page_id == null) {
      return true;
    }
    else if($this->page_privledges($page_id)) {
      return true;
    }
    return false;
  }
  private function get_page_id($page_name) {
    $this->model->dbh->setTable(Config::read('auth.pages.table'));
    $page = $this->model->dbh->selectData(array(Config::read('auth.page.field') => $page_name),array(Config::read('auth.id.field')),true);
    if(!empty($page)) {
      return $page[Config::read('auth.id.field')];
    }
    else {
      return null;
    } 
  }
  private function page_privledges($page_id) {
    $this->model->dbh->setTable(Config::read('auth.pages.table').'_'.Config::read('auth.privledges.table'));
    $data = $this->model->dbh->selectData(array(Config::read('auth.page.field') => $page_id));
    if(empty($data)) {
      return true;
    }
    foreach($data as $connection) {
      if(isset($this->user->privledges[$connection[Config::read('auth.privledge.field')]])) {
        return true;
      }
    }
    return false;
  }
  private function get_page() {
    if(isset($this->router)) {
      return $this->router->getcurrent();
    }
    else if(isset($_GET['p'])) {
      return $_GET['p'];
    }
    else {
      return null;
    }
  }
  private function autologin() {
    if(isset($_POST['lmbd_login']) && isset($_POST['lmbd_pass'])) {
      if($this->check_login($_POST['lmbd_login'])) {
        if($this->authorize($_POST['lmbd_login'],$_POST['lmbd_pass'])) {
          return true;
        }
      }
      $this->failed_login = true;
    }
    return false;
  }
  private function check_login($login) {
    if(!empty($login)) {
      $min_length = Config::read('auth.login.minlength');
      $max_length = Config::read('auth.login.maxlength');
      if($min_length == null) {
        $min_length = 3;
      }
      if($max_length == null) {
        $max_length = 16;
      }
      if(strlen($login) >= $min_length && strlen($login) <= $max_length) {
        if(Config::read('auth.login.field') == 'email') {
          if(filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return true;
          }
        }
        else {
          $rule = Config::read('auth.login.allowed');
          if($rule == null) {
            return true;
          }
          else if(preg_match($rule, $login) == false) {
            return true;
          }
        }
      }
    }
    return false;
  }
  public function setsession($data=null) {
    if(isset($this->user)) {
      if($data == null) {
        $data = $this->user->createUserArray();
      }
      if(!$this->user->lastLogin()) {
        $_SESSION['FIRST_LOGIN'] = 1;
      }
    }
    $_SESSION['lmbd_auth'] = serialize($data);
  }
  private function authorize($login,$pass) {
    $this->model->dbh->setTable(Config::read('auth.users.table'));
    $data = $this->model->dbh->selectData(array(Config::read('auth.login.field') => $login),null,true);
    if(!empty($data)) {
      if($data[Config::read('auth.active.field')] == Config::read('auth.active.y.field')) {
        $salt_field = Config::read('auth.salt.field');
        $pass_field = Config::read('auth.pass.field');
        $pass_hashed = $this->hash($pass,$data[$salt_field]);
        if($pass_hashed == $data[$pass_field]) {
          $this->user = new User($this->model,$data);
          $this->user->getUserPrivledges();
          $this->setsession($this->user->createUserArray());
          $this->is_logged = true;
          return true;
        }
      }
    }
    return false;
  }
  public function check_first_login() {
    if(isset($_SESSION['FIRST_LOGIN'])) {
      $this->first_login = true;
      return true;
    }
    else {
      if(isset($this->first_login)) {
        unset($this->first_login);
      }
      return false;
    }
  }
  public function unsetFirstLogin() {
    if(isset($this->user)) {
      $id_field = Config::read('auth.id.field');
      if(isset($_SESSION['FIRST_LOGIN'])) {
        unset($_SESSION['FIRST_LOGIN']);
      }
      $this->model->dbh->updateData(array(Config::read('auth.last.login.field')=>time()),array($id_field=>$this->user->$id_field));
    }
  }
  public function updatePassword($password) {
    if(isset($this->user)) {
      $id_field = Config::read('auth.id.field');
      $salt_field = Config::read('auth.salt.field');
      $pass_field = Config::read('auth.pass.field');
      $this->model->dbh->setTable(Config::read('auth.users.table'));
      $salt = uniqid(mt_rand(), true);
      $hash = $this->hash($password, $salt);
      $this->model->dbh->updateData(array($pass_field=>$hash,$salt_field=>$salt),array($id_field=>$this->user->$id_field));
    }
  }
  public function checkUserExists($login) {
    $this->model->dbh->setTable(Config::read('auth.users.table'));
    $existing = $this->model->dbh->selectData(array(Config::read('auth.login.field') => $login),array(Config::read('auth.id.field')),true);
    if(empty($existing)) {
      return false;
    }
    else {
      return true;
    }
  }
  public function hash ($p, $s, $c=1000, $kl=32, $a="sha256") {
    #pbkdf2
    $hl = strlen(hash($a, null, true));
    $kb = ceil($kl / $hl);
    $dk = '';
    for ( $block = 1; $block <= $kb; $block ++ ) {
      $ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);
      for ( $i = 1; $i < $c; $i ++ )
      $ib ^= ($b = hash_hmac($a, $b, $p, true));
      $dk .= $ib;
    }
    return base64_encode(substr($dk, 0, $kl));
  }
  public function get_group_users($group_id) {
    $users = array();
    if(is_numeric($group_id)) {
      $this->model->dbh->setTable(Config::read('auth.users.table').'_'.Config::read('auth.groups.table'));
      $users_id = $this->model->dbh->selectData(array(Config::read('auth.group.field') => $group_id));
      $this->model->dbh->setTable(Config::read('auth.users.table'));
      foreach($users_id as $user_id) {
        $user_id = $user_id[Config::read('auth.user.field')];
        $user = $this->model->dbh->selectData(array(Config::read('auth.id.field') => $user_id, Config::read('auth.active.field') => Config::read('auth.active.y.field')),null,true);
        if(!empty($user)) {
          $users[$user_id] = $user;
        }
      }
    }
    return $users;
  }
  public function is_same_user() {
    if (!isset($_SESSION['CREATED'])) {
      $_SESSION['CREATED'] = time();
    }
    else if (time() - $_SESSION['CREATED'] > Config::read('auth.session.expire')) {
      session_regenerate_id(true);
      $_SESSION['CREATED'] = time();
    }
    if(isset($_SESSION['HTTP_USER_AGENT'])) {
      if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {
        return false;
      }
    }
    else {
      $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
    }
    return true;
  }
  public function logout() {
    if(isset($_SESSION['lmbd_auth'])) {
      unset($_SESSION['lmbd_auth']);
    }
    if(isset($_SESSION['FIRST_LOGIN'])) {
      unset($_SESSION['FIRST_LOGIN']);
    }
  }
  public function check_page_old() {
    $this->model->dbh->setTable("strony");
    $dane = $this->model->dbh->selectData(array("nazwa" => CURRENT_PAGE));
    if(isset($dane[0]['nazwa'])) {
      return $dane[0]['id'];
    }
    else {
      return null;
    }
  }
  public function check_access_old() {
    if(defined('CURRENT_PAGE')) {
      if(CURRENT_PAGE != null) {
        if(Config::read('auth.is.logged') == 1) {
          $pid = $this->check_page();  
          if(is_numeric($pid)) {
            if(!$this->page_privledges($pid, unserialize($_SESSION['UPRAWNIENIA']))) {
              return false;
            }
          }
        }
        else {
          return false;
        }
      }
    }
    return true;
  }
}

?>