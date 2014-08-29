<?php

class User {
  private $model;
  private $rawdata;
  
  public function __construct($model,$data) {
    $this->model = $model;
    $this->rawdata = $data;
    $this->setUser($data);
  }
  public function setUser($source) {
    if(is_array($source) && !empty($source)) {
      foreach($source as $key => $val) {
        $this->rawdata[$key] = $val;
        $this->$key = $val;
      }
      return true;
    }
    return false;
  }
  private function setUserPrivledge($key,$value) {
    $this->privledges[$key] = $value;
    $this->rawdata['privledges'][$key] = $value;
  }
  public function setUserGroup($value) {
    $this->groups[] = $value;
    $this->rawdata['groups'][] = $value;
  }
  public function addNewUser($user_array=null,$auto_apply=true) {
    if($user_array == null) {
      $user_array = $this->rawdata;
      if(isset($user_array['groups'])) {
        unset($user_array['groups']);
      }
      if(isset($user_array['privledges'])) {
        unset($user_array['privledges']);
      }
    }
    $id_field = Config::read('auth.id.field');
    $this->model->dbh->startTransaction();
    $this->model->dbh->setTable(Config::read('auth.users.table'));
    $user_id = $this->model->dbh->insertData($user_array);
    $this->$id_field = $user_id;
    $this->rawdata[$id_field] = $user_id;
    if((is_numeric($this->$id_field)) && (isset($this->groups))) {
      $this->model->dbh->setTable(Config::read('auth.users.table').'_'.Config::read('auth.groups.table'));
      foreach($this->groups as $group) {
        $this->model->dbh->insertData(array(Config::read('auth.user.field') => $this->$id_field, Config::read('auth.group.field') => $group));
      }
      if($auto_apply) {
        $this->model->dbh->applyTransaction();
      }
      return true;
    }
    return false;
  }
  public function checkIfActive() {
    if(isset($this->privledges)) {
      if(in_array('NOVERIFY',$this->privledges)) {
        $this->active = Config::read('auth.active.y.field');
      }
      else {
        $this->active = Config::read('auth.active.n.field');
      }
    }
    else {
      $this->active = Config::read('auth.active.n.field');
    }
  }
  public function getUserPrivledges() {
    $id_field = Config::read('auth.id.field');
    if(isset($this->privledges)) {
      return true;
    }
    else if(isset($this->groups)) {
      $this->model->dbh->setTable(Config::read('auth.groups.table').'_'.Config::read('auth.privledges.table'));
      $privledges = array();
      
      foreach($this->groups as $group) {
        $privledges = $this->model->dbh->selectData(array(Config::read('auth.group.field') => $group));
        if(!empty($privledges)) {
          foreach($privledges as $privledge) {
            $privledges_id[$privledge[Config::read('auth.privledge.field')]] = $privledge[Config::read('auth.privledge.field')];
          }
        }
      }
      if(!empty($privledges_id)) {
        $this->model->dbh->setTable(Config::read('auth.privledges.table'));
        foreach($privledges_id as $privledge_id) {
          $privledges_data = $this->model->dbh->selectData(array(Config::read('auth.id.field') => $privledge_id),null,true);
          $this->setUserPrivledge($privledges_data[$id_field],$privledges_data[Config::read('auth.privcode.field')]);
        }
        return true;
      }
    }
    else if(isset($this->$id_field)) {
      $this->model->dbh->setTable(Config::read('auth.users.table').'_'.Config::read('auth.groups.table'));
      $groups = $this->model->dbh->selectData(array(Config::read('auth.user.field') => $this->$id_field));
      if(!empty($groups)) {
        foreach($groups as $group) {
          $this->setUserGroup($group[Config::read('auth.group.field')]);
        }
        if($this->getUserPrivledges()) {
          return true;
        }
      }
    }
    else if(isset($this->authcode)) {
      $this->model->dbh->setTable(Config::read('auth.authcodes.table'));
      $authcode = $this->model->dbh->selectData(array(Config::read('auth.authcode.field') => $this->authcode),null,true);
      $this->model->dbh->setTable(Config::read('auth.authcodes.table').'_'.Config::read('auth.groups.table'));
      $groups = $this->model->dbh->selectData(array(Config::read('auth.authcode.field') => $authcode['id']));
      if(!empty($groups)) {
        foreach($groups as $group) {
          $this->setUserGroup($group[Config::read('auth.group.field')]);
        }
        if($this->getUserPrivledges()) {
          return true;
        }
      }
    }
    return false;
  }
  public function checkNoDuplicate() {
    #deprecated
    $this->model->dbh->setTable(Config::read('auth.users.table'));
    $login_field = Config::read('auth.login.field');
    if($this->model->dbh->selectCount(array($login_field => $this->$login_field)) == 0) {
      return true;
    }
    else {
      return false;
    }
  }
  public function createUserArray() {
    return $this->rawdata;
  }
  public function createUserArray_old() {
    $return_array = array();
    if(!empty($this->privledges) && !empty($this->groups)) {
      if(isset($this->id)) {
        $return_array['id'] = $this->id;
      }
      if(isset($this->imie)) {
        $return_array['imie'] = $this->imie;
      }
      else {
        $return_array['imie'] = '';
      }
      if(isset($this->nazwisko)) {
        $return_array['nazwisko'] = $this->nazwisko;
      }
      else {
        $return_array['nazwisko'] = '';
      }
      if(isset($this->dataurodzenia)) {
        $return_array['dataurodzenia'] = $this->dataurodzenia;
      }
      else {
        $return_array['dataurodzenia'] = '';
      }
      if(isset($this->email)) {
        $return_array['email'] = $this->email;
      }
      else {
        $return_array['email'] = '';
      }
      if(isset($this->telefon)) {
        $return_array['telefon'] = $this->telefon;
      }
      else {
        $return_array['telefon'] = '';
      }
      if(isset($this->ulica)) {
        $return_array['ulica'] = $this->ulica;
      }
      else {
        $return_array['ulica'] = '';
      }
      if(isset($this->nrdomu)) {
        $return_array['nrdomu'] = $this->nrdomu;
      }
      else {
        $return_array['nrdomu'] = '';
      }
      if(isset($this->kodpocztowy)) {
        $return_array['kodpocztowy'] = $this->kodpocztowy;
      }
      else {
        $return_array['kodpocztowy'] = '';
      }
      if(isset($this->miejscowosc)) {
        $return_array['miejscowosc'] = $this->miejscowosc;
      }
      else {
        $return_array['miejscowosc'] = '';
      }
      if(isset($this->privledges)) {
        $return_array['privledges'] = $this->privledges;
      }
      else {
        $return_array['privledges'] = '';
      }
      if(isset($this->groups)) {
        $return_array['groups'] = implode(", ", $this->groups);
      }
      else {
        $return_array['groups'] = '';
      }
      
      return $return_array;
    }
  }
  public function lastLogin() {
    $id_field = Config::read('auth.id.field');
    $this->model->dbh->setTable(Config::read('auth.users.table'));
    $user = $this->model->dbh->selectData(array($id_field => $this->$id_field),null,true);
    if(empty($user[Config::read('auth.last.login.field')])) {
      return false;
    }
    else {
      $this->model->dbh->updateData(array(Config::read('auth.last.login.field')=>time()),array($id_field=>$this->$id_field));
      return true;
    }
  }
  public function createUserSession_old() {
    if(!empty($this->privledges) && !empty($this->groups)) {
      if(!$this->lastLogin()) {
        $_SESSION['FIRST_LOGIN'] = 1;
      }
      $_SESSION['ID'] = $this->id;
      $_SESSION['IMIE'] = $this->imie;
      $_SESSION['NAZWISKO'] = $this->nazwisko;
      $_SESSION['EMAIL'] = $this->email;
      $_SESSION['privledges'] = serialize($this->privledges);
      $_SESSION['groups'] = serialize($this->groups);
      return true;
    }
    else {
      return false;
    }
  }
  public function updateProfile() {
    $this->model->dbh->setTable(Config::read('auth.users.table'));
    $id_field = Config::read('auth.id.field');
    foreach($this->rawdata as $key => $value) {
      if(!is_array($value)) {
        $this->model->dbh->updateData(array($key=>$value),array($id_field=>$this->$id_field));
      }
    }
  }
}

?>