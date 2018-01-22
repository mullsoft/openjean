<?php
include('Net/SFTP.php');

$sftp = new Net_SFTP('www.domain.tld');
if (!$sftp->login('username', 'password')) {
    exit('Login Failed');
}

$sftp->chmod(0777, 'filename.remote');
//$sftp->chmod(0777, 'dirname.remote', true); // recursively change permissions on a directory
// has the same syntax as http://php.net/touch
$sftp->touch('filename.remote');
$sftp->chown('filename.remote', $uid);
//$sftp->chown('filename.remote', $uid, true); // recursively change the owner
$sftp->chgrp('filename.remote', $gid);
//$sftp->chgrp('filename.remote', $gid, true); // recursively change the group
$sftp->truncate('filename.remote', $size);