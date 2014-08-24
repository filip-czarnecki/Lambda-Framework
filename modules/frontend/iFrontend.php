<?php

interface iFrontend {
  public function __construct($view);
	public function navigation($current, $previous=array());
	public function newicons($path='',$size='md-2');
	public function icon($name,$file,$destination="#");
	public function endicons();
  public function newform($post_array=null,$class="form-horizontal",$params='',$fallback=array());
  public function endform();
  public function formsubmit($name,$content,$class="btn-default",$label="");
  public function fileupload($label);
  public function formslider($name, $label, $value=50, $min=1, $max=100);
  public function forminput($name,$label,$placeholder="",$required=null,$helptext=null,$attribute="",$type="default",$class="md-9",$value="");
  public function formselect($name,$label,$val=null,$data,$required=null,$helptext=null,$attribute="",$sort=true);
  public function formhidden($name,$value);
  public function dependency($field_change,$field_dependent,$data,$type='replace');
	public function message($type,$header,$content);
	public function modal($title,$content);
	public function newgrid();
	public function grid($number);
	public function endgrid();
	public function progressbar($valuenow,$valuemin=0,$valuemax=100);
	public function button($name,$action,$size="",$icon=null);
	public function panel($heading,$body,$type='panel-default',$options='');
	public function dropdown($description,$buttons,$class='');
}

?>