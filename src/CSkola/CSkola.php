<?php
//  MAIN CLASS FOR SKOLA
//  @PACKAGE SkolaCore
//

class CSkola implements ISingleton {

  private static $instance = null;
  public $config = array();
  public $request;
  public $data;
  public $db;
  public $views;
  public $session;
  public $timer = array();
  
   
  //  CONSTRUCTOR: INCLUDES APPLICATION CONFIG AND CREATES REF TO $ly
  protected function __construct() {
    // time page generation
    $this->timer['first'] = microtime(true); 
    
    $ly = &$this;
    require(SKOLA_SITE_PATH.'/config.php');
    
    // Start a named session
    session_name($this->config['session_name']);
    session_start();
    $this->session = new CSession($this->config['session_key']);
    $this->session->PopulateFromSession();

    // Set default date/time-zone
    date_default_timezone_set($this->config['timezone']);
    
    // Create a database object.
    if(isset($this->config['database'][0]['dsn'])) {
      $this->db = new CDatabase($this->config['database'][0]['dsn']);
    }
    
    // Create a container for all views and theme data
    $this->views = new CViewContainer();
    
    // Create a object for the user
    $this->user = new CMUser($this);
  
  }

  //  GET INSTANCE OF LATEST CREATED OBJECT OR CREATE NEW, SINGLETON PATTERN
  public static function Instance() {
    if(self::$instance == null) {
      self::$instance = new CSkola();
    }
    return self::$instance;
  }
   
  //  FRONTCONTROLLER, CHECK URL AND ROUTE TO CONTROLLERS.
  public function FrontControllerRoute() {
    // Divide url in controller, method and parameters
    $this->request = new CRequest($this->config['url_type']);
    $this->request->Init($this->config['base_url']);
    $controller = $this->request->controller;
    $method     = $this->request->method;
    $arguments  = $this->request->arguments;

    // Is the controller existing and is it enabled in config.php?
    $controllerExists  = isset($this->config['controllers'][$controller]);
    $controllerEnabled  = false;
    $className      = false;
    $classExists    = false;
    $controllerAccess  = true;

    if($controllerExists) {
      $controllerEnabled  = ($this->config['controllers'][$controller]['enabled'] == true);
      $className      = $this->config['controllers'][$controller]['class'];
      $classExists    = class_exists($className);

      /*
      if(isset($this->config['controllers'][$controller]['access'])){
        switch($this->config['controllers'][$controller]['access']) {
          case 'admin': $controllerAccess  = ($this->user['hasRoleAdmin']) ? true : false; break;
          default: throw new Exception('Unknown access right');
        }
      }
      */
    }
    
    // Check if user has access right.
    //if(!$controllerAccess) die('404. You have no access right to this page.');
    
    // Check if controller has a callable method in the controller class, if then call it
    if($controllerExists && $controllerEnabled && $classExists) {
      $reflector = new ReflectionClass($className);
      if($reflector->implementsInterface('IController')) {
        $formattedMethod = str_replace(array('_', '-'), '', $method);
        if($reflector->hasMethod($formattedMethod)) {
          $controllerObj = $reflector->newInstance();
          $methodObj = $reflector->getMethod($formattedMethod);
          if($methodObj->isPublic()) {
            $methodObj->invokeArgs($controllerObj, $arguments);
          } else {
            die("404. " . get_class() . ' error: Controller method not public.');
          }
        } else {
          die("404. " . get_class() . ' error: Controller does not contain method.');
        }
      } else {
        die('404. ' . get_class() . ' error: Controller does not implement interface IController.');
      }
    }
    else {
      die('404. Page is not found.');
    }
  }
  
//  THEME ENGINE RENDER, RENDERS THE VIEWS USING SELECTED THEME.
  public function ThemeEngineRender() {
    // Save to session before output anything
    $this->session->StoreInSession();
    
    // Is theme enabled?
    if(!isset($this->config['theme'])) {
      return;
    }
    
    // Get the paths and settings for the theme
    $themeName  = $this->config['theme']['name'];
    $themePath  = SKOLA_INSTALL_PATH . "/themes/{$themeName}";
    $themeUrl  = $this->request->base_url . "themes/{$themeName}";
   
    // Add stylesheet path to the $ly->data array
    $this->data['stylesheet'] = "{$themeUrl}/style.css";

    // Include the global functions.php and the functions.php that are part of the theme
    $ly = &$this;
    include(SKOLA_INSTALL_PATH . '/themes/functions.php');
    $themeFunctions = "{$themePath}/functions.php";
    if(is_file($themeFunctions)) {
      include $themeFunctions;
    }

    // Extract $ly->data, $ly->views to own variables and handover to the template file
    extract($this->data);
    extract($this->views->GetData());
    include("{$themePath}/default.tpl.php");
  }

}
