<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

echo $sftp->size('filename.remote');
print_r($sftp->stat('filename.remote'));
print_r($sftp->lstat('filename.remote'));