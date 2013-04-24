<?php
/**
* Admin Control Panel to manage admin stuff.
*
* @package SkolaCore
*/
class CCAdminControlPanel extends CObject implements IController {


  /**
* Constructor
*/
  public function __construct() {
    parent::__construct();
  }


  /**
* Show profile information of the user.
*/
  public function Index() {
  if(!$this->user['isAuthenticated']){
    $this->AddMessage('notice', 'You need to login.');
    $this->session->SetNextRedirect($this->request->current_url);
    $this->RedirectTo('user', 'login');
  }
  if($this->user['hasRoleAdmin']){
    $this->views->SetTitle('Admin Control Panel');
    $this->views->AddInclude(dirname(__FILE__) . '/index.tpl.php', array(
      'hasRoleAdmin'=>$this->user['hasRoleAdmin'],
      'users'=>$this->user->viewAllUsers(),
      'allow_create_user' => CKronos::Instance()->config['create_new_users'],
      'create_user_url' => $this->CreateUrl('user', 'create'),
    ));
  } else {
    $this->views->SetTitle('Admin Control Panel');
    $this->views->AddInclude(dirname(__FILE__) . '/index.tpl.php', array(
      'hasRoleAdmin'=>$this->user['hasRoleAdmin'],
    ));
  }
  }
 
 public function Edit($id) {
  if(!$this->user['isAuthenticated']){
    $this->AddMessage('notice', 'You need to login.');
    $this->RedirectTo('user', 'login');
  }
  if($this->user['hasRoleAdmin']){
    $user = $this->user->viewUser($id);
    $userGroups = $this->user->viewUserGroups($id);
    //$groups = $this->user->viewGroups();
    $form = new CFormAdminUser($this, $user[0], $userGroups);
    if($form->Check() === false) {
      $this->AddMessage('notice', 'Some fields did not validate and the form could not be processed.');
      $this->RedirectToController('edit/'.$id);
    }
    $this->views->SetTitle('Admin Control Panel - Users');
    $this->views->AddInclude(dirname(__FILE__) . '/user.tpl.php', array(
      'hasRoleAdmin'=>$this->user['hasRoleAdmin'],
      'user_form'=>$form->GetHTML(),
    ));
  } else {
    $this->views->SetTitle('Admin Control Panel - Users');
    $this->views->AddInclude(dirname(__FILE__) . '/user.tpl.php', array(
      'hasRoleAdmin'=>$this->user['hasRoleAdmin'],
    ));
  }
  }
  
  /**
* Save updates to profile information.
*/
  public function UpdateUserProfile($form) {
  $ret = $this->user->UpdateProfile($form['acronym']['value'],$form['name']['value'],$form['email']['value'],$form['id']['value']);
    $this->AddMessage($ret, 'Users profile is updated.', 'Failed updating users profile.');
    $this->RedirectToController('edit/'.$form['id']['value']);
}
  
      /**
* Update users access groups.
*/
  public function UpdateUserAccess($form) {
  $this->user->DeleteUserGroups($form['id']['value']);
  if(isset($_POST['groups'])){
    foreach($_POST['groups'] as $group){
      if($group == 'admin'){
        $this->user->SetUserGroups($form['id']['value'], 1);
      }
      if($group == 'user'){
        $this->user->SetUserGroups($form['id']['value'], 2);
      }
    }
  }
    $this->RedirectToController('edit/'.$form['id']['value']);
  }
  
  
    /**
* Update users password.
*/
  public function UpdateUserPassword($form) {
    if($form['password']['value'] != $form['password1']['value'] || empty($form['password']['value']) || empty($form['password1']['value'])) {
      $this->AddMessage('error', 'Password does not match or is empty.');
    } else {
      $ret = $this->user->ChangePassword($form['password']['value'], $form['id']['value']);
      $this->AddMessage($ret, 'Users password is changed.', 'Failed changing users password.');
    }
    $this->RedirectToController('edit/'.$form['id']['value']);
  }
}
