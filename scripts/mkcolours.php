#!/usr/bin/php -q
<?php

$col1 = file("/home/mike/seme4/colours1.txt");
$col = [];
for ($n = 0; $n < 15; $n++)
{
	$cols = [];
	for ($m = 0; $m < 6; $m++)
	{
		$i = ($m * 16) + $n;
		$cols[$m] = $col1[$i];
	}
	$col[$n] = $cols;
}
print json_encode($col);