#!/usr/bin/php -q
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
chdir("/var/www/html/openjean");
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
require_once('phpseclib/Net/SFTP.php');

$sftp = new Net_SFTP('sftp.livedrive.com');
if (!$sftp->login('mike@mullsoft.co.uk', 'Pex1ngt0n')) {
    exit('Login Failed');
}

// outputs the contents of filename.remote to the screen
//echo $sftp->get('music/music25/Richard Moult - Last Night I Dreamt Of Hibrihteselle (2015)/04 Hour Glass.flac');
// copies filename.remote to filename.local from the SFTP server
var_dump($sftp->rawlist('music/music25/Richard Moult - Last Night I Dreamt Of Hibrihteselle (2015)'));
