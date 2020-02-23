<?php

require "bootstrap.php";

use Strukt\Event\Event;
use Strukt\Env;
use Cobaia\Doctrine\MonologSQLLogger;

Env::set("root_dir", getcwd());
Env::set("rel_app_ini", "/cfg/app.ini");
// Env::set("rel_static_dir", "/public/static");
Env::set("rel_mod_ini", "/cfg/module.ini");
Env::set("is_dev", true);

Env::set("rel_appsrc_dir", "app/src/");
Env::set("rel_db_ini", "cfg/db.ini");
Env::set("logger_name", "Strukt Logger");
Env::set("logger_file", "logs/app.log");

$loader = new App\Loader(null);

$app = $loader->getApp();

Strukt\Core\Registry::getInstance()->set("app.dep.logger.sqllogger", new Event(function(){

	return new MonologSQLLogger(null, null, __DIR__ . '/logs/');
}));

foreach(array(
	new App\Provider\Logger(),
	new App\Provider\EntityManager(),
	new App\Provider\EntityManagerAdapter()) as $provider)
		$provider->register();

$app->initialize();