jQuery(function($)
{
	$('#admin_menu_roles').on('blur', '.form_textfield input', function()
	{
		var dom_obj = $(this),
			dom_name = dom_obj.val(),
			dom_url = dom_obj.parent('.form_textfield').siblings('input').val()
			dom_select = dom_obj.parent('.form_textfield').siblings('.form_select').children('select');

		dom_select.attr('name', dom_select.attr('id') + "[" + dom_url + "|" + dom_name + "]").val('custom_name');
	});
});