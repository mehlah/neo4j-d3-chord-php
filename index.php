<?php
require 'bootstrap.php';
require 'graph.php';

use lithium\net\http\Router;
use lithium\action\Dispatcher;
use lithium\action\Response;

Router::connect('/follows', array(), function($request) {
	$graph = new Graph();
	$graph->create();
	$result = $graph->matrix();

	$json = [];
	foreach ($result as $k => $res) {
		$json[$k]['name'] = $res[0];
		for ($i = 0; $i < count($res[1]); $i++) {
			$json[$k]['follows'][] = $res[1][$i];
		}
	}

	$response = new Response(['body' => json_encode($json)]);
	$response->headers('Content-Type', 'application/json'); //should be fixed soon in Lithium
	return $response;
});

Router::connect('/', array(), function($request) {
	$body = file_get_contents('public/index.html');
	return new Response(compact('body'));
});

echo Dispatcher::run(new lithium\action\Request());