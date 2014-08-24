<?php

class Content {
	public function getPages($ctg=null) {
    if($ctg == null) {
      $data = null;
    }
    else {
      $data = array(Config::read('content.category.field') => $ctg);
    }
    $this->model->dbh->setTable(Config::read('content.pages.table'));
    return $this->model->dbh->selectData($data,array(Config::read('content.id.field'),Config::read('content.name.field')));
    #return Util::flatten($pages,Config::read('content.name.field'),Config::read('content.id.field'));
	}
	public function getContentByID($id) {
    if(is_numeric($id)) {
      $this->id = $id;
      $this->model->dbh->setTable(Config::read('content.pages.table'));
      return $this->model->dbh->selectData(array(Config::read('content.id.field') => $id),array(Config::read('content.name.field'),Config::read('content.content.field')),true);
    }
	}
	public function getContentByName($name) {
    $this->name = $name;
    $this->model->dbh->setTable(Config::read('content.pages.table'));
    $content = $this->model->dbh->selectData(array(Config::read('content.name.field') => $name),array(Config::read('content.content.field')),true);
    if(empty($content)) {
      return '';
    }
    else {
      return $content[Config::read('content.content.field')];
    }
	}
	public function updateContent($content,$id=null) {
    if($id == null) {
      $id = $this->id;
    }
    if(is_numeric($id) && isset($content[Config::read('content.content.field')])) {
      $this->model->dbh->setTable(Config::read('content.pages.table'));
      return $this->model->dbh->updateData(array(Config::read('content.content.field')=>$content[Config::read('content.content.field')]),array(Config::read('content.id.field')=>$id));
    }
	}
}

?>