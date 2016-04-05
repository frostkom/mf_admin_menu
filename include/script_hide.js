jQuery(function($)
{
	$('#screen-meta-links, #collapse-menu, #footer-upgrade').remove();
	//#wpfooter should not be removed since it might contain analytics code
	//#wpadminbar should not be removed or hidden because it's needed on mobile devices
});