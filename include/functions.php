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

	$wp_admin_bar->remove_menu('view');
	$wp_admin_bar->remove_menu('updates');
	$wp_admin_bar->remove_menu('comments');
	$wp_admin_bar->remove_menu('new-content');
	//$wp_admin_bar->remove_menu('my-account');

	/*$screen = get_current_screen();
	$screen->remove_help_tabs();*/
}

function init_admin_menu()
{
	if(get_current_user_id() > 0)
	{
		$option = get_option('setting_hide_admin_bar');

		if($option != '' && $option != 'yes' && ($option == "no" || $option == "none" || !current_user_can($option)))
		{
			add_filter('show_admin_bar', '__return_false');

			add_action('wp_before_admin_bar_render', 'admin_bar_admin_menu'); 

			wp_enqueue_style('style_admin_menu', plugin_dir_url(__FILE__)."style_hide.css");
			//mf_enqueue_script('script_admin_menu', plugin_dir_url(__FILE__)."script_hide.js", array('logout_url' => wp_logout_url()));
		}
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
		"setting_hide_admin_bar" => __("Show admin bar", 'lang_admin_menu'),
		"setting_admin_menu_roles" => __("Show or hide", 'lang_admin_menu'),
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

function setting_hide_admin_bar_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_data = get_settings_roles(array('yes' => true, 'no' => true));

	echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'compare' => $option));
}

function setting_admin_menu_roles_callback()
{
	global $menu;

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
					0 => __('Profile', 'lang_admin_menu'),
					1 => 'read',
					2 => 'profile.php',
				);
			}

			foreach($menu as $item)
			{
				if($item[0] != '')
				{
					//$item_name = strip_tags($item[0]);
					//$item_name = trim(preg_replace("/(\<span(.*)\<\/span\>)/is", "", $item[0]));
					$update_count = get_match("/(\<span.*\<\/span\>)/is", $item[0], false);
					$item_name = trim(str_replace($update_count, "", $item[0]));

					$item_capability = $item[1];
					$item_url = $item[2];

					$option_temp = $item_url.'|'.$item_name;

					if(!(is_array($option) && count($option) > 0 && isset($option[$option_temp])))
					{
						echo "<div class='flex_flow tight'>"
							.show_textfield(array('value' => $item_name))
							.input_hidden(array('value' => $item_url))
							.show_select(array('data' => $arr_data, 'name' => "setting_admin_menu_roles[".$option_temp."]", 'compare' => $item_capability))
						."</div>";
					}
				}
			}
		}

		if(is_array($option) && count($option) > 0)
		{
			foreach($option as $key => $value)
			{
				list($item_url, $item_name) = explode('|', $key);

				echo "<div class='flex_flow tight'>"
					.show_textfield(array('value' => $item_name))
					.input_hidden(array('value' => $item_url))
					.show_select(array('data' => $arr_data, 'name' => "setting_admin_menu_roles[".$key."]", 'compare' => $value))
				."</div>";
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
	global $menu;

	$option = get_option('setting_admin_menu_roles');

	if(is_array($option) && count($option) > 0)
	{
		foreach($option as $key => $value)
		{
			list($item_url, $item_name) = explode('|', $key);

			if($value != "custom_name" && ($value == "none" || !current_user_can($value)))
			{
				remove_menu_page($item_url);
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
					}
				}
			}
		}
	}
}