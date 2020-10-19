<?php

namespace App\Provider;

use Strukt\Env;
use Strukt\Core\Registry;
use Strukt\Contract\AbstractProvider;
use Strukt\Contract\ProviderInterface;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class EntityManager extends AbstractProvider implements ProviderInterface{

	public function __construct(){

		//
	}

	public function register(){

		try{

			$paths = array_map(function($path){

				return sprintf("%s/%s", Env::get("root_dir"), $path);

			}, glob(sprintf("%s*/*", Env::get("rel_appsrc_dir"))));

			// $cacheDir = sprintf("%s/cache", Env::get("root_dir"));
			// if (!is_dir($cacheDir))
			//     mkdir($cacheDir);
	
			// $config = Setup::createAnnotationMetadataConfiguration($paths, 
			// 														Env::get("is_dev"), 
			// 														$cacheDir);

			$config = Setup::createAnnotationMetadataConfiguration($paths, Env::get("is_dev"));
			$config->setSQLLogger($this->core()->get("app.dep.logger.sqllogger")->exec());
			$config->setAutoGenerateProxyClasses(true);

			//registering noop annotation autoloader - allow all annotations by default
			AnnotationRegistry::registerLoader('class_exists');
			$config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader(), $paths));

			//obtaining the entity manager
			$em = DoctrineEntityManager::create(parse_ini_file(Env::get("rel_db_ini")), $config);
		}
		catch(\Exception $e){

			$this->core()->get("app.logger")->error($e);

			$em = null;
		}

		$this->core()->set("app.em", $em);	
	}
}

