<?php
/**
* A form for editing the user profile.
*
* @package SkolaCore
*/
class CFormUserProfile extends CForm {

  /**
* Constructor
*/
  public function __construct($object, $user) {
    parent::__construct();
    $this->AddElement(new CFormElementText('acronym', array('readonly'=>true, 'value'=>$user['acronym'])))
         ->AddElement(new CFormElementText('name', array('value'=>$user['name'], 'required'=>true)))
         ->AddElement(new CFormElementText('email', array('value'=>$user['email'], 'required'=>true)))
         ->AddElement(new CFormElementSubmit('update_profile', array('callback'=>array($object, 'DoProfileSave'))))
     ->AddElement(new CFormElementPassword('password'))
         ->AddElement(new CFormElementPassword('password1', array('label'=>'Password again:')))
         ->AddElement(new CFormElementSubmit('change_password', array('callback'=>array($object, 'DoChangePassword'))));
         
    $this->SetValidation('name', array('not_empty'))
         ->SetValidation('email', array('not_empty'));
  }
  
}
