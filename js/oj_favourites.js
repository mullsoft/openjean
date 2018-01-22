// Catalog: favourites
selectable.category = "false";
selectable.group = "false";
selectable.item = "false";

content_show_functions["favourites"] = function(ojid)
{
	var parentid = 0;
	var el = jQuery("#filter-menu-value-" + ojid);
	if (el.length > 0)
	{
		parentid = oj_extract_id(jQuery(el).parent("div").attr("id"));
	}
	var url = get_ajax_url() + "&action=attributes&ojid=" + ojid + "&parent=" + parentid;
	console.debug("favourites show " + url);
	$('#oj-display-panel').load(url, function()
	{
		console.debug("favourites show loaded");
		jQuery(".filter-label-text").removeClass('oj-selected');
		jQuery("#filter-label-text-" + ojid).addClass('oj-selected');
//		var img = jQuery('img.oj-image:first');
//		if (img.length > 0)
//		{
//			image_urls[ojid] = jQuery(img).attr("src");
//		}
//		console.debug("image_urls", image_urls);
		window.console && console.log("ajax attribute load completed");
	});
};

new_entity_extras['favourites', 'item'] = function ()
{
	console.debug("extras");
	jQuery("#oj-new-item-name").after('<button id="oj-get-title-button" class="btn btn-default btn-rounded btn-sm" onclick="oj_get_title();">Find Title</button>');
};

function oj_get_title()
{
	var e = jQuery('#site-link-3').val();
	if (e)
	{
		e = e.trim();
		if (e)
		{
			var ajaxdata = {
				user:user,
				catalog:catalog,
				index:index,
				ojhost:oj_get_ojhost(),
				ojmode:ojmode,
				action:"gettitle",
				url:e
			};
			var url = get_base_url() + "/ojAjax.php";
			jQuery.post(url, ajaxdata, function(returndata)
			{
				jQuery("#oj-new-item-name").val(returndata);
				jQuery("#oj-new-item-name").change();
				console.debug("contents", returndata);
			});
		}
	}
}