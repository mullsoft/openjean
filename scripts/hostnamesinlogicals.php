#!/usr/bin/php -q
<?php

chdir("/var/www/html/openjean");
ini_set('memory_limit', '1024M');
require_once("OJDatabase.php");

$logicals = OJ_Logicals::get_logicals();
$hname = gethostname();
//IS-28219
$heidegger = 
[
"music25"=>"/mnt/drive0/music25",
"music26"=>"/mnt/drive0/music26",
"music27"=>"/mnt/drive0/music27",
"music23"=>"/mnt/drive1/music23",
"music24"=>"/mnt/drive1/music24",
"music19"=>"/mnt/drive2/music19",
"music20"=>"/mnt/drive2/music20",
"music21"=>"/mnt/drive2/music21",
"music22"=>"/mnt/drive2/music22",
"music10"=>"/mnt/drive3/music10",
"music11"=>"/mnt/drive3/music11",
"music12"=>"/mnt/drive3/music12",
"music13"=>"/mnt/drive3/music13",
"music14"=>"/mnt/drive3/music14",
"music2"=>"/mnt/drive3/music2",
"music3"=>"/mnt/drive3/music3",
"music4"=>"/mnt/drive3/music4",
"music5"=>"/mnt/drive3/music5",
"music6"=>"/mnt/drive3/music6",
"music7"=>"/mnt/drive3/music7",
"music8"=>"/mnt/drive3/music8",
"music9"=>"/mnt/drive3/music9",
"music1"=>"/mnt/drive4/music1",
"music15"=>"/mnt/drive4/music15",
"music16"=>"/mnt/drive4/music16",
"music17"=>"/mnt/drive4/music17",
"music18"=>"/mnt/drive4/music18",
"music28"=>"/media/ShareCenter01/music28"
];
//var_dump($logicals);
foreach ($logicals as $lname=>$logical)
{
//	print $lname;
	if (substr($lname, 0, 5) === 'music')
	{
		$logical->host = $hname;
		$logical->save();
		$newlog1 = new OJ_Logicals(["name"=>$lname, "value"=>$heidegger[$lname], "alternative"=>$logical->alternative, "host"=>"heidegger"]);
		$newlog1->save();
		$newlog2 = new OJ_Logicals(["name"=>$lname, "value"=>"/music/".$lname, "alternative"=>$logical->alternative, "host"=>"IS-28219", "value_ftp_id"=>1]);
		$newlog2->save();
	}
}