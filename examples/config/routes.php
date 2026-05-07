<?php

use NaN\App\Middleware\Router\{Route, RoutesCollection};
use NaN\Http\Response;

return new RoutesCollection(
	new Route('/', function () {
		return new Response(body: tpl()->render('index', [
			'title' => env('TITLE', 'NaN'),
		]));
	}),
);
