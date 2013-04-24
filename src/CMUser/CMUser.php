<?php
/**
* A model for an authenticated user.
*
* @package SkolaCore
*/
class CMUser extends CObject implements IHasSQL, ArrayAccess {

  /**
* Properties
*/
  public $profile = array();


  /**
* Constructor
*/
  public function __construct($ly=null) {
    parent::__construct($ly);
    $profile = $this->session->GetAuthenticatedUser();
    $this->profile = is_null($profile) ? array() : $profile;
    $this['isAuthenticated'] = is_null($profile) ? false : true;
  }


  /**
* Implementing ArrayAccess for $this->profile
*/
  public function offsetSet($offset, $value) { if (is_null($offset)) { $this->profile[] = $value; } else { $this->profile[$offset] = $value; }}
  public function offsetExists($offset) { return isset($this->profile[$offset]); }
  public function offsetUnset($offset) { unset($this->profile[$offset]); }
  public function offsetGet($offset) { return isset($this->profile[$offset]) ? $this->profile[$offset] : null; }


  /**
* Implementing interface IHasSQL. Encapsulate all SQL used by this class.
*
* @param string $key the string that is the key of the wanted SQL-entry in the array.
*/
  public static function SQL($key=null) {
    $queries = array(
      'drop table user' => "DROP TABLE IF EXISTS User;",
      'drop table group' => "DROP TABLE IF EXISTS Groups;",
      'drop table user2group' => "DROP TABLE IF EXISTS User2Groups;",
      'create table user' => "CREATE TABLE IF NOT EXISTS User (id INTEGER PRIMARY KEY, acronym TEXT KEY UNIQUE, name TEXT, email TEXT, algorithm TEXT, salt TEXT, password TEXT, created DATETIME default (datetime('now')), updated DATETIME default NULL);",
      'create table group' => "CREATE TABLE IF NOT EXISTS Groups (id INTEGER PRIMARY KEY, acronym TEXT KEY, name TEXT, created DATETIME default (datetime('now')), updated DATETIME default NULL);",
      'create table user2group' => "CREATE TABLE IF NOT EXISTS User2Groups (idUser INTEGER, idGroups INTEGER, created DATETIME default (datetime('now')), PRIMARY KEY(idUser, idGroups));",
      'insert into user' => 'INSERT INTO User (acronym,name,email,algorithm,salt,password) VALUES (?,?,?,?,?,?);',
      'insert into group' => 'INSERT INTO Groups (acronym,name) VALUES (?,?);',
      'insert into user2group' => 'INSERT INTO User2Groups (idUser,idGroups) VALUES (?,?);',
      'check user password' => 'SELECT * FROM User WHERE (acronym=? OR email=?);',
      'get group memberships' => 'SELECT * FROM Groups AS g INNER JOIN User2Groups AS ug ON g.id=ug.idGroups WHERE ug.idUser=?;',
      'update profile' => "UPDATE User SET acronym=?, name=?, email=?, updated=? WHERE id=?;",
      'update password' => "UPDATE User SET algorithm=?, salt=?, password=?, updated=? WHERE id=?;",
    'view all users' => "SELECT * FROM User ORDER BY created DESC;",
    'view user' => "SELECT * FROM User WHERE id=?;",
    'view all groups' => "SELECT * FROM Groups;",
    'view user groups' => "SELECT idGroups FROM User2Groups WHERE idUser=?;",
    'delete user groups' => "DELETE FROM User2Groups WHERE idUser=?;",
     );
    if(!isset($queries[$key])) {
      throw new Exception("No such SQL query, key '$key' was not found.");
    }
    return $queries[$key];
  }


  /**
* Init the database and create appropriate tables.
*/
  public function Init() {
    try {
      $this->db->ExecuteQuery(self::SQL('drop table user2group'));
      $this->db->ExecuteQuery(self::SQL('drop table group'));
      $this->db->ExecuteQuery(self::SQL('drop table user'));
      $this->db->ExecuteQuery(self::SQL('create table user'));
      $this->db->ExecuteQuery(self::SQL('create table group'));
      $this->db->ExecuteQuery(self::SQL('create table user2group'));
      $password = $this->CreatePassword('root');
      $this->db->ExecuteQuery(self::SQL('insert into user'), array('root', 'Root of administration', 'mats.beddinge@gmail.com', $password['algorithm'], $password['salt'], $password['password']));
      $idRootUser = $this->db->LastInsertId();
      $password = $this->CreatePassword('doe');
      $this->db->ExecuteQuery(self::SQL('insert into user'), array('doe', 'Dideleydoo', 'doe@dbwebb.se', $password['algorithm'], $password['salt'], $password['password']));
      $idDoeUser = $this->db->LastInsertId();
      $this->db->ExecuteQuery(self::SQL('insert into group'), array('admin', 'The Administrator Group'));
      $idAdminGroup = $this->db->LastInsertId();
      $this->db->ExecuteQuery(self::SQL('insert into group'), array('user', 'The User Group'));
      $idUserGroup = $this->db->LastInsertId();
      $this->db->ExecuteQuery(self::SQL('insert into user2group'), array($idRootUser, $idAdminGroup));
      $this->db->ExecuteQuery(self::SQL('insert into user2group'), array($idRootUser, $idUserGroup));
      $this->db->ExecuteQuery(self::SQL('insert into user2group'), array($idDoeUser, $idUserGroup));
      $this->AddMessage('success', 'Successfully created the database tables and created a default admin user as root:root and an ordinary user as doe:doe.');
    } catch(Exception$e) {
      die("$e<br/>Failed to open database: " . $this->config['database'][0]['dsn']);
    }
  }
  

  /**
* Login by autenticate the user and password. Store user information in session if success.
*
* Set both session and internal properties.
*
* @param string $akronymOrEmail the emailadress or user akronym.
* @param string $password the password that should match the akronym or emailadress.
* @returns booelan true if match else false.
*/
  public function Login($akronymOrEmail, $password) {
    $user = $this->db->ExecuteSelectQueryAndFetchAll(self::SQL('check user password'), array($akronymOrEmail, $akronymOrEmail));
    $user = (isset($user[0])) ? $user[0] : null;
    if(!$user) {
      return false;
    } else if(!$this->CheckPassword($password, $user['algorithm'], $user['salt'], $user['password'])) {
      return false;
    }
    unset($user['algorithm']);
    unset($user['salt']);
    unset($user['password']);
    if($user) {
      $user['isAuthenticated'] = true;
      $user['groups'] = $this->db->ExecuteSelectQueryAndFetchAll(self::SQL('get group memberships'), array($user['id']));
      foreach($user['groups'] as $val) {
        if($val['id'] == 1) {
          $user['hasRoleAdmin'] = true;
        }
        if($val['id'] == 2) {
          $user['hasRoleUser'] = true;
        }
      }
      $this->profile = $user;
      $this->session->SetAuthenticatedUser($this->profile);
    }
    return ($user != null);
  }
  

  /**
* Logout. Clear both session and internal properties.
*/
  public function Logout() {
    $this->session->UnsetAuthenticatedUser();
    $this->profile = array();
    $this->AddMessage('success', "You have logged out.");
  }
  

  /**
* Create new user.
*
* @param $acronym string the acronym.
* @param $password string the password plain text to use as base.
* @param $name string the user full name.
* @param $email string the user email.
* @returns boolean true if user was created or else false and sets failure message in session.
*/
  public function Create($acronym, $password, $name, $email) {
    $pwd = $this->CreatePassword($password);
    try{
    $this->db->ExecuteQuery(self::SQL('insert into user'), array($acronym, $name, $email, $pwd['algorithm'], $pwd['salt'], $pwd['password']));
  }
  catch(Exception $e) {
    $this->AddMessage('error', "Failed to create user. Acronym might be used try a new one.");
    return array();
  }
  if($this->db->RowCount() == 0) {
    $this->AddMessage('error', "Failed to create user.");
    return false;
  }
  return true;
}
  

  /**
* Create password.
*
* @param $plain string the password plain text to use as base.
* @param $algorithm string stating what algorithm to use, plain, md5, md5salt, sha1, sha1salt.
* defaults to the settings of site/config.php.
* @returns array with 'salt' and 'password'.
*/
  public function CreatePassword($plain, $algorithm=null) {
    $password = array(
      'algorithm'=>($algorithm ? $algoritm : CSkola::Instance()->config['hashing_algorithm']),
      'salt'=>null
    );
    switch($password['algorithm']) {
      case 'sha1salt': $password['salt'] = sha1(microtime()); $password['password'] = sha1($password['salt'].$plain); break;
      case 'md5salt': $password['salt'] = md5(microtime()); $password['password'] = md5($password['salt'].$plain); break;
      case 'sha1': $password['password'] = sha1($plain); break;
      case 'md5': $password['password'] = md5($plain); break;
      case 'plain': $password['password'] = $plain; break;
      default: throw new Exception('Unknown hashing algorithm');
    }
    return $password;
  }
  

  /**
* Check if password matches.
*
* @param $plain string the password plain text to use as base.
* @param $algorithm string the algorithm mused to hash the user salt/password.
* @param $salt string the user salted string to use to hash the password.
* @param $password string the hashed user password that should match.
* @returns boolean true if match, else false.
*/
  public function CheckPassword($plain, $algorithm, $salt, $password) {
    switch($algorithm) {
      case 'sha1salt': return $password === sha1($salt.$plain); break;
      case 'md5salt': return $password === md5($salt.$plain); break;
      case 'sha1': return $password === sha1($plain); break;
      case 'md5': return $password === md5($plain); break;
      case 'plain': return $password === $plain; break;
      default: throw new Exception('Unknown hashing algorithm');
    }
  }
  
    /**
* Change user password.
*
* @param $plain string plaintext of the new password
* @returns boolean true if success else false.
*/
  public function ChangePassword($plain, $id=null) {
    $id = isset($id) ? $id : $this['id'];
  $date = date('Y-m-d H:i:s');
  $password = $this->CreatePassword($plain);
    $this->db->ExecuteQuery(self::SQL('update password'), array($password['algorithm'], $password['salt'], $password['password'], $date, $id));
    if($this->db->RowCount() === 1){
    $this['updated'] = $date;
    $this->session->SetAuthenticatedUser($this->profile);
    return true;
  }
  return false;
  }

  /**
* Update user profile to database and update user profile in session if user updates own profile, (not by admin).
*
* @returns boolean true if success else false.
*/
   public function UpdateProfile($acronym, $name, $email, $userid=null) {
  $id = isset($userid) ? $userid : $this['id'];
  $date = date('Y-m-d H:i:s');
  try{
    $this->db->ExecuteQuery(self::SQL('update profile'), array($acronym, $name, $email, $date, $id));
  }
  catch(Exception $e) {
    $this->AddMessage('error', "Failed to update user. Acronym might be used try a new one.");
    return false;
  }
  if($this->db->RowCount() === 1){
    if(!isset($userid)){
      $this['name'] = $name;
      $this['email'] = $email;
      $this['updated'] = $date;
      $this->session->SetAuthenticatedUser($this->profile);
    }    
    return true;
  }
  return false;
  }
  
/**
* Delete groups from user.
*/
  public function DeleteUserGroups($id) {
    try {
    $this->db->ExecuteQuery(self::SQL('delete user groups'), array($id));
  } catch(Exception $e) {
    return array();
  }
  }

  /**
* Set groups for user.
*/
  public function SetUserGroups($userid, $groupid) {
    try {
    $this->db->ExecuteQuery(self::SQL('insert into user2group'), array($userid, $groupid));
  } catch(Exception $e) {
    return array();
  }
  }
  
  
    /**
* View all users.
*/
  public function viewAllUsers() {
    try {
    return $this->db->ExecuteSelectQueryAndFetchAll(self::SQL('view all users'));
  } catch(Exception $e) {
    return array();
  }
  }
  
      /**
* View user.
*/
  public function viewUser($id) {
    try {
    return $this->db->ExecuteSelectQueryAndFetchAll(self::SQL('view user'), array($id));
  } catch(Exception $e) {
    return array();
  }
  }
  
      /**
* View all groups.
*/
  public function viewGroups() {
    try {
    return $this->db->ExecuteSelectQueryAndFetchAll(self::SQL('view all groups'));
  } catch(Exception $e) {
    return array();
  }
  }
  
        /**
* View user.
*/
  public function viewUserGroups($id) {
    try {
    return $this->db->ExecuteSelectQueryAndFetchAll(self::SQL('view user groups'), array($id));
  } catch(Exception $e) {
    return array();
  }
  }
}
