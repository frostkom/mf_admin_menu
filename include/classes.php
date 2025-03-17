<?php

class mf_admin_menu
{
	function __construct(){}

	function check_option($option)
	{
		return ($option != '' && $option != 'yes' && (in_array($option, array('no', 'none')) || !current_user_can($option)));
	}

	function get_settings_roles_for_select($data)
	{
		if(!isset($data['yes'])){			$data['yes'] = false;}
		if(!isset($data['no'])){			$data['no'] = false;}
		if(!isset($data['default'])){		$data['default'] = false;}
		if(!isset($data['custom_name'])){	$data['custom_name'] = false;}
		if(!isset($data['none'])){			$data['none'] = false;}

		$arr_data = array();

		if($data['yes'] == true)
		{
			$arr_data['yes'] = "-- ".__("Yes", 'lang_admin_menu')." --";
		}

		if($data['default'] == true)
		{
			$arr_data[''] = "-- ".__("Default", 'lang_admin_menu')." --";
		}

		$arr_data = get_roles_for_select(array('array' => $arr_data, 'add_choose_here' => false));

		if($data['no'] == true)
		{
			$arr_data['no'] = "-- ".__("No", 'lang_admin_menu')." --";
		}

		if($data['none'] == true)
		{
			$arr_data['none'] = "-- ".__("None", 'lang_admin_menu')." --";
		}

		if($data['custom_name'] == true)
		{
			$arr_data['custom_name'] = "-- ".__("Custom Name", 'lang_admin_menu')." --";
		}

		return $arr_data;
	}

	function parse_role_select($data)
	{
		$arr_item = explode('|', $data['key']);

		if(count($arr_item) == 2)
		{
			$item_parent = false;
			$item_url = $arr_item[0];
			$item_name = $arr_item[1];
		}

		else
		{
			$item_parent = $arr_item[0];
			$item_url = $arr_item[1];
			$item_name = $arr_item[2];
		}

		/*$item_name = strip_tags($item_name);
		$item_name = htmlspecialchars_decode($item_name);
		$item_name = strip_tags($item_name);
		$item_name = trim($item_name);*/

		if(strpos($item_url, "post_type=page&post_title=") === false) // Ignore setting_theme_core_templates
		{
			return "<li class='flex_flow tight".($item_parent == false ? "" : " child")."'>"
				.show_textfield(array('value' => $item_name))
				.input_hidden(array('value' => $item_url))
				.show_select(array('data' => $data['array'], 'name' => 'setting_admin_menu_roles['.$data['key'].']', 'value' => $data['capability']))
			."</li>";
		}
	}

	function init()
	{
		load_plugin_textdomain('lang_admin_menu', false, str_replace("/include", "", dirname(plugin_basename(__FILE__)))."/lang/");

		if(is_user_logged_in())
		{
			if($this->check_option(get_site_option('setting_show_public_admin_bar')))
			{
				add_filter('show_admin_bar', '__return_false');
			}
		}
	}

	function settings_callback($page_options)
	{
		if(is_array($page_options))
		{
			foreach($page_options as $key => $value)
			{
				if($value == '')
				{
					unset($page_options[$key]);
				}
			}
		}

		return $page_options;
	}

	function settings_admin_menu()
	{
		if(IS_SUPER_ADMIN)
		{
			$options_area = __FUNCTION__;

			add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

			$arr_settings = array();

			/*if(is_multisite())
			{
				$arr_settings['setting_sort_sites_a2z'] = __("Sort Sites in Alphabetical Order", 'lang_admin_menu');
			}*/

			$arr_settings['setting_show_public_admin_bar'] = __("Show Public Admin Bar", 'lang_admin_menu');
			$arr_settings['setting_admin_menu_roles'] = __("Show or hide", 'lang_admin_menu');

			show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings, 'callback' => array($this, 'settings_callback')));
		}
	}

	function settings_admin_menu_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Admin Menu", 'lang_admin_menu'));
	}

	function setting_show_public_admin_bar_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		settings_save_site_wide($setting_key);
		$option = get_site_option($setting_key, get_option($setting_key, 'no'));

		echo show_select(array('data' => $this->get_settings_roles_for_select(array('yes' => true, 'no' => true)), 'name' => $setting_key, 'value' => $option));
	}

	function setting_admin_menu_roles_callback()
	{
		global $menu, $submenu;

		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = $this->get_settings_roles_for_select(array('default' => true, 'custom_name' => true, 'none' => true));

		$arr_parent_items = array();

		if(count($menu) > 0)
		{
			if(!in_array('profile.php', $menu))
			{
				$menu[71] = array(
					0 => __("Profile", 'lang_admin_menu'),
					1 => 'read',
					2 => 'profile.php',
				);
			}

			foreach($menu as $item)
			{
				if($item[0] != '')
				{
					$update_count = get_match("/(\<span.*\<\/span\>)/is", $item[0], false);

					if($update_count != '')
					{
						$item_name = trim(strip_tags(str_replace($update_count, "", $item[0])));

						if($item_name != '')
						{
							$item_capability = $item[1];
							$item_url = $item[2];

							$item_key = $item_url.'|'.$item_name;

							if(!(is_array($option) && count($option) > 0 && isset($option[$item_key])))
							{
								$arr_parent_items[$item_url][$item_url] = array('key' => $item_key, 'capability' => $item_capability);

								if(isset($submenu[$item_url]) && is_array($submenu[$item_url]))
								{
									foreach($submenu[$item_url] as $subkey => $subitem)
									{
										$subitem_name = trim(strip_tags($subitem[0]));

										if($subitem_name != '')
										{
											$subitem_url = $subitem[2];

											if($subitem_url != $item_url)
											{
												$subitem_key = $item_url.'|'.$subitem_url.'|'.$subitem_name;
												$subitem_capability = $subitem[1];

												$arr_parent_items[$item_url][$subitem_url] = array('key' => $subitem_key, 'capability' => $subitem_capability);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if(is_array($option) && count($option) > 0)
		{
			foreach($option as $item_key => $item_capability)
			{
				$arr_item_key = explode('|', $item_key);

				$arr_parent_items[$arr_item_key[0]][$arr_item_key[1]] = array('key' => $item_key, 'capability' => $item_capability);
			}
		}

		echo "<ul id='admin_menu_roles'>";

			foreach($arr_parent_items as $parent_key => $arr_items)
			{
				foreach($arr_items as $arr_item)
				{
					echo $this->parse_role_select(array('array' => $arr_data, 'key' => $arr_item['key'], 'capability' => $arr_item['capability']));
				}
			}

		echo "</ul>";
	}

	function admin_init()
	{
		global $wpdb;

		if(IS_SUPER_ADMIN)
		{
			$plugin_include_url = plugin_dir_url(__FILE__);

			mf_enqueue_style('style_admin_menu_wp', $plugin_include_url."style_wp.css");
			mf_enqueue_script('script_admin_menu_wp', $plugin_include_url."script_wp.js", array('blogid' => $wpdb->blogid));
		}
	}

	function admin_menu()
	{
		global $menu, $submenu;

		if(!isset($menu[81]))
		{
			$menu[81] = array('', 'read', 'separator3', '', 'wp-menu-separator');
		}

		remove_submenu_page("index.php", "my-sites.php");

		/*if(!IS_SUPER_ADMIN)
		{
			remove_submenu_page("tools.php", "export.php");
			remove_submenu_page("tools.php", "import.php");
		}*/

		$option = get_option('setting_admin_menu_roles');

		if(is_array($option) && count($option) > 0)
		{
			foreach($option as $key => $value)
			{
				$arr_item = explode('|', $key);

				if(count($arr_item) == 2)
				{
					$item_parent = false;
					$item_url = $arr_item[0];
					$item_name = $arr_item[1];
				}

				else
				{
					$item_parent = $arr_item[0];
					$item_url = $arr_item[1];
					$item_name = $arr_item[2];
				}

				if($value != "custom_name" && ($value == "none" || !current_user_can($value)))
				{
					if($item_parent == false)
					{
						remove_menu_page($item_url);
					}

					else
					{
						remove_submenu_page($item_parent, $item_url);
					}
				}

				else if($value != "")
				{
					if(count($menu) > 0)
					{
						foreach($menu as $key => $item)
						{
							if($item[0] != '')
							{
								$menu_url = $item[2];

								if($item_parent == false)
								{
									if($item_url == $menu_url)
									{
										$update_count = get_match("/(\<span.*\<\/span\>)/is", $item[0], false);

										if($update_count != '')
										{
											$menu_name = trim(str_replace($update_count, "", $item[0]));

											if($item_name != $menu_name)
											{
												$menu[$key][0] = $item_name." ".$update_count;
											}
										}
									}
								}

								else
								{
									if(isset($submenu[$menu_url]) && is_array($submenu[$menu_url]))
									{
										foreach($submenu[$menu_url] as $subkey => $subitem)
										{
											$subitem_name = $subitem[0];
											$subitem_url = $subitem[2];

											if($item_url == $subitem_url)
											{
												$submenu[$subkey][0] = $item_name;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	function edit_user_profile($user)
	{
		$option = get_site_option('setting_show_public_admin_bar');

		if($this->check_option($option))
		{
			mf_enqueue_script('script_admin_menu', plugin_dir_url(__FILE__)."script_profile.js", get_plugin_version(__FILE__)); //Should be moved to admin_init?
		}
	}
}