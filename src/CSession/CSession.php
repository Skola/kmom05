<?php
//  WRAPPER FOR SESSION, READ AND STORE VALUES ON SESSION. MAINTAINS FLASH VALUES FOR ONE PAGELOAD
//  @PACKAGE SkolaCore
//

class CSession {

//  Members
private $key;
private $data = array();
private $flash = null;


//  Constructor
public function __construct($key) {
    $this->key = $key;
  }


//  Set values
public function __set($key, $value) {
    $this->data[$key] = $value;
  }


//  Get values
public function __get($key) {
    return isset($this->data[$key]) ? $this->data[$key] : null;
  }

/**
* Get, Set or Unset the authenticated user
*/
  public function SetAuthenticatedUser($profile) { $this->data['authenticated_user'] = $profile; }
  public function UnsetAuthenticatedUser() { unset($this->data['authenticated_user']); }
  public function GetAuthenticatedUser() { return $this->authenticated_user; }
  
  
//  Set flash values, to be remembered one page request
public function SetFlash($key, $value) {
    $this->data['flash'][$key] = $value;
}



//  Get flash values, if any.
public function GetFlash($key) {
    return isset($this->flash[$key]) ? $this->flash[$key] : null;
}


/**
* Add message to be displayed to user on next pageload. Store in flash.
* @param $type string the type of message, for example: notice, info, success, warning, error.
* @param $message string the message.
*/
public function AddMessage($type, $message) {
    $this->data['flash']['messages'][] = array('type' => $type, 'message' => $message);
}


/**
* Get messages, if any. Each message is composed of a key and value. Use the key for styling.
* @returns array of messages. Each array-item contains a key and value.
*/
public function GetMessages() {
    return isset($this->flash['messages']) ? $this->flash['messages'] : null;
}

/**
* Set redirect to be used on next pageload. Store in flash.
* @param $redirect string the url.
*/
public function SetNextRedirect($redirect) {
    $this->data['flash']['nextredirect'] = $redirect;
}

/**
* Get url for next redirect.
* @return url for next redirect.
*/
public function GetNextRedirect() {
    return isset($this->flash['nextredirect']) ? $this->flash['nextredirect'] : null;
}


//  Store values into session.
public function StoreInSession() {
    $_SESSION[$this->key] = $this->data;
}


//  Store values from this object into the session.
public function PopulateFromSession() {
  if(isset($_SESSION[$this->key])) {
    $this->data = $_SESSION[$this->key];
    if(isset($this->data['flash'])) {
      $this->flash = $this->data['flash'];
      unset($this->data['flash']);
    }
    }
}
}
