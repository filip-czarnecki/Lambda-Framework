<?php

class Authtoken {
  public function __construct() {
    $this->tokens_table = Config::read('authtoken.tokens.table');
    $this->users_table = Config::read('authtoken.users.table');
    $this->token_field = Config::read('authtoken.token.field');
    $this->tokenid_field = Config::read('authtoken.tokenid.field');
    $this->userid_field = Config::read('authtoken.userid.field');
    $this->email_field = Config::read('authtoken.email.field');
    $this->expiration_field = Config::read('authtoken.expiration.field');
    $this->expiration_field_type = Config::read('authtoken.expiration.field.type');
    $this->attempts_field = Config::read('authtoken.attempts.field');
    $this->expiration_field_format = Config::read('authtoken.expiration.field.format');
    if($this->expiration_field_format == null) {
      $this->expiration_field_format = 'Y-m-d H:i:s';
    }
  }
  public function autoRemoveTokens() {
    $this->model->dbh->setTable($this->tokens_table);
    $tokens = $this->model->dbh->selectData(null,array($this->tokenid_field,$this->expiration_field));
    foreach($tokens as $token) {
      $expires = $token[$this->expiration_field];
      if($this->expiration_field_type == 'DATETIME') {
        $expires = strtotime($expires);
      }
      if($expires < time()) {
        $this->model->dbh->deleteData(array($this->tokenid_field => $token[$this->tokenid_field]));
      }
    }
  }
  public function startSession($userid=null) {
    if($userid == null) {
      $userid = $this->userid;
    }
    $_SESSION['authtoken'] = $userid;
  }
  public function getSession() {
    if(isset($this->userid)) {
      return $this->userid;
    }
    else if(isset($_SESSION['authtoken'])) {
      $this->userid = $_SESSION['authtoken'];
      return $this->userid;
    }
    else {
      return null;
    }
  }
  public function endSession() {
    if(isset($this->userid)) {
      unset($this->userid);
    }
    if(isset($_SESSION['authtoken'])) {
      unset($_SESSION['authtoken']);
    }
  }
  public function uniqueToken($auto_check=true,$minval=null,$maxval=null) {
    $token = $this->generateToken($minval,$maxval);
    if($auto_check) {
      while($this->checkTokenExists($token) == false) {
        $token = $this->generateToken($minval,$maxval);
      }
    }
    $this->token = $token;
    return $token;
  }
  public function checkTokenExists($token) {
    $this->model->dbh->setTable($this->tokens_table);
    $existing = $this->model->dbh->selectData(array($this->token_field => $token),null,true);
    if(empty($existing)) {
      return true;
    }
    else {
      return false;
    }
  }
  public function generateToken($minval=null,$maxval=null) {
    if($minval == null) {
      $minval = Config::read('authtoken.default.minval');
    }
    if($maxval == null) {
      $maxval = Config::read('authtoken.default.maxval');
    }
    return mt_rand($minval,$maxval);
  }
  public function saveToken($userid=null,$token=null,$expiration=null) {
    if($userid == null) {
      $userid = $this->userid;
    }
    if($token == null) {
      if(isset($this->token)) {
        $token = $this->getToken();
      }
      else {
        $token = $this->uniqueToken();
      }
    }
    if($expiration == null) {
      $expiration = Config::read('authtoken.default.expiration');
    }
    
    $expires = time() + $expiration;
    if($this->expiration_field_type == 'DATETIME') {
      $expires = date($this->expiration_field_format,$expires);
    }
    $this->model->dbh->setTable($this->tokens_table);
    $insert = $this->model->dbh->insertData(array($this->userid_field => $userid, $this->token_field => $token, $this->expiration_field => 'function_CONVERT(DateTime,\''.$expires.'\',120)'));
    if(is_numeric($insert)) {
      return true;
    }
    else {
      return false;
    }
  }
  public function getUserID($email) {
    $this->model->dbh->setTable($this->users_table);
    $user = $this->model->dbh->selectData(array($this->email_field => $email),array($this->userid_field),true);
    if(!empty($user)) {
      $this->userid = $user[$this->userid_field];
      return $this->userid;
    }
    return null;
  }
  public function checkToken($token,$userid=null) {
    if($userid == null) {
      $userid = $this->userid;
    }
    $this->model->dbh->setTable($this->tokens_table);
    $token = $this->model->dbh->selectData(array($this->userid_field => $userid, $this->token_field => $token),array($this->expiration_field),true);
    if(!empty($token)) {
      $expires = $token[$this->expiration_field];
      if($this->expiration_field_type == 'DATETIME') {
        $expires = strtotime($expires);
      }
      if($expires > time()) {
        return true;
      }
    }
    return false;
  }
  public function checkTokenActive($userid=null) {
    if($userid == null) {
      $userid = $this->userid;
    }
    $this->model->dbh->setTable($this->tokens_table);
    $existing = $this->model->dbh->selectData(array($this->userid_field => $userid));
    if(!empty($existing)) {
      foreach($existing as $token) {
        $expires = $token[$this->expiration_field];
        if($this->expiration_field_type == 'DATETIME') {
          $expires = strtotime($expires);
        }
        if($expires > time()) {
          return false;
        }
      }
    }
    return true;
  }
  public function checkTokenLock($userid=null) {
    if($userid == null) {
      $userid = $this->userid;
    }
    $messages_lock = Config::read('authtoken.lock.messages');
    $this->model->dbh->setTable($this->tokens_table);
    $existing = $this->model->dbh->selectData(array($this->userid_field => $userid));
    if($messages_lock != 0 && count($existing) >= $messages_lock) {
      return false;
    }
    return true;
  }
  public function checkAccountLock($userid=null) {
    if($userid == null) {
      $userid = $this->userid;
    }
    $max_attempts = Config::read('authtoken.lock.attempts');
    $this->model->dbh->setTable($this->users_table);
    $user = $this->model->dbh->selectData(array($this->userid_field => $userid),array($this->attempts_field),true);
    $user_attempts = $user[$this->attempts_field];
    if(empty($user_attempts)) {
      $user_attempts = 0;
    }
    if($max_attempts == 0 || $user_attempts < $max_attempts) {
      return true;
    }
    else {
      return false;
    }
  }
  public function clearAccount() {
  
  }
  public function sendToken($email,$token=null) {
    if($token == null) {
      if(isset($this->token)) {
        $token = $this->getToken();
      }
    }
    $headers = 'From: '.Config::read('authtoken.mail.from')."\r\n" .
        'Reply-To: '.Config::read('authtoken.mail.from')."\r\n" .
        'X-Mailer: PHP/' . phpversion();
    return mail($email, 'Token', $token, $headers);
  }
  public function setUserID($id) {
    $this->userid = $id;
  }
  public function getToken() {
    return $this->token;
  }
}

?>