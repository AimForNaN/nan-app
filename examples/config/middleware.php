<?php

use NaN\Application\Middleware\MiddlewareCollection;

$router = include(__DIR__ . '/routes.php');

return new MiddlewareCollection($router);
