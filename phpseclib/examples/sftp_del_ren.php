<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

$sftp->delete('filename.remote'); // deletes directories recursively
// non-recursive delete
$sftp->delete('dirname.remote', false);
$sftp->rename('filename.remote', 'newname.remote');