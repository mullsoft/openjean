/* 
 * Class to construct an accordion type menu for filters
 */

var specimen_data = {
	containerid: "containerid",
	idprefix: "", // used if two filters with the same contents are needed to enable unique ids
	callback: function(returndata){},
	valuecallback: function(categoryid, number_of_values){}, // optional, if present this function will be called to provide the values. If number_of_values == 0 it should construct
										   // an array of category or value objects as below and call filter.fillvalues(categoryid, items) to add them. If number_of_values == -1
										   // this indicates a leaf node
	radio: false, // optional, false by default, if true only single selection of values is permitted throughout the tree, if a string then that is used as the name
				  // for the radio group, always propagates.
	categories: [
		{
			id: "categoryid", // optional, an id attribute for the html element, if not provided one will be generated
			label: "a label", // the text that will appear
			value: null, // optional, if not provided the label is used
			cssclass: "", // optional, an additional css class for the element
			selectable: false, // optional, if set the category will show a checkbox or radiobox irrespective of any propagation
			radio: false, // optional, false by default, if true only single selection of values is permitted, if a string then that is used as the name for the radio group
			tooltip: false, // false turns tooltip off, string or function(category) returning string is used as a tooltip, true provides default tooltip for draggable items
			draggable: false, // optional, if true then all draggable items at the same level can be dragged and dropped to change order
			dropcallback: function(returndata, dropid){}, // optional, if present this will be called when a draggable category is dropped onto this
			propagate: "none", // optional, default "none"; other values "down" checking this value will cause all checkboxes to be checked in subcategories; "up" check 
			//checkboxes in ancestor categories
			propagatefrom: "acategoryid", // optional, if present propagation up or down will be performed by a check on the category with the given id.
										  // if the id of this category or "self" this category will have a checkbox and a seperate arrow button to expand/collapse
			values: [
				{
					id: "elementid", // optional, an id attribute for the html element, if not provided one will be generated
					label: "a label", // the text that will appear
					value: "avalue", // optional, if not provided the label is used
					cssclass: "", // optional, an additional css class for the element
					selectable: false, // optional, if set the category will show a checkbox or radiobox irrespective of any propagation
					radio: false, // optional, false by default, if true only single selection of values is permitted, if a string then that is used as the name for the radio group
					draggable: false, // if true then all draggable items at the same level can be dragged and dropped to change order
					tooltip: false, // false turns tooltip off, string or function returning string is used as a tooltip, true provides default tooltip for draggable items
					dropcallback: function(returndata, dropid){}, // optional, if present this will be called when a draggable category is dropped onto this
					propagate: "none", // optional, default "none"; other values "down" checking this value will cause all checkboxes to be checked in subcategories; "up" check 
					//checkboxes in ancestor categories
					propagatefrom: "acategoryid", // optional, if present propagation up or down will be performed by a check on the category with the given id.
												  // if the id of this category or "self" this category will have a checkbox and a seperate arrow button to expand/collapse
					relatedto:"a,b", // optional a comma seperated list of elementids of related checkboxes, a check here will automatically add a check on each of these
					values: [ // if sub values are present this will be treated as a subcategory rather than a selectable value
						{
							id: "elementid", // optional, an id attribute for the html element, if not provided one will be generated
							label: "alabel", // the text that will appear
							value: "avalue", // optional, if not provided the label is used
							tooltip: false, // false turns tooltip off, string or function returning string is used as a tooltip, true provides default tooltip for draggable items
							selectable: true, // optional, if set the category will show a checkbox or radiobox irrespective of any propagation
						}
					]
				}
			]
		}
	]
};

var specimen_return_data = {
	openid:"none", // the id of the open top level category, if there is one. The value "none" or null or missing property indicates that none are open
	type: "event type", // the type of event e.g shown, hidden, change
	targetid:"target id", // the id of the component on which the event was triggered
	checked:[ // a list of all values that are selected
		{
			category: "categoryid",
			element: "elementid",
			value: "value"
		}
	],
	unchecked:[ // a list of all values that are not selected
		{
			category: "categoryid",
			element: "elementid",
			value: "value"
		}
	],
	newly_checked:[ // a list of thse values that are newly selected
		{
			category: "categoryid",
			element: "elementid",
			value: "value"
		}
	],
	newly_unchecked:[ // a list of all values that are newly unselected
		{
			category: "categoryid",
			element: "elementid",
			value: "value"
		}
	]
};

function FILTER(data)
{
	this.data = data;
	this.categories = {};
	this.current_drop = null;
	this.draggable_categories = [];
	this.default_tooltip = "drag this item to reorder";
	this.propagate_down = {};
	this.propagate_up = {};
	this.related_to = {};
	this.ignore_callback = false;
	this.current_checked = [];
	this.visibility_check = null;
	
	this.initialise = function()
	{
		if (!this.data.hasOwnProperty('idprefix'))
		{
			this.data.idprefix = "";
		}
		var container = jQuery('#' + this.data.containerid);
		container.addClass("panel-group");
		for (var c = 0; c < this.data.categories.length; c++)
		{
			var category = this.data.categories[c];
			this.append_category(category, false);
		}
		container.on("show.bs.collapse", this.get_show_function(this));
		container.on("shown.bs.collapse", this.get_callback_function(this, -1));
		container.on("hidden.bs.collapse", this.get_callback_function(this, -1));
	};
	
	var get_category_values_string = function(filter, values, c)
	{
		var category = filter.get_category_by_id(c);
//		console.debug("get_category_values_string category", category, "for", c);
//		console.debug("get_category_values_string categories", filter.categories);
		var cb = "checkbox";
		var nm = "";
		if (!category.hasOwnProperty('radio') && filter.data.hasOwnProperty('radio'))
		{
			category['radio'] = filter.data.radio;
		}
		if (category.hasOwnProperty("radio") && category.radio)
		{
			cb = "radio";
			if (typeof category.radio === 'string')
			{
				nm = ' name="' + category.radio + '"';
			}
			else
			{
				nm = ' name="filter-radio-' + category.id + '"';
			}
		}
		var ret = "";
		for (var v = 0; v < values.length; v++)
		{
			var val = values[v];
			if (!val.hasOwnProperty("id"))
			{
				val.id = c + "-" + v;
			}
			if (!val.hasOwnProperty("value"))
			{
				val.value = val.label;
			}
			var params = "";
			if (val.hasOwnProperty('parameters'))
			{
				for (var param in val.parameters)
				{
					if (val.parameters.hasOwnProperty(param))
					{
						params += '<div style="display:none" id="' + filter.data.idprefix + 'parameter-' + val.id + '-' + param + '">' + category.parameters[param] + '</div>';
					}
				}
			}
			if (val.hasOwnProperty("values"))
			{
				// subcategory
				filter.categories[val.id] = val;
				if (val.hasOwnProperty('propagate'))
				{
					var prop = val['propagate'];
//					console.debug("propagate", prop);
					if (prop !== 'none')
					{
						var propfromid;
						if (val.hasOwnProperty('propagatefrom'))
						{
							propfromid = val['propagatefrom'];
						}
						else
						{
							propfromid = val.id;
						}
						if (prop === 'down')
						{
							filter.propagate_down[propfromid] = val.id;
						}
						else if (prop === 'up')
						{
							filter.propagate_up[propfromid] = val.id;
						}
					}
				}
				var subid = 'filter-menu-list-' + c;
				ret += get_category_div_string(filter, subid, val) + params;
//				ret += '<li class="filter-menu-value" id="filter-menu-value-' + filter.data.idprefix + val.id + '">' +
//				ret += '<div class="filter-menu-value" id="filter-menu-value-' + filter.data.idprefix + val.id + '">' +
//						'<div id="inner-' + filter.data.idprefix + val.id + '">' + 
//						get_category_div_string(filter, subid, val) + params +
//						'</div>';
			}
			else
			{
				var tt = val.id + ": " + val.label;
				var ttdata = "";
				if (val.hasOwnProperty('tooltip'))
				{
					tt = val['tooltip'];
					var ttd = tt.indexOf("::");
					if (ttd >= 0)
					{
						ttdata = '<input type="hidden" class="filter-tooltip-data" value="' + tt.substr(0, ttd) + '"/>' +
								'<input type="hidden" class="filter-tooltip-text" value="' + tt.substr(ttd + 2) + '"/>';
						tt = "from: " + tt.substr(ttd + 2);
					}
				}
				if (val.hasOwnProperty('relatedto'))
				{
					this.related_to[val.id] = val['relatedto'].split(',');
				}
				var cssclass = "";
				if (val.hasOwnProperty("cssclass"))
				{
					cssclass = val["cssclass"] + " ";
				}
				if ((filter.visibility_check != null) && !filter.visibility_check.hasOwnProperty(val.id))
				{
					cssclass += 'filter-invisible ';
				}
				var sel = !val.hasOwnProperty("selectable") || val["selectable"] || val["selectable"] === "true";
				var seldisp = sel?"":' style="display:none;"';
				var selstr = '<input type="' + cb + '" class="filter-menu-checkbox" id="' + filter.data.idprefix + val.id + '" value="' + val.value + '"' + nm + seldisp + '/>';
//				ret += '<li class="' + cssclass + 'filter-menu-value" id="filter-menu-value-' + filter.data.idprefix + val.id + '"><div class="oj-panel-heading">' +
//				ret += '<div class="' + cssclass + 'filter-menu-value" id="filter-menu-value-' + filter.data.idprefix + val.id + '">' +
//						'<div class="oj-panel-heading">' +
//					'<label class="filter-menu-label">' +
//					selstr +
//					'<div class="filter-label-all"><span class="filter-label-pre"></span><span class="filter-label-text" id="' + filter.data.idprefix + 'filter-label-text-' +
//					val.id + '" title="' + tt + '">' + val.label +
//					'</span><span class="filter-label-post"></span>' + ttdata +
//					'</div>' + params + '</label>' +
//					'</div></div>';
				ret += '<div class="' + cssclass + 'oj-panel-heading" id="' + filter.data.idprefix + 'oj-panel-heading-' + val.id + '">' +
					'<label class="filter-menu-label">' +
					selstr +
					'<div class="filter-label-all"><span class="filter-label-pre"></span><span class="filter-label-text" id="' + filter.data.idprefix + 'filter-label-text-' +
					val.id + '" title="' + tt + '">' + val.label +
					'</span><span class="filter-label-post"></span>' + ttdata +
					'</div>' + params + '</label>' +
					'</div>';
			}

		}
		return ret;
	};
	
	var get_category_div_string = function(filter, containerid, category)
	{
		var drg = '';
		var cssclass = category.cssclass;
		if (category.draggable)
		{
			drg = ' draggable="true"';
			filter.draggable_categories.push(category);
			cssclass += ' filter-menu-draggable';
		}
		else
		{
			cssclass += ' filter-menu-not-draggable';
		}
		if (filter.visibility_check != null)
		{
//			console.debug("check visibility", category.id);
			if (!filter.visibility_check.hasOwnProperty(category.id))
			{
				cssclass += ' filter-invisible';
			}
		}
		if (!category.hasOwnProperty('selectable'))
		{
			category['selectable'] = false;
		}
		var cb = "checkbox";
		var nm = "";
		if (!category.hasOwnProperty('radio') && filter.data.hasOwnProperty('radio'))
		{
			category['radio'] = filter.data.radio;
		}
		if (category.hasOwnProperty("radio") && category.radio)
		{
			cb = "radio";
			if (typeof category.radio === 'string')
			{
				nm = ' name="' + category.radio + '"';
			}
			else
			{
				nm = ' name="filter-radio-' + category.id + '"';
			}
		}
//		var cb = category.radio?"radio":"checkbox";
		var tt = "";
		if (category.tooltip)
		{
			tt = '';
			if ((category.tooltip === true) && category.draggable)
			{
				tt += filter.default_tooltip;
			}
			else if (typeof category.tooltip === 'string')
			{
				tt += category.tooltip;
			}
			else if (typeof category.tooltip === "function")
			{
				tt += category.tooltip(category);
			}
//			tt += '"';
		}
		else if (category.draggable)
		{
			tt = filter.default_tooltip;
		}
		else
		{
			tt = category.id + ": " + category.label;
		}
		var catlink;
		if (category.hasOwnProperty('propagate'))
		{
			var prop = category['propagate'];
//					console.debug("propagate", prop);
			if (prop !== 'none')
			{
				var propfromid;
				if (category.hasOwnProperty('propagatefrom'))
				{
					propfromid = category['propagatefrom'];
					if (propfromid == 'self')
					{
						propfromid = category.id;
					}
				}
				else
				{
					propfromid = category.id;
				}
				if (prop === 'down')
				{
					filter.propagate_down[propfromid] = category.id;
				}
				else if (prop === 'up')
				{
					filter.propagate_up[propfromid] = category.id;
				}
			}
		}//&#9652;
		var params = "";
		if (category.hasOwnProperty('parameters'))
		{
			for (var param in category.parameters)
			{
				if (category.parameters.hasOwnProperty(param))
				{
					params += '<div style="display:none" id="' + filter.data.idprefix + 'parameter-' + category.id + '-' + param + '">' + category.parameters[param] + '</div>';
				}
			}
		}
//		console.debug("category", category);
		if (category.selectable || (category.hasOwnProperty('propagatefrom') && ((category['propagatefrom'] == category.id) || (category['propagatefrom'] == 'self'))))
		{
			catlink = '<input type="' + cb + '" class="filter-menu-checkbox filter-menu-checkbox-plus filter-menu-checkbox-' + category.id + '" id="' + filter.data.idprefix + 'input-' + category.id +
					'" value="' + category.value + '"' + nm + '/>' +
//					'<label class="filter-menu-label" for="' + filter.data.idprefix + 'link-' + category.id + '">' +
					'<a class="open-close" id="' + filter.data.idprefix + 'link-' + category.id + '" href="#' + filter.data.idprefix + 'panel-collapse-' + category.id +
					'" data-toggle="collapse" data-parent="#' + containerid + '">' +
					'<div class="filter-label-all"><span class="filter-label-pre"></span><span class="filter-label-text" id="' + filter.data.idprefix + 'filter-label-text-' +
					category.id + '" title="' + tt + '">' + category.label +
					'</span><span class="filter-label-post"></span>' +
					'</div></a>';// +
//					'</label>';
		}
		else
		{
			catlink = '<a id="' + filter.data.idprefix + 'link-' + category.id + '" href="#' + filter.data.idprefix + 'panel-collapse-' + category.id +
					'" data-toggle="collapse" data-parent="#' + containerid + '">' +
					'<div class="filter-label-all"><span class="filter-label-pre"></span><span class="filter-label-text" id="' + filter.data.idprefix + 'filter-label-text-' +
					category.id + '" title="' + tt + '">' + category.label +
					'</span><span class="filter-label-post"></span>' +
					'</div>' + '</a>' +
					'<input type="hidden" value="' + category.value + '" id="' + filter.data.idprefix + 'input-' + category.id + '"/>';
		}
		var categorydivstring = '<div class="filter-menu-value panel panel-default ' + cssclass + '" id="' + filter.data.idprefix + 'filter-menu-value-' + category.id + '"' + drg + '>' +
				'<div class="panel-heading oj-panel-heading" id="' + filter.data.idprefix + 'oj-panel-heading-' + category.id + '">' +
				'<h2 class="panel-title">' +
				catlink +
				'<div class="header-button"><button type="button" class="select-all-button btn btn-xs" title="select all">&#9745;</button>' +
				'<button type="button" class="reset-all-button btn btn-xs" title="reset all">&times;</button></div>' +
				'</h2>' +
				'<div class="filter-selection-container" id="' + filter.data.idprefix + 'filter-selection-container-' + category.id + '"></div>' +
				'</div>' +
				'<div class="panel-collapse collapse" id="' + filter.data.idprefix + 'panel-collapse-' + category.id + '">' +
				'<div class="panel-body filter-panel-body panel-group" id="filter-panel-group-' + category.id + '">' + 
				'<div class="panel filter-menu-list" id="' + filter.data.idprefix + 'filter-menu-list-' + category.id + '">';
//				'<ul class="filter-menu-list" id="' + filter.data.idprefix + 'filter-menu-list-' + category.id + '">';
		if (jQuery.isArray(category.values))
		{
			categorydivstring += get_category_values_string(filter, category.values, category.id);
		}
		categorydivstring += '</div></div></div>' + params + '</div>';
		return categorydivstring;
	};
	
	this.set_visibility_check = function (vc)
	{
		this.visibility_check = vc;
	};
	
	this.do_callback = function(usingid, callback_index)
	{
		if (typeof usingid === 'undefined')
		{
			usingid = "###";
		}
		var cbf = this.get_callback_function(this, callback_index);
		var ev = {
			type: "call",
			target: {
				id: usingid
			}
		};
		cbf(ev);
	};
	
	this.get_callback_function = function(thefilter, callback_index)
	{
		return function(event)
		{
			var theevent = event || window.event;
//			console.debug("ignorecallback", thefilter.ignore_callback);
//			show_stackTrace();
			if (!thefilter.ignore_callback)
			{
				var cat = "###"; // not any possible id

				if (callback_index >= 0)
				{
					var selection_div = jQuery('#'  + thefilter.data.idprefix + 'filter-selection-container-' + callback_index);
					selection_div.html('');
					cat = selection_div.closest(".panel").attr("id").substr(thefilter.data.idprefix.length);
//					console.debug('callback_index',callback_index, "cat", cat);
				}
				if (thefilter.related_to.hasOwnProperty(theevent.target.id))
				{
					var relto = thefilter.related_to[theevent.target.id];
					for (var n = 0; n < relto.length; n++)
					{
						var chk = jQuery('#' + theevent.target.id).prop("checked");
						jQuery('#' + relto[n]).prop("checked", chk);
					}
				}
//				console.debug("ppg", thefilter.propagate_down);
//				console.debug("ppg", theevent.target.id);
				var catid = theevent.target.id.substr("input-".length);
				if (thefilter.propagate_down.hasOwnProperty(catid))
				{
					var chk = jQuery('#' + thefilter.data.idprefix + 'input-' + catid).prop("checked");
					console.debug("pg down", theevent);
					thefilter.ignore_callback = true;
					jQuery('#' + thefilter.data.idprefix + 'filter-menu-value-' + thefilter.propagate_down[catid] + " .filter-menu-checkbox").prop("checked", chk);
					thefilter.ignore_callback = false;
				}
				var returndata = thefilter.get_return_data();
				returndata["type"] = theevent.type;
				returndata["targetid"] = theevent.target.id;
				var opened = jQuery("#" + thefilter.data.idprefix + thefilter.data.containerid + "> .panel .in");

	//			console.debug('opened',opened);

				if (opened.length === 0)
				{
					returndata["openid"] = "none";
				}
				else
				{
					returndata["openid"] = jQuery(opened[0]).parent("div").attr("id").substr(thefilter.data.idprefix.length);
				}

	//			var rtnopenid = returndata["openid"];
	//			console.debug('rtnopenid',rtnopenid);

				if (thefilter.categories.hasOwnProperty(cat))
				{
					var category = thefilter.categories[cat];
					if (category.radio)
					{
						// close it
						jQuery("#" + thefilter.data.idprefix + cat + " .panel-collapse").removeClass("in");
						jQuery("#" + thefilter.data.idprefix + cat + " .panel-collapse").addClass("collapse");
					}
				}
				console.debug("type", event.type, "target", event.target.id);
				if (event.type === 'shown')
				{
					var inpid = "link-" + event.target.id.substr("panel-collapse-".length);
//					if (jQuery('#' + thefilter.data.idprefix + inpid).is(".open-close"))
//					{
//						jQuery('#' + thefilter.data.idprefix + inpid).html('&#9652;');
//					}
				}
				else if (event.type === 'hidden')
				{
					var inpid = "link-" + event.target.id.substr("panel-collapse-".length);
//					if (jQuery('#' + thefilter.data.idprefix + inpid).is(".open-close"))
//					{
//						jQuery('#' + thefilter.data.idprefix + inpid).html('&#9662;');
//					}
				}
//				console.debug("callback data", returndata);
				if (event.type !== 'hidden')
				{
					thefilter.data.callback(returndata);
					for (var n = 0; n < returndata.checked.length; n++)
					{
	//					console.debug('returndata.checked[n].category',returndata.checked[n].category, "cat", cat);
						if (cat === returndata.checked[n].category)
						{
							var item_div_string = '<div class="filter-selection-item" id="' + thefilter.data.idprefix + returndata.checked[n].element + 
									'-selected"><span class="filter-selection-pre"></span><span class="filter-selection-label">' + 
									returndata.checked[n].label + '</span><span class="filter-selection-post"></span></div>';
							selection_div.append(jQuery(item_div_string));
						}
					}
					thefilter.check_for_checked();
				}
			}
		};
	};
	
	this.get_show_function = function (filter)
	{
		return function(event)
		{
			var theevent = event || window.event;
//			console.debug("show", theevent.target.id);
			if (filter.data.hasOwnProperty('valuecallback'))
			{
				var upid = jQuery('#' + theevent.target.id).closest("div.panel").attr("id").substr(filter.data.idprefix.length);
				var id = upid.substr("filter-menu-value-".length);
				var category = filter.categories[id];
				console.debug("show callback category", upid, id, category);
				var nval = (!category.hasOwnProperty('values'))?-1:category.values.length;
				filter.data.valuecallback(id, nval);
			}
		};
	};
	
	this.clear_values = function (categoryid)
	{
		var cat = this.categories[categoryid];
		if (cat)
		{
			cat.values = [];
			this.close_category(categoryid);
			jQuery("#" + this.data.idprefix + "filter-menu-list-" + categoryid).html('');
		}
	};
	
	this.clear_all_categories = function ()
	{
		this.ignore_callback = true;
//		console.debug("1.set ignorecallback", this.ignore_callback);
		for (var categoryid in this.categories)
		{
			if (this.categories.hasOwnProperty(categoryid))
			{
				this.clear_values(categoryid);
			}
		}
		this.ignore_callback = false;
//		console.debug("2.set ignorecallback", this.ignore_callback);
	};
	
	this.fill_values = function(categoryid, items)
	{
//		console.debug("show", categoryid, items);
		for (var n = 0; n < items.length; n++)
		{
			var cat = items[n];
			this.categories[cat["id"]] = cat;
		}
		jQuery("#" + this.data.idprefix + "filter-menu-list-" + categoryid).html(get_category_values_string(this, items, categoryid));
		if (jQuery('#input-' + categoryid).is(":checked"))
		{
			jQuery("#" + this.data.idprefix + "filter-menu-list-" + categoryid + " .filter-menu-checkbox").prop("checked", true);
		}
		var thisfilter = this;
		var thisc = categoryid;
		var category = this.get_category_by_id(categoryid);
		category['values'] = items;
//		console.debug("c", c);
		jQuery("#" + this.data.idprefix + "filter-menu-value-" + categoryid + " .filter-menu-checkbox").each(function()
		{
//			var thisid = jQuery(this).attr("id");
//			var dash = thisid.lastIndexOf('-');
//			var thisc = parseInt(thisid.substr(dash + 1));
//			console.debug(thisid, thisc);
			jQuery(this).on("change", thisfilter.get_callback_function(thisfilter, thisc));
		});
		jQuery("#" + this.data.idprefix + "filter-menu-value-" + categoryid + " .select-all-button").on("click", function()
		{
//			console.debug("select all");
			jQuery("#" + thisfilter.data.idprefix + "filter-menu-value-" + thisc + " .filter-menu-checkbox").prop("checked", true);
			thisfilter.do_callback('###', thisc);
		});
		jQuery("#" + this.data.idprefix + "filter-menu-value-" + categoryid + " .reset-all-button").on("click", function()
		{
//			console.debug("reset all");
			jQuery("#" + thisfilter.data.idprefix + "filter-menu-value-" + thisc + " .filter-menu-checkbox").prop("checked", false);
			thisfilter.do_callback('###', thisc);
		});
		if (category.draggable)
		{
			var item = jQuery('#' + this.data.idprefix + categoryid);
			var thisfilter = this;
//			item.addClass("dimension");
			item.on("dragstart", function(e)
			{
				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/plain', $(this).attr("id"));
//						console.debug("original", e);
				thisfilter.current_drop = null;
			});
			jQuery("#" + categoryid + " .panel-title").on("dragenter", function(e)
			{
				var div = jQuery(this).closest(".panel");
				var divid = jQuery(div).attr("id").substr(this.data.idprefix.length);
//				var dash = divid.lastIndexOf('-');
				thisfilter.current_drop = divid;
				jQuery(div).addClass("over");
//						console.debug("enter", $(div).attr("id"));
			});
			jQuery("#" + categoryid + " .panel-title").on("dragleave", function(e)
			{
				var div = jQuery(this).closest(".panel");
				jQuery(div).removeClass("over");
//						console.debug("leave", $(div).attr("id"));
				thisfilter.current_drop = null;
			});
			jQuery("#" + categoryid + " .panel-title").on("dragover", function(e)
			{
				if (e.originalEvent.preventDefault) {
					e.originalEvent.preventDefault(); // Necessary. Allows us to drop.
				}

				e.originalEvent.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

				return false;
			});
			item.on("dragend", function(e)
			{
				if (thisfilter.current_drop != null)
				{
					var dropid = thisfilter.current_drop;
					thisfilter.current_drop = null;
//					var dropid = "dimension-" + cdrop;
					var thisid = jQuery(this).attr("id");
					if (thisid != dropid)
					{
//								console.debug("drop " + thisid + " on " + dropid);
						$('#' + dropid).removeClass("over");
						thisfilter.drop(thisid, dropid);
						if (e.originalEvent.stopPropagation) {
							e.originalEvent.stopPropagation(); // Stops some browsers from redirecting.
						}
//								console.debug(e);
//								console.debug("data is", e.originalEvent.dataTransfer.getData('text/plain'));
//								console.debug("this is", $(this).attr("id"));
					}
				}
				return false;
			});
		}
	};
	
	// returns the value of the input checkbox, radiobox or hidden that corresponds to this category id
	this.get_value = function(categoryid)
	{
		return jQuery('#' + this.data.idprefix + 'input-' + categoryid).val();
	};
	
	// returns the text that corresponds to this category id
	this.get_text = function(categoryid)
	{
		return jQuery('#' + this.data.idprefix + 'filter-label-text-' + categoryid).text().trim();
	};
	
	this.check_for_checked = function()
	{
		for (var catid in this.categories)
		{
			if (this.categories.hasOwnProperty(catid))
			{
				var l = jQuery('#' + this.data.idprefix + catid + " input:checked").length;
				if (l === 0)
				{
					jQuery('#' + this.data.idprefix + catid + " .reset-all-button").hide();
				}
				else
				{
					jQuery('#' + this.data.idprefix + catid + " .reset-all-button").show();
				}
//				console.debug("checked " + catid, jQuery('#' + catid + " input:checked").length);
			}
		}
	};
	
	this.get_return_data = function()
	{
		var last_checked = this.current_checked;
		this.current_checked = [];
		var ret = {
			checked:[],
			unchecked:[],
			newly_checked:[],
			newly_unchecked:[]
		};
		var thisfilter = this;
		jQuery(".filter-menu-checkbox").each(function()
		{
			var el = jQuery(this);
			var elid = el.attr("id");
			var obj = {
				category: el.closest(".panel").attr("id").substr(thisfilter.data.idprefix.length),
				element: elid,
				value: el.val(),
				label: el.parent("label").children('div.filter-label-all').children("span.filter-label-text").text()
			};
			if (el.is(":checked"))
			{
				ret.checked.push(obj);
				thisfilter.current_checked.push(elid);
				if (jQuery.inArray(elid, last_checked) < 0)
				{
					ret.newly_checked.push(obj);
				}
			}
			else
			{
				ret.unchecked.push(obj);
				if (jQuery.inArray(elid, last_checked) >= 0)
				{
					ret.newly_unchecked.push(obj);
				}
			}
		});
		return ret;
	};
	
	this.append_category = function(category, addtodata)
	{
//		console.debug("append_category", category);
		if (!category.hasOwnProperty('value'))
		{
			category.value = category.label;
		}
		if (!category.hasOwnProperty('radio'))
		{
			category.radio = this.data.hasOwnProperty('radio')?this.data.radio:false;
		}
		if (!category.hasOwnProperty('draggable'))
		{
			category.draggable = false;
		}
		if (!category.hasOwnProperty('cssclass'))
		{
			category.cssclass = "";
		}
		if (typeof c === 'undefined')
		{
			c = this.data.categories.length;
		}
		if (!category.hasOwnProperty('id'))
		{
			category.id = this.data.categories.length;
		}
		if ((typeof addtodata === 'undefined') || addtodata)
		{
			this.data.categories.push(category);
		}
        if (category.hasOwnProperty('propagate'))
		{
			var prop = category['propagate'];
//			console.debug("propagate", prop);
			if (prop !== 'none')
			{
				var propfromid;
				if (category.hasOwnProperty('propagatefrom'))
				{
					if (category.propagatefrom === 'self')
					{
						propfromid = category.id;
					}
					else
					{
						propfromid = category['propagatefrom'];
					}
				}
				else if (category.hasOwnProperty("id"))
				{
					propfromid = category.id;
				}
				if (prop === 'down')
				{
					this.propagate_down[propfromid] = category.id;
				}
				else if (prop === 'up')
				{
					this.propagate_up[propfromid] = category.id;
				}
			}
		}
		
		this.categories[category.id] = category;
		var categorydivstring = get_category_div_string(this, this.data.containerid, category);
		jQuery('#' + this.data.containerid).append(jQuery(categorydivstring));
//		jQuery(".filter-menu-checkbox-" + c).on("change", this.get_callback_function(this, category.id));
		var thisfilter = this;
		var thisc = category.id;
//		console.debug("c", c);
		jQuery("#filter-menu-value-" + category.id + " .filter-menu-checkbox").each(function()
		{
//			var thisid = jQuery(this).attr("id");
//			var dash = thisid.lastIndexOf('-');
//			var thisc = parseInt(thisid.substr(dash + 1));
//			console.debug(thisid, thisc);
			jQuery(this).on("change", thisfilter.get_callback_function(thisfilter, thisc));
		});
		jQuery("#filter-menu-value-" + thisc + " .select-all-button").on("click", function()
		{
//			console.debug("select all");
			jQuery("#filter-menu-value-" + thisc + " .filter-menu-checkbox").prop("checked", true);
			thisfilter.do_callback('###', thisc);
		});
		jQuery("#filter-menu-value-" + thisc + " .reset-all-button").on("click", function()
		{
//			console.debug("reset all");
			jQuery("#filter-menu-value-" + thisc + " .filter-menu-checkbox").prop("checked", false);
			thisfilter.do_callback('###', thisc);
		});
		if (category.draggable)
		{
			var item = jQuery('#' + category.id);
			var thisfilter = this;
//			item.addClass("dimension");
			item.on("dragstart", function(e)
			{
				e.originalEvent.dataTransfer.effectAllowed = 'move';
				e.originalEvent.dataTransfer.setData('text/plain', $(this).attr("id"));
//						console.debug("original", e);
				thisfilter.current_drop = null;
			});
			jQuery("#" + category.id + " .panel-title").on("dragenter", function(e)
			{
				var div = jQuery(this).closest(".panel");
				var divid = jQuery(div).attr("id");
//				var dash = divid.lastIndexOf('-');
				thisfilter.current_drop = divid;
				jQuery(div).addClass("over");
//						console.debug("enter", $(div).attr("id"));
			});
			jQuery("#" + category.id + " .panel-title").on("dragleave", function(e)
			{
				var div = jQuery(this).closest(".panel");
				jQuery(div).removeClass("over");
//						console.debug("leave", $(div).attr("id"));
				thisfilter.current_drop = null;
			});
			jQuery("#" + category.id + " .panel-title").on("dragover", function(e)
			{
				if (e.originalEvent.preventDefault) {
					e.originalEvent.preventDefault(); // Necessary. Allows us to drop.
				}

				e.originalEvent.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

				return false;
			});
			item.on("dragend", function(e)
			{
				if (thisfilter.current_drop != null)
				{
					var dropid = thisfilter.current_drop;
					thisfilter.current_drop = null;
//					var dropid = "dimension-" + cdrop;
					var thisid = jQuery(this).attr("id");
					if (thisid != dropid)
					{
//								console.debug("drop " + thisid + " on " + dropid);
						$('#' + dropid).removeClass("over");
						thisfilter.drop(thisid, dropid);
						if (e.originalEvent.stopPropagation) {
							e.originalEvent.stopPropagation(); // Stops some browsers from redirecting.
						}
//								console.debug(e);
//								console.debug("data is", e.originalEvent.dataTransfer.getData('text/plain'));
//								console.debug("this is", $(this).attr("id"));
					}
				}
				return false;
			});
		}
	};
	
	this.remove_category_by_id = function(categoryid)
	{
		jQuery('#' + categoryid).remove();
		var newcats = [];
		for (var c = 0; c < this.data.categories.length; c++)
		{
			var cat = this.data.categories[c];
			if (cat.id != categoryid)
			{
				newcats.push(cat);
			}
		}
		if (newcats.length != this.data.categories.length)
		{
			this.data.categories = newcats;
		}
	};
	
	this.remove_category_by_index = function(idx)
	{
		var newcats = [];
		for (var c = 0; c < this.data.categories.length; c++)
		{
			var cat = this.data.categories[c];
			if (c == idx)
			{
				jQuery('#' + cat.id).remove();
			}
			else
			{
				newcats.push(cat);
			}
		}
		if (newcats.length != this.data.categories.length)
		{
			this.data.categories = newcats;
		}
	};
	
	this.remove_categories_after_index = function(idx)
	{
		var newcats = [];
		for (var c = 0; c < this.data.categories.length; c++)
		{
			var cat = this.data.categories[c];
			if (c > idx)
			{
				jQuery('#' + this.data.idprefix + cat.id).remove();
			}
			else
			{
				newcats.push(cat);
			}
		}
		if (newcats.length != this.data.categories.length)
		{
			this.data.categories = newcats;
		}
	};
	
	this.get_category_by_id = function(categoryid)
	{
		var ret = null;
		if (this.categories.hasOwnProperty(categoryid))
		{
			ret = this.categories[categoryid];
		}
		return ret;
	};
	
	this.get_category_by_index = function(idx)
	{
		var ret = null;
		var catdivs = jQuery('#' + this.data.idprefix + this.data.containerid + " > .panel");
		if ((idx >= 0) && (idx < catdivs.length))
		{
			var catid = jQuery(catdivs[idx]).attr("id").substr(this.data.idprefix.length);
			ret = this.get_category_by_id(catid);
		}
		return ret;
	};
	
	// in the order in which they appear on the screen
	this.get_categories = function()
	{
		var ret = [];
		var catdivs = jQuery('#' + this.data.idprefix + this.data.containerid + " > .panel");
		for (var i = 0; i < catdivs.length; i++)
		{
			var catid = jQuery(catdivs[i]).attr("id").substr(this.data.idprefix.length);
			ret.push(this.get_category_by_id(catid));
		}
		return ret;
	};
	
	this.drop = function(thisid, dropid)
	{
		jQuery('#' + thisid).insertBefore(jQuery('#' + dropid));
		var dropcat = this.get_category_by_id(dropid);
		if ((dropcat != null) && dropcat.hasOwnProperty("dropcallback"))
		{
			dropcat.dropcallback(this.get_return_data(), dropid);
		}
//		var container = jQuery('#' + this.data.containerid);
//		var cdrop = parseInt(dropid.substr(10));
//		if (cdrop == 0)
//		{
//			jQuery('#' + thisid).insertAfter(container);
//		}
//		else
//		{
//			var afterid = 'dimension-' + (cdrop - 1);
////									console.debug("insert #" + thisid + " after #" + afterid);
//			if (thisid == afterid)
//			{
//				var thisid1 = 'dimension-' + cdrop;
//				if (cdrop == 1)
//				{
//					jQuery('#' + thisid1).insertAfter(container);
//				}
//				else
//				{
//					var afterid1 = 'dimension-' + (cdrop - 2);
//					jQuery('#' + thisid1).insertAfter($('#' + afterid1));
////											console.debug("insert #" + thisid1 + " after #" + afterid1);
//				}
//			}
//			else
//			{
//				jQuery('#' + thisid).insertAfter($('#' + afterid));
//			}
//		}
//		var thisfilter = this;
//		container.children("div.panel").each(function()
//		{
////									console.debug($(this).index(), $(this).attr("id"));
//			var oldid = jQuery(this).attr("id");
//			var newid = "dimension-" + (jQuery(this).index() - 1);
//			if (newid !== oldid)
//			{
//				jQuery(this).attr("id", newid);
//				if (thisfilter.categories.hasOwnProperty(oldid) && thisfilter.categories.hasOwnProperty(newid))
//				{
//					var oldcat = thisfilter.categories[oldid];
//					var newcat = thisfilter.categories[newid];
//					oldcat.id = newid;
//					newcat.id = oldid;
//					thisfilter.categories[oldid] = newcat;
//					thisfilter.categories[newid] = oldcat;
//				}
//			}
//		});
	};
	
	// if categoryid is an array assume first is topmost and open in succession
	this.open_category = function(categoryid)
	{
		if (Array.isArray(categoryid))
		{
			for (var i = 0; i < categoryid.length; i++)
			{
				jQuery('#' + this.data.idprefix + categoryid[i]).collapse("show");
			}
		}
		else
		{
			console.debug("showing " + this.data.idprefix + "panel-collapse-" + categoryid);
			jQuery('#' + this.data.idprefix + "panel-collapse-" + categoryid).collapse("show");
		}
	};
	
	this.close_category = function(categoryid)
	{
		jQuery('#' + this.data.idprefix + "panel-collapse-" + categoryid).collapse("hide");
	};
	
	this.toggle_category = function(categoryid)
	{
		jQuery('#' + this.data.idprefix + "panel-collapse-" + categoryid).collapse("toggle");
	};
	
	this.click_on = function (valueid)
	{
		jQuery('#' + this.data.idprefix + valueid).click();
	};
	
	this.propagate_check_down = function(checkboxid)
	{
		var li = jQuery('#' + this.data.idprefix + checkboxid).parent('.filter-menu-label').parent('.filter-menu-value').next().children("div").attr('id');
		jQuery('#' + li + " .filter-menu-checkbox").prop("checked", true);
	};
	
	this.get_property = function(categoryid, property_name)
	{
		var ret = null;
		if (this.categories.hasOwnProperty(categoryid) && this.categories[categoryid].hasOwnProperty(property_name))
		{
			ret = this.categories[categoryid][property_name];
		}
		return ret;
	};
}