<?php

class Util {

  public static function letter_only($text,$limit=64) {
    if(preg_match('/(*UTF8)^[a-zA-ZążśźęćńółĄŻŚŹĘĆŃÓŁ]{1,'.$limit.'}$/', $text)) {
      return true;
    } else {
      return false;
    }
  }
  public static function data_to_array($data, $key, $value, $strip=true, $exclude_empty=false, $exclude=null) {
    $return_array = array();
    foreach($data as $piece) {
      if($exclude != null) {
        if(stripos($piece[$value],$exclude) !== false) {
          continue;
        }
      }
      else if($exclude_empty) {
        if(empty($piece[$value])) {
          continue;
        }
      }
      if($strip) {
        $return_array[$piece[$key]] = stripslashes($piece[$value]);
      }
      else {
        $return_array[$piece[$key]] = $piece[$value];
      }
    }
    return $return_array;
  }
  public static function getpostparam($param) {
    if(isset($_POST[$param])) {
      if($_POST[$param] != null && $_POST[$param] != '') {
        return $_POST[$param];
      }
      else {
        return null;
      }
    }
    else {
      return null;
    }
  }
  public static function checkpassword($password) {
    $rank=0;
    
    if(preg_match("/[A-Z]/", $password)) {
      $rank++;
    }
    if(preg_match("/[a-z]/", $password)) {
      $rank++;
    }
    if(preg_match("/[0-9]/", $password)) {
      $rank++;
    }
    if(preg_match("/.[=,-,+,!,@,#,$,%,^,&,*,?,_,~,-,L,(,)]/", $password)) {
      $rank++;
    }
    return $rank;
  }
  public static function checkarray($array_to_check) {
    foreach($array_to_check as $key => $value) {
      if(empty($value)) {
        $array_to_check[$key] = "error";
      }
    }
    return $array_to_check;
  }
  public static function checkparams() {
    $return_array = array();
    if(isset($_POST['lmbd_required'])) {
      if(is_array($_POST['lmbd_required'])) {
        foreach($_POST['lmbd_required'] as $required) {
          if(Util::getpostparam($required) == null) {
            $return_array[$required] = "error";
          }
          else {
            $return_array[$required] = Util::getpostparam($required);
          }
        }
      }
    }
    if(!empty($_POST)) {
      foreach($_POST as $key => $value) {
        if(!isset($return_array[$key])) {
          $return_array[$key] = $value;
        }
      }
    }
    if(isset($_POST['lmbd_ignore'])) {
      if(is_array($_POST['lmbd_ignore'])) {
        foreach($_POST['lmbd_ignore'] as $ignore) {
          unset($return_array[$ignore]);
        }
      }
    }
    return $return_array;
  }
  public static function implode($array_to_implode) {
    $return_array = array();
    
    foreach($array_to_implode as $element) {
      foreach($element as $key => $value) {
        if(!is_numeric($key)) {
          $return_array[] = $value;
        }
      }
    }
    
    return implode(", ", $return_array);
  }
  public static function getyears($startdate = 1900) {
    $return_array = array();
    for ($i = date("Y"); $i >= $startdate; $i--) {
      $return_array[$i] = $i;
    }
    return $return_array;
  }
  public static function formatdate($date,$format='Y-m-d H:i:s') {
    return Util::datetostring(strtotime($date),$format);
  }
  public static function datetostring($date,$format='Y-m-d H:i:s') {
    return date($format,$date);
  }
  public static function clean($string) {
     $polskie = array(',', ' - ',' ','ę', 'Ę', 'ó', 'Ó', 'Ą', 'ą', 'Ś', 's', 'ł', 'Ł', 'ż', 'Ż', 'Ź', 'ź', 'ć', 'Ć', 'ń', 'Ń','-',"'","/","?", '"', ":", 'ś', '!','.', '&', '&amp;', '#', ';', '[',']','domena.pl', '(', ')', '`', '%', '”', '„', '…');
     $miedzyn = array('-','-','-','e', 'e', 'o', 'o', 'a', 'a', 's', 's', 'l', 'l', 'z', 'z', 'z', 'z', 'c', 'c', 'n', 'n','-',"","","","","",'s','','', '', '', '', '', '', '', '', '', '', '', '', '');
     $string = str_replace($polskie, $miedzyn, $string);
     $string = str_replace(" ", "-", $string);
     $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
     return trim(preg_replace('/-+/', '-', $string), "-");
  }
  public static function flatten($data,$keys,$primary=null,$separator=' ') {
    $return_array = array();
    if(is_array($keys)) {
      $multiple = true;
    }
    else {
      $multiple = false;
    }
    foreach($data as $element) {
      if($multiple) {
        $text = '';
        foreach($keys as $key) {
          if(isset($element[$key])) {
            $text .= $element[$key].$separator;
          }
        }
        $text = rtrim($text,$separator);
      }
      else {
        if(isset($element[$keys])) {
          $text = $element[$keys];
        }
      }
      if($primary == null) {
        $return_array[] = $text;
      }
      else {
        $return_array[$element[$primary]] = $text;
      }
    }
    return $return_array;
  }
  public static function get_extension($file_name){
    $ext = explode('.', $file_name);
    $ext = array_pop($ext);
    return strtolower($ext);
  }
  public static function get_count($count_array) {
    if(isset($count_array[0]) && is_array($count_array[0])) {
      $count = reset($count_array[0]);
      if(isset($count[0])) {
        return $count[0];
      }
    }
    return 0;
  }
  public static function scan_dir($dir) {
    if(file_exists($dir)) {
      $files = array();
      $directories = array();
      
      if($handle = opendir($dir)) {
        $exclude = array('.', '..');
        while (false !== ($obj = readdir($handle))) {
          if(!in_array($obj, $exclude)) {
            if(is_file($dir.'/'.$obj)) {
              $files[$dir.'/'.$obj] = $obj;
            }
            else if(is_dir($dir.'/'.$obj)) {
              $directories[$dir.'/'.$obj] = $obj;
              $recur = Util::scan_dir($dir.'/'.$obj);
              $files = $files + $recur['files'];
              $directories = $directories + $recur['directories'];
            }
          }
        }
      }
      return array('files'=>$files,'directories'=>$directories);
    }
  }
  public static function remove_dir($dir,$remove_root=true,$exclude=null,$scan=null) {
    if(file_exists($dir)) {
      if($scan == null) {
        $scan = Util::scan_dir($dir);
      }
      foreach($scan['files'] as $file_path => $file_name) {
        if($file_name != $exclude.'.php') {
          if(!unlink($file_path)) {
            return false;
          }
        }
      }
      foreach($scan['directories'] as $directory_path => $directory_name) {
        if(!rmdir($directory_path)) {
          return false;
        }
      }
      if($remove_root) {
        if(count(scandir($dir)) == 2) {
          rmdir($dir);
        }
      }
      return true;
    }
  }
  public static function get_string_between($string,$start,$end) {
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
  }
  public static function check_name($name) {
    return preg_match('/^[a-zĄĆĘŁÓŚŻŹąćęłóśżź ]+$/iu', $name);
  }
  public static function get_true_ip() {
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    
    if(filter_var($client, FILTER_VALIDATE_IP)) {
      $ip = $client;
    }
    else if(filter_var($forward, FILTER_VALIDATE_IP)) {
      $ip = $forward;
    }
    else {
      $ip = $remote;
    }
    return $ip;
  }
  public static function trim_str($string,$maxlength,$end='...') {
    if(strlen($string) >= $maxlength) {
      return substr($string,0,$maxlength).$end;
    }
    return trim($string);
  }
  public static function strip_nl($string) {
    return trim(preg_replace('/\s\s+/', ' ', $string));
  }
}

?>