<?php

require_once 'build.php';
require_once 'utilsDir.php';

$_SESSION['key_log'] = "root";
$build = new build();
$build->setPaths();

$file = $_SESSION['path_build'] . base64_decode($_GET['download']);

$utilsDir = new utilsDir();
if (file_exists($file)) {
    $utilsDir->file_download($file);
}
else {
    echo "<h1>Content error</h1><p>The file does $file not exist!</p>";
}

