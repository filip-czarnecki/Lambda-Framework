<?php

class Paliouser {
  public function __construct($cert_name,$model) {
    $this->user_exists = false;
    $this->user_sync_failure = false;
    $this->user_active = false;
    $this->user_multiple = false;
    $this->user_direct = false;
    $this->cert_name = $cert_name;
    $this->model = $model;
    if(Config::read('paliouser.auto.check') == 1) {
      $this->getuserdata();
    }
  }
  private function getuserdata() {
    if(isset($_SESSION['paliouser_id'])) {
      $this->getfromsession();
      return true;
    }
    else {
      if($this->getfromdb()) {
        return true;
      }
    }
    return false;
  }
  private function getfromsession() {
    $this->user_exists = true;
    $this->user_active = true;
    $this->user_direct = unserialize($_SESSION['paliouser_direct']);
    $this->id = $_SESSION['paliouser_id'];
    $this->imie = $_SESSION['paliouser_imie'];
    $this->nazwisko = $_SESSION['paliouser_nazwisko'];
    $this->email = $_SESSION['paliouser_email'];
    $this->privledges = unserialize($_SESSION['paliouser_privledges']);
  }
  private function getfromdb() {
    $this->model->dbh->setTable("UZYTKOWNICY");
    $users = $this->model->dbh->selectData(array('CERT_CN' => $this->cert_name),array('P_USER_ID','IMIE','NAZWISKO','EMAIL','SYNC'));
    if(!empty($users)) {
      $this->user_exists = true;
      if(count($users) > 1) {
        $this->user_multiple = true;
      }
      foreach($users as $user) {
        if(Config::read('paliouser.allow.direct.login') == 1) {
          $this->user_direct = true;
          if($this->setusersession($user)) {
            return true;
          }
        }
        else if($this->sync($user)) {
          if($this->setusersession($user)) {
            return true;
          }
        }
      }

    }
    return false;
  }
  private function check_active($user_id) {
    $this->model->dbh->setTable("P_USERS");
    $user = $this->model->dbh->selectData(array('ID' => $user_id),array('STATUS','EXPIRE_DATE'),true);
    if($user['STATUS'] == 'N') {
      $this->user_active = true;
      return true;
    }
    return false;
  }
  private function setusersession($user_data) {
    if(!empty($user_data)) {
      if(Config::read('paliouser.check.active') == 1) {
        if(!$this->check_active($user_data['P_USER_ID'])) {
          return false;
        }
      }
      $this->id = $user_data['P_USER_ID'];
      $this->imie = $user_data['IMIE'];
      $this->nazwisko = $user_data['NAZWISKO'];
      $this->email = $user_data['EMAIL'];
      $this->privledges = $this->getprivledges();
      $_SESSION['paliouser_direct'] = serialize($this->user_direct);
      $_SESSION['paliouser_id'] = $user_data['P_USER_ID'];
      $_SESSION['paliouser_imie'] = $user_data['IMIE'];
      $_SESSION['paliouser_nazwisko'] = $user_data['NAZWISKO'];
      $_SESSION['paliouser_email'] = $user_data['EMAIL'];
      $_SESSION['paliouser_privledges'] = serialize($this->privledges);
      return true;
    }
    else {
      return false;
    }
  }
  private function sync($sync) {
    if(!empty($sync)) {
      $sync_time = substr($sync['SYNC'], 0, -4);
      $allowable_time = strtotime("-1 minute");
      if($sync_time > $allowable_time) {
        $this->id = $sync['P_USER_ID'];
        $this->imie = $sync['IMIE'];
        $this->nazwisko = $sync['NAZWISKO'];
        $this->email = $sync['EMAIL'];
        $this->user_sync_failure = false;
        return true;
      }
    }
    $this->user_sync_failure = true;
    return false;
  }
  public function getusermenu() {
    if(!$this->user_direct) {
    $this->model->dbh->setTable("UZYTKOWNICY");
    $menu = $this->model->dbh->selectData(array('P_USER_ID' => $this->id),array("CACHE"),true);
      return $menu['CACHE'];
    }
    else {
      return '';
    }
  }
  public function getprivledges() {
    $privledges = array();
    $this->model->dbh->setTable("P_USERS_ROLES_REGIONS");
    $roles = $this->model->dbh->selectData(array('P_USER_ID' => $this->id),array("P_ROLE_ID"));
    foreach($roles as $role) {
      $this->model->dbh->setTable("P_PRIVS_ROLES");
      $privs_roles = $this->model->dbh->selectData(array('P_ROLE_ID' => $role['P_ROLE_ID']),array("P_PRIV_ID"));
      foreach($privs_roles as $priv_role) {
        $this->model->dbh->setTable("P_PRIVS");
        $privledge = $this->model->dbh->selectData(array('ID' => $priv_role['P_PRIV_ID']),array("ID","NAME"),true);
        if(!empty($privledge)) {
          $privledges[$privledge['ID']] = $privledge['NAME'];
        }
      }
    }
    return $privledges;
  }
}

?>