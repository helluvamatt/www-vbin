<?php

$app = require '../bootstrap.php';

$app->get('/', function () use ($app) {
	$app->render("editor.twig");
})->name('/');

$app->get('/:id', function($share_id) use ($app) {
	// Handle loading from the database
	$id = base_convert($share_id, 16, 10);
	$pasteModel = Schneenet\Vbin\Models\Paste::find($id);
	$data = array();
	if (isset($pasteModel))
	{
		$data['previous_id'] = $share_id;
		$data['previous_lang'] = $pasteModel->lang;
		$data['previous_title'] = $pasteModel->title;
		$data['previous_paste'] = $pasteModel->data;
		$app->render("editor.twig", $data);
	}
	else
	{
		$app->notFound();
	}
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
	$redirectUri = $app->urlFor('/:id', array('id' => $pasteModel->shareId));
	$app->response->redirect($redirectUri, 303);
})->name('save');

$app->get('/history/:id', function($share_id) use ($app) {
	$id = base_convert($share_id, 16, 10);
	$model = Schneenet\Vbin\Models\Paste::find($id);
	if (isset($model))
	{
		$historyRecords = Schneenet\Vbin\Models\Paste::history($model);
		$data = array(
			'historyRecords' => $historyRecords,
			'model' => $model
		);
		$app->render("history.twig", $data);
	}
	else
	{
		$app->notFound();
	}
})->name('/history/:id');

$app->get('/diff/:comparison', function($comp) use ($app) {
	// TODO Do comparison
})->name('/diff/:comparison');

$app->run();