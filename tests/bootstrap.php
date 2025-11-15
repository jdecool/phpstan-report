<?php

declare(strict_types=1);

$loader = require_once __DIR__ . '/../vendor/autoload.php';

\define('UPDATE_EXPECTATIONS', filter_var(getenv('UPDATE_EXPECTATIONS') ?: getenv('UP'), FILTER_VALIDATE_BOOLEAN));

return $loader;
