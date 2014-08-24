<?php

$app = require '../bootstrap.php';

$app->get('/', function () use ($app) {
	$app->render("editor.twig");
})->name('/');

$app->get('/p/:id(/:rev)', function($id, $rev = null) use ($app) {
	// Handle loading from the database
	$model = Schneenet\Vbin\Models\Paste::with(array('revisions' => function($q) {
		$q->orderBy('created_at', 'desc');
	}))->find($id);
	
	if (isset($model))
	{
		if (isset($rev) == false)
		{
			$revision = $model->revisions->first();
		}
		else
		{
			$revision = $model->revisions->find($rev);
		}
		
		if (isset($revision))
		{
			$data = array(
				'id' => $model->id,
				'model' => $revision
			);
			$app->render("editor.twig", $data);
			return;
		}
	}
	$app->notFound();
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

$app->get('/history/:id', function($id) use ($app) {
	$model = Schneenet\Vbin\Models\Paste::with(array('revisions' => function($q) {
		$q->orderBy('created_at', 'desc');
	}))->find($id);
	if (isset($model))
	{
		$app->render("history.twig", array('model' => $model));
	}
	else
	{
		$app->notFound();
	}
})->name('/history/:id');

$app->get('/diff/:id/:spec', function($id, $spec) use ($app) {

	$model = Schneenet\Vbin\Models\Paste::with(array('revisions' => function($q) {
		$q->orderBy('created_at', 'desc');
	}))->find($id);
	
	if (isset($model))
	{
		// Parse $spec: $from+$to
		if (preg_match('/([0-9a-zA-Z]{8}) ([0-9a-zA-Z]{8})/', $spec, $matches))
		{
			$from = $matches[1];
			$to = $matches[2];
			
			$fromRevision = $model->revisions->find($from);
			$toRevision = $model->revisions->find($to);
			
			// Process diff between revisions
			$diff = new Schneenet\Diff($fromRevision->data, $toRevision->data);
			$diff->parseUnifiedDiff();
			
			$data = array(
				'model' => $model,
				'diff' => $diff,
				'fromRev' => $from,
				'toRev' => $to
			);
			$app->render("diff.twig", $data);
			return;
		}
		else 
		{
			$app->log->error('Failed to parse spec: ' . $spec);
		}
	}
	else
	{
		$app->log->error('Model not found: ' . $id);
	}
	$app->notFound();
	
})->name('/diff/:id/:spec');

$app->run();