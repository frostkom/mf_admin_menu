<?php
/*
Plugin Name: MF Admin Menu
Plugin URI: https://github.com/frostkom/mf_admin_menu
Description:
Version: 2.9.37
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_admin_menu
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_admin_menu
*/

if(!function_exists('is_plugin_active'))
{
	require_once(ABSPATH."wp-admin/includes/plugin.php");
}

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	$obj_admin_menu = new mf_admin_menu();

	add_action('init', array($obj_admin_menu, 'init'));

	if(is_admin())
	{
		register_activation_hook(__FILE__, 'activate_admin_menu');
		register_uninstall_hook(__FILE__, 'uninstall_admin_menu');

		/*if(is_multisite())
		{
			add_action('admin_bar_menu', array($obj_admin_menu, 'admin_bar_menu'));
		}*/

		add_action('admin_init', array($obj_admin_menu, 'settings_admin_menu'));
		add_action('admin_init', array($obj_admin_menu, 'admin_init'), 0);
		add_action('admin_menu', array($obj_admin_menu, 'admin_menu'), 999);

		add_action('show_user_profile', array($obj_admin_menu, 'edit_user_profile'));
		add_action('edit_user_profile', array($obj_admin_menu, 'edit_user_profile'));

		function activate_admin_menu()
		{
			mf_uninstall_plugin(array(
				'options' => array('setting_sort_sites_a2z', 'setting_hide_admin_bar', 'setting_hide_screen_options', 'setting_show_screen_options', 'setting_show_admin_bar'),
			));
		}

		function uninstall_admin_menu()
		{
			mf_uninstall_plugin(array(
				'options' => array('setting_show_public_admin_bar', 'setting_admin_menu_roles'),
			));
		}
	}
}