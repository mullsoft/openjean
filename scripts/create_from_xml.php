#!/usr/bin/php -q
<?php

ini_set('memory_limit', '1024M');
chdir("/var/www/html/openjean");
require_once("OJDatabase.php");
$opts = getopt('t', [], $optind);
if (array_key_exists('t', $opts))
{
	OJ_Row::$_testing = true;
	print "testing\n";
}
$newargv = array_slice($argv, $optind);
if (count($newargv) > 0)
{
	if (file_exists($newargv[0]))
	{
		$xml = simplexml_load_file($newargv[0]);
		$nm = $xml->getName();
		print $nm."\n";// exit;
		switch ($nm)
		{
			case "catalog":
				$cat = OJ_Catalogs::from_xml($xml);
				$catalog_name = (string)$xml["name"];
//				print $catalog_name."\n"; exit;
				$cname = strtolower($catalog_name);
				$css_filename = "css/oj_".$cname.".css";
				if (!file_exists($css_filename))
				{
					file_put_contents($css_filename, "<?php\nrequire_once 'OJDatabase.php';\n\$catalogs_id = OJ_Catalogs::get_catalog_id('".$catalog_name."');\n".
						"\$color = OJ_Parameters::get_parameter_value(\$catalogs_id, 'bgcolor');\nheader ('Content-type: text/css');\n?>\n");
				}
				$js_filename = "js/oj_".$cname.".js";
				if (!file_exists($js_filename))
				{
					file_put_contents($js_filename, "// Catalog: ".$catalog_name."\n");
				}
				$php_filename = "oj_".$cname.".php";
				if (!file_exists($php_filename))
				{
					file_put_contents($php_filename, "<?php\nclass OJ_".ucfirst($catalog_name)."_Catalog_Display extends OJ_Catalog_Display\n{\n}");
				}
				var_dump($cat);
				break;
			case "entity":
				$ent = OJ_Entities::from_xml($xml);
				var_dump($ent);
				break;
		}
	//		$ent = OJ_Entities::from_xml($xmlent);
		print "finished\n";
	//		var_dump($ent);
	}
}