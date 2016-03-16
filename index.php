<?php
/*
Plugin Name: MF Admin Menu
Plugin URI: 
Description: 
Version: 2.3.0
Author: Martin Fors
Author URI: http://frostkom.se
*/

if(is_admin())
{
	include_once("include/functions.php");

	register_uninstall_hook(__FILE__, 'uninstall_admin_menu');

	add_action('init', 'init_admin_menu');
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