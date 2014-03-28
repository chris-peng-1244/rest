<?php
$loader = new Phalcon\Loader();
$loader->registerDirs(array(
	__DIR__ . '/app/models'
))->register();
$di = new Phalcon\DI\FactoryDefault();
$di->set('db', function() {
	return new Phalcon\Db\Adapter\Pdo\Mysql(array(
		'host' => 'localhost',
		'username' => 'root',
		'password' => '',
		'dbname' => 'robotics'
	));
});
$app = new Phalcon\Mvc\Micro($di);

$app->get('/api/robots', function() use ($app) {
	$robots = Robots::find();

	$data = array();
	foreach($robots as $robot) {
		$data[] = array(
			'id' => $robot->id,
			'name' => $robot->name,
		);
	}
	echo json_encode($data);
});

$app->get('/api/robots/search/{name}', function($name) {
	$robots = Robots::find(array("name = ?0",
		"bind" => array($name)
	));
	$data = array();
	foreach ($robots as $robot) {
		$data[] = array(
			'id' => $robot->id,
			'name' => $robot->name,
		);
	}
	echo json_encode($data);
});

$app->get('/api/robots/{id:[0-9]+}', function($id) {
	$robot = Robots::findFirst(array(
		"id = ?0",
		"bind" => array($id)
	));

	$response = new Phalcon\Http\Response;
	if (!$robot) {
		$response->setJsonContent(array('status' => 'NOT-FOUND'));
	} else {
		$response->setJsonContent(array(
			'status' => 'FOUND',
			'data' => array(
				'id' => $robot->id,
				'name' => $robot->name
			)
		));
	}
	return $response;
});

$app->post('/api/robots', function() use($app) {
	$robot = new Robots;
	$robot->name = $app->request->getPost('name');
	$robot->type = $app->request->getPost('type');
	$robot->year = $app->request->getPost('year');

	$response = new Phalcon\Http\Response();
	if ($robot->save() == true) {
		$response->setStatusCode(201, 'Created');
		$robot->id = $robot->id;
		$response->setJsonContent(array('status' => 'OK', 'data' => $robot->toArray()));
	} else {
		getErrors($response, $robot);
	}
	return $response;
});

$app->put('/api/robots/{id:[0-9]+}', function($id) use($app) {
	$robot = Robots::findFirst(array(
		"id = ?0",
		"bind" => array($id)
	));

	$response = new Phalcon\Http\Response();
	if (!$robot) {
		$response->setJsonContent(array('status' => 'NOT-FOUND'));
	} else {
		$robot->name = $app->request->getPut('name');
		$robot->type = $app->request->getPut('type');
		$robot->year = $app->request->getPut('year');
		if ($robot->save()) {
			$response->setJsonContent(array('status' => 'OK'));
		} else {
			getErrors($response, $robot);
		}
	}
	return $response;
});

$app->delete('/api/robots/{id:[0-9]+}', function($id) {
	$robot = Robots::findFirst(array(
		"id = ?0",
		"bind" => array($id)
	));
	$response = new Phalcon\Http\Response();
	if (!$robot) {
		$response->setJsonContent(array('status' => 'NOT-FOUND'));
	} else {
		if ($robot->delete()) {
			$response->setJsonContent(array('status' => 'OK'));
		} else {
			getErrors($response, $robot);
		}
	}
	return $response;
});

$app->notFound(function() use ($app) {
	$app->response->setStatusCode(404, "Not Found")->sendHeaders();
	echo 'This is crazy, but this page was not found!';
});
$app->handle();

function getErrors($response, $model)
{
	$response->setStatusCode(409, "Conflict");
			$errors = array();
			foreach ($model->getMessages() as $message) {
				$errors[] = $message->getMessage();
			}

			$response->setJsonContent(array('status' => 'ERROR', 'message' => $errors));
}
