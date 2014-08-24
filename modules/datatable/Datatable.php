<?php

class Datatable {
  public function getdatafromdb($table,$what,$fields) {
    $this->model->dbh->setTable($table);
    $data = $this->model->dbh->selectData($what,$fields);
    if($data != null) {
      $this->settable($fields,$data);
      return true;
    }
    else {
      return false;
    }
  }
  public function settable($fields,$data,$click=null,$name=null,$form=false,$formvalues=null) {
    if($name != null) {
      $this->name = $name;
    }
    if($click != null) {
      $this->clickable = true;
      
      $strpos = strpos($click, '<ID>');
      if($strpos !== false) {
        $this->clickhref1 = substr($click, 0, $strpos);
        $this->clickhref2 = substr($click, $strpos+4);
      }
      else {
        $this->clickhref1 = $click;
        $this->clickhref2 = '';
      }
    }
    $this->form = $form;
    $this->formvalues = $formvalues;
    $this->fields = $this->stitle(array_keys($fields));
    $this->data = $this->aadata($data,$fields);
  }
  public function aadata($data, $fields) {
    $jsarray = '';
    foreach($data as $element) {
      $jsarray .= '[ ';
      foreach($element as $key => $value) {
        if($key == "id") {
          $id = $value;
        }
        if(in_array($key,$fields) && !is_numeric($key)) {
          $jsarray .= ' "'.$value.'", ';
        }
      }
      if($this->form == true) {
        $jsarray .= '"<input type=\"checkbox\" name=\"lmbd_selection[]\" value=\"'.$id.'\">" ],';
      }
      else {
        $jsarray .= '],';
      }
    }
    return $jsarray;
  }
  public function stitle($fields) {
    $jsarray = '';
    foreach($fields as $field) {
      if($field == 'hidden') {
        $jsarray .= '{ "bVisible": false },';
      }
      else {
        $jsarray .= '{ "sTitle": "'.$field.'" },';
      }
    }
    if($this->form == true) {
      $jsarray .= '{ "sTitle": "Zaznacz", "sWidth": "5%", "sClass": "center", "bSortable": false },';
    }
    return $jsarray;
  }
	public function viewtable($params='"bFilter": false,"bInfo": false,"bPaginate": false,',$visual='table-striped table-bordered table-hover') {
    if(isset($this->name)) {
      $name = $this->name;
    }
    else {
      $name = 'datatable_'.md5(mt_rand());
    }
    $this->datatable_classic($name,$params,$visual);
	}
	public function datatable_classic ($name,$params,$visual) {
	
		$this->view->setarea('head_script');
		$this->view->display('

      $(document).ready(function() {
        var Datatable = $(\'#'.$name.'\').dataTable( {
          '.$params.'
            "oLanguage": {
                "sUrl": "'.Config::read('lmbd.modules.dir').'/'.Config::read('datatable.lang.path').'"
            },
            "aaData": [
            '.$this->data.'
            ],
            "aoColumns": [
            '.$this->fields.'
            ]
        } );
        ');
    if($this->form == true) {
        $this->view->display('
				$(\'#'.$name.'\').delegate(\'tbody > tr\', \'click\', function ()
				{
        if (event.target.type !== \'checkbox\') {
          $(\':checkbox\', this).trigger(\'click\');
        }
				});
				');
    }
    if(isset($this->clickable) && $this->clickable == true) {
        $this->view->display('
				$(\'#'.$name.'\').delegate(\'tbody > tr\', \'click\', function () {
          var aData = Datatable.fnGetData( this );
          window.location.href = \''.$this->clickhref1.'\' + aData[0] + \''.$this->clickhref2.'\';
        } );
      ');
    }
		$this->view->display('
		} );
		');

		$this->view->setPreviousArea();
		if($this->formvalues != null) {
      $this->view->display('<form role="form" method="post">');
		}
		$this->view->display('
		<table cellpadding="0" cellspacing="0" border="0" class="display datatable table '.$visual.'" id="'.$name.'">
		</table>
		');
		if($this->formvalues != null && !empty($this->data)) {
      if(is_array($this->formvalues)) {
        $this->view->display('Zaznaczone: <select name="'.$name.'">');
        foreach($this->formvalues as $key => $value) {
          $this->view->display('<option value='.$key.'>'.$value.'</option>');
        }
        $this->view->display('</select> <button type="submit" name="lmbd_form" id="lmbd_form" class="btn btn-default">OK</button>');
      }
      $this->view->display('</form>');
		}
	
	}
}

?>