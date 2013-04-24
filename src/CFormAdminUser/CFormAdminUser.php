<?php
/**
* A form for editing the user profile.
*
* @package SkolaCore
*/
class CFormAdminUser extends CForm {

  /**
* Constructor
*/
  public function __construct($object, $user, $accessGroups) {
    parent::__construct();
  $adminGroup = false;
  $userGroup = false;
  foreach($accessGroups as $accessGroup){
    if($accessGroup['idGroups']==1) $adminGroup = true;
    if($accessGroup['idGroups']==2) $userGroup = true;
  }
  
    $this->AddElement(new CFormElementText('id', array('value'=>$user['id'], 'readonly'=>true)))
     ->AddElement(new CFormElementText('acronym', array('value'=>$user['acronym'], 'required'=>true)))
         ->AddElement(new CFormElementText('name', array('value'=>$user['name'], 'required'=>true)))
         ->AddElement(new CFormElementText('email', array('value'=>$user['email'], 'required'=>true)))
     ->AddElement(new CFormElementSubmit('update_profile', array('callback'=>array($object, 'UpdateUserProfile'))))
     ->AddElement(new CFormElementCheckbox('groups1', array('value'=>'user', 'nameArray'=>'groups', 'checked'=>$userGroup, 'id'=>'user', 'label'=>'user')))
     ->AddElement(new CFormElementCheckbox('groups2', array('value'=>'admin', 'nameArray'=>'groups', 'checked'=>$adminGroup, 'id'=>'admin', 'label'=>'admin')))
         ->AddElement(new CFormElementSubmit('update_groups', array('callback'=>array($object, 'UpdateUserAccess'))))
     ->AddElement(new CFormElementPassword('password'))
         ->AddElement(new CFormElementPassword('password1', array('label'=>'Password again:')))
         ->AddElement(new CFormElementSubmit('change_password', array('callback'=>array($object, 'UpdateUserPassword'))));
         
    $this->SetValidation('acronym', array('not_empty'))
     ->SetValidation('name', array('not_empty'))
         ->SetValidation('email', array('not_empty'));
  }
}
