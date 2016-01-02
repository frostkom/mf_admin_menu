<?php

function add_action_admin_menu($links)
{
	$links[] = "<a href='".admin_url('options-general.php?page=settings_mf_base#settings_admin_menu')."'>".__("Settings", 'lang_admin_menu')."</a>";

	return $links;
}

function settings_admin_menu()
{
	$options_page = "settings_mf_base";
	$options_area = "settings_admin_menu";

	add_settings_section(
		$options_area,
		"",
		'settings_admin_menu_callback',
		$options_page
	);

	$arr_settings = array(
		"setting_admin_menu_roles" => __("Show or hide", 'lang_admin_menu'),
	);

	foreach($arr_settings as $handle => $text)
	{
		add_settings_field($handle, $text, $handle."_callback", $options_page, $options_area);

		register_setting($options_page, $handle, 'validate_settings_admin_menu');
	}
}

function settings_admin_menu_callback()
{
	echo "<div id='settings_admin_menu'>&nbsp;</div>
	<a href='#settings_admin_menu'><h3>".__("Admin Menu", 'lang_admin_menu')."</h3></a>";
}

function setting_admin_menu_roles_callback()
{
	global $menu;

	$option = get_option('setting_admin_menu_roles');

	$arr_data = array();

	$arr_data[] = array("", "-- ".__("Default", 'lang_admin_menu')." --");

	$roles = get_all_roles();

	foreach($roles as $key => $value)
	{
		$key = get_role_first_capability($key);

		$arr_data[] = array($key, __($value));
	}

	$arr_data[] = array("none", "-- ".__("None", 'lang_admin_menu')." --");

	echo "<div class='flex_flow tight'>";

		if(count($menu) > 0)
		{
			echo "<table>";

				foreach($menu as $item)
				{
					if($item[0] != '')
					{
						$item_name = strip_tags($item[0]);
						$item_capability = $item[1];
						$item_url = $item[2];

						$option_temp = $item_url.'|'.$item_name;

						if(!(is_array($option) && count($option) > 0 && isset($option[$option_temp])))
						{
							echo "<tr>
								<td>".$item_name."</td>
								<td>"
									.show_select(array('data' => $arr_data, 'name' => "setting_admin_menu_roles[".$option_temp."]", 'compare' => $item_capability))
								."</td>
							</tr>";
						}
					}
				}

			echo "</table>";
		}

		if(is_array($option) && count($option) > 0)
		{
			echo "<table>";

				foreach($option as $key => $value)
				{
					list($item_url, $item_name) = explode('|', $key);

					echo "<tr>
						<td>".$item_name."</td>
						<td>"
							.show_select(array('data' => $arr_data, 'name' => "setting_admin_menu_roles[".$key."]", 'compare' => $value))
						."</td>
					</tr>";
				}

			echo "</table>";
		}

	echo "</div>";
}

function validate_settings_admin_menu($page_options)
{
	foreach($page_options as $key => $value)
	{
		if($value == "")
		{
			unset($page_options[$key]);
		}
	}

    return $page_options;
}

function menu_admin_menu()
{
	$option = get_option('setting_admin_menu_hidden');

	if(is_array($option) && count($option) > 0)
	{
		foreach($option as $key => $value)
		{
			if($value == "on")
			{
				list($item_url, $item_name) = explode('|', $key);

				remove_menu_page($item_url);
			}
		}
	}

	$option = get_option('setting_admin_menu_roles');

	if(is_array($option) && count($option) > 0)
	{
		foreach($option as $key => $value)
		{
			if($value == "none" || !current_user_can($value))
			{
				list($item_url, $item_name) = explode('|', $key);

				remove_menu_page($item_url);
			}
		}
	}
}