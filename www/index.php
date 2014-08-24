<?php

$app = require '../bootstrap.php';

function loadModel(\Slim\Route $route)
{
	// Handle loading the model from the database
	$id = $route->getParam('id');
	$app = \Slim\Slim::getInstance();
	$model = Schneenet\Vbin\Models\Paste::with(array('revisions' => function($q) {
		$q->orderBy('created_at', 'desc');
	}))->find($id);
	
	if (isset($model))
	{
		$app->view->appendData(array('model' => $model));
	}
	else
	{
		$app->notFound();
	}
};

$app->get('/', function () use ($app) {
	$app->render("editor.twig");
})->name('/');

$app->get('/p/:id(/:rev)', 'loadModel', function($id, $rev = null) use ($app) {
	
	if (isset($rev) == false)
	{
		$revision = $app->view->get('model')->revisions->first();
	}
	else
	{
		$revision = $app->view->get('model')->revisions->find($rev);
	}
		
	if (isset($revision))
	{
		$data = array(
			'revision' => $revision
		);
		$app->render("editor.twig", $data);
	}
	else
	{
		$app->notFound();
	}
})->name('/p/:id/:rev');

$app->post('/save', function() use ($app) {
	// Handle saving from the editor
	$id = $app->request->post('id');
	if ($id == '')
	{
		// $id not set, create a new paste tree
		$id = \Schneenet\HashId::create($app->request->post('paste'), $app->request->getIp() . time() . rand());
		$pasteModel = new Schneenet\Vbin\Models\Paste();
		$pasteModel->id = $id;
		$pasteModel->save();
	}
	else 
	{
		// Find an existing paste
		$pasteModel = Schneenet\Vbin\Models\Paste::find($id);
	}
	
	$revisionModel = new Schneenet\Vbin\Models\PasteRevision(array(
		'lang' => $app->request->post('lang'),
		'title' => $app->request->post('title'),
		'data' => $app->request->post('paste') 
	));
	$revisionModel->createId();
	$pasteModel->revisions()->save($revisionModel);
	$app->flash('message', array('class' => 'alert alert-success', 'text' => "Saved!"));
	$redirectUri = $app->urlFor('/p/:id/:rev', array('id' => $pasteModel->id));
	$app->response->redirect($redirectUri, 303);
})->name('save');

$app->get('/history/:id', 'loadModel', function($id) use ($app) {
	$app->render("history.twig");
})->name('/history/:id');

function handleDiff(\Slim\Route $route) 
{
	$app = \Slim\Slim::getInstance();
	$model = $app->view->get('model');
	$spec = $route->getParam('spec');
	
	// Parse $spec: $from+$to
	if (preg_match('/([0-9a-zA-Z]{8})\@([0-9a-zA-Z]{8})/', $spec, $matches))
	{
		$from = $matches[1];
		$to = $matches[2];
	
		$fromRevision = $model->revisions->find($from);
		$toRevision = $model->revisions->find($to);
	
		// Process diff between revisions
		$diff = new Schneenet\Diff($fromRevision->data, $toRevision->data);
		$app->view->set('diff', $diff);
		$app->view->set('spec', $spec);
		$app->view->set('fromRev', $from);
		$app->view->set('toRev', $to);
	}
	else 
	{
		$app->notFound();	
	}
};

$app->get('/diff/:id/:spec', 'loadModel', 'handleDiff', function($id, $spec) use ($app) {
	//$diff = $app->view->get('diff');
	//$diff->parseUnifiedDiff();
	$app->render("udiff.twig");
})->name('/diff/:id/:spec');

$app->get('/compare/:id/:spec', 'loadModel', 'handleDiff', function($id, $spec) use ($app) {
	$diff = $app->view->get('diff');
	$diff->processForSideBySide();
	$app->render("diff.twig");
})->name('/compare/:id/:spec');


$app->run();