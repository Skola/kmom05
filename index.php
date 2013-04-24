<?php
// BOOTSTRAP
//
define('SKOLA_INSTALL_PATH', dirname(__FILE__));
define('SKOLA_SITE_PATH', SKOLA_INSTALL_PATH . '/site');

require(SKOLA_INSTALL_PATH.'/src/bootstrap.php');

$ly = CSkola::Instance();


// FRONTCONTROLLER ROUTE
$ly->FrontControllerRoute();


// THEME ENGINE RENDER
$ly->ThemeEngineRender();
