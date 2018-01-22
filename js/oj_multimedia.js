/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

other_filter_catalog_categories['multimedia'] = ['music by artist', 'music by genre'];
select_categories['multimedia'] = 'music by genre';
var import_artists_filter;
var artists_types = [];


content_show_functions["multimedia"] = function(ojid)
{
	var parentid = 0;
	var el = jQuery("#filter-menu-value-" + ojid);
	if (el.length > 0)
	{
//		parentid = oj_extract_id(jQuery(el).parent("ul").attr("id"));
		parentid = oj_extract_id(jQuery(el).parent("div").attr("id"));
	}
	var url = get_ajax_url() + "&action=attributes&ojid=" + ojid + "&parent=" + parentid;
	console.debug("multimedia show " + url);
	$('#oj-display-panel').load(url, function()
	{
		console.debug("multimedia show loaded");
//		check_checked_under(ojid);
		thefilter.open_category(ojid);
//		var img = jQuery('img.oj-image:first');
//		if (img.length > 0)
//		{
//			image_urls[ojid] = jQuery(img).attr("src");
//		}
//		console.debug("image_urls", image_urls);
		window.console && console.log("ajax attribute load completed");
	});
};
content_show_functions["artists"] = function(ojid)
{
	console.log("artists " + ojid);
	var parentid = 0;
	var el = jQuery("#filter-menu-value-" + ojid);
	if (el.length > 0)
	{
//		parentid = oj_extract_id(jQuery(el).parent("ul").attr("id"));
		parentid = oj_extract_id(jQuery(el).parent("div").attr("id"));
	}
	var url = get_ajax_url() + "&action=attributes&ojid=" + ojid + "&parent=" + parentid;
	$('#oj-display-panel').load(url, function()
	{
		console.debug("artists show loaded");
		jQuery(".filter-label-text").removeClass('oj-selected');
		jQuery("#filter-label-text-" + ojid).addClass('oj-selected');
		thefilter.open_category(ojid);
		window.console && console.log("ajax attribute load completed");
	});
};

select_entity_functions["loadplaylist"] = function ()
{
	console.debug("loadplaylist");
	var chk = jQuery('#oj-select-entity-contents input:checked');
	if (chk.length > 0)
	{
		var val = chk.val();
		var valbits = val.split('|');
		jQuery('#oj-player-playlist-id').val(valbits[0]);
		var plname = chk.parent("label").children("span.oj-select-group-span").text().trim();
		jQuery('#oj-player-playlist-name').val(plname);
		var ajaxdata = {
			action: "getplaylist",
			catalog: "multimedia",
			user: user,
			ojhost:oj_get_ojhost(),
			ojid: valbits[0]
		};
		console.debug("ajaxdata", ajaxdata);
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			clear_playlist();
			console.debug(returndata);
			var ojitems = returndata.split('^');
			var ojids = [];
			for (var n = 0; n < ojitems.length; n++)
			{
				var ojitem = ojitems[n];
				var ojitembits = ojitem.split("|");
				ojids.push(ojitembits[0]);
				jQuery('#' + ojitembits[0]).prop("checked", true);
				var title = ojitembits[1].trim();
				var grpid = ojitembits[2];
				var grp = ojitembits[3];
				var liid = 'oj-player-playlist-item-' + ojitembits[0];
				jQuery('#oj-player-playlist-list').append('<li draggable="true" class="oj-playlist-item" id="' + liid + '" title="from: ' + grp + '">' +
						'<span class="oj-playlist-item-text">' + title + '</span>' +
					'<div class="oj-player-playlist-item-album" id="oj-player-playlist-item-album-' + grpid + '" style="display:none;">' + grp + '</div>' +
					'</li>');
			}
			jQuery('#save-playlist-button').prop("disabled", true);
			do_play_playlist(ojids, 0);
		});
	}
};

select_entity_functions["addtoplaylist"] = function ()
{
	console.debug("addtoplaylist");
	var chk = jQuery('#oj-select-entity-contents input:checked');
	if (chk.length > 0)
	{
		var val = chk.val();
		var valbits = val.split('|');
		var plname = chk.parent("label").children("span.oj-select-group-span").text().trim();
		jQuery('#oj-player-playlist-name').val(plname);
		var ojid = jQuery('#oj-select-entity-data').val();
		var ajaxdata = {
			action: "addtoplaylist",
			catalog: "multimedia",
			ojhost:oj_get_ojhost(),
			user: user,
			plid: valbits[0],
			ojid: ojid
		};
		console.debug("ajaxdata", ajaxdata);
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			console.debug(returndata);
		});
	}
};

category_options['multimedia'] = function(ojid)
{
	var ret = [];
	var val = thefilter.get_value(ojid);
	var vals = val.split('|');
	if (vals[1] === 'GROUP')
	{
		ret["propagate"] = "down";
		ret["propagatefrom"] = ojid;
	}
	return ret;
};

context_menu_options['multimedia'] = {
	item : {
		groups: [["addplay"]],
		actions: {
			addplay: {
				name: 'Add to playlist',
				onClick: function(itm) {
					jQuery('#oj-select-entity-data').val(itm);
					addto_playlist();
					// run when the action is clicked
				}
			}
		}
	},
	group: {
		hidden: ["newsubgrp"],
		groups: [["addfav"]],
		actions: {
			addfav: {
			  name: 'Add to favourites',
			  onClick: function(itm) {
				// run when the action is clicked
				}
			}
		}
	}
};

set_number_of_links_out_functions['multimedia'] = function(ojids, ltype, subtype)
{
	var ltype1;
	var justchild = false;
	if (typeof ltype === 'undefined')
	{
		ltype1 = "child,member";
//		justchild = true;
	}
	else if (Array.isArray(ltype))
	{
		ltype1 = ltype.join(',');
	}
	else
	{
		ltype1 = ltype;
		justchild = ltype1 === 'child';
	}
	var subtype1;
	if (typeof subtype === 'undefined')
	{
		subtype1 = "indexentry,track,null,artist subtype";
	}
	else if (Array.isArray(subtype))
	{
		subtype1 = subtype.join(',');
	}
	else
	{
		subtype1 = subtype;
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
	if (subtype1)
	{
		ajaxdata["subtype"] = subtype1;
	}
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
		console.debug("returndata", returndata);
		var nums = JSON.parse(returndata);
		console.debug("nums", nums);
		for (var id in nums)
		{
			var txt = [];
			var some = false;
			if (nums.hasOwnProperty(id))
			{
				var obj = nums[id];
				for (var ty in obj)
				{
					var nty = obj[ty];
					var isarray = Array.isArray(nty) || (typeof nty === 'object');
					if (obj.hasOwnProperty(ty) && (isarray || (obj[ty] > 0)))
					{
						some = true;
						if (isarray)
						{
							for (var nt in nty)
							{
								if (nty.hasOwnProperty(nt) && (nty[nt] > 0))
								{
									if (justchild)
									{
										switch (nt)
										{
											case "track":
												txt.push("" + nty[nt] + " tracks");
												break;
											case "indexentry":
												txt.push("" + nty[nt] + " artists");
												break;
											case "artist subtype":
												txt.push("" + nty[nt] + " albums");
												break;
											default:
												txt.push("" + nty[nt] + " subgenres");
												break;
										}
										
									}
									else
									{
										switch (nt)
										{
											case "track":
												txt.push("" + nty[nt] + " tracks");
												break;
											case "indexentry":
												txt.push("" + nty[nt] + " artists");
												break;
											case "artist subtype":
												txt.push("" + nty[nt] + " albums");
												break;
											default:
												txt.push("" + nty[nt] + " subgenres");
												break;
										}
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
								switch (ty)
								{
									case "child":
										txt.push("" + nty + " subgenres");
										break;
									case "indexentry":
										txt.push("" + nty + " artists");
										break;
									case "member":
										txt.push("" + nty + " albums");
										break;
								}
	//							txt.push(ty + ":" + obj[ty]);
							}
						}
					}
				}
			}
			var thetext = some?(" (" + txt.join(" ") + ")"):"";
			jQuery("[id$=link-" + id + "] span.filter-label-post").text(thetext);
//				console.debug("number of links for", ojidarray[n], nums[n]);
		}
	});
};


var myPlaylist;
var playlist_context_menu;
var default_image_url = get_base_url() + "/image/ojplayer_default.jpg";
init_functions.push(function()
{
	artists_types = enumerations["artist subtype"];
	var ajaxdata = {
		action: "playlistdiv",
		catalog: "multimedia",
		user: user,
		ojhost:oj_get_ojhost()
	};
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
		jQuery("body").append(returndata);
		selectable['category'] = "none";
		if (index !== 'internet radio')
		{
			myPlaylist = new jPlayerPlaylist({
			  jPlayer: "#jquery_jplayer_1",
			  cssSelectorAncestor: "#jp_container_1"
			}, [
			],
			{
			//  playlistOptions: {
			//    enableRemoveControls: true
			//  },
			  swfPath: "/js",
			  supplied: "wav,mp3",
				wmode: "window",
				preload: "auto",
				keyEnabled: false,
				audioFullScreen: false,
				useStateClassSkin:true,
				solution: "html, flash"
			//  smoothPlayBar: true,
			//  audioFullScreen: true // Allows the audio poster to go full screen via keyboard
			});
		//	$("#jquery_jplayer_1").on("$.jPlayer.event.play", function(event) {
		//		window.console && console.log("play");
		//		$("oj-player-title-header").text(myPlaylist.playlis[myPlaylist.current].title);
		//	});
			jQuery("#jquery_jplayer_1").bind(jQuery.jPlayer.event.play, function(event)
			{
				jQuery.each(myPlaylist.playlist, function(index, song)
				{
					var li = jQuery("#oj-player-playlist-list li")[index];
					if(index == myPlaylist.current)
					{
						jQuery(li).addClass("oj-strong");
						var el = jQuery(li)[0];
						var title = jQuery(li).children('div.oj-player-playlist-item-album').text().trim();
						var track = jQuery(li).children('span.oj-playlist-item-text').text().trim();
						jQuery("#oj-player-title-header").text(track);
						jQuery("#oj-player-album-header").text(title);
						var grpelid = jQuery(li).children('div.oj-player-playlist-item-album').attr("id");
						var grpid = oj_extract_id(grpelid);
						var itmid = oj_extract_id(jQuery(li).attr("id"));
//						var lastdash = grpelid.lastIndexOf('-');
//						var grpid = grpelid.substr(lastdash + 1);
						if (image_urls.hasOwnProperty(grpid))
						{
							var iurl = image_urls[grpid];
							console.debug("setting img to " + iurl);
							jQuery('#oj-player-img').attr("src", iurl);
						}
						else
						{
							var ajaxdata = {
								action: "imageurl",
								catalog: "multimedia",
								user: user,
								ojhost:oj_get_ojhost(),
								ojid: grpid
							};
							var url = get_base_url() + "/ojAjax.php";
							jQuery.post(url, ajaxdata, function(returndata)
							{
								jQuery('#oj-player-img').attr("src", returndata.trim());
								console.debug("image url for " + grpid + " " + returndata);
							});
						}
						el.scrollIntoView();
						var ajaxdata = {
							action: "setparameter",
							catalog: "multimedia",
							user: user,
							name:"lastplay" + grpid,
							value: "" + index + "|" + itmid + "|0"
						};
						var url = get_base_url() + "/ojAjax.php";
						jQuery.post(url, ajaxdata, function(returndata)
						{
							console.debug("set parameter lastplay" + grpid + " to " + index + "|" + itmid + "|0 " + returndata);
						});
		//				var container = $('#oj-playlist-list');
		//				var scrollTo = $(li);
		//				container.scrollTop(scrollTo.offset().top - container.offset().top + container.scrollTop());
					}
					else
					{
						jQuery(li).removeClass("oj-strong");
					}
				});
			});
			jQuery("#jquery_jplayer_1").bind(jQuery.jPlayer.event.pause, function(event)
			{
				jQuery.each(myPlaylist.playlist, function(index, song)
				{
					var li = jQuery("#oj-player-playlist-list li")[index];
					if(index == myPlaylist.current)
					{
						var el = jQuery(li)[0];
						var grpelid = jQuery(li).children('div.oj-player-playlist-item-album').attr("id");
						var grpid = oj_extract_id(grpelid);
						var itmid = oj_extract_id(jQuery(li).attr("id"));
						console.debug(event);
						var count = Math.floor(event.jPlayer.status.currentTime);
						var ajaxdata = {
							action: "setparameter",
							catalog: "multimedia",
							user: user,
							name:"lastplay" + grpid,
							value: "" + index + "|" + itmid + "|" + count.toString()
						};
						var url = get_base_url() + "/ojAjax.php";
						jQuery.post(url, ajaxdata, function(returndata)
						{
							console.debug("set parameter lastplay" + grpid + " to " + index + "|" + itmid + "|" + count + " " + returndata);
						});
		//				var container = $('#oj-playlist-list');
		//				var scrollTo = $(li);
		//				container.scrollTop(scrollTo.offset().top - container.offset().top + container.scrollTop());
					}
					else
					{
						jQuery(li).removeClass("oj-strong");
					}
				});
			});
			if (index == 'music by genre' || index == 'music by artist')
			{
				jQuery("#oj-import-entity-contents ul.nav").append('<li><a target="_self" href="#oj-import-artist" data-toggle="tab">Artists</a></li>');
				jQuery("#oj-import-entity-contents div.tab-content").append('<div class="tab-pane" id="oj-import-artist">' +
								'<h3>Select Artist(s) <span id="select-artists-heading"></span></h3>' +
								'<div class="row"><div id="oj-import-artists-div" class="col-xs-6 col-sm-6 col-md-6"></div>' +
								'<div id="oj-import-artists-list-div" class="col-xs-5 col-sm-5 col-md-5">' +
								'<ul id="oj-import-artists-list"></ul>' +
								'<button id="new-artist-button" class="btn btn-default" onclick="new_artist();">New Artist</button>' +
								'</div></div></div>');
				jQuery("#new-artist-type-select-span").html(make_artist_type_select("new-artist-type-select"));
//				jQuery("#oj-select-artist-category-contents .oj-select-category-input").on("change", check_create_new_artist);
				jQuery("#new-artist-name").on("change keyup paste", check_create_new_artist);
			}
			var plcmdata = {
				fetchElementData: function(rowElem) {
		//			console.debug(rowElem)
					var elid = jQuery(rowElem).attr('id');
					var lastdash = elid.lastIndexOf('-');
					return elid.substr(lastdash + 1);
				},
				actions: {
					rmv: {
					name: 'Remove from playlist',
					onClick: function(plitemid) {
						console.debug(plitemid);
						remove_from_playlist(plitemid);
					  // run when the action is clicked
					}
				  }
				}
			};
			playlist_context_menu = new BootstrapMenu('.oj-playlist-item', plcmdata);
			jQuery("#oj-main-nav").after('<div class="oj-button-group" id="oj-button-group-playlists">' +
							'<p class="oj-button-label" id="oj-button-label-playlists">playlists</p>' +
							'<ul class="nav navbar-nav">' +
							'<li><p class="navbar-btn"><a target="_self" href="#" class="btn btn-default btn-sm" onclick="play_playlist();">Play</a></p></li>' +
							'<li><p class="navbar-btn"><a target="_self" href="#" class="btn btn-default btn-sm" onclick="clear_playlist();">Clear</a></p></li>' +
							'<li><p class="navbar-btn"><a target="_self" href="#" class="btn btn-default btn-sm" onclick="load_playlist();">Load</a></p></li>' +
							'<li><p class="navbar-btn"><a target="_self" href="#" class="btn btn-default btn-sm" onclick="show_playlist();">Show</a></p></li>' +
							'</ul></div>');
		}
	});
//	var str1 = "abc<>def&".toHtmlEntities();oj-playlist-item
//	console.debug(str1);
//	console.debug(str1.fromHtmlEntities());
//	$('#oj-player').on('hide.bs.modal', function()
//	{
//		window.console && console.log("hide");
//	});
});

function remove_from_playlist(plitemid)
{
	console.debug("remove " + plitemid);
}

function check_checked_under(ojid)
{
	console.debug ("check_checked_under", ojid);
	var isitem = jQuery('#filter-menu-' + ojid).length == 0;
	if (isitem)
	{
		var el1 = jQuery("#filter-label-text-" + ojid).closest(".filter-menu-list");
		var el1id = jQuery(el1).attr("id");
		var lastdash = el1id.lastIndexOf('-');
		var ojid1 = el1id.substr(lastdash + 1);
		check_checked_under(ojid1);
	}
//	else
//	{
//		var has1 = jQuery('#filter-menu-' + ojid + " input:checked").length > 0;
//		var all = jQuery('#filter-menu-' + ojid + " input:checked").length == jQuery('#filter-menu-' + ojid + " input").length;
//		if (has1)
//		{
//			var cl = all?"oj-alltoplay":"oj-sometoplay";
//			jQuery('#filter-menu-' + ojid + " .filter-label-text").removeClass('oj-alltoplay');
//			jQuery('#filter-menu-' + ojid + " .filter-label-text").removeClass('oj-sometoplay');
//			jQuery("#filter-label-text-" + ojid).addClass(cl);
//			jQuery('#filter-menu-list-' + ojid + " input:checked").parent('label').find(".filter-label-text").addClass("oj-alltoplay");
//		}
//	}
}

function quit_playlist()
{
	$("#jquery_jplayer_1").jPlayer("stop");
	$('#oj-player').modal("hide");
	clear_playlist();
}

function save_playlist()
{
	var plname = jQuery('#oj-player-playlist-name').val();
	if (plname && plname !== "playlist")
	{
		do_save_playlist(plname);
	}
	else
	{
		var ival = jQuery(".oj-player-playlist-item-album:first").text().trim();
		ojAlert({
		  type: "prompt",
		  messageText: "Name of playlist",
		  alertType: "primary",
		  initialValue: ival
		}).done(function (e)
		{
			if (e)
			{
				e = e.trim();
				if (e.length > 0)
				{
					do_save_playlist(e);
					jQuery('#oj-player-playlist-name').val(e);
				}
			}
		});
	}
	window.console && console.log("save ");
}

function do_save_playlist(plname)
{
	var lst = [];
	hide_playlist();
	please_wait("constructing playlist");
	jQuery(".oj-playlist-item").each(function()
	{
		var thisid = oj_extract_id(jQuery(this).attr("id"));
		var thistext = jQuery(this).children(".oj-playlist-item-text").text().trim();
		var grpid = oj_extract_id(jQuery(this).children(".oj-player-playlist-item-album").attr("id"));
		var grptext = jQuery(this).children(".oj-player-playlist-item-album").text().trim();
		var obj = {
			id: thisid,
			name: thistext,
			albumid: grpid,
			albumname: grptext
		}
		lst.push(obj);
	});
	var ajaxdata =
	{
		action: "playlist",
		catalog: catalog,
		user: user,
		ojhost:oj_get_ojhost(),
		name: plname,
		list: JSON.stringify(lst)
	};
	console.debug(ajaxdata);
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
		console.debug("returndata", returndata);
		stop_please_wait();
		if (returndata.startsWith("id="))
		{
			jQuery('#oj-player-playlist-id').val(returndata.substr(3));
		}
		else if (returndata.startsWith("file="))
		{
			var url = get_catalog_url() + "&action=downloadfile&file=" + encodeURIComponent(returndata.substr(5));
//			console.debug("url", url);
			nobeforeunload = true;
			window.location = url;
			setTimeout(function() {
				nobeforeunload = false;
			}, 500);
		}
	});
}

function download_playlist_files()
{
	var plname = jQuery('#oj-player-playlist-name').val();
	if (!plname || plname === "playlist")
	{
		plname = jQuery(".oj-player-playlist-item-album:first").text().trim();
	}
	ojAlert({
		type: "custom",
		messageText: "Download details",
		alertType: "primary",
		custom: "prompt1,choice0,choice1,choice2,choice3",
		prompt1:
		{
			label: "Playlist name",
			value: plname
		},
		choice0:
		{
			label:"Format",
			options:"flac|wav|mp3",
			value:"flac",
			events: {
				change: function ()
				{
					var val = jQuery('#choice0').val();
					if (val == "mp3")
					{
						jQuery('#oj-alert-control-div-choice2').hide();
						jQuery('#oj-alert-control-div-choice3').hide();
						jQuery('#oj-alert-control-div-choice1').show();
					}
					else
					{
						jQuery('#oj-alert-control-div-choice2').show();
						jQuery('#oj-alert-control-div-choice3').show();
						jQuery('#oj-alert-control-div-choice1').hide();
					}
				}
			}
		},
		choice1:
		{
			label: "Quality (kbps)",
			options: '64|128|320',
			value: "320",
			display:"none"
		},
		choice2:
		{
			label: "Bit depth",
			options: '16|24',
			value: "16"
		},
		choice3:
		{
			label:"Sampling (kHz)",
			options:'44.1|48|96|192',
			value:"96"
		}
	}).done(function (e)
	{
		if (e)
		{
			do_download_playlist(e);
		}
	});
}

function do_download_playlist(e)
{
	console.debug(e);
	jQuery('#oj-player-playlist-name').val(e['prompt1']);
	var lst = [];
	hide_playlist();
	please_wait("constructing playlist");
	jQuery(".oj-playlist-item").each(function()
	{
		var thisid = oj_extract_id(jQuery(this).attr("id"));
		var thistext = jQuery(this).children(".oj-playlist-item-text").text().trim();
		var grpid = oj_extract_id(jQuery(this).children(".oj-player-playlist-item-album").attr("id"));
		var grptext = jQuery(this).children(".oj-player-playlist-item-album").text().trim();
		var obj = {
			id: thisid,
			name: thistext,
			albumid: grpid,
			albumname: grptext
		}
		lst.push(obj);
	});
	var ajaxdata =
	{
		action: 'audiodownload',
		catalog: catalog,
		user: user,
		ojhost:oj_get_ojhost(),
		name: e['prompt1'],
		bits: e['choice2'],
		rate:e['choice3'],
		qual: e['choice1'],
		format:e['choice0'],
		list: JSON.stringify(lst)
	};
	console.debug(ajaxdata);
	var url = get_base_url() + "/ojAjax.php";
	jQuery.post(url, ajaxdata, function(returndata)
	{
		console.debug("returndata", returndata);
		stop_please_wait();
		if (returndata.startsWith("id="))
		{
			jQuery('#oj-player-playlist-id').val(returndata.substr(3));
		}
		else if (returndata.startsWith("file="))
		{
			var url = get_catalog_url() + "&action=downloadfile&file=" + encodeURIComponent(returndata.substr(5));
//			console.debug("url", url);
			nobeforeunload = true;
			window.location = url;
			setTimeout(function() {
				nobeforeunload = false;
			}, 500);
		}
	});
}

function download_playlist_text()
{
	ojAlert({
		type: "custom",
		messageText: "Playlist Format",
		alertType: "primary",
		custom: "prompt1,choice1",
		prompt1:
		{
			label: "Playlist name",
			value: "playlist"
		},
		choice1:
		{
			label:"Format",
			options:"m3u|extended m3u|pls",
			value:"pls"
//			events: {
//				change: function ()
//				{
//					var val = jQuery('#choice0').val();
//					if (val == "mp3")
//					{
//						jQuery('#oj-alert-control-div-choice2').hide();
//						jQuery('#oj-alert-control-div-choice3').hide();
//						jQuery('#oj-alert-control-div-choice1').show();
//					}
//					else
//					{
//						jQuery('#oj-alert-control-div-choice2').show();
//						jQuery('#oj-alert-control-div-choice3').show();
//						jQuery('#oj-alert-control-div-choice1').hide();
//					}
//				}
//			}
		},
	}).done(function (e)
	{
		if (e)
		{
			do_download_playlist_text(e);
		}
	});
}

function do_download_playlist_text(e)
{
	var nm = e['prompt1'];
	var fmt = e['choice1'];
	var extension = "m3u";
	var mimetype = "audio/x-mpegurl";
	var txt = "";
	if (fmt == "pls")
	{
		extension = "pls";
		mimetype = "audio/x-scpls";
		txt = "[playlist]\n";
	}
	else if (fmt == "extended m3u")
	{
		txt = "#EXTM3U\n";
	}
	var n = 0;
	jQuery('#oj-player-playlist-list li.oj-playlist-item').each(function()
	{
		var itemid = oj_extract_id(jQuery(this).attr("id"));
		var title = jQuery(this).attr("title");
		var a = "unknown";
		var s = jQuery(this).children("span").text().trim();
//		console.debug("construct playlist title", title);
		if (title)
		{
			var ta = title.split(':');
			var tt = ta.length == 1?title:ta.slice(1).join().trim();
//			console.debug("construct playlist tt", tt);
			var tta = tt.split('-');
			if (tta.length > 1)
			{
				a = tta[0].trim();
			}
//			console.debug("construct playlist a", a);
		}
		var url = get_user_url() + "&catalog=multimedia&index=" + index + "&action=audio&ojid=" + itemid;
		n++;
		if (fmt == "pls")
		{
			txt += "File" + n + "=" + url + "\nTitle" + n + "=" + a + " - " + s + "\nLength" + n + "=-1\n";
		}
		else if (fmt == "extended m3u")
		{
			txt += "#EXTINF:-1," + a + " - " + s + "\n" + url + "\n";
		}
		else
		{
			txt += url + "\n";
		}
		
//		console.debug("construct playlist url", url);
//		var urlmp3 = url + "&format=mp3";
	});
	if (fmt == "pls")
	{
		txt += "NumberOfEntries=" + n + "\n" + "Version=2\n";
	}
	download(txt, nm + '.' + extension, mimetype);
}

function clear_playlist()
{
//	$('#oj-playlist-list').html('');
	myPlaylist.remove();
	console.debug("clear playlist");
	jQuery('#oj-player-playlist-list').html('');
	jQuery('input[type="checkbox"]:checked').prop("checked", false);
}

function has_playlist()
{
	return myPlaylist.playlist.length > 0;
}

function play_playlist()
{
	var ojids = [];
	var ojitems = [];
	var img = jQuery('img.oj-image:first');
	if (img.length > 0)
	{
		jQuery('#oj-player-img').attr("src", img.attr("src"));
	}
	else
	{
		jQuery('#oj-player-img').attr("src", default_image_url);
	}
	jQuery('#oj-player-img-div').show();
	jQuery('#oj-player-playlist-name').val("playlist");
	jQuery("#oj-player-title-header").text('');
	jQuery("#oj-player-album-header").text('');
	var new1s = 0;
	jQuery('input.filter-menu-checkbox:checked').each(function()
	{
		var val = jQuery(this).val();
		var vals = val.split('|');
		if (vals[1] === 'ITEM')
		{
			var group = "";
			var grpid = vals[0];
			try
			{
				var itemttdata = jQuery(this).parent("label").children("div.filter-label-all").children("input.filter-tooltip-data");
				if (itemttdata.length > 0)
				{
					group = jQuery(this).parent("label").children("div.filter-label-all").children("input.filter-tooltip-text").val();
					grpid = jQuery(itemttdata).val();
				}
				else
				{
					var ttdata = jQuery(this).closest(".filter-class-group").children(".panel-heading").find(".filter-tooltip-data");
					if (ttdata.length > 0)
					{
						group = jQuery(this).closest(".filter-class-group").children(".panel-heading").find(".filter-label-text").attr("title");
						grpid = jQuery(ttdata).val();
					}
					else
					{
						group = jQuery(this).closest(".filter-class-group").children(".panel-heading").find(".filter-label-text").text();
						var grpelid = jQuery(this).closest(".filter-class-group").attr("id");
						var lastdash = grpelid.lastIndexOf('-');
						grpid = grpelid.substr(lastdash + 1);
					}
				}
			}
			catch (e)
			{
				console.debug(e);
			}
			ojitems.push({
				id: vals[0],
				group: group,
				grpid : grpid
			});
			ojids.push(vals[0]);
			new1s++;
		}
		else if ((vals[1] === 'GROUP') && (index === "internet radio"))
		{
			ojitems.push({
				id: vals[0],
				group: "",
				grpid : vals[0]
			});
			ojids.push(vals[0]);
			new1s++;
		}
	});
//		var alreadythere = [];
	var nremove = 0;
	jQuery("#oj-player-playlist-list .oj-playlist-item").each(function()
	{
		var itemid = oj_extract_id(jQuery(this).attr("id"));
		var ind = ojids.indexOf(itemid)
		if (ind < 0)
		{
			if (jQuery('#' + itemid).length > 0)
			{
				console.debug("remove from playlist", itemid);
				jQuery(this).remove();
				nremove++;
			}
		}
		else
		{
			ojitems[ind] = null;
		}
	});
	if (ojitems.length > 0)
	{
		var samegrpid = ojitems[0].grpid;
		for (var n = 0; n < ojitems.length; n++)
		{
			if (ojitems[n] != null)
			{
				var ojitem = ojitems[n];
				var title = jQuery('#filter-label-text-' + ojitem.id).text().trim();
				var grp = ojitem.group.toHtmlEntities();
				var liid = 'oj-player-playlist-item-' + ojitem.id;
				if (ojitems[n].grpid != samegrpid)
				{
					samegrpid = 0;
				}
				jQuery('#oj-player-playlist-list').append('<li draggable="true" class="oj-playlist-item" id="' + liid + '" title="from: ' + grp + '">' +
						'<span class="oj-playlist-item-text">' + title + '</span>' +
					'<div class="oj-player-playlist-item-album" id="oj-player-playlist-item-album-' + ojitem.grpid + '" style="display:none;">' + grp + '</div>' +
					'</li>');
			}
		}
		do_play_playlist(ojids, samegrpid);
	}
}

function do_play_playlist(ojids, samegrpid)
{
	construct_new_playlist();
	if (samegrpid > 0)
	{
		var ajaxdata = {
			action: "getparameter",
			catalog: "multimedia",
			user: user,
			name:"lastplay" + samegrpid
		};
		var url = get_base_url() + "/ojAjax.php";
		jQuery.post(url, ajaxdata, function(returndata)
		{
			console.debug("get parameter lastplay is " + returndata);
			jQuery(".oj-playlist-item").each(function()
			{
				jQuery(this).on('dragstart', handleDragStart);
				jQuery(this).on('dragenter', handleDragEnter)
				jQuery(this).on('dragover', handleDragOver);
				jQuery(this).on('dragleave', handleDragLeave);
				jQuery(this).on('drop', handleDrop);
				jQuery(this).on('dragend', handleDragEnd);
			});
			var ajaxdata1 = {
				global: false,
				action: "audiopreload",
				catalog: "multimedia",
				index: "music by genre",
				user: user,
				ojid: ojids.join(',')
			};
			var url1 = get_base_url() + "/ojAjax.php";
//			var url1 = get_user_url() + "&catalog=multimedia&index=audio&action=audiopreload&ojid=" + ojids.join(',');
			jQuery.post(url1, ajaxdata1, function(returndata1)
			{
				console.debug("audio preload returns", returndata1);
			});
			if (returndata != "null")
			{
				var rd = returndata.split("|");
				var idx = parseInt(rd[0]);
				var itmid = rd[1];
				var tim = parseInt(rd[2]);
				if ((idx > 0) || (tim > 0))
				{
					ojAlert(
					{
						type:"confirm",
						messageText: "resume \"" + jQuery("#oj-player-playlist-item-" + itmid + ">span").text() + "\" at " + tim + " seconds"
					}).done(function(e)
					{
						console.debug("selected", e);
						if (e)
						{
							myPlaylist.play(idx, tim);
						}
					});
				}
			}
			window.console && console.debug(myPlaylist);
//			window.console && console.debug("about to show");
			$('#oj-player').modal("show");
//			window.console && console.debug("about to play");
		//	myPlaylist.play();
//			window.console && console.debug("playing");
		});
	}
}

function show_playlist()
{
	$('#oj-player').modal("show");
}

function hide_playlist()
{
	$('#oj-player').modal("hide");
}

function construct_new_playlist()
{
	myPlaylist.remove();
	jQuery('#oj-player-playlist-list li.oj-playlist-item').each(function()
	{
		var itemid = oj_extract_id(jQuery(this).attr("id"));
		var url = get_user_url() + "&catalog=multimedia&index=" + index + "&action=audio&ojid=" + itemid;
		console.debug("construct play url", url);
		var urlmp3 = url + "&format=mp3";
		var title = jQuery(this).children("span.oj-playlist-item-text").text().trim();
		var pldata = {
		  "title": title,
	//	  artist:"The Stark Palace",
		  "wav": url,
		  "mp3": urlmp3
	//	  oga:"http://www.jplayer.org/audio/ogg/TSP-05-Your_face.ogg",
	//	  poster: "http://www.jplayer.org/audio/poster/The_Stark_Palace_640x360.png"
		};
		myPlaylist.add(pldata);
	});
}

function load_playlist()
{
	var url = get_user_url() + "&catalog=multimedia&index=music+by+genre&action=selectplaylist";
	jQuery('#oj-select-entity-key').val("loadplaylist");
	jQuery('#select-entity-header').text("Select playlist");
	jQuery('#oj-select-entity-contents').load(url, function()
	{
		jQuery('#oj-select-entity').modal("show");
	});
}

function addto_playlist()
{
	var url = get_user_url() + "&catalog=multimedia&index=music+by+genre&action=selectplaylist";
	jQuery('#oj-select-entity-key').val("addtoplaylist");
	jQuery('#oj-select-entity-contents').load(url, function()
	{
		jQuery('#oj-select-entity').modal("show");
	});
}

var dragSrcEl = null;

function handleDragStart(e) {
  // Target (this) element is the source node.
  dragSrcEl = this;

  e.originalEvent.dataTransfer.effectAllowed = 'move';
  e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);

  this.classList.add('dragElem');
}
function handleDragOver(e) {
  console.debug("e", e);
  if (e.preventDefault) {
    e.preventDefault(); // Necessary. Allows us to drop.
  }
  jQuery(this).addClass('over');

  e.originalEvent.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

  return false;
}

function handleDragEnter(e) {
  // this / e.target is the current hover target.
}

function handleDragLeave(e) {
  jQuery(this).removeClass('over');  // this / e.target is previous target element.
}

function handleDrop(e) {
  // this/e.target is current target element.

  if (e.stopPropagation) {
    e.stopPropagation(); // Stops some browsers from redirecting.
  }

  // Don't do anything if dropping the same column we're dragging.
  if (dragSrcEl != this) {
    // Set the source column's HTML to the HTML of the column we dropped on.
    //alert(this.outerHTML);
    //dragSrcEl.innerHTML = this.innerHTML;
    //this.innerHTML = e.dataTransfer.getData('text/html');
    this.parentNode.removeChild(dragSrcEl);
    var dropHTML = e.originalEvent.dataTransfer.getData('text/html');
    this.insertAdjacentHTML('beforebegin',dropHTML);
    var dropElem = this.previousSibling;
    addDnDHandlers(dropElem);
	construct_new_playlist();
	jQuery('#save-playlist-button').prop("disabled", false);
    
  }
  jQuery(this).removeClass('over');
  jQuery(this).removeClass('dragElem');
  return false;
}

function handleDragEnd(e) {
  // this/e.target is the source node.
  jQuery(this).removeClass('over');
  jQuery(this).removeClass('dragElem');

  /*[].forEach.call(cols, function (col) {
    col.classList.remove('over');
  });*/
}

function addDnDHandlers(elem) {
	jQuery(elem).on('dragstart', handleDragStart);
	jQuery(elem).on('dragenter', handleDragEnter)
	jQuery(elem).on('dragover', handleDragOver);
	jQuery(elem).on('dragleave', handleDragLeave);
	jQuery(elem).on('drop', handleDrop);
	jQuery(elem).on('dragend', handleDragEnd);

}

//var cols = document.querySelectorAll('#columns .column');
//[].forEach.call(cols, addDnDHandlers);


function oj_import_callback(returndata)
{
	console.debug("oj_import_callback", returndata);
//	jQuery('#oj-import-artists-list').html('');
	// remove any that are in the list that are also in the tree but unchecked
	jQuery('#oj-import-artists-list input:checked').each(function()
	{
		var thisid = jQuery(this).attr("id");
		var thatid = '#' + thisid.substr(0, thisid.length - 7);
		if ((jQuery(thatid).length > 0) && !jQuery(thatid).is(":checked"))
		{
			jQuery(this).parent("li").remove();
		}
	});
	jQuery('#oj-import-artists-div input:checked').each(function()
	{
		var thisid = jQuery(this).attr("id");
		if (jQuery('#' + thisid + '-listed').length == 0)
		{
			var thisval = jQuery(this).val();
			var thisvalbits = thisval.split('|');
			var id1 = thisvalbits[0];
			var label = jQuery(this).parent('label').children('div.filter-label-all').children('span.filter-label-text').text().trim();
			var lihtml = '<li class="oj-import-artists-list-li"><input type="checkbox" checked="checked" onchange="oj_list_change(\'' + thisid + '\')" id="' + thisid +
					'-listed" class="oj-import-artists-list-cb" value="' + thisval + '"/>' +
					'<button class="aka-button btn btn-secondary btn-sm" onclick="aka(\'' + id1 + '\');">aka</button>' +
					'<input type="text" readonly="readonly" class="import-artists-list-text" value="' +
					label.replace('"','&quot;') + '"/>' + make_artist_type_select(thisid + "-select") + '</li>';
			jQuery('#oj-import-artists-list').append(lihtml);
			if (thisvalbits.length > 3)
			{
				jQuery('#' + thisid + "-select").val(thisvalbits[3]);
			}
		}
	});
	check_import_entity();
}

function make_artist_type_select(id)
{
	var ret = '<select class="oj-import-artist-type-select" name="' + id + '" id="' + id + '">';
	for (var n = 0; n < artists_types.length; n++)
	{
		var sel = n === 0?' selected="selected"':'';
		ret += '<option value="' + artists_types[n] + '"' + sel + '>' + artists_types[n] + '</option>';
	}
	ret += '</select>';
	return ret;
}

function oj_list_change(id)
{
	jQuery('#' + id).prop("checked", false);
	jQuery('#' + id + '-listed').parent("li").remove();
	check_import_entity();
}

function oj_import_show_callback(id)
{
	console.debug("oj_import_show_callback", id);
	var ojname = import_artists_filter.get_text(id);
	var val = import_artists_filter.get_value(id);
	var vals = val.split("|");
	//"&action=show&ojid=" + vals[0] + "&type=" + vals[1] + "&name=" + encodeURIComponent(ojname);
	var url = get_user_url() + "&catalog=multimedia&index=music+by+artist&action=show&selectable=indexentry&ojid=" + vals[0] + "&type=" + vals[1] + "&name=" + encodeURIComponent(ojname);
	jQuery.get(url, function(returndata)
	{
		console.debug("show callback returns", returndata, typeof returndata);
		import_artists_filter.fill_values(id, JSON.parse(returndata));
		jQuery('#oj-import-artists-list .oj-import-artists-list-cb').each(function()
		{
			var thisid = jQuery(this).attr("id");
			var len = thisid.length - "-listed".length;
			var otherid = thisid.substr(0, len);
			jQuery('#' + otherid).prop("checked", true);
		});
//		jQuery('#import-filter-menu-list .filter-menu-checkbox').on("change", check_import_entity);
	});
}

import_entity_show_functions['multimedia'] = function()
{
	jQuery('#new-artist-name').val("");
	jQuery('#select-artists-heading').text("");
	jQuery('#oj-import-artists-list').html('');
	jQuery("#oj-import-artists-div input[type='checkbox']").prop("checked", false);
	jQuery("#oj-import-artists-div input[type='radio']").prop("checked", false);
	var idata = {
		"containerid" : "oj-import-artists-div",
		"idprefix": "import-",
		"callback" : oj_import_callback,
		"valuecallback" : oj_import_show_callback,
		"categories" : other_cats["music by artist"]
//		"radio": true
	};
	console.debug("1.import_artists_filter", idata);
	if (import_artists_filter == null)
	{
		console.debug("setting up import_artists_filter");
		import_artists_filter = new FILTER(idata);
		import_artists_filter.initialise();
	}
};

function folder_select()
{
	var title = jQuery('.oj-folder-radio:checked').parent('label').children('span').text().trim();
	var dash = title.indexOf(" - ");
	if (dash > 0)
	{
		var artist = title.substr(0, dash);
		var url = get_ajax_url() + "&action=getartist&artist=" + encodeURIComponent(artist);
		jQuery.get(url, function(data)
		{
			jQuery('#debug').html(data);
			jQuery('#select-artists-heading').text(artist);
			jQuery('#new-artist-name').val(artist);
			console.debug("getartist", data);
			var dt1 = JSON.parse(data);
			var dt = dt1["artists"];
			artists_types = dt1["atypes"];
			jQuery('#oj-import-artists-list').html('');
			for (var id in dt)
			{
				if (dt.hasOwnProperty(id))
				{
					var nm = dt[id];
					var nmbits = nm.split('|');
					var cbid = "import-" + id;
					var thisval = cbid + '|ITEM|Artists';
					if (nmbits.length > 1)
					{
						thisval += '|' + nmbits[1];
					}
					var valbits = thisval.split('|');
					jQuery('#' + cbid).prop("checked", true);
					var lihtml = '<li class="oj-import-artists-list-li"><input type="checkbox" checked="checked" onchange="oj_list_change(\'' + cbid + '\')" id="' + cbid +
							'-listed" class="oj-import-artists-list-cb" value="' + thisval + '"/>' +
							'<button class="aka-button btn btn-secondary btn-sm" onclick="aka(\'' + id + '\');">aka</button>' +
							'<input type="text" readonly="readonly" class="import-artists-list-text" value="' +
							nmbits[0].replace('"','&quot;') + '"/>' + make_artist_type_select(cbid + "-select") + '</li>';
					jQuery('#oj-import-artists-list').append(lihtml);
					if (valbits.length > 3)
					{
						jQuery('#' + cbid + "-select").val(valbits[3]);
					}
				}
			}
			check_import_entity();
		});
	}
	check_import_entity();
}

function import_entity()
{
	console.debug("import entity");
	var art = [];
	jQuery('#oj-import-artists-list input:checked').each(function()
	{
		var v = jQuery(this).val();
		if (v.indexOf("import-") === 0)
		{
			v = v.substr("import-".length);
		}
		var va = v.split('|');
		var artstr = va[0] + "|" + jQuery('#import-' + va[0] + '-select').val();
		console.debug("artstr", artstr);
		art.push(artstr);
	});
	var cat1 = jQuery('#oj-import-category-div :checked').val();
	var cata = cat1.split('|');
	var cat = cata[0];
	// ojmode=" + ojmode + "&user=" + user + "&catalog=" + catalog + "&index=" + index
	var ajaxdata = {
		user:user,
		catalog:catalog,
		index:index,
		ojhost:oj_get_ojhost(),
		ojmode:ojmode,
		action:"import",
		logical: jQuery('#oj-select-logical-id').val(),
		folder: jQuery('#oj-unimported-folders :checked').val(),
		category: cat,
		artists: art.join(',')
	};
//	console.debug("import ajaxdata", ajaxdata);
	
	jQuery.ajax({
		type: "POST",
		url: get_base_url() + "/ojAjax.php",
		data: ajaxdata,
		success: function(returndata){
			stop_please_wait();
			console.debug("imported", returndata);
			ojAlert({
			  type: "alert",
			  messageText: "imported with id " + returndata,
			  alertType: "primary"
			});
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			stop_please_wait();
			console.debug("imported", textStatus, errorThrown);
			ojAlert({
			  type: "alert",
			  messageText: "import failed " + textStatus + ', ' + errorThrown,
			  alertType: "primary"
			});
		}
	});
	jQuery('#oj-import-entity').modal("hide");
	please_wait("import in progress")
}

import_entity_checks['multimedia'] = function()
{
	return jQuery('#oj-import-artists-list-div :checked').length >= 1;
};

//function oj_new_artist_callback(returndata)
//{
//	console.debug("new artist callback", returndata);
//}
//
//function oj_new_artist_show_callback(id)
//{
//	console.debug("new artist show callback", id);
//}
//
//var newartistfilter;

function select_new_artist_category()
{
	console.debug("select_new_artist_category");
}

var newartistfilter;

function oj_new_artist_callback()
{
	
}

function oj_new_artist_show_callback(id, number_of_values)
{
	var ojname = newartistfilter.get_text(id);
	var val = newartistfilter.get_value(id);
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
		var url = get_user_url() + "&catalog=" + vals[2] + "&index=music+by+artist&action=show&ojid=" + vals[0] + "&type=" + type + "&name=" +
				encodeURIComponent(ojname) + "&selectable=category";
		console.debug("3.show select category callback url", url);
		jQuery.get(url, function(returndata)
		{
//			console.debug("show callback returns", returndata, typeof returndata);
			newartistfilter.fill_values(id, JSON.parse(returndata));
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
			jQuery("#oj-select-artist-category-contents input.filter-menu-checkbox").off("change");
			jQuery("#oj-select-artist-category-contents input.filter-menu-checkbox").on("change", check_create_new_artist);
		});
	}
}

function new_artist()
{
//	console.debug("artistcats in new artist", acats);
	var current_artists = jQuery('#oj-select-artist-category-contents').html().trim();
	console.debug("current_artists", current_artists, current_artists.length);
	if (current_artists && current_artists.length > 0)
	{
		console.debug("1");
		jQuery('#oj-import-entity').modal("hide");
		jQuery('#oj-select-artist-category').modal("show");
		jQuery('#oj-select-artist-category .oj-select-category-input').prop("checked", false);
	}
	else
	{
		var ndata = {
			"containerid" : "oj-select-artist-category-contents",
			"idprefix": "new-artist-",
			"callback" : oj_new_artist_callback,
			"valuecallback" : oj_new_artist_show_callback,
			"categories" : other_selectable_cats["music by artist"],
			"radio":"new-artist-group"
		};
		newartistfilter = new FILTER(ndata);
		newartistfilter.initialise();
		jQuery('#oj-import-entity').modal("hide");
		jQuery('#oj-select-artist-category').modal("show");
		jQuery('#oj-select-artist-category .oj-select-category-input').prop("checked", false);
		jQuery("#oj-select-artist-category-contents input.filter-menu-checkbox").on("change", check_create_new_artist);
//		console.debug("2");
//		var ajaxdata = {
//			user: user,
//			catalog: catalog,
//			index: 'music by artist',
//			ojhost:oj_get_ojhost(),
//			ojmode: ojmode,
//	//		prefix: prefix,
//	//		root: root,
//			action: "categories"
//		};
//		var url = get_base_url() + "/ojAjax.php";
//		jQuery('#oj-select-artist-category-contents').load(url, ajaxdata, function(resp)
//		{
//		console.debug("3");
//			jQuery('#oj-import-entity').modal("hide");
//			jQuery('#oj-select-artist-category').modal("show");
//			jQuery('#oj-select-artist-category .oj-select-category-input').prop("checked", false);
//		});
	}
//	jQuery('#oj-new-entity-form').load(url, function()
//	{
//		
//	});
}

function quit_new_artist()
{
	$('#oj-select-artist-category').modal("hide");
	jQuery('#oj-import-entity').modal("show");
}

function create_new_artist()
{
	var name = jQuery("#new-artist-name").val().trim();
	var nm = name.toHtmlEntities();
	var typ = jQuery("#new-artist-type-select").val();
	var cat = jQuery("#oj-select-artist-category-contents input:checked").val();
	console.debug("name", nm, "type", typ, "category", cat);
	var artists_cat = catalog_ids['multimedia'];
	var catbits = cat.split('|');
	var category1 = catbits[0];
	var xml = '<entity type="' + entity_type_ids['CATEGORY'] + '" catalog="' + artists_cat + '" subtype="artist">' +
			'<name>' + nm + '</name>' +
			'<pages></pages><links>' +
			'<link direction="to" catalog="' + artists_cat + '" ordinal="0" hidden="0" type="child" subtype="indexentry" other="' + category1 + '">' + nm + '</link>' +
			'</links><children/></entity>';
	console.debug(xml);
	var url = get_base_url() + "/ojAjax.php";
	var ajaxdata = {
		user:user,
		catalog:catalog,
		index:index,
		ojhost:oj_get_ojhost(),
		ojmode:ojmode,
		action:"create",
		xml: xml
	};
	jQuery.post(url, ajaxdata, function(returndata1)
	{
		console.debug("returndata", returndata1);
		var id1 = returndata1.trim();
		var cbid = 'import-' + id1
		var thisval = id1 + '|ITEM|Artists';
		var lihtml = '<li class="oj-import-artists-list-li"><input type="checkbox" checked="checked" onchange="oj_list_change(\'' + cbid + '\')" id="' + cbid +
				'-listed" class="oj-import-artists-list-cb" value="' + thisval + '"/>' +
				'<button class="aka-button btn btn-secondary btn-sm" onclick="aka(\'' + id1 + '\');">aka</button>' +
				'<input type="text" readonly="readonly" class="import-artists-list-text" value="' +
				name.replace('"','&quot;') + '"/>' + make_artist_type_select(cbid + "-select") + '</li>';
		jQuery('#oj-import-artists-list').append(lihtml);
		console.debug("import_artists_filter", import_artists_filter);
		import_artists_filter.clear_values(category1);
		check_import_entity();
		jQuery('#' + cbid + "-select").val(typ);
	});
	$('#oj-select-artist-category').modal("hide");
	jQuery('#oj-import-entity').modal("show");
}

function check_create_new_artist()
{
	console.debug("1");
	var nm = jQuery("#new-artist-name").val().trim();
	console.debug("2");
	var cat = jQuery("#oj-select-artist-category-contents input:checked");
	console.debug("3");
	jQuery("#create-new-artist-button").prop("disabled", nm.length == 0 || cat.length == 0);
	console.debug("4");
}

function aka(id)
{
	console.debug(id);
	var name = jQuery('#import-' + id + "-listed").parent("li").children(".import-artists-list-text").val();
    ojAlert({
      type: "prompt",
      messageText: "Alternative name for " + name,
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
					action:"aka",
					ojid:id,
					tablename: "entities",
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