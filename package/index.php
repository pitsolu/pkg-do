<?php

require "bootstrap.php";

use Strukt\Http\Response;
use Strukt\Http\Request;
use Strukt\Http\RedirectResponse;
use Strukt\Http\Session;

use Strukt\Router\Middleware\ExceptionHandler;
use Strukt\Router\Middleware\Authentication; 
use Strukt\Router\Middleware\Authorization;
use Strukt\Router\Middleware\StaticFileFinder;
use Strukt\Router\Middleware\Session as SessionMiddleware;
use Strukt\Router\Middleware\Router as RouterMiddleware;
// use App\Middleware\Cors as CorsMiddleware;

use Strukt\Framework\Provider\Validator as ValidatorProvider;
use Strukt\Framework\Provider\Annotation as AnnotationProvider;
use Strukt\Framework\Provider\Router as RouterProvider;

use App\Provider\Logger as LoggerProvider;
use App\Provider\EntityManager as EntityManagerProvider;
use App\Provider\EntityManagerAdapter as EntityManagerAdapterProvider;
use App\Provider\Normalizer as NormalizerProvider;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Cobaia\Doctrine\MonologSQLLogger;

use Strukt\Event\Event;
use Strukt\Env;

Env::set("root_dir", getcwd());
Env::set("rel_app_ini", "/cfg/app.ini");
Env::set("rel_static_dir", "/public/static");
Env::set("rel_mod_ini", "/cfg/module.ini");
Env::set("is_dev", true);

Env::set("rel_appsrc_dir", "app/src/");
Env::set("rel_db_ini", "cfg/db.ini");
Env::set("logger_name", "Strukt Logger");

$now = new DateTime("now");
Env::set("logger_file", sprintf("logs/app-%s.log", $now->format("Y-m-d")));

$kernel = new Strukt\Router\Kernel(Request::createFromGlobals());

$kernel->inject("app.dep.logger.sqllogger", function() use($now){

	$logfile = sprintf("/logs/doctrine-%s.log", $now->format("Y-m-d"));

	$handler = new StreamHandler(__DIR__ . $logfile, Logger::DEBUG);

	$logger = new Logger("doctrine-logger");

	return new MonologSQLLogger($logger, $handler);
});

$kernel->inject("app.dep.author", function(Session $session){

	return array(

		//show_secrets
	);
});

/**/ //strukt-strukt//
$kernel->inject("app.dep.authentic", function(Session $session){

	$user = new Strukt\User();
	$user->setUsername($session->get("username"));

	return $user;
});
/**/ //strukt-strukt//

$kernel->inject("app.dep.session", function(){

	return new Session;
});

$kernel->providers(array(

	ValidatorProvider::class,
	AnnotationProvider::class,
	RouterProvider::class,
	LoggerProvider::class,
	EntityManagerProvider::class,
	EntityManagerAdapterProvider::class,
	NormalizerProvider::class
));

$kernel->middlewares(array(
	
	
	ExceptionHandler::class,
	SessionMiddleware::class,
	Authorization::class,
	Authentication::class,
	// CorsMiddleware::class,
	RouterMiddleware::class,
));

$loader = new App\Loader($kernel);
$app = $loader->getApp(); 
$app->runDebug();