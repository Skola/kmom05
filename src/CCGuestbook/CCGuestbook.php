<?php
//  GUESTBOOK CONTROLLER guestbook controller as an example to show off some basic controller and model-stuff.
//  @PACKAGE SkolaCore
//

class CCGuestbook extends CObject implements IController {

//  Constructor
public function __construct() {
  parent::__construct();
  $this->guestbookModel = new CMGuestbook();
}


//  Implementing interface IController. All controllers must have an index action.
public function Index() {
  $this->views->SetTitle('Skola Guestbook Example');
  $this->views->AddInclude(dirname(__FILE__) . '/index.tpl.php', array(
      'entries'=>$this->guestbookModel->ReadAll(),
      'formAction'=>$this->request->CreateUrl('guestbook/handler')
    ));
}


//  Handle posts from the form and take appropriate action.
public function Handler() {
  if(isset($_POST['doAdd'])) {
    $this->guestbookModel->Add(strip_tags($_POST['newEntry']));
    }
    elseif(isset($_POST['doClear'])) {
    $this->guestbookModel->DeleteAll();
    }
    elseif(isset($_POST['doCreate'])) {
    $this->guestbookModel->Init();
    }
    header('Location: ' . $this->request->CreateUrl('guestbook'));
} 
}
