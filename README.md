#Lambda Framework#

Lambda Framework is a web application framework written in PHP and released under the terms of the MIT license. It's goal is to provide simple and developer-friendly environment for creating any type of web applications.

**Features:**

- Modular design, everything is a module
- Custom dependency injection system
- Works out of box, no initial configuration required
- Lambda MVC architecture
- Application routing with user-friendly URLs
- Template system with support for caching views
- Simple browser-based managing application (currently in alpha state)
- Custom DB driver system with simple interface (select, insert, update, delete) supporting both relational and non-relational databases
- Fully integrated Bootstrap 3.x module for creating front-end
- Powerful theming module, you can easily adapt any html template to work with with Lambda Framework

**Available modules:**

- Auth (authorizing users with username and password, database required)
- Auth x509 (authorizing users with certificate)
- Bootstrap (creating front-end elements)
- Cache (caching content)
- Datatable (creating Bootstrap/jQuery tables from arrays or directly from DB)
- Model (DB drivers manager)
- Event (event manager)
- Frontend (front-end framework manager)
- Lang (internationalization)
- Logger (logging events)
- Router (application routing)
- Util (utility library)
- View (theming)

## Creating simple Lambda MVC application ##
1) Create application folder structure within modules folder (default: modules). You need to create following folders:
	a) Controller folder (ex. modules/application)
	b) Templates (view) folder, inside controller folder (ex. modules/application/templates)
	c) Model folder with \_model after application name (ex. modules/application_model)
2) Create module.ini file within application controller folder (modules/application/module.ini)
```INI
version = 1.0
enabled = 1
appcontroller = 1
autoload = 1
classname = ApplicationController
dependency[template] = 0.5
dependency[application_model] = 1.0
lmbd_compatible = 0.5
```
3) Create application controller class file. The filename must be exactly as classname defined in module.ini with php extension, ex. ApplicationController.php (modules/application/ApplicationController.php)
```PHP
class ApplicationController implements iConstruct {
  #When implementing iConstuct, init pseudocontructor is executed when all dependencies are ready
  public function init() {
    #Let's set default theme
    $this >template >setTheme('default');
    #And point to recently created template dir
    $this >template >setTemplateDir('application/templates');
    
    #Now let's get some data from application model and pass it to view
    $this->template->content = $this->applicationmodel->getContent();
    #View simple template
    $this->template->view('hello');
  }
}
```

4) Create simple template file inside templates file, ex. hello.php (modules/application/templates/hello.php)
```PHP
$this->view->setArea('title');
$this->view->display('Some title');

$this->view->setArea('body');
$this->view->display($this->content);
```

5) Create module.ini file within application model folder (modules/application_model/module.ini)
```INI
version = 1.0
enabled = 1
autoload = 1
inject = 1
classname = ApplicationModel
lmbd_compatible = 0.5
```

6) Create application model class file (modules/application_model/ApplicationModel.php)
```PHP
class ApplicationModel {
  public function getContent() {
    return 'Hello world!';
  }
}
```

7) Now you can run the application in the browser. If you are seeing Appverse instead of your application, disable Appverse or set your application as default (lmbd.default.module) in modules/controller/config.php

## Configuring framework ##
------------------

###Index file and modules directory###

Index (index.php) is an entry file, called by user and responsible for calling Lambda Framework main controller called LMBDV1. Currently it has only one configurable value: "modules_dir" which contains modules folder path and defaults to "modules".

###URL rewriting###

Lambda Framework provides default .htaccess file with some basic rules for URL rewriting. Of course you can customize this file and move its content to the apache main configuration file (if you are using apache and have access).
You can pass to the index file three parameters:

Parameter  | Used by | Description
------------- | ------------- | ------------- 
m  | LMBDV1 Controller | Module name (must be appcontroller) 
p | Application (via Router) | Page name 
id | Application (via Router) | Additional parameters separated by @ 

###Configuration files###

There are three types of configuration files:

- module.ini files
- install.ini files
- config.php files

**module.ini** files are used mainly by the LMBVD1 controller, only if the controller works in discovery mode.
Available settings below:

Name  | Description
------------- | -------------
version  | Module version (must be decimal)
enabled  | Tells if module is enabled or not (1 - enabled, 0 - disabled)
autoload | Whether module object should be created automatically by the main controller and added to its pool (1 - enabled, 0 - disabled)
appcontroller | Whether module is application controller. Application controller objects are always automatically created by the main controller  (1 - enabled, 0 - disabled)
classname | Classname of the main module class, must be the same as the filename
inject | Whether module object should be injected to the modules which require it (1 - yes, 0 - no)
config | Configuration file name (without .php extension). Configuration file is included before module is called by the main controller
dependency[module_name] | Array of the required modules with module name as key and minimal required version as value
lmbd_compatible | Lambda Framework version required by the module

© 2014 Filip Czarnecki

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
