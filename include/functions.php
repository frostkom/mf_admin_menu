<?php

function admin_bar_admin_menu()
{
	global $wp_admin_bar;

	//$wp_admin_bar->remove_menu('menu-toggle');
	$wp_admin_bar->remove_menu('wp-logo');

	if(is_multisite())
	{
		//$wp_admin_bar->remove_menu('my-sites');
		$wp_admin_bar->remove_menu('site-name');
	}

	else
	{
		$wp_admin_bar->remove_menu('live');
		$wp_admin_bar->remove_menu('updates');
	}

	$wp_admin_bar->remove_menu('view');
	$wp_admin_bar->remove_menu('comments');
	$wp_admin_bar->remove_menu('new-content');
	//$wp_admin_bar->remove_menu('my-account');
}

function screen_options_admin_menu($display_boolean, $wp_screen_object)
{
	/*$blacklist = array('post.php', 'post-new.php', 'index.php', 'edit.php');

	if(in_array($GLOBALS['pagenow'], $blacklist))
	{*/
		$wp_screen_object->render_screen_layout();
		$wp_screen_object->render_per_page_options();

		return false;
    /*}
	return true;*/
}

function help_tabs_admin_menu()
{
	$screen = get_current_screen();
	$screen->remove_help_tabs();
}

function init_admin_menu()
{
	if(get_current_user_id() > 0)
	{
		$option = get_option_or_default('setting_show_admin_bar', get_option('setting_hide_admin_bar'));

		if($option != '' && $option != 'yes' && ($option == "no" || $option == "none" || !current_user_can($option)))
		{
			add_action('wp_before_admin_bar_render', 'admin_bar_admin_menu'); 

			wp_enqueue_style('style_admin_menu', plugin_dir_url(__FILE__)."style_hide.css");
		}

		$option = get_option('setting_show_public_admin_bar');

		if($option != '' && $option != 'yes' && ($option == "no" || $option == "none" || !current_user_can($option)))
		{
			add_filter('show_admin_bar', '__return_false');
		}

		$option = get_option_or_default('setting_show_screen_options', get_option('setting_hide_screen_options'));

		if($option != '' && $option != 'yes' && ($option == "no" || $option == "none" || !current_user_can($option)))
		{
			add_filter('screen_options_show_screen', 'screen_options_admin_menu', 10, 2);
			add_action('wp_before_admin_bar_render', 'help_tabs_admin_menu'); 
		}
	}
}

function show_profile_admin_menu($user)
{
	$option = get_option('setting_show_public_admin_bar');

	if($option != '' && $option != 'yes' && ($option == "no" || $option == "none" || !current_user_can($option)))
	{
		mf_enqueue_script('script_users', plugin_dir_url(__FILE__)."script_hide.js", $arr_remove);
	}
}

function get_settings_roles($data)
{
	if(!isset($data['yes'])){			$data['yes'] = false;}
	if(!isset($data['no'])){			$data['no'] = false;}
	if(!isset($data['default'])){		$data['default'] = false;}
	if(!isset($data['custom_name'])){	$data['custom_name'] = false;}
	if(!isset($data['none'])){			$data['none'] = false;}

	$arr_data = array();

	if($data['yes'] == true)
	{
		$arr_data["yes"] = "-- ".__("Yes", 'lang_admin_menu')." --";
	}

	if($data['default'] == true)
	{
		$arr_data[''] = "-- ".__("Default", 'lang_admin_menu')." --";
	}

	$arr_data = get_roles_for_select(array('array' => $arr_data, 'add_choose_here' => false));

	if($data['no'] == true)
	{
		$arr_data["no"] = "-- ".__("No", 'lang_admin_menu')." --";
	}

	if($data['none'] == true)
	{
		$arr_data["none"] = "-- ".__("None", 'lang_admin_menu')." --";
	}

	if($data['custom_name'] == true)
	{
		$arr_data["custom_name"] = "-- ".__("Custom Name", 'lang_admin_menu')." --";
	}

	return $arr_data;
}

function settings_admin_menu()
{
	$options_area = __FUNCTION__;

	add_settings_section($options_area, "", $options_area."_callback", BASE_OPTIONS_PAGE);

	$arr_settings = array(
		'setting_show_admin_bar' => __("Show admin bar", 'lang_admin_menu'),
		'setting_show_public_admin_bar' => __("Show public admin bar", 'lang_admin_menu'),
		'setting_show_screen_options' => __("Show screen options", 'lang_admin_menu'),
		'setting_admin_menu_roles' => __("Show or hide", 'lang_admin_menu'),
	);

	foreach($arr_settings as $handle => $text)
	{
		add_settings_field($handle, $text, $handle."_callback", BASE_OPTIONS_PAGE, $options_area);

		register_setting(BASE_OPTIONS_PAGE, $handle, 'validate_settings_admin_menu');
	}
}

function settings_admin_menu_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);

	echo settings_header($setting_key, __("Admin Menu", 'lang_admin_menu'));
}

function setting_show_admin_bar_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option_or_default($setting_key, get_option('setting_hide_admin_bar', 'yes'));

	echo show_select(array('data' => get_settings_roles(array('yes' => true, 'no' => true)), 'name' => $setting_key, 'value' => $option));
}

function setting_show_public_admin_bar_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key, 'yes');

	echo show_select(array('data' => get_settings_roles(array('yes' => true, 'no' => true)), 'name' => $setting_key, 'value' => $option));
}

function setting_show_screen_options_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option_or_default($setting_key, get_option('setting_hide_screen_options', 'yes'));

	echo show_select(array('data' => get_settings_roles(array('yes' => true, 'no' => true)), 'name' => $setting_key, 'value' => $option));
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

	return "<div class='flex_flow tight'>"
		.($item_parent == false ? "" : "<div> - </div>")
		.show_textfield(array('value' => $item_name))
		.input_hidden(array('value' => $item_url))
		.show_select(array('data' => $data['array'], 'name' => "setting_admin_menu_roles[".$data['key']."]", 'value' => $data['capability']))
	."</div>";
}

function setting_admin_menu_roles_callback()
{
	global $menu, $submenu;

	mf_enqueue_script('script_admin_menu_wp', plugin_dir_url(__FILE__)."script_wp.js");

	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_data = get_settings_roles(array('default' => true, 'custom_name' => true, 'none' => true));

	echo "<div id='admin_menu_roles'>";

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
					$item_name = trim(str_replace($update_count, "", $item[0]));

					$item_capability = $item[1];
					$item_url = $item[2];

					$item_key = $item_url.'|'.$item_name;

					if(!(is_array($option) && count($option) > 0 && isset($option[$item_key])))
					{
						echo parse_role_select(array('array' => $arr_data, 'key' => $item_key, 'capability' => $item_capability));

						/*foreach($submenu[$item_url] as $subkey => $subitem)
						{
							$subitem_name = $subitem[0];
							$subitem_url = $subitem[2];

							if($subitem_url != $item_url)
							{
								$subitem_key = $item_url.'|'.$subitem_url.'|'.$subitem_name;
								$subitem_capability = $subitem[1];

								echo parse_role_select(array('array' => $arr_data, 'key' => $subitem_key, 'capability' => $subitem_capability));
							}
						}*/
					}
				}
			}
		}

		if(is_array($option) && count($option) > 0)
		{
			foreach($option as $item_key => $item_capability)
			{
				echo parse_role_select(array('array' => $arr_data, 'key' => $item_key, 'capability' => $item_capability));
			}
		}

	echo "</div>";
}

function validate_settings_admin_menu($page_options)
{
	if(is_array($page_options))
	{
		foreach($page_options as $key => $value)
		{
			if($value == "")
			{
				unset($page_options[$key]);
			}
		}
	}

    return $page_options;
}

function menu_admin_menu()
{
	global $menu, $submenu;

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
									$menu_name = trim(str_replace($update_count, "", $item[0]));

									if($item_name != $menu_name)
									{
										$menu[$key][0] = $item_name." ".$update_count;
									}
								}
							}

							else
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