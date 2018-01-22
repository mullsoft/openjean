<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

// outputs the contents of filename.remote to the screen
echo $sftp->get('filename.remote');
// copies filename.remote to filename.local from the SFTP server
$sftp->get('filename.remote', 'filename.local');
