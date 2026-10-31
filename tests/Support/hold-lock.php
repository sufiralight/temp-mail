<?php

$directory = $argv[1];
$handle = fopen($directory.'/lock', 'c');
flock($handle, LOCK_EX);
fwrite(STDOUT, "locked\n");
fflush(STDOUT);
fgets(STDIN);
flock($handle, LOCK_UN);
fclose($handle);
