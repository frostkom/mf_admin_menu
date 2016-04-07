jQuery(function($)
{
	$('#collapse-menu, #footer-upgrade').remove(); //#screen-meta-links, 
	//#wpfooter should not be removed since it might contain analytics code
	//#wpadminbar should not be removed or hidden because it's needed on mobile devices
});