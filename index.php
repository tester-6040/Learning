<?php

declare(strict_types=1);

$target = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$target .= '/public/';

header('Location: ' . $target);
exit;
