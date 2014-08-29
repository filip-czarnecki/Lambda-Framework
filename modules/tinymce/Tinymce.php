<?php

class Tinymce {
	public function jsinit() {
		$this->view->setArea('head');
		$this->view->display('<script type="text/javascript" src="'.Config::read('lmbd.modules.dir').'/tinymce/tinymce.min.js"></script>');
		$this->view->setArea('head_script');
		$this->view->display('
    tinymce.init({
        language : "pl",
        selector: "textarea",
        theme: "modern",
        extended_valid_elements: "span[class],i[class]",
        plugins: [
            "advlist autolink lists link image charmap print preview hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen",
            "insertdatetime media nonbreaking save table contextmenu directionality",
            "emoticons template paste textcolor colorpicker textpattern"
        ],
        toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
        toolbar2: "print preview media | forecolor backcolor emoticons",
        image_advtab: true
    });
		');
	}
}

?>