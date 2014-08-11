<?php

$app = require '../bootstrap.php';

$app->get('/', function () use ($app) {
	$app->render("editor.twig");
})->name('/');

$app->get('/:id', function($id) use ($app) {
	// TODO Editor
	$data = array();
	$app->render("editor.twig", $data);
})->name('/:id');

$app->post('/save', function() use ($app) {
	// TODO Handle saving from the editor
})->name('save');

$app->get('/history/:id', function($id) use ($app) {
	// TODO Do history
})->name('/history/:id');

$app->get('/diff/:comparison', function($comp) use ($app) {
	// TODO Do comparison
})->name('/diff/:comparison');

$app->run();