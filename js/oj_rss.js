/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

selectable.category = "false";
selectable.group = "false";
selectable.item = "false";

content_show_functions["rss"] = function(ojid)
{
	console.log("content show rss " + ojid);
	var parentid = 0;
	var el = jQuery("#oj-panel-heading-" + ojid);
//	var tocheck = ojid
	if (el.length > 0)
	{
//		parentid = oj_extract_id(jQuery(el).parent("ul").attr("id"));
		parentid = oj_extract_id(jQuery(el).parent("div").parent("div").attr("id"));
		if (el.hasClass("oj-rss-notviewed"))
		{
			el.removeClass("oj-rss-notviewed");
			el.addClass("oj-rss-viewed");
		}
		jQuery(".filter-label-text").removeClass("oj-selected");
		jQuery("#filter-label-text-" + ojid).addClass("oj-selected");
//		tocheck = parentid;
	}
	var url = get_ajax_url() + "&action=attributes&ojid=" + ojid + "&parent=" + parentid;
	$('#oj-display-panel').load(url, function()
	{
		console.debug("ajax attribute load completed"/*, data*/);
		if (jQuery("#oj-panel-heading-" + ojid).hasClass("filter-class-item"))
		{
//			var fid = oj_extract_id(jQuery("#filter-menu-value-" + ojid).parent("ul").attr("id"));
			var fid = oj_extract_id(jQuery("#oj-panel-heading-" + ojid).parent("div").attr("id"));
			check_containing_feed(fid);
		}
		else
		{
			jQuery("#filter-menu-value-" + ojid + " .filter-class-category").each(function ()
			{
				var fid = oj_extract_id(jQuery(this).attr("id"));
				check_containing_feed(fid);
			});
			jQuery("#filter-menu-value-" + ojid + " .filter-class-group").each(function ()
			{
				var fid = oj_extract_id(jQuery(this).attr("id"));
				check_containing_feed(fid);
			});
		}
	});
};

context_menu_options['rss'] = {
//	item : {
//		groups: [["addplay"]],
//		actions: {
//			addplay: {
//				name: 'Add to playlist',
//				onClick: function(itm) {
//					jQuery('#oj-select-entity-data').val(itm);
//					addto_playlist();
//					// run when the action is clicked
//				}
//			}
//		}
//	},
	group: {
		hidden: ["newsubgrp", "newitem"],
		groups: [["reload", "markread"]],
		actions: {
			reload: {
				name: 'Reload',
				onClick: function(itm)
				{
					oj_reloadrss(itm);
				}
			},
			markread: {
				name: 'Mark as Read',
				onClick: function(itm)
				{
					var ajaxdata =
					{
						action: "rssmarkread",
						catalog: "RSS",
						user: user,
						ojhost:oj_get_ojhost(),
						feed: itm
					};
					var url = get_base_url() + "/ojAjax.php";
					jQuery.post(url, ajaxdata, function(returndata)
					{
						console.debug("rssmarkread returns " + returndata);
						jQuery('#filter-menu-list-' + itm + " div.filter-class-item").removeClass("oj-rss-notviewed").addClass("oj-rss-viewed");
						check_containing_feed(itm);
					});
				}
			}
		}
	},
	category: {
		hidden: ["newgroup", "newitem"],
		groups: [["reload", "markread"]],
		actions: {
			reload: {
				name: 'Reload',
				onClick: function(itm)
				{
					var ajaxdata =
					{
						action: "rssreload",
						catalog: "RSS",
						user: user,
						ojhost:oj_get_ojhost(),
						feed: itm
					};
					var url = get_base_url() + "/ojAjax.php";
					jQuery.post(url, ajaxdata, function(returndata)
					{
						console.debug("rssreload returns " + returndata);
						thefilter.clear_values(itm);
						stop_please_wait();
						thefilter.open_category(itm);
						check_containing_feed(itm);
					});
					please_wait("reloading " + thefilter.get_text());
				}
			},
			markread: {
				name: 'Mark as Read',
				onClick: function(itm)
				{
					var ajaxdata =
					{
						action: "rssmarkread",
						catalog: "RSS",
						user: user,
						ojhost:oj_get_ojhost(),
						feed: itm
					};
					var url = get_base_url() + "/ojAjax.php";
					jQuery.post(url, ajaxdata, function(returndata)
					{
						console.debug("rssmarkread returns " + returndata);
						jQuery('#filter-menu-list-' + itm + " div.filter-class-item").removeClass("oj-rss-notviewed").addClass("oj-rss-viewed");
						check_containing_feed(itm);
					});
				}
			}
		}
	}
};

function oj_reloadrss(itm)
{
	var ajaxdata =
	{
		action: "rssreload",
		catalog: "RSS",
		user: user,
		ojhost:oj_get_ojhost()
	};
	if ((typeof itm !== 'undefined') && itm)
	{
		ajaxdata["feed"] = itm;
	}
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
		console.debug("reload rss", returndata);
		if ((typeof itm !== 'undefined') && itm)
		{
			console.debug("rssreload returns " + returndata);
			thefilter.clear_values(itm);
			thefilter.open_category(itm);
			check_containing_feed(itm);
		}
		else
		{
			thefilter.clear_all_categories();
			check_categories();
		}
		stop_please_wait();
	});
	please_wait("reloading " + thefilter.get_text());

}

function check_containing_feed(feedid)
{
	var ret = false;
//	console.debug("check_containing_feed", feedid);
//	if (jQuery("#filter-menu-" + feedid).hasClass('filter-class-group'))
//	{
//		console.debug("check_containing_feed group", feedid);
		var unread = jQuery('#filter-menu-list-' + feedid + " div.oj-rss-notviewed").length;
//		console.debug("unread", unread, "feed", feedid);
		if (unread > 0)
		{
			jQuery("#filter-menu-value-" + feedid + ">div.oj-panel-heading").addClass("has-unread");
		}
		else
		{
			var ajaxdata =
			{
				action: "rssunread",
				catalog: "RSS",
				user: user,
				ojhost:oj_get_ojhost(),
				feed: feedid
			};
			var url = get_base_url() + "/ojAjax.php";
			jQuery.post(url, ajaxdata, function(returndata1)
			{
				var returndata = JSON.parse(returndata1);
				var num = parseInt(returndata[feedid.toString()]);
//				console.debug("rssunread returns", num, returndata, feedid.toString(), returndata[feedid.toString()]);
				if (num > 0)
				{
					jQuery("#filter-menu-value-" + feedid + ">div.oj-panel-heading").addClass("has-unread");
					ret = true;
				}
				else
				{
					jQuery("#filter-menu-value-" + feedid + ">div.oj-panel-heading").removeClass("has-unread");
				}
				check_categories();
			});
		}
//	}
//	else if (jQuery("#filter-menu-" + feedid).hasClass('filter-class-category'))
//	{
//		console.debug("check_containing_feed category", feedid, jQuery("#filter-menu-list-" + feedid + " div.filter-class-group").length);
//		jQuery("#filter-menu-list-" + feedid + " div.filter-class-entity").each(function()
//		{
//			console.debug("check_containing_feed found group");
//			var fid = oj_extract_id(jQuery(this).attr("id"));
//			console.debug("check_containing_feed found", fid);
//			ret = check_containing_feed(fid) || ret;
//		});
//		if (ret)
//		{
//			jQuery("#filter-menu-" + feedid + ">div.oj-panel-heading").addClass("has-unread");
//		}
//		else
//		{
//			jQuery("#filter-menu-" + feedid + ">div.oj-panel-heading").removeClass("has-unread");
//		}
//	}
	return ret;
}

function check_categories()
{
	jQuery('.filter-class-category').each(function()
	{
		var thisid = oj_extract_id(jQuery(this).attr("id"));
		var ajaxdata =
		{
			action: "rssunread",
			catalog: "RSS",
			user: user,
			ojhost:oj_get_ojhost(),
			feed: thisid
		};
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata1)
		{
			var returndata = JSON.parse(returndata1);
			var num = parseInt(returndata[thisid.toString()]);
//			console.debug("check_categories rssunread", thisid, "returns", num, returndata, thisid.toString(), returndata[thisid.toString()]);
			if (num > 0)
			{
				jQuery("#filter-menu-value-" + thisid + ">div.oj-panel-heading").addClass("has-unread");
				ret = true;
			}
			else
			{
				jQuery("#filter-menu-value-" + thisid + ">div.oj-panel-heading").removeClass("has-unread");
			}
		});
//		console.debug("check category", thisid);
//		if (jQuery(this).children("div.panel-collapse").find(".has-unread").length > 0)
//		{
//			jQuery(this).children("div.oj-panel-heading").addClass("has-unread");
//			console.debug("check category found");
//		}
//		else
//		{
//			console.debug("check category not found");
//			var is_open = jQuery("#filter-menu-list-"+ thisid).children("li").length > 0;
//			if (is_open)
//			{
//				jQuery(this).children("div.oj-panel-heading").removeClass("has-unread");
//			}
//			else
//			{
//				var ajaxdata =
//				{
//					action: "rssunread",
//					catalog: "RSS",
//					user: user,
//					ojhost:oj_get_ojhost(),
//					feed: thisid
//				};
//				var url = get_base_url() + "/ojAjax.php";
//				jQuery.post(url, ajaxdata, function(returndata)
//				{
//					console.debug("check_categories rssunread", thisid, "returns", returndata);
//					if (returndata > 0)
//					{
//						jQuery("#filter-menu-" + thisid + ">div.oj-panel-heading").addClass("has-unread");
//						ret = true;
//					}
//					else
//					{
//						jQuery("#filter-menu-" + thisid + ">div.oj-panel-heading").removeClass("has-unread");
//					}
//				});
//			}
//		}
	});
}

init_functions.push(function()
{
	console.debug("rss init");
	jQuery("div.filter-class-entity").each(function()
	{
		var id = oj_extract_id(jQuery(this).attr("id"));
		console.debug("rss init check:", id);
		check_containing_feed(id);
	});
	jQuery("#oj-main-nav").after('<div class="oj-button-group" id="oj-button-group-feeds">' +
					'<p class="oj-button-label" id="oj-button-label-feeds">feeds</p>' +
					'<ul class="nav navbar-nav">' +
					'<li><p class="navbar-btn"><a target="_self" href="#" class="btn btn-default btn-sm" onclick="oj_reloadrss();">Reload</a></p></li>' +
					'</ul></div>');
});

collapse_show_functions.push(function ()
{
	console.debug("rss collapse show");
	jQuery("div.filter-class-group").each(function()
	{
		var id = oj_extract_id(jQuery(this).attr("id"));
		console.debug("rss collapse", id);
		check_containing_feed(id);
	});
});