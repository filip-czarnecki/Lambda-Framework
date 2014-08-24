<?php

class Frontend_framework implements iFrontend {
  public function __construct($view) {
    $this->view = $view;
    $this->include_basic();
    $this->glyphicons_fix();
  }
	public function navigation($current, $previous=array()) {
		$this->view->setArea('title');
		$this->view->display($current);
		$this->view->setPreviousArea();
		if(empty($previous)) {
			$this->view->display('<ol class="breadcrumb"><li class="active"><i class="glyphicon glyphicon-home"></i> '.$current.'</li></ol>');
		}
		else {
			$this->view->display('<ol class="breadcrumb">');
			$i = 0;
			foreach($previous as $link_key => $link_val) {
        $pos = strpos($link_val, ' (id ');
        if($pos !== false) {
          $link_val = substr($link_val, 0, $pos);
        }
        if($i == 0) {
          $icon = '<i class="glyphicon glyphicon-home"></i> ';
        }
        else {
          $icon = '';
        }
				$this->view->display('<li><a href="'.$link_key.'">'.$icon.$link_val.'</a></li>');
				$i++;
			}
			$this->view->display('<li class="active">'.$current.'</li></ol>');
		}
	}
	private function include_basic() {
    $this->include_css('bootstrap-glyphicons.css');
    $this->include_css('datatables.css');
    $this->include_css('bootstrap-select.min.css');
    $this->include_css('filedrop.css');
    $this->include_css('slider.css');
    $this->include_css('bootstrap-datetimepicker.min.css');
    $this->view->display('<!--[if lt IE 9]>');
    $this->include_js('html5shiv.js');
    $this->include_js('respond.min.js');
    $this->view->display('<![endif]-->');
    $this->include_js('jquery.min.js');
    $this->include_js('bootstrap.min.js');
    $this->include_js('jquery.dataTables.js');
    $this->include_js('datatables.js');
    $this->include_js('bootstrap-select.min.js');
    $this->include_js('jquery.filedrop.js');
    $this->include_js('filedrop.script.js');
    $this->include_js('bootstrap-slider.js');
    $this->include_js('moment-2.4.0.js');
    $this->include_js('bootstrap-datetimepicker.pl.js');
    $this->include_js('bootstrap-datetimepicker.js');
	}
	private function glyphicons_fix() {
		$this->view->setArea('head_style');
		$this->view->display('
    @charset "UTF-8";

    .glyphicon-bell:before {
      content: "🔔";
    }
    .glyphicon-bookmark:before {
      content: "🔖";
    }
    .glyphicon-briefcase:before {
      content: "💼";
    }
    .glyphicon-calendar:before {
      content: "📅";
    }
    .glyphicon-camera:before {
      content: "📷";
    }
    .glyphicon-fire:before {
      content: "🔥";
    }
    .glyphicon-lock:before {
      content: "🔒";
    }
    .glyphicon-paperclip:before {
      content: "📎";
    }
    .glyphicon-pushpin:before {
      content: "📌";
    }
    .glyphicon-wrench:before {
      content: "🔧";
    }
		');
	}
	public function newicons($path='',$size='md-2') {
    if(empty($path)) {
      $this->path = '';
    }
    else {
      $this->path = Config::read('lmbd.modules.dir').'/'.$path.'/';
    }
		$this->view->display('<div class="row">');
    $this->icon_count = 0;
    $this->icon_size = $size;
	}
	public function icon($name,$file,$destination="#") {
    if($this->icon_count != 0) {
      if($this->icon_count % 6 == 0) {
        $this->view->display('</div>');
        $this->view->display('<div class="row">');
      }
    }
		$this->view->display('<div class="col-'.$this->icon_size.' text-center">
          <a href="'.$destination.'"><img src="'.$this->path.$file.'">
          <h5>'.$name.'</h5></a>
        </div>');
    $this->icon_count++;
	}
	public function endicons() {
      $this->view->display('</div>');
	}
  public function newform($post_array=null,$class="form-horizontal",$params='',$fallback=array()) {
    $this->form_ready = true;
    $this->fallback = $fallback;
    $this->fields = array();
    $this->form_class = $class;
    
		if(is_array($post_array)) {
			$this->check = $post_array;
		}
		else {
			$this->check = Util::checkparams();
		}
		if(empty($this->check)) {
      $this->form_ready = false;
		}
		
    $this->view->display('<form class="'.$class.'" '.$params.' role="form" method="post">');
  }
  public function endform() {
    $this->view->display('</form>');
    if($this->form_ready) {
      if(isset($this->check['lmbd_required'])) {
        unset($this->check['lmbd_required']);
      }
      if(isset($this->check['lmbd_form'])) {
        unset($this->check['lmbd_form']);
      }
      return $this->check;
    }
    else {
      return null;
    }
  }
  public function formsubmit($name,$content,$class="btn-default",$label="") {    
		$this->view->display('
		<div class="form-group">
		<label for="'.$name.'" class="col-md-3 control-label">'.$label.'</label>
		<div class="col-md-9">
		      <button type="submit" name="'.$name.'" id="'.$name.'" class="btn '.$class.'">'.$content.'</button>
		    </div>
		  </div>
		');
  }
	public function button($name,$action,$size="",$icon=null) {
		if($icon != null) {
			$this->view->display('<button type="button" onclick="window.location=\''.$action.'\'" class="btn btn-default '.$size.'"><i class="glyphicon '.$icon.'"></i> '.$name.'</button>');
		}
		else {
			$this->view->display('<button type="button" onclick="window.location=\''.$action.'\'" class="btn btn-default '.$size.'">'.$name.'</button>');
		}
	}
	public function dropdown($description,$buttons,$class='') {
    $this->view->display('<div class="btn-group"><button class="btn btn-default '.$class.' dropdown-toggle" type="button" data-toggle="dropdown">'.$description.' <span class="caret"></span></button><ul class="dropdown-menu">');
    foreach($buttons as $name=>$address) {
      $this->view->display('<li><a href="'.$address.'">'.$name.'</a></li>');
    }
    $this->view->display('</ul></div>');
	}
  public function check($value) {
    if(isset($this->check[$value])) {
      return $this->check[$value];
    }
    else {
      return null;
    }
  }
  public function fileupload($label) {    
		$this->view->display('
		<div class="form-group">
      <label for="message" class="col-md-3 control-label">'.$label.'</label>
      <div class="col-md-9 well" id="dropbox">
          <div class="message">
            <div class="col-md-4"></div>
            <div class="col-md-4"><br /><br /><br /><i class="glyphicon glyphicon-download"></i> Przeciągnij i upuść zdjęcie tutaj lub <input type="file" name="file" id="file"></div>
            <div class="col-md-4"></div>
          </div>
      </div>
     </div>
		');
  }
  public function formslider($name, $label, $value=50, $min=1, $max=100) {
    $check = $this->check($name);
    if(!empty($check)) {
      $value = $check;
    }
    $this->view->setArea('footer_script');
		$this->view->display('
        $(\'#'.$name.'\').slider({
          min:'.$min.',
          max:'.$max.',
          value:'.$value.',
          formater: function(value) {
            return value;
          }
        });
		');
  
    $this->view->setPreviousArea();
		$this->view->display('
		<div class="form-group">
		<label for="'.$name.'" class="col-md-3 control-label">'.$label.'</label>
		<div class="col-md-9">
      <input type="text" name="'.$name.'" id="'.$name.'">
		</div>
		</div>
		');
  }
  public function forminput($name,$label,$placeholder="",$required=null,$helptext=null,$attribute="",$type="default",$class="md-9",$value="") {
    $this->fields[$name] = 'input-'.$type;
    $check = $this->check($name);
    if(isset($check) && $check == "error") {
      $this->form_ready = false;
      $this->view->display('<div class="form-group has-error">
        <label for="'.$name.'" id="label_'.$name.'" class="col-md-3 control-label">'.$label.'</label>
        <div class="col-'.$class.'">
      ');
    }
    else if(isset($check) || isset($this->fallback[$name])) {
      if(!empty($check)) {
        $value = $check;
      }
      else if(!empty($this->fallback[$name])) {
        $value = $this->fallback[$name];
      }
      $this->view->display('<div class="form-group">
        <label for="'.$name.'" id="label_'.$name.'" class="col-md-3 control-label">'.$label.'</label>
        <div class="col-'.$class.'">
      ');
    }
    else {
      $this->view->display('<div class="form-group">
        <label for="'.$name.'" id="label_'.$name.'" class="col-md-3 control-label">'.$label.'</label>
        <div class="col-'.$class.'">
      ');
    }
    if($type == "default") {
      $this->view->display('<input type="text" class="form-control" name="'.$name.'" id="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'" '.$attribute.'>');
    }
    else if($type == "password") {
      $this->view->display('<input type="password" class="form-control" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$attribute.'>');
    }
    else if($type == "datetime") {
			$this->view->setArea('footer_script');
      $this->view->display('
          $("#datetimepicker_'.$name.'").datetimepicker({startDate: -Infinity,endDate: Infinity,language: "pl"});
      ');
      $this->view->setPreviousArea();
      $this->view->display('<div id="datetimepicker_'.$name.'" class="input-group date"><input type="text" class="form-control" data-format="YYYY-MM-DD HH:mm" name="'.$name.'" id="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'" '.$attribute.'><span class="input-group-addon" id="addon_'.$name.'"><span class="glyphicon glyphicon-calendar"></span></span></div>');
    }
    else if($type == "date") {
			$this->view->setArea('footer_script');
      $this->view->display('
          $("#datetimepicker_'.$name.'").datetimepicker({startDate: -Infinity,endDate: Infinity,language: "pl",pickTime: false});
      ');
      $this->view->setPreviousArea();
      $this->view->display('<div id="datetimepicker_'.$name.'" class="input-group date"><input type="text" class="form-control" data-format="YYYY-MM-DD" name="'.$name.'" id="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'" '.$attribute.'><span class="input-group-addon" id="addon_'.$name.'"><span class="glyphicon glyphicon-calendar"></span></span></div>');
    }
    else if($type == "textarea") {
      if($attribute == "") {
        $attribute = 'rows="3"';
      }
      $this->view->display('<textarea class="form-control" name="'.$name.'" id="'.$name.'" '.$attribute.'>'.$value.'</textarea>');
    }
    if($helptext != null) {
      $this->view->display('<p class="help-block"><i class="glyphicon glyphicon-question-sign"></i> '.$helptext.'</p>');
    }
    if($required != null) {
      $this->view->display('<input type="hidden" name="lmbd_required[]" value="'.$name.'">');
    }
    else if($attribute == 'disabled') {
      $this->view->display('<input type="hidden" name="lmbd_ignore[]" value="'.$name.'">');
    }
    $this->view->display('</div></div>');
  }
  public function formselect($name,$label='',$val=null,$data,$required=null,$helptext=null,$attribute="",$sort=true) {
    $this->fields[$name] = 'select';
    $check = $this->check($name);
    if(strpos($attribute,'multiple') !== false) {
      $multiple = true;
      if($val != null) {
        $attribute .= ' title="'.$val.'"';
      }
      $html_name = ''.$name.'[]';
    }
    else {
      $multiple = false;
      $html_name = $name;
    }
    if($this->form_class == 'form-inline') {
      $column = '';
      $label_class = ' ';
    }
    else {
      $column = '<div class="col-md-9">';
      $label_class = 'col-md-3 ';
    }
    
    if(empty($label)) {
      $label_html = '';
    }
    else {
      $label_html = '<label for="'.$name.'" id="label_'.$name.'" class="'.$label_class.'control-label">'.$label.'</label>';
    }
    
    if($check == "error") {
      $this->form_ready = false;
      $this->view->display('<div class="form-group has-error">'.$label_html.$column.'<select class="selectpicker" name="'.$html_name.'" id="'.$name.'" '.$attribute.' data-style="btn-danger">');
    }
    else if(!empty($check) || $val != null) {
      $this->view->display('<div class="form-group">'.$label_html.$column.'<select class="selectpicker" name="'.$html_name.'" id="'.$name.'" '.$attribute.'>');
    }
    else {
      $this->view->display('<div class="form-group">'.$label_html.$column.'<select class="selectpicker" name="'.$html_name.'" id="'.$name.'" '.$attribute.'>');
    }
    if($sort == true) {
      asort($data);
    }
    
    $selected = false;
    if(!empty($check) && $check != 'error') {
      $select = $check;
    }
    else if($val != null) {
      $select = $val;
    }
    else {
      $select = null;
    }
    foreach($data as $key => $value) {
      if(is_array($select)) {
        if(in_array($key,$select)) {
          $this->view->display('<option value="'.$key.'" selected>'.$value.'</option>');
        }
        else {
          $this->view->display('<option value="'.$key.'">'.$value.'</option>');
        }
      }
      else {
        if($key == $select) {
          $this->view->display('<option value="'.$key.'" selected>'.$value.'</option>');
          $selected = true;
        }
        else {
          $this->view->display('<option value="'.$key.'">'.$value.'</option>');
        }
      }
    }
    if(($selected == false) && ($val != null) && ($multiple == false)) {
      $this->view->display('<option value="" selected>'.$val.'</option>');
    }
    $this->view->display('</select>');
    
    if($helptext != null) {
      $this->view->display('<p class="help-block"><i class="glyphicon glyphicon-question-sign"></i> '.$helptext.'</p>');
    }
    if($required != null) {
      $this->view->display('<input type="hidden" name="lmbd_required[]" value="'.$name.'">');
    }
    if($this->form_class != 'form-inline') {
      $this->view->display('</div>');
    }
    $this->view->display('</div>');
    if(!isset($this->selectpicker)) {
			$this->view->setArea('footer_script');
      $this->view->display('$(\'.selectpicker\').selectpicker();');
      $this->view->setPreviousArea();
      if(!$this->view->getVoid()) {
        $this->selectpicker = true;
      }
    }
  }
  public function formhidden($name,$value) {
    $this->fields[$name] = 'hidden';
    $this->view->display('<input type="hidden" name="'.$name.'" value="'.$value.'">');
  }
  public function getfieldtype($name) {
    if(isset($this->fields[$name])) {
      return $this->fields[$name];
    }
    return null;
  }
  public function dependency($field_change,$field_dependent,$data,$type='replace') {
    if($this->getfieldtype($field_dependent) == 'select') {
      $selectpicker = true;
      $addon = false;
    }
    else if($this->getfieldtype($field_dependent) == 'input-date') {
      $selectpicker = false;
      $addon = true;
    }
    else {
      $selectpicker = false;
      $addon = false;
    }
    
    if($selectpicker) {
      $field_action_ready_hide = "selectpicker('hide')";
      $field_action_hide = "selectpicker('hide')";
      $field_action_show = "selectpicker('show')";
    }
    else {
      $field_action_ready_hide = "hide()";
      $field_action_hide = "hide('slow')";
      $field_action_show = "show('slow')";
    }
		if($type == 'replace') {
			$this->view->setArea('footer_script');
			
			$this->view->display('
			$(\'#'.$field_change.'\').change(function() {
		    $(\'#'.$field_dependent.'\').find(\'*\').not( ":contains(\'Wybierz\')" ).remove();
			');
			foreach($data as $data_key => $data_value) {
				$this->view->display('
			    if ($(this).val() === \''.$data_key.'\') {
			  ');
			  		foreach($data_value as $key => $value) {
              $this->view->display('
              $(\'<option>\').val(\''.$key.'\').text(\''.$value.'\').appendTo(\'#'.$field_dependent.'\');
              ');
			  		}
				$this->view->display('}');
			}
			if($selectpicker) {
        $this->view->display('
          $(\'#'.$field_dependent.'\').selectpicker(\'refresh\');
        ');
			}
      $this->view->display('
      });
      ');
      $this->view->setPreviousArea();
		}
		else if($type == 'hide') {
		
			$this->view->setArea('footer_script');
			$this->view->display('$(document).ready(function() {');
			foreach($data as $value) {
				$this->view->display('
			    if ($(\'#'.$field_change.'\').val() === \''.$value.'\') {
				 		$(\'#label_'.$field_dependent.'\').hide();
				 		$(\'#'.$field_dependent.'\').'.$field_action_ready_hide.';
				 		');
				 		if($addon) {
              $this->view->display('$(\'#addon_'.$field_dependent.'\').hide();');
				 		}
				 		$this->view->display('
          }
        ');
			}
			$this->view->display('});');
			$this->view->display('$(\'#'.$field_change.'\').change(function() {');
			foreach($data as $value) {
				$this->view->display('
			    if ($(this).val() === \''.$value.'\') {
				 		$(\'#label_'.$field_dependent.'\').hide("slow");
				 		$(\'#'.$field_dependent.'\').'.$field_action_hide.';
				 		');
				 		if($addon) {
              $this->view->display('$(\'#addon_'.$field_dependent.'\').hide("slow");');
				 		}
				 		$this->view->display('
          }
        ');
			}
			$this->view->display('});');
      $this->view->setPreviousArea();
		}
		else if($type == 'show') {
			$this->view->setArea('footer_script');
			$this->view->display('$(\'#'.$field_change.'\').change(function() {');
			foreach($data as $value) {
				$this->view->display('
			    if ($(this).val() === \''.$value.'\') {
				 		$(\'#label_'.$field_dependent.'\').show("slow");
				 		$(\'#'.$field_dependent.'\').'.$field_action_show.';
				 		');
				 		if($addon) {
              $this->view->display('$(\'#addon_'.$field_dependent.'\').show("slow");');
				 		}
				 		$this->view->display('
          }
        ');
			}
			$this->view->display('});');
      $this->view->setPreviousArea();
		}
  }
	public function message($type,$header,$content) {
	
    $this->view->display('<div class="alert '.$type.'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>'.$header.'</strong><br /><br />
    '.$content.'
    </div>');
	}
	public function modal($title,$content) {
		$this->view->setArea('footer_script');
    $this->view->display("
    $('#lmbd_modal').modal()
    ");

    $this->view->setPreviousArea();
    $this->view->display('
      <div class="modal fade" id="lmbd_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title">'.$title.'</h4>
            </div>
            <div class="modal-body">'.$content.'</div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Strona główna</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Podgląd</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Dodaj kolejne</button>
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->
    ');
	}
	public function newgrid() {
		$this->view->display('<div class="row">');
		$this->gridcount = 0;
	}
	public function grid($number) {
		if($this->gridcount != 0) {
			$this->view->display('</div>');
			if($this->gridcount % 12 == 0) {
				$this->view->display('</div><div class="row">');
			}
		}
		$this->view->display('<div class="col-md-'.$number.'">');
		$this->gridcount = $this->gridcount + $number;
	}
	public function endgrid() {
		$this->view->display('</div></div>');
	}
	public function progressbar($valuenow,$valuemin=0,$valuemax=100) {
		$this->view->display('<div class="progress"><div class="progress progress-striped"><div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$valuenow.'" aria-valuemin="'.$valuemin.'" aria-valuemax="'.$valuemax.'" style="width: '.$valuenow.'%"></div></div></div>');
	}
	public function panel($heading,$body,$type='panel-default',$options='') {
    $this->view->display('
    <div class="panel '.$type.'" '.$options.'>
      <div class="panel-heading">'.$heading.'</div>
      <div class="panel-body">'.$body.'</div>
    </div>
    ');
	}
	public function include_js($include) {
		$this->view->setArea('head');
		$this->view->display('<script src="'.Config::read('lmbd.modules.dir').'/frontend_bootstrap/common/js/'.$include.'"></script>');
	}
	public function include_css($include) {
		$this->view->setArea('head');
		$this->view->display('<link href="'.Config::read('lmbd.modules.dir').'/frontend_bootstrap/common/css/'.$include.'" rel="stylesheet" media="screen">');
	}
}

?>