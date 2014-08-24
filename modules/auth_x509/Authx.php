<?php

require_once(Config::read('lmbd.modules.dir').'/auth_x509/config.php');

class Authx implements iConstruct {
  public function init() {
    $this->cert_provided = false;
    $this->cert_wrong_issuer = false;
    $this->cert_expired = false;

    if(Config::read('authx.use.sessions') == 1) {
      if(isset($_SESSION['authx_cert_name']) && isset($_SESSION['authx_cert_issuer']) && isset($_SESSION['authx_cert_expiration'])) {
        $this->setCertSession();
      }
      else {
        if($this->certCheck()) {
          $this->setSessions();
        }
      }
    }
    else {
      $this->certCheck();
    }
    if(Config::read('authx.auto.authorize') == 1) {
      $this->setIssuer(Config::read('authx.auto.authorize.certissuer'));
      $this->authorize();
    }
    if(Config::read('authx.regenerate.session') == 1) {
      $this->regenerateSession(Config::read('authx.regenerate.time'));
    }
  }
  public function regenerateSession($time) {
    if (!isset($_SESSION['authx_created'])) {
      $_SESSION['authx_created'] = time();
    }
    else if (time() - $_SESSION['authx_created'] > $time) {
      session_regenerate_id(true);
      $_SESSION['authx_created'] = time();
    }
  }
  public function setSessions() {
    $_SESSION['authx_cert_name'] = $this->cert_name;
    $_SESSION['authx_cert_issuer'] = $this->cert_issuer;
    $_SESSION['authx_cert_expiration'] = $this->cert_expiration;
  }
  public function setCertSession() {
    $this->cert_name = $_SESSION['authx_cert_name'];
    $this->cert_issuer = $_SESSION['authx_cert_issuer'];
    $this->cert_expiration = $_SESSION['authx_cert_expiration'];
    $this->cert_provided = true;
  }
  public function certCheck() {
    if(isset($_SERVER['SSL_CLIENT_CERT'])) {
      $cert = openssl_x509_parse($_SERVER['SSL_CLIENT_CERT'],true);
      $this->cert_name = $cert['subject']['CN'];
      $this->cert_issuer = $cert['issuer']['CN'];
      $this->cert_expiration = $cert['validTo_time_t'];
      $this->cert_provided = true;
      return true;
    }
    return false;
  }
  public function setIssuer($issuer) {
    $this->issuer = $issuer;
  }
  public function authorize() {
    if(!empty($this->issuer)) {
      if($this->cert_issuer == $this->issuer) {
        if($this->cert_expiration > time()) {
          return true;
        }
        else {
          $this->cert_expired = true;
        }
      }
      else {
        $this->cert_wrong_issuer = true;
      }
    }
    return false;
  }
  public function debug() {
    print_r(openssl_x509_parse($_SERVER['SSL_CLIENT_CERT'],true));
  }
}

?>