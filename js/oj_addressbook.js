/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
selectable.category = "false";
selectable.group = "false";
selectable.item = "false";

content_show_functions["addressbook"] = function(ojid)
{
	console.log("addressBook " + ojid);
	var parentid = 0;
	var el = jQuery("#filter-menu-value-" + ojid);
	if (el.length > 0)
	{
		parentid = oj_extract_id(jQuery(el).parent("div").attr("id"));
	}
	var url = get_ajax_url() + "&action=attributes&ojid=" + ojid + "&parent=" + parentid;
	$('#oj-display-panel').load(url, function()
	{
		console.debug("ajax attribute load completed"/*, data*/);
	});
};
//content_show_functions["AddressBook"] = function(ojid)
//{
//	console.log("AddressBook " + ojid);
//	var url = get_ajax_url() + "&action=attributes&ojid=" + ojid;
//	$('#oj-display-panel').load(url, function(data)
//	{
//		console.debug("ajax attribute load completed"/*, data*/);
//	});
//};

radio_status["addressbook"] = "addressbook-radio";