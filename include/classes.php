<?php

class mf_admin_menu
{
	function __construct()
	{

	}

	function admin_init()
	{
		global $wpdb;

		if(IS_SUPER_ADMIN)
		{
			$plugin_include_url = plugin_dir_url(__FILE__);
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_style('style_admin_menu_wp', $plugin_include_url."style_wp.css", $plugin_version);
			mf_enqueue_script('script_admin_menu_wp', $plugin_include_url."script_wp.js", array('blogid' => $wpdb->blogid), $plugin_version);
		}
	}
}