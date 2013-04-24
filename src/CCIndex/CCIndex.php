<?php
//  STANDARD CONTROLLER LAYOUT
//  @PACKAGE SkolaCore
//

class CCIndex extends CObject implements IController {

/**
* Constructor
*/
  public function __construct() {
    parent::__construct();
  }
  

/**
* Implementing interface IController. All controllers must have an index action.
*/
  public function Index() {  
    $this->views->SetTitle('Index Controller');
    $this->views->AddInclude(dirname(__FILE__) . '/index.tpl.php', array('menu'=>$this->Menu()));
  }


/**
* A menu that shows all available controllers/methods
*/
  private function Menu() {  
    $items = array();
    foreach($this->config['controllers'] as $key => $val) {
      if($val['enabled']) {
        $rc = new ReflectionClass($val['class']);
        $items[] = $key;
        $methods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method) {
          if($method->name != '__construct' && $method->name != '__destruct' && $method->name != 'Index') {
            $items[] = "$key/" . strtolower($method->name);
          }
        }
      }
    }
    return $items;
  }
  
}
