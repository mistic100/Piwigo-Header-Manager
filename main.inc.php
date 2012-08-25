<?php 
/*
Plugin Name: Header Manager
Version: auto
Description: Header Manager allows to simply manage gallery banners. You can upload a picture from your computer or use a picture already in the gallery.
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=608
Author: Mistic
Author URI: http://www.strangeplanet.fr
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable;
define('HEADER_MANAGER_PATH',    PHPWG_PLUGINS_PATH . 'header_manager/');
define('HEADER_MANAGER_ADMIN',   get_root_url() . 'admin.php?page=plugin-header_manager');
define('HEADER_MANAGER_DIR',     PWG_LOCAL_DIR . 'banners/');
define('HEADER_MANAGER_TABLE',   $prefixeTable . 'category_banner');
define('HEADER_MANAGER_VERSION', '1.0.3');

add_event_handler('init', 'header_manager_init');

function header_manager_init()
{
  if (defined('PWG_HELP')) return;
  
  global $conf;
  $conf['header_manager'] = unserialize($conf['header_manager']);
    
  include_once(HEADER_MANAGER_PATH . 'include/functions.inc.php');
  include_once(HEADER_MANAGER_PATH . 'include/header_manager.inc.php');
  
  if (!defined('IN_ADMIN'))
  {
    add_event_handler('render_page_banner', 'header_manager_render');
  }
  else
  {
    add_event_handler('loc_begin_admin_page', 'header_manager_check_version');
    add_event_handler('get_admin_plugin_menu_links', 'header_manager_admin_menu');
    add_event_handler('tabsheet_before_select', 'header_manager_tab', EVENT_HANDLER_PRIORITY_NEUTRAL, 2);
    add_event_handler('delete_categories', 'header_manager_delete_categories');
  }
}

/**
 * Header Manager admin link
 */
function header_manager_admin_menu($menu) 
{
  array_push($menu, array(
    'NAME' => 'Header Manager',
    'URL' => HEADER_MANAGER_ADMIN,
  ));
  return $menu;
}

/**
 * tab on album edition page
 */
function header_manager_tab($sheets, $id)
{
  if ($id == 'album')
  {
    load_language('plugin.lang', HEADER_MANAGER_PATH);
    
    $sheets['headermanager'] = array(
      'caption' => l10n('Banner'),
      'url' => HEADER_MANAGER_ADMIN.'-album&amp;cat_id='.$_GET['cat_id'],
      );
  }
  
  return $sheets;
}

/**
 * updating the plugin
 */
function header_manager_check_version()
{
  global $pwg_loaded_plugins, $page;
  
  if (
    ( 
      @$page['page'] == 'intro' or 
      @$_GET['section'] == 'header_manager/admin.php' 
    )
    and 
    (
      $pwg_loaded_plugins['header_manager']['version'] == 'auto' or
      version_compare($pwg_loaded_plugins['header_manager']['version'], HEADER_MANAGER_VERSION, '<')
    ) 
  )
  {
    include_once(HEADER_MANAGER_PATH . 'include/install.inc.php');
    header_manager_install();
    
    if ($pwg_loaded_plugins['header_manager']['version'] != 'auto')
    {
      $query = '
UPDATE '. PLUGINS_TABLE .'
SET version = "'. HEADER_MANAGER_VERSION .'"
WHERE id = "header_manager"';
      pwg_query($query);
    }
  }
}

?>