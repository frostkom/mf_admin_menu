<?php
/*
Plugin Name: MF Admin Menu
Plugin URI: https://github.com/frostkom/mf_admin_menu
Description: 
Version: 2.6.9
Author: Martin Fors
Author URI: http://frostkom.se
Text Domain: lang_admin_menu
Domain Path: /lang

GitHub Plugin URI: frostkom/mf_admin_menu
*/

include_once("include/functions.php");

add_action('init', 'init_admin_menu');

if(is_admin())
{
	register_uninstall_hook(__FILE__, 'uninstall_admin_menu');

	add_action('admin_init', 'settings_admin_menu');
	add_action('admin_menu', 'menu_admin_menu', 999);

	load_plugin_textdomain('lang_admin_menu', false, dirname(plugin_basename(__FILE__)).'/lang/');

	function uninstall_admin_menu()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_hide_admin_bar', 'setting_admin_menu_roles'),
		));
	}
}