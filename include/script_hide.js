jQuery(function($)
{
	$('#wpadminbar, #screen-meta-links, #collapse-menu, #footer-upgrade').remove(); //#wpfooter should not be removed since it might contain analytics code

	$('body').append("<a href='" + script_admin_menu.logout_url + "' id='wp_logout'><i class='fa fa-lg fa-power-off'></i></a>");
});