<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

// puts a three-byte file named filename.remote on the SFTP server
$sftp->put('filename.remote', 'xxx');
// puts an x-byte file named filename.remote on the SFTP server,
// where x is the size of filename.local
$sftp->put('filename.remote', 'filename.local', NET_SFTP_LOCAL_FILE);
