<?php
require_once 'build.php';
require_once 'blowfish.php';

$_SESSION['key_log'] = "root";

$build = new build();
$build->main();
