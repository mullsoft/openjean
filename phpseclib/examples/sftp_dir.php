<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

$sftp->mkdir('test'); // create directory 'test'
$sftp->chdir('test'); // open directory 'test'
echo $sftp->pwd(); // show that we're in the 'test' directory
$sftp->chdir('..'); // go back to the parent directory
$sftp->rmdir('test'); // delete the directory
// if the directory had files in it we'd need to do a recursive delete
//$sftp->delete('test');
