<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
//echo "here0\n";
require "login/loginheader.php";
//echo "here0a\n";
ini_set('memory_limit', '1024M');
$ojmode = isset($_GET['ojmode']) ? $_GET['ojmode'] : 'db';
$editingst = isset($_GET['edit']) ? $_GET['edit'] : 'false';
$ojediting = $editingst === 'true';
require_once('OJDatabase.php');
OJ_Utilities::set_system_parameter("editing", $editingst === 'true');
$user = isset($_GET['user']) ? $_GET['user'] : (isset($_SESSION['username'])?$_SESSION['username']:'guest');
$catsetfromget = 0;
$indsetfromget = 0;
//var_dump($_GET);
if (isset($_GET['catalog']))
{
	$catalog = $_GET['catalog'];
	$catsetfromget = 1;
}
else
{
	$cat = OJ_Access::most_recent_catalog_for($user);
	if ($cat)
	{
		$catalog = OJ_Catalogs::get_catalog_name($cat);
	}
	else
	{
		$catalog = "multimedia";
	}
}
if (isset($_GET['index']))
{
	$index = $_GET['index'];
	$indsetfromget = 1;
}
else
{
	$ind = OJ_Access::most_recent_index_for($user, $catalog);
	if ($ind)
	{
		$index = OJ_Indexes::get_index_name($ind);
	}
	else
	{
		$index = "default";
	}
}
$somethingsetfromget = $catsetfromget + $indsetfromget;
//function get_catalog_and_index($cat, $ind)
//{
//	$catalog = $cat === null?"multimedia":$cat;
//	$index = $ind === null?($catalog == 'multimedia'?'audio':"default"):$ind;
//	$ret = array();
//	switch ($catalog."__".$index)
//	{
//		case "multimedia__artists":
//			$ret[0] = "artists";
//			$ret[1] = "audio";
//			break;
//		default:
//			$ret[0] = $catalog;
//			$ret[1] = $index;
//			break;
//	}
//	$ret[2] = $cat;
//	$ret[3] = $ind;
//	return $ret;
//}
//echo "original ".$catalog."  ".$index."\n";
$ci = OJ_Catalog_Index_Correspondence::get_catalog_and_index_names($catalog, $index);  //get_catalog_and_index($catalog, $index);
$catalog = $ci["catalog"];
$index = $ci["index"];
//echo "replace with ".$catalog."  ".$index."\n";
$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
$al = explode('/', $actual_link);
$lenal = count($al);
$al[$lenal - 1] = "login";
array_push($al, "logout.php");
$logout_link = implode('/', $al);
$al[$lenal] = "logout1.php";
$logout1_link = implode('/', $al);
//echo "here index 1";
?>
<html>
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Openjean <?php echo ucfirst($catalog); ?></title>
		<base target="_blank"/>
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
		<link type="text/css" href="css/jquery-ui.css" rel="stylesheet"/> 
		<link type="text/css" href="css/bootstrap.min.css" rel="stylesheet"/>
		<link type="text/css" href="css/bootstrap-datetimepicker.css" rel="stylesheet"/>
		<link type="text/css" href="css/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
		<link rel="StyleSheet" type="text/css" href="css/jplayer.blue.monday.css"/>
		<link rel="StyleSheet" type="text/css" href="jquery.fileTree/jqueryFileTree.css"/>
		<link rel="StyleSheet" type="text/css" href="css/ojfilters.css"/>
		<link rel="StyleSheet" type="text/css" href="css/oj_base.css"/>
		<link rel="StyleSheet" type="text/css" href="css/oj.css"/>
		<script src="js/jquery-2.1.4.js"></script>
		<script src="js/jquery.form.js"></script> 
		<script src="js/jquery-ui.js"></script>
		<script src="js/jquery.jplayer.min.js"></script>
		<script src="js/jplayer.playlist.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/moment-with-locales.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/BootstrapMenu.js"></script>
		<script src="js/bootstrap-datetimepicker.js"></script>
		<script src="js/tinymce/tinymce.min.js"></script>
		<script src="js/tinymce/jquery.tinymce.min.js"></script>
		<script src="jquery.fileTree/jquery.easing.js"></script>
		<script src="jquery.fileTree/jqueryFileTree.js"></script>
		<script type="text/javascript" src="js/stringset.js"></script>
		<script type="text/javascript">
			var user = "<?php echo $user; ?>";
			var catalog = "<?php echo strtolower($catalog); ?>";
			var index = "<?php echo $index; ?>";
			var catsetfromget = <?php echo $catsetfromget; ?>;
			var indsetfromget = <?php echo $indsetfromget; ?>;
			var somethingsetfromget = catsetfromget + indsetfromget;
			console.debug("catalog", catalog, "index", index);
			var ojmode = "<?php echo $ojmode; ?>";
			var catalog_ids = <?php echo json_encode(OJ_Catalogs::get_all_catalog_ids()); ?>;
			var entity_type_ids = <?php echo json_encode(OJ_Entity_Types::get_all_entity_type_ids()); ?>;
			var logout_url = "<?php echo $logout_link; ?>";
			var logout1_url = "<?php echo $logout1_link; ?>";
//			var category_subtypes = ;
//			var group_subtypes = ;
//			console.debug("catalog_ids", catalog_ids, "entity_type_ids", entity_type_ids);
//			console.debug("category subtypes", subtypes['category'], "group subtypes", subtypes['group']);
//			console.debug("category attributes", category_attributes, "group attributes", group_attributes, "item attributes", item_attributes);
//			var inFormOrLink = false;
//			console.debug(logout_url);
		</script>
		<script type="text/javascript" src="js/ojfilters.js"></script>
		<script type="text/javascript" src="js/oj.js"></script>
    </head>
    <body>
		<div id="debug"></div>
		<div class="oj-navbar">
			<nav role="navigation" class="navbar navbar-default">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a target="_self" href="#" class="navbar-brand"><img src="image/ojicon1.gif"/></a>
					<div id="navbar-identification">
					<span id="navbar-catalog-name"></span>.<span id="navbar-index-name"></span>
					</div>
				</div>
				<!-- Collection of nav links and other content for toggling -->
				<div id="navbarCollapse" class="collapse navbar-collapse">
					<div id="oj-main-nav" class="oj-button-group">
						<p class="oj-button-label" id="oj-button-label-catalog">openjean</p>
						<ul class="nav navbar-nav" id="oj-main-navbar">
							<li class="dropdown">
								<a href="#" target="_self" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Catalog <span class="caret"></span></a>
								<ul id="oj-catalogs-menu" class="dropdown-menu">
								</ul>
							</li>
							<li class="dropdown">
								<a target="_self" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Index <span class="caret"></span></a>
								<ul id="oj-index-menu" class="dropdown-menu">
								</ul>
							</li>
							<li class="dropdown">
								<a target="_self" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Entity <span class="caret"></span></a>
								<ul id="oj-entity-menu" class="dropdown-menu">
									<li><a target="_self" href="#" onclick="oj_search();">Search</a></li>
									<li><a target="_self" href="#" onclick="oj_reset();">Reset</a></li>
									<li class="divider"></li>
									<li><a target="_self" href="#" onclick="new_entity('category');">New Category</a></li>
									<li><a target="_self" href="#" onclick="rename_category();">Rename Category</a></li>
									<li id="oj-entity_menu_divider" class="divider"></li>
<!--									<li><a target="_self" href="#" onclick="please_wait('test', 5, function()
										{
											console.debug('in test function');
										});">Test</a></li>
									<li><a href="#" onclick="new_group();">New Group</a></li>
									<li><a target="_self" href="#" onclick="new_item();">New Item</a></li>
									<li><a target="_self" href="#" onclick="show_import_entity();">Import</a></li>
									<li><a target="_self" href="#" onclick="delete_entity();">Delete</a></li> -->
								</ul>
							</li>
						</ul>
					</div>
					<p class = "navbar-text" id="oj-search-text"></p>
					<ul class="nav navbar-nav navbar-right">
						<li><a target="_self" href="<?php echo $logout_link; ?>">Logout</a></li>
					</ul>
				</div>
			</nav>
		</div>
		<div class="container">
			<div class="row">
				<div id="oj-catalog-list" class="oj-catalog-list" tabindex="0">
					<div id="oj-catalog-list-filter" class="oj-filter">
						
					</div>
				</div>
				<!-- TODO -->
				<div id="oj-display-panel" class="oj-display-panel">
				</div>
			</div>
		</div>
		<div id="oj-import-entity" tabindex="-1" class="modal fade bs-example-modal-lg" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Openjean Import Folder</h4>
					</div>
					<div id="oj-import-entity-contents" class="modal-body">
						<ul  class="nav nav-pills">
							<li class="active">
								<a target="_self"  href="#oj-import-folder" data-toggle="tab">Folder</a>
							</li>
							<li><a target="_self" href="#oj-import-category" data-toggle="tab">Category</a>
							</li>
						</ul>

						<div class="tab-content clearfix">
							<div class="tab-pane active" id="oj-import-folder">
								<h3>Select Folder</h3>
								<select name="oj-select-logical" id="oj-select-logical-id" class="oj-select" onchange="select_logical();"></select>
								<div id="oj-unimported-folders">

								</div>
							</div>
							<div class="tab-pane" id="oj-import-category">
								<h3>Select Category</h3>
								<div id="oj-import-category-div">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" onclick="quit_import_entity();">Abort</button>
						<button id="save-import-entity-button" type="button" class="btn btn-primary" onclick="import_entity();">Save</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<div id="oj-new-entity" tabindex="-1" class="modal fade bs-example-modal-lg" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">Openjean New Entity</h4>
					</div>
					<div id="oj-new-entity-contents" class="modal-body">
						<?php
						?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" onclick="quit_new_entity();">Abort</button>
						<button id="save-new-entiy-button" type="button" class="btn btn-primary" onclick="save_new_entity();">Save</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<div id="oj-select-entity" tabindex="-1" class="modal fade bs-example-modal-lg" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="select-entity-header">Openjean Select Entity</h4>
						<input type="hidden" id="oj-select-entity-key" value="select"/>
						<input type="hidden" id="oj-select-entity-data" value="data"/>
					</div>
					<div class="modal-body">
						<div id="oj-select-entity-contents">
						</div>
						<div id="oj-select-entity-extra">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" onclick="quit_select_entity();">Abort</button>
						<button id="save-new-entity-button" type="button" class="btn btn-primary" onclick="select_entity();">Ok</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<div id="oj-please-wait" tabindex="-1" class="modal fade bs-example-modal-lg" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Please wait...</h4>
					</div>
					<div id="oj-please-wait-contents" class="modal-body">
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<div id="oj-make-note" tabindex="-1" class="modal fade bs-example-modal-lg" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="make-note-header">Openjean Notepad</h4>
						<input type="hidden" id="oj-make-note-entity" value="0"/>
					</div>
					<div class="modal-body">
						<div id="oj-make-note-contents">
							<textarea id="oj-make-note-textarea"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" onclick="quit_make_note();">Abort</button>
						<button id="save-new-entity-button" type="button" class="btn btn-primary" onclick="save_note();">Save</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
    </body>
</html>
