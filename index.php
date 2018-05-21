<?php
/*
Plugin Name: MF Admin Menu
Plugin URI: https://github.com/frostkom/mf_admin_menu
Description: 
Version: 2.9.11
Licence: GPLv2 or later
Author: Martin Fors
Author URI: http://frostkom.se
Text Domain: lang_admin_menu
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_admin_menu
*/

include_once("include/classes.php");
include_once("include/functions.php");

$obj_admin_menu = new mf_admin_menu();

add_action('init', 'init_admin_menu');

if(is_admin())
{
	register_activation_hook(__FILE__, 'activate_admin_menu');
	register_uninstall_hook(__FILE__, 'uninstall_admin_menu');

	if(is_multisite())
	{
		add_action('admin_bar_menu', 'admin_bar_menu_admin_menu');
	}

	add_action('admin_init', 'settings_admin_menu');
	add_action('admin_init', array($obj_admin_menu, 'admin_init'), 0);
	add_action('admin_menu', 'menu_admin_menu', 999);

	add_action('show_user_profile', 'show_profile_admin_menu');
	add_action('edit_user_profile', 'show_profile_admin_menu');

	load_plugin_textdomain('lang_admin_menu', false, dirname(plugin_basename(__FILE__)).'/lang/');

	function activate_admin_menu()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_hide_admin_bar', 'setting_hide_screen_options', 'setting_show_screen_options'),
		));
	}

	function uninstall_admin_menu()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_sort_sites_a2z', 'setting_show_admin_bar', 'setting_show_public_admin_bar', 'setting_admin_menu_roles'),
		));
	}
}