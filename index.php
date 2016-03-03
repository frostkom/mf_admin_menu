<?php
/*
Plugin Name: MF Admin Menu
Plugin URI: 
Description: 
Version: 2.1.0
Author: Martin Fors
Author URI: http://frostkom.se
*/

if(is_admin())
{
	include_once("include/functions.php");

	add_action('init', 'init_admin_menu');
	add_action('admin_init', 'settings_admin_menu');
	add_action('admin_menu', 'menu_admin_menu', 999);

	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'add_action_admin_menu');
	add_filter('network_admin_plugin_action_links_'.plugin_basename(__FILE__), 'add_action_admin_menu');

	load_plugin_textdomain('lang_admin_menu', false, dirname(plugin_basename(__FILE__)).'/lang/');
}