var selectfilter;
var nobeforeunload = false;
var new_entity_extras = {};
var category_attributes;
var group_attributes;
var item_attributes;
var changing = false;
var collapse_show_functions = [];
var content_show_functions = [];
var import_entity_checks = {};
var import_entity_show_functions = {};
var set_number_of_links_out_functions = {};
var init_functions = [];
var radio_status = {};
var category_options = {};
var context_menu_options = {};
var image_urls = {};
var selectable = {
	category:"default",
	group:"default",
	item:"default"
};
var select_entity_functions = {};
var search_exclude;
var fcats;
var fscats;
var gcats; // for selecting groups
var other_cats = {}; // any other catalog filter categories
var other_selectable_cats = {};
var other_filter_catalog_categories = {};
var select_categories = {};
var thefilter;
var subtypes = {
	category:"",
	group:""
};
var entity_names = {
	category: "category",
	group:"group",
	item: "item"
};
var enumerations = {};
var logicals;
var $userrole;
var selected_panel = null;
var current_panel = null;

function stringStartsWith (string, prefix)
{
    return string.slice(0, prefix.length) == prefix;
}

function stringStartsWithIgnoreCase(string, prefix)
{
	return stringStartsWith(string.toLowerCase(), prefix.toLowerCase());
}

function stringEndsWith (string, suffix)
{
    return suffix == '' || string.slice(-suffix.length) == suffix;
}

function stringEndsWithIgnoreCase(string, suffix)
{
	return stringEndsWith(string.toLowerCase(), suffix.toLowerCase());
}

/**
 * Convert a string to HTML entities
 */
String.prototype.toHtmlEntities = function() {
    return this.replace(/[\u00A0-\u9999<>\&]/gim, function(s) {
        return "&#" + s.charCodeAt(0) + ";";
    });
};

/**
 * Create string from HTML entities
 */
String.prototype.fromHtmlEntities = function() {
    return this.replace(/&#\d+;/gm,function(s) {
        return String.fromCharCode(s.match(/\d+/gm)[0]);
    })
};

String.prototype.ucfirst = function() {
    return this.length === 0?this:(this.charAt(0).toUpperCase() + this.slice(1));
}

function stackTrace() {
    var err = new Error();
    return err.stack;
}

function show_stackTrace() {
    var err = new Error();
    console.debug(err.stack);
}

function isFunction(functionToCheck) {
	var getType = {};
	return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}

function storageAvailable(type) {
    try {
        var storage = window[type],
            x = '__storage_test__';
        storage.setItem(x, x);
        storage.removeItem(x);
        return true;
    }
    catch(e) {
        return e instanceof DOMException && (
            // everything except Firefox
            e.code === 22 ||
            // Firefox
            e.code === 1014 ||
            // test name field too, because code might not be present
            // everything except Firefox
            e.name === 'QuotaExceededError' ||
            // Firefox
            e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
            // acknowledge QuotaExceededError only if there's something already stored
            storage.length !== 0;
    }
}

function httpGet(theUrl)
{
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            return xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET", theUrl, false );
    xmlhttp.send();
}

function download(data, filename, type) {
    var file = new Blob([data], {type: type});
    if (window.navigator.msSaveOrOpenBlob) // IE10+
        window.navigator.msSaveOrOpenBlob(file, filename);
    else { // Others
        var a = document.createElement("a"),
                url = URL.createObjectURL(file);
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(function() {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);  
        }, 0); 
    }
}

function getParameterByName(name, def) {
	if (typeof def === 'undefined')
	{
		def = null;
	}
	var ret = def;
    var url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)");
	var results = regex.exec(url);
    if (results)
	{
		if (results[2])
		{
			ret = decodeURIComponent(results[2].replace(/\+/g, " "));
		}
	}
    return ret;
}

function oj_get_ojhost()
{
	var ret = 0;
	if (storageAvailable('localStorage') && localStorage['ojhost'])
	{
		ret = localStorage['ojhost'];
	}
	return ret;
}

function oj_get_session()
{
	var ret = [];
	if (storageAvailable('sessionStorage') && sessionStorage['ojsession'])
	{
		var ojs = sessionStorage['ojsession'];
		ret = ojs.split("__");
	}
	return ret;
}

function oj_save_session(catalog, index)
{
	if (storageAvailable('sessionStorage'))
	{
		sessionStorage['ojsession'] = catalog + "__" + index;
	}
}

function oj_iframe_loaded(iframeid, len)
{
	console.debug("loaded iframe", iframeid, len);
	console.debug("body", jQuery("#" + iframeid + " body").html());
}

function oj_iframe_error(iframeid)
{
	console.debug("error iframe", iframeid);
}

function oj_is_ancestor_div(id0, id1)
{
	var str = '#' + id0 + " #" + id1;
	return $(str).length > 0;
}

function oj_is_descendant_div(slug0, slug1)
{
	var str = '#' + id1 + " #" + id0;
	return $(str).length > 0;
}

function get_base_url()
{
	var href = window.location.href;
	var lastsl = href.lastIndexOf('/');
	return href.substr(0, lastsl);
}

function get_user_url()
{
	return get_base_url() + "/ojAjax.php?ojhost=" + oj_get_ojhost() + "&ojmode=" + ojmode + "&user=" + user;
}

function get_ajax_url()
{
	return get_base_url() + "/ojAjax.php?ojhost=" + oj_get_ojhost() + "&ojmode=" + ojmode + "&user=" + user + "&catalog=" + catalog + "&index=" + encodeURIComponent(index);
}

function get_catalog_url()
{
	return get_base_url() + "/ojAjax.php?ojhost=" + oj_get_ojhost() + "&ojmode=" + ojmode + "&user=" + user + "&catalog=" + catalog;
}

function oj_select_entity(id)
{
	window.console && console.log("select id " + id);
}

function last_part(str, sep)
{
	var lastsep = str.lastIndexOf(sep);
	return lastsep < 0?str:str.substr(lastsep + sep.length);
}

function oj_extract_id(str)
{
	return last_part(str, '-');
}

function is_function(obj) {
  return !!(obj && obj.constructor && obj.call && obj.apply);
}

function stamp()
{
	var ajaxdata = {
		user:user,
		catalog:catalog,
		index:index,
		ojhost:oj_get_ojhost(),
		ojmode:ojmode,
		action:"stamp"
	};
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
		console.debug("stamp " + returndata);
		if (storageAvailable('localStorage') && !localStorage['ojhost'])
		{
			localStorage['ojhost'] = returndata;
		}
	});
}

//var scrolled = 0;

function scroll_up()
{
	var scrolled = jQuery("#oj-catalog-list").scrollTop();
	if(scrolled != 0)
	{
		var inc = jQuery("#oj-catalog-list").height() - 50;
		scrolled = scrolled - inc;
//		console.debug("scroll up", scrolled);
		jQuery("#oj-catalog-list").animate({
			scrollTop:  scrolled
		});
	}
}

function scroll_down()
{
	var scrolled = jQuery("#oj-catalog-list").scrollTop();
	var inc = jQuery("#oj-catalog-list").height() - 50;
	scrolled = scrolled + inc;
	if (scrolled < jQuery("#oj-catalog-list-filter").height())
	{
//		console.debug("scroll down", scrolled);
		jQuery("#oj-catalog-list").animate({
			scrollTop:  scrolled
		});
	}
}

function scroll_top()
{
//	scrolled = 0;
	jQuery("#oj-catalog-list").animate({
		scrollTop:  0
	});
}

function on_collapse_show(event)
{
	var thisid = $(this).attr("id");
	var divid = '#' + $('#' + thisid + ' .oj-entity-div').attr("id");
	load_entity_div(divid, event);
	var ojid = $('#' + event.target.id + ' input[name="oj-entity-id"]').val();
	var lastdash = divid.lastIndexOf('-');
	var divojid = divid.substr(lastdash + 1);
	if (divojid == ojid)
	{
		console.debug('Show event fired on #' + event.target.id + " ojid " + ojid);
		if (content_show_functions.hasOwnProperty(catalog.toLowerCase()))
		{
			content_show_functions[catalog.toLowerCase()](ojid);
		}
	}
}

function load_entity_div(divid, event)
{
	window.console && console.log("load_entity_div divid " + divid);
	if ($(divid).children('div').length == 0)
	{
		var ojid = $(divid.replace('div', 'id')).val();
		var ojname = $(divid.replace('div', 'name')).val();
		var ojtype = $(divid.replace('div', 'type')).val();
		var cat = $(divid.replace('div', 'catalog')).val();
		var url = get_ajax_url() + "&catalog=" + cat + "&action=open&ojid=" + ojid + "&type=" + ojtype + "&name=" + encodeURIComponent(ojname);
		console.debug("url", url);
		var after_load = null;
		if (is_function(event))
		{
			after_load = event;
			event = null;
		}
		// TODO not test catalog
		if ((ojtype != 'ITEM') || (catalog == 'artists'))
		{
			$(divid).load(url, function()
			{
				set_up_collapse_show();
				$(divid).addClass("full");
				after_load && after_load();
				window.console && console.log("ajax load completed");
			});
		}
		else if (ojtype != 'GROUP')
		{
			event && event.preventDefault();
		}
		window.console && console.log("select id " + ojid + " " + ojname + " " + ojtype);
	}
}

function set_up_collapse_show()
{
	$(".oj-catalog-list-class .panel-collapse").off("show.bs.collapse", on_collapse_show);
	$(".oj-catalog-list-class .panel-collapse").on("show.bs.collapse", on_collapse_show);
	$(".oj-catalog-list-class .panel-title").each(function ()
	{
		var pdiv = $(this).parent().attr("id");
		var bdiv = '#' + pdiv.replace('heading-div', 'panel-body');
		var ojid = $(bdiv + ' input[name="oj-entity-id"]').val();
		var ojiddec = parseInt(ojid);
		var ojidhex = ojiddec.toString(16);
		var ojname = $(bdiv + ' input[name="oj-entity-name"]').val();
		var ojtype = $(bdiv + ' input[name="oj-entity-type"]').val();
		$(this).attr("title", ojid + " (" + ojidhex + "): " + ojtype + " " + ojname);
//		window.console && console.log(ojname + " " + ojid);
		
	});
//	for (var n = 0; n < collapse_show_functions.length; n++)
//	{
//		collapse_show_functions[n]();
//	}
//	$('.panel').on('show.bs.collapse', function (e) {
//		var v = $('#' + e.target.id + ' input[name="oj-entity-id"]').val()
//		console.log('Show event fired on #' + e.target.id + " ojid " + v);
//	});
}

function oj_callback(returndata)
{
	console.debug("callback", returndata);
	if ((returndata.type == 'change') || (returndata.type == 'call'))
	{
		var obj = null;
		if (returndata.checked.length == 1)
		{
			obj = returndata.checked[0];
		}
		else if ((returndata.checked.length > 1) && (returndata.newly_checked.length >= 1))
		{
			obj = returndata.newly_checked[0];
		}
		else if (returndata.newly_unchecked.length >= 1)
		{
			obj = returndata.newly_unchecked[0];
		}
		if (obj != null)
		{
			if (content_show_functions.hasOwnProperty(catalog.toLowerCase()))
			{
				var val = obj.value;
				var vals = val.split('|');
				content_show_functions[catalog.toLowerCase()](vals[0]);
			}
		}
	}
	console.debug("callback complete");
}

function oj_show_callback(id, number_of_values)
{
	var ojname = thefilter.get_text(id);
	var val = thefilter.get_value(id);
	var vals = val.split("|");
	jQuery(".filter-label-text").removeClass('oj-selected');
	jQuery("#filter-label-text-" + id).addClass('oj-selected');
	selected_panel = id;
	console.debug("show callback on", id, url);
	if (content_show_functions.hasOwnProperty(catalog.toLowerCase()))
	{
		content_show_functions[catalog.toLowerCase()](vals[0]);
	}
	if (number_of_values === 0)
	{
		var type = vals[1];
		var url = get_user_url() + "&catalog=" + vals[2] + "&index=" + index + "&action=show&ojid=" + vals[0] + "&type=" + type + "&name=" +
				encodeURIComponent(ojname) + "&selectable=" + selectable[type.toLowerCase()];
		console.debug("3.show callback url", url);
		jQuery.get(url, function(returndata)
		{
			var obj = JSON.parse(returndata);
			console.debug("show callback returns", obj);
			thefilter.fill_values(id, obj);
			jQuery(".oj-panel-heading").off("mouseenter");
			jQuery(".oj-panel-heading").off("mouseleave");
			jQuery(".oj-panel-heading").on("mouseenter", function(event)
			{
				panel_heading_on_mouseenter(event);
			});
			jQuery(".oj-panel-heading").on("mouseleave", function(event)
			{
				panel_heading_on_mouseleave(event);
			});
			var ids = [];
			ids[0] = id;
			if (obj)
			{
				for (var n = 0; n < obj.length; n++)
				{
					ids[n + 1] = obj[n].id;
				}
			}
			set_number_of_links_out(ids);
		});
	}
//	console.debug("show callback url", url);
}

function do_rename_category(catid, catname)
{
    ojAlert({
      type: "prompt",
      messageText: "New name for " + catname,
      alertType: "primary"
    }).done(function (e)
	{
		if (e)
		{
			e = e.trim();
			if (e.length > 0)
			{
				var ajaxdata = {
					user:user,
					catalog:catalog,
					index:index,
					ojhost:oj_get_ojhost(),
					ojmode:ojmode,
					action:"rename",
					ojid:id,
					name:e
				};
				var url = get_base_url() + "/ojAjax.php";
				jQuery.post(url, ajaxdata, function(returndata1)
				{
					console.debug(returndata1);
				});
			}
		}
		console.debug(e);
    });
}

function rename_category(initial_checked)
{
	var chk = function()
	{
		var ck = jQuery('#oj-new-category-name').val().trim().length > 0 && jQuery('#oj-select-entity-extra input.oj-checkbox:checked').length > 0 &&
				jQuery('#oj-select-entity-contents input.oj-select-category-input:checked').length > 0;
		jQuery('#save-new-entity-button-2').prop("disabled", !ck);
		console.debug("check", ck);
	};
	jQuery('#oj-select-entity-key').val("renamecategory");
	jQuery('#select-entity-header').text("Select category");
	jQuery('#oj-select-entity-extra').html('<div class="oj-select-extra-div">' +
			'<label for="oj-new-category-name" class="oj-select-extra-label"><span>New name</span></label>' +
			'<input type="text" id="oj-new-category-name" class="oj-select-extra-text"/>' +
			'</div>' +
			'<div class="oj-select-extra-div control-group"><div class="controls">' +
			'<label class="oj-checkbox-label oj-select-extra-label"><span>Rename Link</span>' +
			'<input type="checkbox" id="oj-rename-link-checkbox" checked="checked" class="oj-checkbox"/></label>' +
			'<label class="oj-checkbox-label oj-select-extra-label"><span>Rename Entity</span>' +
			'<input type="checkbox" id="oj-rename-entity-checkbox" checked="checked" class="oj-checkbox"/></label>' +
			'</div></div>');
	jQuery('#oj-select-entity-extra input.oj-checkbox').on("change", chk);
	jQuery('#oj-new-category-name').on('change keyup paste', chk);
	select_category('rename-category-', 'true', chk, initial_checked);
}

function rename_group(initial_checked)
{
	var chk = function()
	{
		var ck = jQuery('#oj-new-group-name').val().trim().length > 0 && jQuery('#oj-select-entity-extra input.oj-checkbox:checked').length > 0 &&
				jQuery('#oj-select-entity-contents input.filter-menu-checkbox:checked').length > 0;
		jQuery('#save-new-entity-button-2').prop("disabled", !ck);
		console.debug("check", ck);
	};
	jQuery('#oj-select-entity-key').val("renamegroup");
	jQuery('#select-entity-header').text("Select group");
	jQuery('#oj-select-entity-extra').html('<div class="oj-select-extra-div">' +
			'<label for="oj-new-group-name" class="oj-select-extra-label"><span>New name</span></label>' +
			'<input type="text" id="oj-new-group-name" class="oj-select-extra-text"/>' +
			'</div>' +
			'<div class="oj-select-extra-div control-group"><div class="controls">' +
			'<label class="oj-checkbox-label oj-select-extra-label"><span>Rename Link</span>' +
			'<input type="checkbox" id="oj-rename-link-checkbox" checked="checked" class="oj-checkbox"/></label>' +
			'<label class="oj-checkbox-label oj-select-extra-label"><span>Rename Entity</span>' +
			'<input type="checkbox" id="oj-rename-entity-checkbox" checked="checked" class="oj-checkbox"/></label>' +
			'</div></div>');
	jQuery('#oj-select-entity-extra input.oj-checkbox').on("change", chk);
	jQuery('#oj-new-group-name').on('change keyup paste', chk);
	select_group('rename-group-', 'true', chk, initial_checked);
}

function get_subtype_list(subtype)
{
	var ret = "";
	if (subtype)
	{
		var lp = subtype.indexOf('(');
		var rp = subtype.indexOf(')');
		console.debug("lp", lp, "rp", rp);
		ret = '<select class="oj-subtype-list">'
		var def = "";
		var vals = [];
		if ((lp > 0) && (rp > lp))
		{
			def = subtype.substr(0, lp);
			vals = subtype.substr(lp + 1, rp - lp - 1).split('|');
		}
		else
		{
			vals = subtype.split('|');
			if (vals.length > 0)
			{
				def = vals[0];
			}
		}
		for (var n = 0; n < vals.length; n++)
		{
			if (vals[n])
			{
				var sel = vals[n] === def?' selected="selected"':'';
				ret += '<option class="oj-subtype-list-item" value="' + vals[n] + '"' + sel + '>' + vals[n] + '</option>';
			}
		}
		ret += '</select>';
	}
	return ret;
}

function new_entity(etype, containertype, initialchecked)
{
	var etypel = etype.toLowerCase();
	var etypeu = etype.toUpperCase();
	if (typeof containertype === 'undefined')
	{
		containertype = 'category';
	}
	if (typeof initialchecked === 'undefined')
	{
		initialchecked = 0;
	}
	var chk = function()
	{
		var ck = jQuery('#oj-new-' + etypel + '-name').val().trim().length > 0 &&
				((jQuery('#oj-select-entity-contents input.oj-select-' + etypel + '-input').length == 0) ||
				(jQuery('#oj-select-entity-contents input.oj-select-' + etypel + '-input:checked').length > 0));
//		console.debug("1.check", ck);
//		jQuery('#oj-select-entity-attributes input.oj-input').each(function ()
//		{
//			var thisid = jQuery(this).attr("id");
//			ck = ck && (jQuery('#' + thisid).val().trim().length > 0);
//			console.debug("2.check", ck, jQuery('#' + thisid).val(), jQuery(this).attr("id"), this.value);
//		});
		jQuery('#save-new-entity-button-2').prop("disabled", !ck);
	};
	jQuery('#oj-select-entity-key').val("new" + etypel);
	jQuery('#select-entity-header').text("Select parent " + containertype);
	jQuery('#oj-select-entity-extra').html('<div class="oj-select-extra-div control-group"><div class="controls">' +
			'<label for="oj-new-' + etypel + '-name" class="oj-select-extra-label"><span>New ' + entity_names[etypel] + ' name</span></label>' +
			'<input type="text" id="oj-new-' + etypel + '-name" class="oj-select-extra-text"/>' + get_subtype_list(subtypes[etypel]) +
			'</div></div><div id="oj-select-entity-attributes"></div>');
	jQuery('#oj-select-entity-extra input.oj-checkbox').on("change", chk);
	jQuery('#oj-new-' + etypel + '-name').on('change keyup paste', chk);
	var ajaxdata = {
		user: user,
		catalog: catalog,
		index:index,
		ojhost:oj_get_ojhost(),
		etype: entity_type_ids[etypeu],
		ojmode: ojmode,
		action: "newentityattributes"
	};
	var url = get_base_url() + "/ojAjax.php";
	jQuery('#oj-select-entity-attributes').load(url, ajaxdata, function()
	{
		if (new_entity_extras[catalog, etypel])
		{
			new_entity_extras[catalog, etypel]();
		}
		window['select_' + containertype]('new-' + etypel + '-', 'true', chk, initialchecked);
		jQuery('#oj-attributes-tabs a:first').tab("show");
		jQuery('#oj-select-entity-attributes input.oj-input').on('change keyup paste', chk);
	});
}

//function new_category()
//{
//	var chk = function()
//	{
//		var ck = jQuery('#oj-new-category-name').val().trim().length > 0 &&
//				jQuery('#oj-select-entity-contents input.oj-select-category-input:checked').length > 0;
//		jQuery('#save-new-entity-button').prop("disabled", !ck);
//		console.debug("check", ck);
//	};
//	jQuery('#oj-select-entity-key').val("newcategory");
//	jQuery('#select-entity-header').text("Select parent category");
//	jQuery('#oj-select-entity-extra').html('<div class="oj-select-extra-div control-group"><div class="controls">' +
//			'<label for="oj-new-category-name" class="oj-select-extra-label"><span>New category name</span></label>' +
//			'<input type="text" id="oj-new-category-name" class="oj-select-extra-text"/>' + get_subtype_list(subtypes['category']) +
//			'</div><div id="oj-select-entity-attributes"></div></div>');
//	jQuery('#oj-select-entity-extra input.oj-checkbox').on("change", chk);
//	jQuery('#oj-new-category-name').on('change keyup paste', chk);
//	var ajaxdata = {
//		user: user,
//		catalog: catalog,
//		etype: entity_type_ids["CATEGORY"],
//		ojmode: ojmode,
//		action: "newentityattributes"
//	};
//	var url = get_base_url() + "/ojAjax.php";
//	jQuery('#oj-select-entity-attributes').load(url, ajaxdata, function()
//	{
//		select_category('new-category-', 'true', chk);
//	});
//}

function new_group()
{
	
}

function new_item()
{
    ojAlert({
      type: "prompt",
      messageText: "URL:",
      alertType: "primary"
    }).done(function (e)
	{
		if (e)
		{
			e = e.trim();
			if (e.length > 0)
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
					console.debug("contents", returndata);
				});
//				var contents = httpGet(e);
			}
		}
		console.debug(e);
    });
}

function delete_entity()
{
	
}

function select_logical()
{
	console.debug(jQuery('#oj-select-logical-id').val());
}

select_entity_functions["newcategory"] = function ()
{
	var chk = jQuery("#oj-select-entity-contents input.oj-select-category-input:checked");
	if (chk && (chk.length > 0))
	{
		var catid = oj_extract_id(jQuery(chk).attr("id"));
		var nm = jQuery('#oj-new-category-name').val().trim().toHtmlEntities();
		var propel = jQuery(".oj-subtype-list");
		if (propel.length > 0)
		{
			var sub = propel.val();
			var subtypes = subtypes['item'];
			var lp = subtypes.indexOf('(');
			if (lp > 0)
			{
				subtype += sub + subtypes.substr(lp);
			}
			else
			{
				subtype += sub + "(" + subtypes + ")";
			}
			console.debug("subtype", subtype);
//			xml += '<properties><property type="enumeration" name="subtype">' + sub + subtype.substr(lp) + '</property></properties>';
		}
		var xml = '<entity' + subtype + ' type="' + entity_type_ids["CATEGORY"] + '" catalog="' + catalog_ids[catalog.toLowerCase()] + '"><name>' + nm + '</name>';
		// add attributes
		xml += '</pages><links><link type="child" direction="to" catalog="' + catalog_ids[catalog.toLowerCase()] + '" ordinal="0" hidden="0" other="' + catid + '">' +
				nm + '</link></links><children></children></entity>';
		console.debug("newcategory " + xml);
		var ajaxdata = {
			user:user,
			catalog:catalog,
			index:index,
			ojhost:oj_get_ojhost(),
			ojmode:ojmode,
			action:"create",
			xml:xml
		};
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			console.debug("category created with id " + returndata);
			ojAlert({
			  type: "alert",
			  messageText: "category created with id " + returndata,
			  alertType: "primary"
			}).done(function()
			{
				console.debug("newcategory alert done");
				oj_reload();
			});
		});
	}
}

select_entity_functions["newgroup"] = function ()
{
	var chk = jQuery("#oj-select-entity-contents input.oj-select-category-input:checked");
	if (chk && (chk.length > 0))
	{
		var catid = oj_extract_id(jQuery(chk).attr("id"));
		var nm = jQuery('#oj-new-group-name').val().trim().toHtmlEntities();
		var propel = jQuery(".oj-subtype-list");
		var subtype = " ";
		if (propel.length > 0)
		{
			var sub = propel.val();
			var subtypes = subtypes['group'];
			var lp = subtypes.indexOf('(');
			if (lp > 0)
			{
				subtype += sub + subtypes.substr(lp);
			}
			else
			{
				subtype += sub + "(" + subtypes + ")";
			}
			console.debug("subtype", subtype);
//			xml += '<properties><property type="enumeration" name="subtype">' + sub + subtype.substr(lp) + '</property></properties>';
		}
		var xml = '<entity' + subtype + ' type="' + entity_type_ids["GROUP"] + '" catalog="' + catalog_ids[catalog.toLowerCase()] + '"><name>' + nm + '</name>';
		xml += '<pages>';
		// add attributes
		var atts = {};
		jQuery('#oj-select-entity-attributes .oj-new-entity-component-div').each(function ()
		{
			var pg = jQuery(this).find(".oj-new-entity-attribute-page").val();
			var aname = jQuery(this).find(".oj-new-entity-attribute-name").val();
			var typ = jQuery(this).find(".oj-new-entity-attribute-type").val();
			var ord = jQuery(this).find(".oj-new-entity-attribute-ordinal").val();
			var vis = jQuery(this).find(".oj-new-entity-attribute-vis").val();
			var val = jQuery(this).find(".oj-input").val();
			if (!atts.hasOwnProperty(pg))
			{
				atts[pg] = "";
			}
			atts[pg] += '<attribute type="' + typ + '" visible="' + vis + '" ordinal="' + ord + '"><name>' + aname.toHtmlEntities() + 
					'</name><value>' + val + '</value></attribute>';
		});
		for (var pname in atts)
		{
			if (atts.hasOwnProperty(pname))
			{
				xml += '<page name="'+ pname + '" visible="1">' + atts[pname] + '</page>';
			}
		}
		xml += '</pages><links><link type="child" direction="to" catalog="' + catalog_ids[catalog.toLowerCase()] + '" ordinal="0" hidden="0" other="' + catid + '">' +
				nm + '</link></links><children></children></entity>';
		console.debug("newgroup " + xml);
		var ajaxdata = {
			user:user,
			catalog:catalog,
			index:index,
			ojhost:oj_get_ojhost(),
			ojmode:ojmode,
			action:"create",
			xml:xml
		};
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			stop_please_wait();
			console.debug(entity_names.group + " created with id " + returndata);
			ojAlert({
			  type: "alert",
			  messageText: entity_names.group + " created with id " + returndata,
			  alertType: "primary"
			});
		});
		please_wait("creating new " + entity_names.group);
	}
}

select_entity_functions["newitem"] = function ()
{
	var chk = jQuery("#oj-select-entity-contents input.oj-select-category-input:checked");
	if (chk && (chk.length > 0))
	{
		var subtype = "";
		var catid = oj_extract_id(jQuery(chk).attr("id"));
		var nm = jQuery('#oj-new-item-name').val().trim().toHtmlEntities();
		var propel = jQuery(".oj-subtype-list");
		if (propel.length > 0)
		{
			var sub = propel.val();
			var subtypes = subtypes['item'];
			var lp = subtypes.indexOf('(');
			subtype = 'subtype="';
			if (lp > 0)
			{
				subtype += sub + subtypes.substr(lp);
			}
			else
			{
				subtype += sub + "(" + subtypes + ")";
			}
			subtype += '"';
			console.debug("subtype", subtype);
//			xml += '<properties><property type="enumeration" name="subtype">' + sub + subtype.substr(lp) + '</property></properties>';
		}
		var xml = '<entity' + subtype + ' type="' + entity_type_ids["ITEM"] + '" catalog="' + catalog_ids[catalog.toLowerCase()] + '"><name>' + nm + '</name>';
		xml += '<pages>';
		// add attributes
		var atts = {};
		jQuery('#oj-select-entity-attributes .oj-new-entity-component-div').each(function ()
		{
			var pg = jQuery(this).find(".oj-new-entity-attribute-page").val();
			var aname = jQuery(this).find(".oj-new-entity-attribute-name").val();
			var typ = jQuery(this).find(".oj-new-entity-attribute-type").val();
			var ord = jQuery(this).find(".oj-new-entity-attribute-ordinal").val();
			var vis = jQuery(this).find(".oj-new-entity-attribute-vis").val();
			var val = jQuery(this).find(".oj-input").val();
			if (!atts.hasOwnProperty(pg))
			{
				atts[pg] = "";
			}
			atts[pg] += '<attribute type="' + typ + '" visible="' + vis + '" ordinal="' + ord + '"><name>' + aname.toHtmlEntities() + 
					'</name><value>' + val + '</value></attribute>';
		});
		for (var pname in atts)
		{
			if (atts.hasOwnProperty(pname))
			{
				xml += '<page name="'+ pname + '" visible="1">' + atts[pname] + '</page>';
			}
		}
		xml += '</pages><links><link type="child" direction="to" catalog="' + catalog_ids[catalog.toLowerCase()] + '" ordinal="0" hidden="0" other="' + catid + '">' +
				nm + '</link></links><children></children></entity>';
		console.debug("newitem " + xml);
		var ajaxdata = {
			user:user,
			catalog:catalog,
			index:index,
			ojhost:oj_get_ojhost(),
			ojmode:ojmode,
			action:"create",
			xml:xml
		};
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			stop_please_wait();
			console.debug(entity_names.item + " created with id " + returndata);
			ojAlert({
			  type: "alert",
			  messageText: entity_names.item + " created with id " + returndata,
			  alertType: "primary"
			});
		});
		please_wait("creating new " + entity_names.item);
	}
}

select_entity_functions["renamecategory"] = function ()
{
	var chk = jQuery("#oj-select-entity-contents input.oj-select-category-input:checked");
	if (chk && (chk.length > 0))
	{
		var chkid = jQuery(chk).attr("id");
		var catid = oj_extract_id(chkid);
		var parentid = jQuery('#' + chkid.replace("input", "parent")).val();
		if (parentid == 0)
		{
			ojAlert({
			  type: "alert",
			  messageText: "cannot rename root!",
			  alertType: "primary"
			});
		}
		else
		{
			var catname = jQuery('#rename-category-' + catalog + '-select-category-span-' + catid).text().trim();
			var newname = jQuery('#oj-new-category-name').val().trim();
			if (catname == newname)
			{
				ojAlert({
				  type: "alert",
				  messageText: "The new name is the same as the old name!",
				  alertType: "primary"
				});
			}
			else
			{
				var lnk = jQuery('#oj-rename-link-checkbox').is(":checked")?"true":"false";
				var ent = jQuery('#oj-rename-entity-checkbox').is(":checked")?"true":"false";
				console.debug("renamecategory " + catid + " from " + catname + " to " + newname + " parent " + parentid + " chkid " + chkid + " link " + lnk + " entity " + ent);
				var ajaxdata = {
					user:user,
					catalog:catalog,
					index:index,
					ojhost:oj_get_ojhost(),
					ojmode:ojmode,
					action:"rename",
					ojid:catid,
					parent:parentid,
					name:newname,
					link:lnk,
					entity:ent
				};
				var url = get_base_url() + "/ojAjax.php";
				jQuery.post(url, ajaxdata, function(returndata)
				{
					console.debug(returndata);
					ojAlert({
					  type: "alert",
					  messageText: returndata,
					  alertType: "primary"
					});
				});
			}
		}
	}
}

select_entity_functions["renamegroup"] = function ()
{
	var chk = jQuery("#oj-select-entity-contents input.filter-menu-checkbox:checked");
	console.debug("renamegroup", chk);
	if (chk && (chk.length == 1))
	{
		var chkid = jQuery(chk).attr("id");
		var grpid = oj_extract_id(chkid);
		var grpname = jQuery('#oj-new-group-name').val().trim();
//		var catid = jQuery(chk).closest("ul").attr("id");
		var catid = jQuery(chk).closest("div").attr("id");
		var parentid = oj_extract_id(catid);
		if (parentid == 0)
		{
			ojAlert({
			  type: "alert",
			  messageText: "cannot rename root!",
			  alertType: "primary"
			});
		}
		else
		{
			var oldname = jQuery('#rename-group-filter-label-text-' + parentid).text().trim();
			var lnk = jQuery('#oj-rename-link-checkbox').is(":checked")?"true":"false";
			var ent = jQuery('#oj-rename-entity-checkbox').is(":checked")?"true":"false";
			console.debug("renamegroup " + catid + " from " + oldname + " to " + grpname + " parent " + parentid + " chkid " + chkid + " link " + lnk + " entity " + ent);
			var ajaxdata = {
				user:user,
				catalog:catalog,
				index:index,
				ojhost:oj_get_ojhost(),
				ojmode:ojmode,
				action:"rename",
				ojid:grpid,
				parent:parentid,
				name:grpname,
				link:lnk,
				entity:ent
			};
			var url = get_base_url() + "/ojAjax.php";
			jQuery.post(url, ajaxdata, function(returndata)
			{
				console.debug(returndata);
				ojAlert({
				  type: "alert",
				  messageText: returndata,
				  alertType: "primary"
				});
			});
		}
	}
}

function oj_select_callback(returndata)
{
	var canselect = true;
	jQuery('#save-new-entity-button-2').prop("disabled", canselect);
	console.debug("select returndata", returndata);
}

function oj_select_show_callback(id)
{
	console.debug("select show", id);
	var ojname = selectfilter.get_text(id);
	var val = selectfilter.get_value(id);
	var vals = val.split("|");
	var type = vals[1];
	var url = get_ajax_url() + "&action=show&ojid=" + vals[0] + "&type=" + type + "&name=" + encodeURIComponent(ojname);
	console.debug("1.show callback url", url);
	if (content_show_functions.hasOwnProperty(catalog.toLowerCase()))
	{
		content_show_functions[catalog.toLowerCase()](vals[0]);
	}
	jQuery.get(url, function(returndata)
	{
//		console.debug("show callback returns", returndata, typeof returndata);
		selectfilter.fill_values(id, JSON.parse(returndata));
	});
}

function select_category(prefix, root, check_select, initial_checked)
{
	if (typeof root === 'undefined')
	{
		root = "false";
	}
	if (typeof prefix === 'undefined')
	{
		prefix = "";
	}
	var ind = "default";
	var lccatalog = catalog.toLowerCase();
	if (select_categories.hasOwnProperty(lccatalog))
	{
		ind = select_categories[lccatalog];
	}
	var ajaxdata = {
		user: user,
		catalog: catalog,
		index: ind,
		ojhost:oj_get_ojhost(),
		ojmode: ojmode,
		prefix: prefix,
		root: root,
		action: "categories"
	};
	console.debug("select category", ajaxdata);
	var url = get_base_url() + "/ojAjax.php";
	jQuery('#oj-select-entity-contents').load(url, ajaxdata, function()
	{
		jQuery('#save-new-entity-button-2').prop("disabled", true);
		console.debug("categories complete");
		if (typeof initial_checked !== 'undefined')
		{
			jQuery("#" + prefix + catalog + "-select-category-input-" + initial_checked).prop("checked", true);
			var catname = jQuery('#' + prefix + catalog + '-select-category-span-' + initial_checked).text().trim();
			jQuery('#oj-new-category-name').val(catname);
			jQuery('#save-new-entity-button-2').prop("disabled", false);
		}
		if ((typeof check_select !== 'undefined') && isFunction(check_select))
		{
			jQuery('#oj-select-entity-contents input.oj-select-category-input').on("change", check_select);
		}
	});
	$('#oj-select-entity').modal("show");
}

var sgfilter;
var sg_check_id = 0;
function select_group_show_callback(id, number_of_values)
{
	var ojname = sgfilter.get_text(id);
	var val = sgfilter.get_value(id);
	var vals = val.split("|");
	console.debug("select_group_show callback on", id, number_of_values, vals);
	if ((number_of_values === 0) && (vals[1] !== 'GROUP'))
	{
		var url = get_user_url() + "&catalog=" + vals[2] + "&index=" + index + "&action=show&ojid=" + vals[0] + "&type=" + vals[1] + "&name=" + encodeURIComponent(ojname);
		console.debug("2.show callback url", url);
		jQuery.get(url, function(returndata)
		{
//			console.debug("show callback returns", returndata, typeof returndata);
			var rtd = JSON.parse(returndata);
			for (var n = 0; n < rtd.length; n++)
			{
				var rd = rtd[n];
				var rdval = rd["value"];
				if (rdval.indexOf('|GROUP|') > 0)
				{
					delete rd["propagate"];
					delete rd["propagatefrom"];
					delete rd["values"];
				}
			}
//			console.debug("show callback returns", rtd, typeof returndata);
			sgfilter.fill_values(id, rtd);
			if (sg_check_id > 0)
			{
				var chk1 = jQuery('#' + sgfilter.data.idprefix + sg_check_id);
				if (chk1.length > 0)
				{
					console.debug("checking", '#' + sgfilter.data.idprefix + sg_check_id);
					chk1.prop("checked", true);
					var catname = jQuery('#' + sgfilter.data.idprefix + 'filter-label-text-' + sg_check_id).text().trim();
					jQuery('#oj-new-category-name').val(catname);
					jQuery('#save-new-entity-button-2').prop("disabled", false);
					sg_check_id = 0;
				}
				else
				{
					console.debug("not there", '#' + sgfilter.data.idprefix + sg_check_id);
				}
			}
		});
	}
}

function select_group_callback(returndata)
{
	console.debug("select_group_callback", returndata);
}

function select_group(prefix, root, check_select, initial_checked)
{
//	if (!sgfilter)
//	{
		var gdata = {
			"containerid" : "oj-select-entity-contents",
			"callback" : select_group_callback,
			"valuecallback" : select_group_show_callback,
			"idprefix":prefix,
			"radio":true,
			"categories" : gcats
		};
		sgfilter = new FILTER(gdata);
		sgfilter.initialise();
		jQuery('#save-new-entity-button-2').prop("disabled", true);
		if ((typeof initial_checked !== 'undefined') && (initial_checked > 0))
		{
			var to_open = [];
			var li = jQuery('#filter-menu-value-' + initial_checked);
			while (li.length > 0)
			{
//				var ul = li.parent("ul").attr("id");
//				var nxt1 = oj_extract_id(ul);
				var ul = li.parent("div").attr("id");
				var nxt1 = oj_extract_id(ul);
				to_open.push(nxt1);
				li = jQuery('#filter-menu-value-' + nxt1);
			}
			console.debug("opening", to_open);
			sg_check_id = initial_checked;
			for (var n = to_open.length - 1; n >= 0; n--)
			{
				sgfilter.open_category(to_open[n]);
			}
		}
		if ((typeof check_select !== 'undefined') && isFunction(check_select))
		{
			jQuery('#oj-select-entity-contents input.filter-menu-checkbox').on("change", check_select);
		}
//	}
	$('#oj-select-entity').modal("show");
}

function quit_select_entity()
{
	jQuery('#oj-select-entity').modal("hide");
}

function quit_make_note()
{
	jQuery('#oj-make-note').modal("hide");
}

function save_note()
{
	jQuery('#oj-make-note').modal("hide");
	var ojid = parseInt(jQuery('#oj-make-note-entity').val());
	console.debug("save note", ojid);
	if (ojid > 0)
	{
		var note = tinyMCE.get('oj-make-note-textarea').getContent().trim();
		if (note.length > 0)
		{
			console.debug("save note", note);
			var ajaxdata =
			{
				action: 'makenote',
				catalog: catalog,
				user: user,
				ojhost:oj_get_ojhost(),
				ojid:ojid,
				note:note
			};
//			console.debug(ajaxdata);
			var url = get_base_url() + "/ojAjax.php";
			jQuery.post(url, ajaxdata, function(returndata)
			{
				console.debug("returndata", returndata);
			});
		}
	}
}

function show_make_note()
{
	jQuery('#oj-make-note').modal('show');
	jQuery('#oj-make-note-textarea').focus();
}

function please_wait(msg, tm, donext1)
{
	if (typeof msg === 'undefined')
	{
		msg = "Please wait";
	}
	if (typeof tm === 'undefined')
	{
		tm = 0;
	}
	jQuery('#oj-please-wait-contents').html(msg);
	jQuery('#oj-please-wait').modal({
		backdrop: 'static',
		keyboard: false
	});
	if (tm > 0)
	{
		setTimeout(function (donext)
		{
			stop_please_wait();
			console.debug("donext", donext);
			if (typeof donext !== 'undefined')
			{
				donext();
			}
		}, tm * 1000, donext1);
	}
}

function stop_please_wait()
{
	jQuery('#oj-please-wait-contents').html('');
	jQuery('#oj-please-wait').modal("hide");
}

var hadsearch = "";

function oj_search()
{
    ojAlert({
      type: "prompt",
      messageText: "Search for " + name,
      alertType: "primary"
    }).done(function (e)
	{
		if (e)
		{
			e = e.trim();
			if (e.length > 0)
			{
				jQuery('#oj-search-text').html("search for <strong>" + e + "</strong>");
				var ajaxdata = {
					user:user,
					catalog:catalog,
					index:index,
					ojhost:oj_get_ojhost(),
					ojmode:ojmode,
					action:"search",
					search:e
				};
				var url = get_base_url() + "/ojAjax.php";
				jQuery.post(url, ajaxdata, function(returndata1)
				{
					var vc = JSON.parse(returndata1);
					thefilter.set_visibility_check(vc);
					thefilter.clear_all_categories();
					hadsearch = e;
					console.debug(vc);
				});
			}
			else if (hadsearch)
			{
				jQuery('#oj-search-text').html('');
				hadsearch = "";
				console.debug("resetting filter visibility");
				thefilter.set_visibility_check(null);
				thefilter.clear_all_categories();
			}
		}
		else if (hadsearch)
		{
			jQuery('#oj-search-text').html('');
			hadsearch = "";
			console.debug("resetting filter visibility");
			thefilter.set_visibility_check(null);
			thefilter.clear_all_categories();
		}
		console.debug(e);
    });
}

function oj_reset()
{
	thefilter.clear_all_categories();
}

function select_entity()
{
	jQuery('#oj-select-entity').modal("hide");
	var key = jQuery('#oj-select-entity-key').val();
	console.debug("select_entity", key);
	if (key && select_entity_functions.hasOwnProperty(key))
	{
		console.debug("calling select_entity", key);
		select_entity_functions[key]();
	}
}

function quit_select_entity()
{
	jQuery('#oj-select-entity').modal("hide");
}

function ojAlert (options) {
	var deferredObject = jQuery.Deferred();
	var defaults = {
		type: "alert", //alert, prompt,confirm 
		modalSize: 'modal-sm', //modal-sm, modal-lg
		okButtonText: 'Ok',
		cancelButtonText: 'Cancel',
		yesButtonText: 'Yes',
		noButtonText: 'No',
		headerText: 'Attention',
		messageText: 'Message',
		alertType: 'default', //default, primary, success, info, warning, danger
		inputFieldType: 'text', //could ask for number,email,etc
	}
	jQuery.extend(defaults, options);
  
	var _show = function(){
		var headClass = "navbar-default";
		switch (defaults.alertType) {
			case "primary":
				headClass = "alert-primary";
				break;
			case "success":
				headClass = "alert-success";
				break;
			case "info":
				headClass = "alert-info";
				break;
			case "warning":
				headClass = "alert-warning";
				break;
			case "danger":
				headClass = "alert-danger";
				break;
        }
		jQuery('BODY').append(
			'<div id="ojAlerts" class="modal fade">' +
			'<div class="modal-dialog" class="' + defaults.modalSize + '">' +
			'<div class="modal-content">' +
			'<div id="ojAlerts-header" class="modal-header ' + headClass + '">' +
			'<button id="close-button" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' +
			'<h4 id="ojAlerts-title" class="modal-title">Modal title</h4>' +
			'</div>' +
			'<div id="ojAlerts-body" class="modal-body">' +
			'<div id="ojAlerts-message" ></div>' +
			'</div>' +
			'<div id="ojAlerts-footer" class="modal-footer">' +
			'</div>' +
			'</div>' +
			'</div>' +
			'</div>'
		);

		jQuery('.modal-header').css({
			'padding': '15px 15px',
			'-webkit-border-top-left-radius': '5px',
			'-webkit-border-top-right-radius': '5px',
			'-moz-border-radius-topleft': '5px',
			'-moz-border-radius-topright': '5px',
			'border-top-left-radius': '5px',
			'border-top-right-radius': '5px'
		});
    
		jQuery('#ojAlerts-title').text(defaults.headerText);
		console.debug('defaults.messageText', defaults.messageText);
		if (defaults.messageText !== 'none')
		{
			jQuery('#ojAlerts-message').html(defaults.messageText);
		}
		var keyb = "false", backd = "static";
		var calbackParam = "";
		switch (defaults.type) {
			case 'alert':
				keyb = "true";
				backd = "true";
				jQuery('#ojAlerts-footer').html('<button class="btn btn-' + defaults.alertType + '">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
					calbackParam = true;
					jQuery('#ojAlerts').modal('hide');
				});
				break;
			case 'confirm':
				var btnhtml = '<button id="ojok-btn" class="btn btn-primary">' + defaults.yesButtonText + '</button>';
				if (defaults.noButtonText && defaults.noButtonText.length > 0) {
					btnhtml += '<button id="ojclose-btn" class="btn btn-default">' + defaults.noButtonText + '</button>';
				}
				jQuery('#ojAlerts-footer').html(btnhtml).on('click', 'button', function (e) {
						if (e.target.id === 'ojok-btn') {
							calbackParam = true;
							jQuery('#ojAlerts').modal('hide');
						} else if (e.target.id === 'ojclose-btn') {
							calbackParam = false;
							jQuery('#ojAlerts').modal('hide');
						}
					});
				break;
			case 'prompt':
				jQuery('#ojAlerts-message').html(defaults.messageText + '<br /><br /><div class="form-group"><input type="' + defaults.inputFieldType + '" class="form-control" id="prompt" /></div>');
				jQuery('#ojAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
					calbackParam = jQuery('#prompt').val();
					jQuery('#ojAlerts').modal('hide');
				});
				break;
			case 'custom':
				var flds = defaults.custom.split(',');
				console.debug("flds", flds);
				var html = (defaults.messageText === 'none'?"":("<h3>" + defaults.messageText + '</h3>')) + '<div class="oj-alert-controls">';
				var ev = {};
				for (var n = 0; n < flds.length; n++)
				{
					var fldid = flds[n];
					console.debug("defaults", defaults);
					console.debug("field", fldid);
					if (defaults.hasOwnProperty(fldid))
					{
						var fld = defaults[fldid];
						var lbl = fld.label;
						var disp = "";
						if (fld.hasOwnProperty('display'))
						{
							disp = ' style="display:' + fld["display"] + ';"';
						}
						if (fld.hasOwnProperty('events'))
						{
							ev[fldid] = fld["events"];
						}
						html += '<div class="oj-alert-control-div" id="oj-alert-control-div-' + fldid + '"' + disp + '><label class="oj-alert-label" for="' + fldid + '">' + lbl + '</label>';
						if (fldid.startsWith("prompt"))
						{
							var promptval = "";
							if (fld.hasOwnProperty('value'))
							{
								promptval = ' value="' + fld['value'] + '"';
							}
							html += '<input type="text" class="oj-alert-control" id="' + fldid + '"' + promptval + '/>';
						}
						else if (fldid.startsWith("password"))
						{
							var promptval = "";
							if (fld.hasOwnProperty('value'))
							{
								promptval = ' value="' + fld['value'] + '"';
							}
							html += '<input type="password" class="oj-alert-control" id="' + fldid + '"' + promptval + '/>';
						}
						else if (fldid.startsWith("check"))
						{
							var promptval = "";
							if (fld.hasOwnProperty('value'))
							{
								promptval = ' value="' + fld['value'] + '"';
							}
							html += '<input type="checkbox" class="oj-alert-control" id="' + fldid + '"' + promptval + '/>';
						}
						else if (fldid.startsWith("choice"))
						{
							html += '<select class="oj-alert-control" id="' + fldid + '">';
							var selvals = fld['options'];
							var sel = null;
							if (fld.hasOwnProperty('value'))
							{
								sel = fld['value'];
							}
							var vals = selvals.split('|');
							for (var m = 0; m < vals.length; m++)
							{
								var val = vals[m];
								var selected = val == sel?' selected="selected"':'';
								html += '<option value="' + val + '"' + selected + '>' + val + '</option>';
							}
							html += '</select>';
						}
						else if (fldid.startsWith("dropdown"))
						{
							var lbl = fld.hasOwnProperty('label')?fld['label']:"select";
							var vals = fld["values"].split('|');
							html += '<div class="input-group oj-input-group"><input type="text" class="form-control oj-dropdown-text" aria-label="..." id="' + fldid + '">' +
									'<div class="input-group-btn">' +
								'<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
								lbl + ' <span class="caret"></span></button>' +
								'<ul class="dropdown-menu dropdown-menu-right">';
							for (var m = 0; m < vals.length; m++)
							{
								html += '<li><a href="#" target="_self" onclick="oj_set_dropdown_text(\'' + fldid + '\', \'' + vals[m] + '\');">' + vals[m] + '</a></li>';
							}
							html += '</ul></div></div>';
						}
						html += '</div>';
					}
				}
				html += '</div>';
				jQuery('#ojAlerts-message').html(html);
				jQuery('.oj-alert-label').css(
						{
							"display":"inline-block",
							"width": "25%",
							"text-align":"right",
							"margin-top":"5px",
							"padding-right":"3px"
						});
				jQuery(".oj-alert-control").css(
						{
							"width":"65%"
						});
				for (var fid in ev)
				{
					if (ev.hasOwnProperty(fid))
					{
						for (var e in ev[fid])
						{
							jQuery('#' + fid).on(e, ev[fid][e]);
						}
					}
				}
				jQuery('#ojAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText + '</button>').on('click', ".btn", function ()
				{
					calbackParam = [];
					for (var n = 0; n < flds.length; n++)
					{
						calbackParam[flds[n]] = jQuery('#' + flds[n]).val();
					}
					jQuery('#ojAlerts').modal('hide');
				});
				break;
		}
   
		jQuery('#ojAlerts').modal({ 
          show: false, 
          backdrop: backd, 
          keyboard: keyb 
        }).on('hidden.bs.modal', function (e) {
			jQuery('#ojAlerts').remove();
			deferredObject.resolve(calbackParam);
		}).on('shown.bs.modal', function (e) {
			if (jQuery('#prompt').length > 0) {
				if (defaults.hasOwnProperty("initialValue"))
				{
					jQuery('#prompt').val(defaults["initialValue"]);
				}
				jQuery('#prompt').focus();
			}
		}).modal('show');
	}
    
  _show();  
  return deferredObject.promise();    
}

//$(document).ready(function(){
//  $("#btnAlert").on("click", function(){  	
//    var prom = ezBSAlert({
//      messageText: "hello world",
//      alertType: "danger"
//    }).done(function (e) {
//      $("body").append('<div>Callback from alert</div>');
//    });
//  });   
//  
//  $("#btnConfirm").on("click", function(){  	
//    ezBSAlert({
//      type: "confirm",
//      messageText: "hello world",
//      alertType: "info"
//    }).done(function (e) {
//      $("body").append('<div>Callback from confirm ' + e + '</div>');
//    });
//  });   
//
//  $("#btnPrompt").on("click", function(){  	
//    ezBSAlert({
//      type: "prompt",
//      messageText: "Enter Something",
//      alertType: "primary"
//    }).done(function (e) {
//      ezBSAlert({
//        messageText: "You entered: " + e,
//        alertType: "success"
//      });
//    });
//  });   
//  
//});

var category_context_menu;
var group_context_menu;
var item_context_menu;

function check_if_action_shown(type, act)
{
	if (context_menu_options.hasOwnProperty(catalog.toLowerCase()))
	{
		var cmo = context_menu_options[catalog.toLowerCase()];
		if (cmo.hasOwnProperty("category"))
		{
			var cmoc = cmo['category'];
			if (cmoc.hasOwnProperty('hidden'))
			{
				for (var n = 0; n < cmoc.hidden.length; n++)
				{
					
				}
			}
		}
	}
}

function select_logical()
{
	var logical1 = jQuery('#oj-select-logical-id').val();
	var logical2 = logical1.split('|');
	var logical = logical2[0];
	console.debug("selected logical", logical);
	var url = get_ajax_url() + "&action=unimported&logical=" + logical;
	jQuery('#oj-unimported-folders').load(url, function(data)
	{
//		console.debug("loaded", data);
		jQuery('.oj-folder-radio').on("change", folder_select);
	});
}

var csfilter;

function show_import_entity()
{
	jQuery('#save-import-entity-button').prop('disabled', true);
	jQuery("#oj-import-category-div input[type='checkbox']").prop("checked", false);
	jQuery("#oj-import-category-div input[type='radio']").prop("checked", false);
	jQuery('#oj-import-entity-contents>ul li').removeClass("active");
	jQuery('#oj-import-entity-contents>ul li:first').addClass("active");
	jQuery('#oj-import-entity-contents .tab-pane').removeClass("active");
	jQuery('#oj-import-entity-contents .tab-pane:first').addClass("active");
	select_logical();
	if (jQuery('#oj-import-category-div>div').length > 0)
	{
		if (import_entity_show_functions.hasOwnProperty(catalog.toLowerCase()))
		{
			import_entity_show_functions[catalog.toLowerCase()]();
		}
		jQuery('#oj-import-entity').modal("show");
	}
	else
	{
		var ind = "default";
		var lccatalog = catalog.toLowerCase();
		if (select_categories.hasOwnProperty(lccatalog))
		{
			ind = select_categories[lccatalog];
		}
		var cats = fscats;
		if (other_selectable_cats.hasOwnProperty(ind))
		{
			cats = other_selectable_cats[ind];
		}
		var csdata = {
			"containerid" : "oj-import-category-div",
			"idprefix": "select-import-category-",
			"callback" : oj_select_category_callback,
			"valuecallback" : oj_select_category_show_callback,
			"radio" : "select-category-radio",
			"categories" : cats
		};
		csfilter = new FILTER(csdata);
		csfilter.initialise();
		jQuery('#oj-import-category-div input.filter-menu-checkbox').on("change", check_import_entity);
		if (import_entity_show_functions.hasOwnProperty(catalog.toLowerCase()))
		{
			import_entity_show_functions[catalog.toLowerCase()]();
		}
		jQuery('#oj-import-entity').modal("show");


//		var ajaxdata = {
//			user: user,
//			catalog: catalog,
//			index: ind,
//			ojhost:oj_get_ojhost(),
//			ojmode: ojmode,
//	//		prefix: prefix,
//	//		root: root,
//			action: "categories"
//		};
//		var url = get_base_url() + "/ojAjax.php";
//		jQuery('#oj-import-category-div').load(url, ajaxdata, function(resp)
//		{
////			console.debug("import categories complete", resp);
//			jQuery('.oj-select-category-input').on("change", check_import_entity);
//			if (import_entity_show_functions.hasOwnProperty(catalog.toLowerCase()))
//			{
//				import_entity_show_functions[catalog.toLowerCase()]();
//			}
//			jQuery('#oj-import-entity').modal("show");
//		});
	}
}

function oj_select_category_callback()
{
	
}

function oj_select_category_show_callback(id, number_of_values)
{
	var ojname = csfilter.get_text(id);
	var val = csfilter.get_value(id);
	var vals = val.split("|");
	jQuery(".filter-label-text").removeClass('oj-selected');
	jQuery("#filter-label-text-" + id).addClass('oj-selected');
	selected_panel = id;
	console.debug("show select category callback on", id, url);
	if (content_show_functions.hasOwnProperty(catalog.toLowerCase()))
	{
		content_show_functions[catalog.toLowerCase()](vals[0]);
	}
	if (number_of_values === 0)
	{
		var type = vals[1];
		var ind = index;
		var lccatalog = catalog.toLowerCase();
		if (select_categories.hasOwnProperty(lccatalog))
		{
			ind = select_categories[lccatalog];
		}
		var url = get_user_url() + "&catalog=" + vals[2] + "&index=" + ind + "&action=show&ojid=" + vals[0] + "&type=" + type + "&name=" +
				encodeURIComponent(ojname) + "&selectable=category";
		console.debug("3.show select category callback url", url);
		jQuery.get(url, function(returndata)
		{
//			console.debug("show callback returns", returndata, typeof returndata);
			csfilter.fill_values(id, JSON.parse(returndata));
			jQuery(".oj-panel-heading").off("mouseenter");
			jQuery(".oj-panel-heading").off("mouseleave");
			jQuery(".oj-panel-heading").on("mouseenter", function(event)
			{
				panel_heading_on_mouseenter(event);
			});
			jQuery(".oj-panel-heading").on("mouseleave", function(event)
			{
				panel_heading_on_mouseleave(event);
			});
			jQuery('#oj-import-category-div input.filter-menu-checkbox').off("change");
			jQuery('#oj-import-category-div input.filter-menu-checkbox').on("change", check_import_entity);
		});
	}
}

function oj_reload(index1, catalog1)
{
	var cat = "";
	var ind = "";
	if (typeof catalog1 === 'undefined')
	{
		cat = catalog;
		if (typeof index1 === 'undefined')
		{
			ind = index;
		}
		else
		{
			var du = index1.indexOf("__");
			if (du > 0)
			{
				cat = index1.substr(0, du);
				ind = index1.substr(du + 2);
			}
			else
			{
				ind = index1;
			}
		}
	}
	else
	{
		cat = catalog1;
		ind = index1;
	}
	if (storageAvailable('sessionStorage') && !somethingsetfromget)
	{
		sessionStorage['ojsession'] = cat + "__" + ind;
		window.location.reload(true);
	}
	else
	{
		var url = get_user_url() + "&catalog=" + encodeURIComponent(cat) + "&index=" + encodeURIComponent(ind);
		window.open(url, "_self");
	}
}

function quit_import_entity()
{
	jQuery('#oj-import-entity').modal("hide");
}

function check_import_entity()
{
//	console.debug("check", jQuery('#oj-unimported-folders :checked').length, jQuery('#oj-import-category-div :checked').length, jQuery('#oj-import-artists-list-div :checked').length);
	if ((jQuery('#oj-unimported-folders :checked').length == 1) &&
			(jQuery('#oj-import-category-div :checked').length >= 1) &&
			(!import_entity_checks.hasOwnProperty(catalog.toLowerCase()) || import_entity_checks[catalog.toLowerCase()]()))
	{
		jQuery('#save-import-entity-button').prop('disabled', false);
	}
	else
	{
		jQuery('#save-import-entity-button').prop('disabled', true);
	}
}

function oj_set_dropdown_text(fld, val)
{
	jQuery('#' + fld).val(val);
	jQuery('#' + fld).change();
}

function oj_set_parameter()
{
	console.debug("set parameter");
	var ajaxdata = {
		user:user,
		catalog:catalog,
		index:index,
		ojhost:oj_get_ojhost(),
		ojmode:ojmode,
		action:"parameters"
	};
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
//		console.debug("returndata", returndata);
		var parameters = JSON.parse(returndata);
//		console.debug("parameters", parameters)
		var vals = [];
		for (var p in parameters)
		{
			if (parameters.hasOwnProperty(p))
			{
				vals.push(p);
			}
		}
		ojAlert({
			type: "custom",
			headerText: "Set Parameter",
			messageText: 'none',
			alertType: "primary",
			custom: "dropdown0,choice0,prompt0",
			prompt0:
			{
				label: "value",
				value: ""
			},
			choice0:
			{
				label:"type",
				options:"string|integer|boolean|datetime",
				value:"string"
			},
			dropdown0:
			{
				label: "parameter",
				values: vals.join('|'),
				events: {
					'change keyup paste': function ()
					{
//						console.debug('change keyup paste');
						var pm = jQuery('#dropdown0').val();
						if (parameters.hasOwnProperty(pm))
						{
							var parameter = parameters[pm];
							jQuery('#prompt0').val(parameter["value"]);
							jQuery('#choice0').val(parameter["type"]);
							jQuery('#choice0').attr("disabled", true);
						}
					}
				}
			}
		}).done(function (e)
		{
//			console.debug("done", e);
			if (e)
			{
				if (e.dropdown0 && e.prompt0 && e.choice0)
				{
					var ajaxdata1 = {
						user:user,
						catalog:catalog,
						index:index,
						ojhost:oj_get_ojhost(),
						ojmode:ojmode,
						action:"setparameter",
						name:e.dropdown0,
						type:e.choice0,
						value:e.prompt0
					};
					var url1 = get_base_url() + "/ojAjax.php";
					jQuery.post(url1, ajaxdata1, function(returndata)
					{
						console.debug(returndata);
					});
				}
			}
		});
	});
}

function oj_set_user_role()
{
	console.debug("set user role");
}

function oj_new_logical_category()
{
	console.debug("new logical category");
	ojAlert(
	{
		type:"prompt",
		messageText: "Category name"
	}).done(function(e)
	{
		console.debug("Category name", e);
		var ajaxdata = {
			user:user,
			catalog:catalog,
			index:index,
			ojhost:oj_get_ojhost(),
			ojmode:ojmode,
			action:"newlogicalcategory",
			name:e
		};
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			console.debug(returndata);
		});
	});
}

function oj_new_ftp()
{
	ojAlert({
		type: "custom",
		headerText: "Set Parameter",
		messageText: 'none',
		alertType: "primary",
		custom: "prompt0,prompt1,password0",
		prompt0:
		{
			label: "site",
			value: ""
		},
		prompt1:
		{
			label: "username",
			value: ""
		},
		password0:
		{
			label: "password",
			value: ""
		}
	}).done(function (e)
	{
//			console.debug("done", e);
		if (e)
		{
			if (e.prompt0 && e.prompt1 && e.password0)
			{
				var ajaxdata1 = {
					user:user,
					catalog:catalog,
					index:index,
					ojhost:oj_get_ojhost(),
					ojmode:ojmode,
					action:"newftp",
					site:e.prompt0,
					ftpuser:e.prompt1,
					password:e.password0
				};
				var url1 = get_base_url() + "/ojAjax.php";
				jQuery.post(url1, ajaxdata1, function(returndata)
				{
					console.debug(returndata);
				});
			}
		}
	});
}

function oj_new_logical()
{
	console.debug("new logical");
	var ajaxdata = {
		user:user,
		catalog:catalog,
		index:index,
		ojhost:oj_get_ojhost(),
		ojmode:ojmode,
		action:"getlogicalcategories"
	};
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
//		console.debug("returndata", returndata);
		var parameters = JSON.parse(returndata);
		ojAlert({
			type: "custom",
			headerText: "New Logical",
			messageText: 'none',
			alertType: "primary",
			custom: "dropdown0,choice0,prompt0",
			prompt0:
			{
				label: "value",
				value: ""
			},
			choice0:
			{
				label:"type",
				options:"string|integer|boolean|datetime",
				value:"string"
			},
			dropdown0:
			{
				label: "parameter",
				values: vals.join('|'),
				events: {
					'change keyup paste': function ()
					{
//						console.debug('change keyup paste');
						var pm = jQuery('#dropdown0').val();
						if (parameters.hasOwnProperty(pm))
						{
							var parameter = parameters[pm];
							jQuery('#prompt0').val(parameter["value"]);
							jQuery('#choice0').val(parameter["type"]);
							jQuery('#choice0').attr("disabled", true);
						}
					}
				}
			}
		}).done(function (e)
		{
//			console.debug("done", e);
			if (e)
			{
				if (e.dropdown0 && e.prompt0 && e.choice0)
				{
					var ajaxdata1 = {
						user:user,
						catalog:catalog,
						index:index,
						ojhost:oj_get_ojhost(),
						ojmode:ojmode,
						action:"setparameter",
						name:e.dropdown0,
						type:e.choice0,
						value:e.prompt0
					};
					var url1 = get_base_url() + "/ojAjax.php";
					jQuery.post(url1, ajaxdata1, function(returndata)
					{
						console.debug(returndata);
					});
				}
			}
		});
	});
}

function oj_new_index()
{
	console.debug("new index");
}

function oj_new_user()
{
	console.debug("new user");
}

function set_number_of_links_out(ojids, ltype, subtype)
{
	if (set_number_of_links_out_functions.hasOwnProperty(catalog.toLowerCase()))
	{
		set_number_of_links_out_functions[catalog.toLowerCase()](ojids, ltype, subtype);
	}
	else
	{
		var ltype1;
		var justchild = false;
		if (typeof ltype === 'undefined')
		{
			ltype1 = "child";
			justchild = true;
		}
		else if (Array.isArray(ltype))
		{
			ltype1 = ltype.join(',');
		}
		else
		{
			ltype1 = ltype;
		}
		var ojid;
		var ojidarray;
		if (Array.isArray(ojids))
		{
			ojid = ojids.join(',');
			ojidarray = ojids;
		}
		else
		{
			ojid = ojids;
			ojidarray = [ojids];
		}
		var ajaxdata = {
			user:user,
			catalog:catalog,
			index:index,
			ojhost:oj_get_ojhost(),
			ojmode:ojmode,
			action:"nlinksout",
			ojid:ojid,
			type:ltype1
		};
		if (typeof subtype !== undefined)
		{
			ajaxdata["subtype"] = subtype;
		}
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			var nums = JSON.parse(returndata);
			console.debug("nums", nums);
			for (var id in nums)
			{
				var txt = [];
				if (nums.hasOwnProperty(id))
				{
					var obj = nums[id];
					for (var ty in obj)
					{
						if (obj.hasOwnProperty(ty))
						{
							var nty = obj[ty];
							if (Array.isArray(nty) || (typeof nty === 'object'))
							{
								for (var nt in nty)
								{
									if (nty.hasOwnProperty(nt))
									{
										if (justchild)
										{
											txt.push(nt + ":" + nty[nt]);
										}
										else
										{
											txt.push(ty + "." + nt + ":" + nty[nt]);
										}
									}
								}
							}
							else
							{
								if (justchild)
								{
									txt.push(nty);
								}
								else
								{
									txt.push(ty + ":" + nty);
								}
							}
						}
					}
				}
				jQuery("[id$=link-" + id + "] span.filter-label-post").text(" (" + txt.join(" ") + ")");
//				console.debug("number of links for", ojidarray[n], nums[n]);
			}
		});
	}
}

function list_on_keydown(event)
{
	console.debug("keydown event", event);
}

function panel_heading_on_mouseenter(event)
{
	current_panel = event.target.id;
//	console.debug("current panel heading", current_panel);
}

function panel_heading_on_mouseleave(event)
{
	current_panel = null;
}

jQuery(document).ready(function() {
	var somethingsetfromget = catsetfromget + indsetfromget;
	if (storageAvailable('sessionStorage'))
	{
		if (sessionStorage['ojsession'])
		{
			var ojs = sessionStorage['ojsession'];
			var ojsa = ojs.split("__");
			if (!catsetfromget)
			{
				catalog = ojsa[0];
			}
			if (!indsetfromget)
			{
				index = ojsa[1];
			}
			if (somethingsetfromget)
			{
				sessionStorage['ojsession'] = catalog + "__" + index;
			}
		}
		else
		{
			sessionStorage['ojsession'] = catalog + "__" + index;
		}
	}
	jQuery('head link:last').after('<link rel="stylesheet" href="oj_' + catalog.toLowerCase() + '_css.php"/>');
	document.title = "Openjean " + catalog;
	jQuery("#navbar-catalog-name").text(catalog);
	jQuery("#navbar-index-name").text(index);
//	$.getScript("js/oj_" + catalog.toLowerCase() + ".js");
	jQuery.ui.plugin.add("resizable", "alsoResizeReverse", {

	  start: function() {
		var that = $(this).resizable("instance"),
		  o = that.options,
		  _store = function(exp) {
			$(exp).each(function() {
			  var el = $(this);
			  el.data("ui-resizable-alsoResizeReverse", {
				width: parseInt(el.width(), 10),
				height: parseInt(el.height(), 10),
				left: parseInt(el.css("left"), 10),
				top: parseInt(el.css("top"), 10)
			  });
			});
		  };

		if (typeof(o.alsoResizeReverse) === "object" && !o.alsoResizeReverse.parentNode) {
		  if (o.alsoResizeReverse.length) {
			o.alsoResizeReverse = o.alsoResizeReverse[0];
			_store(o.alsoResizeReverse);
		  } else {
			$.each(o.alsoResizeReverse, function(exp) {
			  _store(exp);
			});
		  }
		} else {
		  _store(o.alsoResizeReverse);
		}
	  },

	  resize: function(event, ui) {
		var that = $(this).resizable("instance"),
		  o = that.options,
		  os = that.originalSize,
		  op = that.originalPosition,
		  delta = {
			height: (that.size.height - os.height) || 0,
			width: (that.size.width - os.width) || 0,
			top: (that.position.top - op.top) || 0,
			left: (that.position.left - op.left) || 0
		  },

		  _alsoResizeReverse = function(exp, c) {
			$(exp).each(function() {
			  var el = $(this),
				start = $(this).data("ui-resizable-alsoResizeReverse"),
				style = {},
				css = c && c.length ?
				c :
				el.parents(ui.originalElement[0]).length ? ["width", "height"] : ["width", "height", "top", "left"];

			  $.each(css, function(i, prop) {
				var sum = (start[prop] || 0) - (delta[prop] || 0);
				if (sum && sum >= 0) {
				  style[prop] = sum || null;
				}
			  });

			  el.css(style);
			});
		  };

		if (typeof(o.alsoResizeReverse) === "object" && !o.alsoResizeReverse.nodeType) {
		  $.each(o.alsoResizeReverse, function(exp, c) {
			_alsoResizeReverse(exp, c);
		  });
		} else {
		  _alsoResizeReverse(o.alsoResizeReverse);
		}
	  },

	  stop: function() {
		$(this).removeData("resizable-alsoResizeReverse");
	  }
	});
	$(document).ajaxStart(function() {
	    $("html,body").css({'cursor' : 'wait'});
	}).ajaxStop(function() {
		$("html,body").css({'cursor' : 'default'});
	});
	var wndHeight = Math.floor(jQuery(window).height() * 0.9);
	$( "#oj-catalog-list" ).resizable({
		alsoResizeReverse: "#oj-display-panel",
		minHeight: wndHeight,
		maxHeight: wndHeight,
		handles: 'e, w'
		
	});
	var lccatalog = catalog.toLowerCase();
	$.getScript("js/oj_" + lccatalog + ".js").done(function(script, textStatus) {
//		set_default_attributes(catalog);
		var ajaxdata = {
			user:user,
			catalog:catalog,
			index:index,
			ojhost:oj_get_ojhost(),
			ojmode:ojmode,
			action:"defatt"
		};
		if (other_filter_catalog_categories.hasOwnProperty(lccatalog))
		{
			ajaxdata["other"] = other_filter_catalog_categories[lccatalog].join(',');
		}
		var url = get_base_url() + "/ojAjax.php";
		console.debug("defatt ajaxdata", ajaxdata);
		jQuery.post(url, ajaxdata, function(returndata)
		{
			console.debug("defatt returndata", returndata);
			var defatt = JSON.parse(returndata);
			category_attributes = defatt[0];
			group_attributes = defatt[1];
			item_attributes = defatt[2];
			search_exclude = defatt[3];
			fcats = JSON.parse(defatt[4]);
			gcats = JSON.parse(defatt[4]);
			fscats = JSON.parse(defatt[4]);
			for (var n = 0; n < fscats.length; n++)
			{
				fscats[n]["selectable"] = true;
			}
			console.debug("fcats", fcats);
			subtypes.category = defatt[5];
			subtypes.group = defatt[6];
//			console.debug("subtypes", subtypes);
			var names = defatt[7].split('|');
			var iname = names[0].split('+');
			var gname = names[1].split('+');
			var cname = names[2].split('+');
			entity_names["item"] = iname[0];
			entity_names["group"] = gname[0];
			entity_names["category"] = cname[0];
			if (iname[2] === 'true')
			{
				jQuery("#oj-entity_menu_divider").after('<li><a target="_self" href="#" onclick="new_entity(\'item\');">New ' + iname[1] + '</a></li>');
			}
			if (gname[2] === 'true')
			{
				jQuery("#oj-entity_menu_divider").after('<li><a target="_self" href="#" onclick="new_entity(\'group\');">New ' + gname[1] + '</a></li>');
			}
			jQuery("#oj-entity_menu_divider").after('<li><a target="_self" href="#" onclick="rename_group();">Rename ' + gname[1] + '</a></li>');
			var indexes = defatt[8];
			var entity_menu_items = defatt[9];
			if (entity_menu_items.length > 0)
			{
				jQuery("#oj-entity-menu").append('<li class="divider"></li>');
				for (var n = 0; n < entity_menu_items.length; n++)
				{
					jQuery("#oj-entity-menu").append(entity_menu_items[n]);
				}
			}
//			console.debug("entity_menu_items", entity_menu_items);
			for (var n = 0; n < indexes.length; n++)
			{
				var ind = indexes[n];
				jQuery('#oj-index-menu').append('<li><a target="_self" href="#" onclick="oj_reload(\'' + ind['value'] + '\')">' + ind['label'] + '</a></li>');
			}
			var cats = defatt[10].split('|');
			for (var n = 0; n < cats.length; n++)
			{
				var catn = cats[n];
				var du = catn.indexOf("__");
				var cat = catn.substr(0, du);
				var ind = catn.substr(du + 2);
				jQuery('#oj-catalogs-menu').append('<li><a target="_self" href="#" onclick="oj_reload(\'' + ind + '\', \'' + cat + '\')">' + cat + '</a></li>');
			}
			logicals = defatt[11];
			console.debug("logicals", logicals);
			var l = 0;
			for (var lg in logicals)
			{
				if (logicals.hasOwnProperty(lg))
				{
					var sel = l === 0?' selected="selected"':'';
					jQuery("#oj-select-logical-id").append('<option value="' + logicals[lg]._data.name + '|' + logicals[lg]._data.value + '|' +
							logicals[lg]._data.alternative + '"' + sel + '>' + lg + '</option>');
					l++;
				}
			}
			var css_classes = defatt[12].split('|');
			jQuery('#oj-catalog-list').addClass(css_classes[0]);
			jQuery('#oj-display-panel').addClass(css_classes[1]);
			jQuery('#oj-display-panel').html('<span class="oj-initial-display">Openjean ' + catalog + '</span>');
			userrole = defatt[13];
			if (userrole == "admin")
			{
				jQuery("#oj-main-navbar").prepend('<li class="dropdown">' +
					'<a href="#" target="_self" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin <span class="caret"></span></a>' +
					'<ul id="oj-admin-menu" class="dropdown-menu">' +
					'<li><a target="_self" href="#" onclick="oj_set_parameter();">Set Parameter</a></li>' +
					'<li class="divider"></li>' +
					'<li><a target="_self" href="#" onclick="oj_new_logical_category();">New Logical Category</a></li>' +
					'<li><a target="_self" href="#" onclick="oj_new_logical();">New Logical</a></li>' +
					'<li class="divider"></li>' +
					'<li><a target="_self" href="#" onclick="oj_new_index();">New Index</a></li>' +
					'<li class="divider"></li>' +
					'<li><a target="_self" href="#" onclick="oj_new_user();">New User</a></li>' +
					'<li><a target="_self" href="#" onclick="oj_set_user_role();">Set User Role</a></li>' +
					'<li class="divider"></li>' +
					'<li><a target="_self" href="#" onclick="oj_new_ftp();">New FTP</a></li>' +
					'</ul></li>');
			}
			console.debug(user, "has role", userrole, "for catalog", catalog);
			enumerations = defatt[14];
			console.debug("enumerations", enumerations);
			if (other_filter_catalog_categories.hasOwnProperty(lccatalog))
			{
				var ofcc = other_filter_catalog_categories[lccatalog];
				console.debug("ofcc", ofcc);
				for (var m = 0; m < ofcc.length; m++)
				{
					var ofc = ofcc[m];
					console.debug("ofcc", m, ofc);
					other_cats[ofc] = JSON.parse(defatt[15 + m]);
					var ascats = JSON.parse(defatt[15 + m]);
					for (var n = 0; n < ascats.length; n++)
					{
						ascats[n]["selectable"] = true;
					}
					other_selectable_cats[ofc] = ascats;
				}
				console.debug("other_cats", other_cats, "other_selectable_cats", other_selectable_cats);
			}
			set_up_collapse_show();
			var mdata = {
				"containerid" : "oj-catalog-list-filter",
				"callback" : oj_callback,
				"valuecallback" : oj_show_callback,
				"categories" : fcats
			};
			if (radio_status.hasOwnProperty(lccatalog))
			{
				mdata.radio = radio_status[lccatalog];
			}
			thefilter = new FILTER(mdata);
			thefilter.initialise();
			if (fcats)
			{
				var fids = [];
				for (var i = 0; i < fcats.length; i++)
				{
					fids[i] = fcats[i].id;
				}
				set_number_of_links_out(fids);
			}
			jQuery('#oj-catalog-list').on("keydown", function(event)
			{
				list_on_keydown(event);
			});
			jQuery('#oj-catalog-list').on("mouseenter", function(event)
			{
				jQuery('#oj-catalog-list').focus();
			});
			jQuery(".oj-panel-heading").on("mouseenter", function(event)
			{
				panel_heading_on_mouseenter(event);
			});
			jQuery(".oj-panel-heading").on("mouseleave", function(event)
			{
				panel_heading_on_mouseleave(event);
			});
			jQuery('#oj-catalog-list').focus();
			var catcmdata = {
				fetchElementData: function(rowElem) {
		//			console.debug(rowElem)
					var elid = jQuery(rowElem).parent("div.filter-class-category").attr('id');
					var lastdash = elid.lastIndexOf('-');
					return elid.substr(lastdash + 1);
				},
				actionsGroups: [
					['newsubcat', 'newgroup', 'newitem' ],
					['recat', 'movecat'],
					['renamecat', 'delcat']
				],
				actions: {
					newsubcat: {
					name: 'New sub-category',
					onClick: function(cat) {
						console.debug(cat);
					  // run when the action is clicked
					}
				  },
				  newgroup: {
					name: 'New group',
					onClick: function(cat) {
					  // run when the action is clicked
					}
				  },
				  newitem: {
					name: 'New item',
					onClick: function(cat) {
						 new_entity('item', 'category', cat);
					  // run when the action is clicked
					}
				  },
				  recat: {
					name: 'Place in another category',
					onClick: function(cat) {
					  // run when the action is clicked
					}
				  },
				  movecat: {
					name: 'Move to another category',
					onClick: function(cat) {
					  // run when the action is clicked
					}
				  },
				  renamecat:{
					name: 'Rename category',
					onClick: function(cat) {
					  // run when the action is clicked
					  rename_category(cat);
					}
				  },
				  delcat:{
					name: 'Delete category',
					onClick: function(cat) {
					  // run when the action is clicked
					}
				  }
				}
			};
			var grpcmdata = {
				fetchElementData: function(rowElem) {
		//			console.debug(rowElem)
					var elid = jQuery(rowElem).parent("div.filter-class-group").attr('id');
					var lastdash = elid.lastIndexOf('-');
					return elid.substr(lastdash + 1);
				},
				actionsGroups: [
					['newsubgrp', 'newitem' ],
					['recat', 'movegrp'],
					['renamegrp', 'delgrp'],
					['mknote']
				],
				actions: {
					newsubgrp: {
						name: 'New sub-group',
						onClick: function(grp) {
						  // run when the action is clicked
						}
					},
					newitem: {
					  name: 'New Item',
					  onClick: function(grp) {
						  new_entity('item', 'group', grp);
						// run when the action is clicked
					  }
					},
					recat: {
					  name: 'Place in another category',
					  onClick: function(grp) {
						// run when the action is clicked
						}
					},
					movegrp: {
					  name: 'Move to another category',
					  onClick: function(grp) {
						// run when the action is clicked
						}
					},
					renamegrp: {
					  name: 'Rename group',
					  onClick: function(grp) {
						  rename_group(grp);
						}
					},
					delgrp: {
					  name: 'Delete group',
					  onClick: function(grp) {
						// run when the action is clicked
						}
					},
					mknote: {
						name: 'Make a note',
						onClick: function(grp) {
							jQuery('#oj-make-note-entity').val(grp);
							show_make_note();
						}
					}
				}
			};
			var itemcmdata = {
				fetchElementData: function(rowElem) {
		//			console.debug(rowElem)
					return jQuery(rowElem).find("input.filter-menu-checkbox").attr('id');
				},
				actionsGroups: [
					['recat', 'moveitm'],
					['renameitm', 'delitm']
				],
				actions: {
					recat: {
					  name: 'Place in another category',
					  onClick: function(itm) {
						  console.debug(itm);
					  }
					},
					moveitm: {
					  name: 'Move to another category',
					  onClick: function(itm) {
						// run when the action is clicked
					  }
					},
					renameitm: {
					  name: 'Rename item',
					  onClick: function(itm) {
						// run when the action is clicked
					  }
					},
					delitm: {
					  name: 'Delete item',
					  onClick: function(itm) {
						// run when the action is clicked
						}
					}
				}
			}
			console.debug("looking for context menu options", catalog);
			if (context_menu_options.hasOwnProperty(lccatalog))
			{
				var cmo = context_menu_options[lccatalog];
				if (cmo.hasOwnProperty("category"))
				{
					var cmoc = cmo["category"];
					if (cmoc.hasOwnProperty("groups"))
					{
						for (var n = 0; n < cmoc.groups.length; n++)
						{
							catcmdata.actionsGroups.push(cmoc.groups[n]);
						}
					}
					if (cmoc.hasOwnProperty("actions"))
					{
						var cmoca = cmoc.actions;
						for (var act in cmoca)
						{
							if (cmoca.hasOwnProperty(act))
							{
								catcmdata.actions[act] = cmoca[act];
							}
						}
					}
					if (cmoc.hasOwnProperty('isShown'))
					{
						var cmoca = cmoc.actions;
						for (var act in cmoca)
						{
							if (cmoca.hasOwnProperty(act))
							{
								catcmdata.actions[act]['isShown'] = cmoca['isShown'];
							}
						}
					}
					else if (cmoc.hasOwnProperty('hidden'))
					{
						for (var n = 0; n < cmoc.hidden.length; n++)
						{
							catcmdata.actions[cmoc.hidden[n]]['isShown'] = function(itm)
							{
								return false;
							};
						}
					}
					if (cmoc.hasOwnProperty('isEnabled'))
					{
						var cmoca = cmoc.actions;
						for (var act in cmoca)
						{
							if (cmoca.hasOwnProperty(act))
							{
								catcmdata.actions[act]['isEnabled'] = cmoca['isEnabled'];
							}
						}
					}
					else if (cmoc.hasOwnProperty('disabled'))
					{
						for (var n = 0; n < cmoc.hidden.length; n++)
						{
							catcmdata.actions[cmoc.hidden[n]]['isEnabled'] = function(itm)
							{
								return false;
							};
						}
					}
				}
				if (cmo.hasOwnProperty("group"))
				{
					var cmog = cmo["group"];
					if (cmog.hasOwnProperty("groups"))
					{
						for (var n = 0; n < cmog.groups.length; n++)
						{
							grpcmdata.actionsGroups.push(cmog.groups[n]);
						}
					}
					if (cmog.hasOwnProperty("actions"))
					{
						var cmoga = cmog.actions;
						for (var act in cmoga)
						{
							if (cmoga.hasOwnProperty(act))
							{
								grpcmdata.actions[act] = cmoga[act];
							}
						}
					}
					if (cmog.hasOwnProperty('isShown'))
					{
						var cmoga = cmog.actions;
						for (var act in cmoga)
						{
							if (cmoga.hasOwnProperty(act))
							{
								grpcmdata.actions[act]['isShown'] = cmoga['isShown'];
							}
						}
					}
					else if (cmog.hasOwnProperty('hidden'))
					{
						for (var n = 0; n < cmog.hidden.length; n++)
						{
							grpcmdata.actions[cmog.hidden[n]]['isShown'] = function(itm)
							{
								return false;
							};
						}
					}
					if (cmog.hasOwnProperty('isEnabled'))
					{
						var cmoga = cmog.actions;
						for (var act in cmoga)
						{
							if (cmoga.hasOwnProperty(act))
							{
								grpcmdata.actions[act]['isEnabled'] = cmoga['isEnabled'];
							}
						}
					}
					else if (cmog.hasOwnProperty('disabled'))
					{
						for (var n = 0; n < cmog.hidden.length; n++)
						{
							grpcmdata.actions[cmog.hidden[n]]['isEnabled'] = function(itm)
							{
								return false;
							};
						}
					}
				}
				if (cmo.hasOwnProperty("item"))
				{
					var cmoi = cmo["item"];
					if (cmoi.hasOwnProperty("groups"))
					{
						for (var n = 0; n < cmoi.groups.length; n++)
						{
							itemcmdata.actionsGroups.push(cmoi.groups[n]);
						}
					}
					if (cmoi.hasOwnProperty("actions"))
					{
						var cmoia = cmoi.actions;
						for (var act in cmoia)
						{
							if (cmoia.hasOwnProperty(act))
							{
								itemcmdata.actions[act] = cmoia[act];
							}
						}
					}
					if (cmoi.hasOwnProperty('isShown'))
					{
						var cmoia = cmoi.actions;
						for (var act in cmoia)
						{
							if (cmoia.hasOwnProperty(act))
							{
								itemcmdata.actions[act]['isShown'] = cmoia['isShown'];
							}
						}
					}
					else if (cmoi.hasOwnProperty('hidden'))
					{
						for (var n = 0; n < cmoi.hidden.length; n++)
						{
							itemcmdata.actions[cmoi.hidden[n]]['isShown'] = function(itm)
							{
								return false;
							};
						}
					}
					if (cmoi.hasOwnProperty('isEnabled'))
					{
						var cmoia = cmoi.actions;
						for (var act in cmoia)
						{
							if (cmoia.hasOwnProperty(act))
							{
								itemcmdata.actions[act]['isEnabled'] = cmoia['isEnabled'];
							}
						}
					}
					else if (cmoi.hasOwnProperty('disabled'))
					{
						for (var n = 0; n < cmoi.hidden.length; n++)
						{
							itemcmdata.actions[cmoi.hidden[n]]['isEnabled'] = function(itm)
							{
								return false;
							};
						}
					}
				}
			}
			category_context_menu = new BootstrapMenu('.filter-class-category>div.panel-heading', catcmdata);
			group_context_menu = new BootstrapMenu('.filter-class-group>div.panel-heading', grpcmdata);
			item_context_menu = new BootstrapMenu('.filter-class-item', itemcmdata);
			for (var n = 0; n < init_functions.length; n++)
			{
				init_functions[n]();
			}
		//	$('a').on('click', function() { inFormOrLink = true; });
		//	$('form').on('submit', function() { inFormOrLink = true; });

//			jQuery(window).on("beforeunload", function() {
//		//		console.debug(nobeforeunload?"null":"Do you really want to logout and leave?");
//				if (!nobeforeunload)
//				{
//					return "Do you really want to logout and leave?";
//				}
//			});
//			jQuery(window).on("unload", function() {
//				console.debug("onload leaving");
//				window.location = logout1_url;
//			});
			tinymce.init({
			  selector: 'textarea',
			  height: 500,
			  menubar: false,
			  plugins: [
				'advlist autolink lists link image charmap print preview anchor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime media table contextmenu paste code'
			  ],
			  toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
			  content_css: [
				'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
				'//www.tinymce.com/css/codepen.min.css']
			});
			jQuery(document).on('focusin', function(e) {
				if ($(e.target).closest(".mce-window").length) {
					e.stopImmediatePropagation();
				}
			});

			stamp();
		});
	});
	
	//window.location = logout_url;
});