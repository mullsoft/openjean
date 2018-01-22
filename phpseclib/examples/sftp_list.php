<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

print_r($sftp->nlist()); // == $sftp->nlist('.')
print_r($sftp->rawlist()); // == $sftp->rawlist('.')