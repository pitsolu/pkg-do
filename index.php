<?php

use Strukt\Http\Response;
use Strukt\Http\Request;
use Strukt\Http\RedirectResponse;
use Strukt\Http\JsonResponse;
use Strukt\Http\Session;

use Strukt\Router\Middleware\ExceptionHandler;
use Strukt\Router\Middleware\Authentication; 
use Strukt\Router\Middleware\Authorization;
use Strukt\Router\Middleware\StaticFileFinder;
use Strukt\Router\Middleware\Session as SessionMiddleware;
use Strukt\Router\Middleware\Router as RouterMiddleware;

use Strukt\Framework\Provider\Validator as ValidatorProvider;
use Strukt\Framework\Provider\Annotation as AnnotationProvider;
use Strukt\Framework\Provider\Router as RouterProvider;
use App\Provider\Logger as LoggerProvider;
use App\Provider\EntityManager as EntityManagerProvider;
use App\Provider\EntityManagerAdapter as EntityManagerAdapterProvider;
use App\Provider\Normalizer as NormalizerProvider;

use Strukt\Event\Event;
use Strukt\Env;

use Cobaia\Doctrine\MonologSQLLogger;

ini_set('display_errors', '1');
ini_set("date.timezone", "Africa/Nairobi");

error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

$appCfg = parse_ini_file("cfg/app.ini");

$loader = require 'vendor/autoload.php';
$loader->add('App', __DIR__.'/lib/');
$loader->add('Seed', __DIR__.'/database/seeder/');
$loader->add($appCfg["app-name"], __DIR__.'/app/src/');

Env::set("is_dev", true);
Env::set("root_dir", getcwd());
Env::set("rel_appsrc_dir", "app/src/");
Env::set("rel_static_dir", "/public/static");
Env::set("rel_mod_ini", "/cfg/module.ini");
Env::set("rel_db_ini", "cfg/db.ini");
Env::set("logger_name", "Strukt Logger");
Env::set("logger_file", "logs/app.log");

$kernel = new Strukt\Router\Kernel(Request::createFromGlobals());
$kernel->inject("app.dep.author", function(){

	return array(

		"permissions" => array(

			// "show_secrets"
		)
	);
});

$kernel->inject("app.dep.logger.sqllogger", function(){

	return new MonologSQLLogger(null, null, __DIR__ . '/logs/');
});

$kernel->inject("app.dep.authentic", function(Session $session){

	$user = new Strukt\User();
	$user->setUsername($session->get("username"));

	return $user;
});

$kernel->inject("app.dep.session", function(){

	return new Session;
});

$kernel->providers(array(

	LoggerProvider::class,
	ValidatorProvider::class,
	AnnotationProvider::class,
	RouterProvider::class,
	EntityManagerProvider::class,
	EntityManagerAdapterProvider::class,
	NormalizerProvider::class
));

$kernel->middlewares(array(
	
	ExceptionHandler::class,
	SessionMiddleware::class,
	Authorization::class,
	Authentication::class,
	StaticFileFinder::class,
	RouterMiddleware::class
));

$loader = new App\Loader($kernel);
$app = $loader->getApp();
$app->runDebug();