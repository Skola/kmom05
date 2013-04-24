<?php
//  HELPER, AVAILABLE FOR ALL THEMES
//  THIS FILE IS INCLUDED RIGHT BEFORE THE THEMES OWN FUNCTION.PHP
//

/**
* Prepend the base_url.
*/
function base_url($url=null) {
  return CSkola::Instance()->request->base_url . trim($url, '/');
}



//  Return the current url.
function current_url() {
  return CSkola::Instance()->request->current_url;
}


/**
* Create a url to an internal resource.
*
* @param string the whole url or the controller. Leave empty for current controller.
* @param string the method when specifying controller as first argument, else leave empty.
* @param string the extra arguments to the method, leave empty if not using method.
*/
function create_url($urlOrController=null, $method=null, $arguments=null) {
  return CSkola::Instance()->request->CreateUrl($urlOrController, $method, $arguments);
}

//  Prepend the theme_url, which is the url to the current theme directory.
function theme_url($url) {
  $ly = CSkola::Instance();
  return "{$ly->request->base_url}themes/{$ly->config['theme']['name']}/{$url}";
}

//  Print debuginformation from the framework.
function get_debug() {
  $kronos = CSkola::Instance();
  if(empty($ly->config['debug'])) {
    return;
  }
  
  $html = null;
  if(isset($ly->config['debug']['db-num-queries']) && $ly->config['debug']['db-num-queries'] && isset($ly->db)) {
    $flash = $ly->session->GetFlash('database_numQueries');
    $flash = $flash ? "$flash + " : null;
    $html .= "<p>Database made $flash" . $ly->db->GetNumQueries() . " queries.</p>";
  }
  if(isset($ly->config['debug']['db-queries']) && $ly->config['debug']['db-queries'] && isset($ly->db)) {
    $flash = $ly->session->GetFlash('database_queries');
    $queries = $ly->db->GetQueries();
    if($flash) {
      $queries = array_merge($flash, $queries);
    }
    $html .= "<p>Database made the following queries.</p><pre>" . implode('<br/><br/>', $queries) . "</pre>";
  }
  if(isset($ly->config['debug']['timer']) && $ly->config['debug']['timer']) {
    $html .= "<p>Page was loaded in " . round(microtime(true) - $ly->timer['first'], 5)*1000 . " msec.</p>";
  } 
  if(isset($ly->config['debug']['skola']) && $ly->config['debug']['skola']) {
    $html .= "<hr><h3>Debuginformation</h3><p>The content of CSkola:</p><pre>" . htmlent(print_r($ly, true)) . "</pre>";
  }
  if(isset($ly->config['debug']['session']) && $kronos->config['debug']['session']) {
    $html .= "<hr><h3>SESSION</h3><p>The content of CSkola->session:</p><pre>" . htmlent(print_r($ly->session, true)) . "</pre>";
    $html .= "<p>The content of \$_SESSION:</p><pre>" . htmlent(print_r($_SESSION, true)) . "</pre>";
  } 
  return $html;
}

  /**
* A menu that shows all available controllers
*/
  function main_menu() {  
    $ly = CSkola::Instance();
  $items = null;
    foreach($ly->config['controllers'] as $key => $val) {
      if($val['enabled']) {
    $selected = ($key == $ly->request->controller) ? 'class="selected"' : null; 
        $items .= "<a href='" . create_url($key) . "' $selected>" . strtoupper($key) . "</a> ";
      }
    }
    return "<nav id='main-menu'>$items</nav>";
  }

/**
* Login menu. Creates a menu which reflects if user is logged in or not.
*/
function login_menu() {
  $ly = CSkola::Instance();
  if($ly->user['isAuthenticated']) {
    $items = "<a href='" . create_url('user/profile') . "'><img class='gravatar' src='" . get_gravatar(20) . "' alt=''> " . $ly->user['acronym'] . "</a> | ";
    if($ly->user['hasRoleAdmin']) {
      $items .= "<a href='" . create_url('admin') . "'>admin</a> | ";
    }
    $items .= "<a href='" . create_url('user/logout') . "'>logout</a> ";
  } else {
    $items = "<a href='" . create_url('user/login') . "'>login</a> ";
  }
  return "<nav>$items</nav>";
}

/**
* Get a gravatar based on the user's email.
*/
function get_gravatar($size=null) {
  return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim(CSkola::Instance()->user['email']))) . '.jpg?r=pg&amp;d=wavatar&amp;' . ($size ? "s=$size" : null);
}

/**
* Escape data to make it safe to write in the browser.
*/
function esc($str) {
  return htmlEnt($str);
}

/**
* Filter data according to a filter. Uses CMContent::Filter()
*
* @param $data string the data-string to filter.
* @param $filter string the filter to use.
* @returns string the filtered string.
*/
function filter_data($data, $filter) {
  return CMContent::Filter($data, $filter);
}


/**
* Display diff of time between now and a datetime.
*
* @param $start datetime|string
* @returns string
*/
function time_diff($start) {
  return formatDateTimeDiff($start);
}


//  Get messages stored in flash-session.
function get_messages_from_session() {
  $messages = CSkola::Instance()->session->GetMessages();
  $html = null;
  if(!empty($messages)) {
    foreach($messages as $val) {
      $valid = array('info', 'notice', 'success', 'warning', 'error', 'alert');
      $class = (in_array($val['type'], $valid)) ? $val['type'] : 'info';
      $html .= "<div class='$class'>{$val['message']}</div>\n";
    }
  }
  return $html;
}

//  Render all views.
function render_views() {
  return CSkola::Instance()->views->Render();
}
