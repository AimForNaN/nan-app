<?php

use NaN\Application\Middleware\Router\{
	Route,
	RoutesCollection,
};
use NaN\Http\Response;

return new RoutesCollection(
	new Route('/', function () {
		$rsp = new Response();

		$rsp->getBody()->write(tpl()->render('index', [
			'title' => env('TITLE', 'NaN'),
		]));

		return $rsp;
	}),
);
