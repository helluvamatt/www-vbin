<?php

$app = require '../bootstrap.php';

$app->get('/', function () use ($app) {
	$app->render("editor.twig");
})->name('/');

$app->get('/:id', function($share_id) use ($app) {
	// Handle loading from the database
	$id = base_convert($share_id, 16, 10);
	$pasteMode = Schneenet\Vbin\Models\Paste::find($id);
	$data = array(
		'previous_id' => $share_id,
		'previous_lang' => $pasteMode->lang,
		'previous_title' => $pasteMode->title,
		'previous_paste' => $pasteMode->data
	);
	$app->render("editor.twig", $data);
})->name('/:id');

$app->post('/save', function() use ($app) {
	// Handle saving from the editor
	$previous_id = $app->request->post('previous_id');
	$pasteModel = Schneenet\Vbin\Models\Paste::create(array(
		'previous_id' => $previous_id == '' ? null : $previous_id,
		'lang' => $app->request->post('lang'),
		'title' => $app->request->post('title'),
		'data' => $app->request->post('paste') 
	));
	$share_id = str_pad(base_convert($pasteModel->id, 10, 16), 8, "0", STR_PAD_LEFT);
	$redirectUri = $app->urlFor('/:id', array('id' => $share_id));
	$app->response->redirect($redirectUri, 303);
})->name('save');

$app->get('/history/:id', function($id) use ($app) {
	$data = array(
		
	);
	$app->render("history.twig", $data);
})->name('/history/:id');

$app->get('/diff/:comparison', function($comp) use ($app) {
	// TODO Do comparison
})->name('/diff/:comparison');

$app->run();