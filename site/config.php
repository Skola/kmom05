<?php
//  CONFIGURATION OF APPLICATION: USER SPECIFIC
//  @PACKAGE SkolaCore
//

//  Set level of error reporting
error_reporting(-1);
ini_set('display_errors', 1);

//  Set databases
$ly->config['database'][0]['dsn'] = 'sqlite:' . SKOLA_SITE_PATH . '/data/.ht.sqlite';

//  Debug enabler for get_debug() theme helper
$ly->config['debug']['skola'] = false;
$ly->config['debug']['session'] = false;
$ly->config['debug']['timer'] = true;
$ly->config['debug']['db-num-queries'] = true;
$ly->config['debug']['db-queries'] = true;

//  Define session name
$ly->config['session_name'] = preg_replace('/[:\.\/-_]/', '', $_SERVER["SERVER_NAME"]);
$ly->config['session_key'] = 'skola';

//  Define server timezone
$ly->config['timezone'] = 'Europe/Stockholm';

//  Define internal character encoding
$ly->config['character_encoding'] = 'UTF-8';

//  Define language
$ly->config['language'] = 'en';

//  Set a base_url to use another than the default calculated
$ly->config['base_url'] = null;

/**
* How to hash password of new users, choose from: plain, md5salt, md5, sha1salt, sha1.
*/
$ly->config['hashing_algorithm'] = 'sha1salt';

/**
* Allow or disallow creation of new user accounts.
*/
$ly->config['create_new_users'] = true;

//  What type of urls should be used?
//  default      = 0      => index.php/controller/method/arg1/arg2/arg3
//  clean        = 1      => controller/method/arg1/arg2/arg3
//  querystring  = 2      => index.php?q=controller/method/arg1/arg2/arg3
$ly->config['url_type'] = 1;

//  Define the controllers, their classname and enable/disable them.
//  The array-key is matched against the url, for example:
//  the url 'developer/dump' would instantiate the controller with the key "developer", that is
//  CCDeveloper and call the method "dump" in that class. This process is managed in:
//  $ly->FrontControllerRoute();
//  which is called in the frontcontroller phase from index.php.
$ly->config['controllers'] = array(
  'index'  => array('enabled' => true,'class' => 'CCIndex'),
  'test'  => array('enabled' => true,'class' => 'CCTest'),
  'guestbook'  => array('enabled' => true,'class' => 'CCGuestbook'),
  'user'  => array('enabled' => true,'class' => 'CCUser'),
  'content'  => array('enabled' => true,'class' => 'CCContent'),
  'blog'  => array('enabled' => true,'class' => 'CCBlog'),
  'page'  => array('enabled' => true,'class' => 'CCPage'),
  'admin' => array('enabled' => true,'class' => 'CCAdminControlPanel', 'access' => 'admin'),
);

//  Settings for theme
$ly->config['theme'] = array(
  // The name of the theme in the theme directory
  'name'    => 'core',
);
