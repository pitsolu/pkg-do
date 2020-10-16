<?php

namespace App\Command\Doctrine;

use Strukt\Console\Input;
use Strukt\Console\Output;
use Strukt\Core\Registry;
use Strukt\Env;

use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

use App\Contract\Entity;

/**
* generate:models  Doctrine Generate Models
* 
* Usage:
*	
*      generate:models [<filter>] 
*
* Arguments:
*
*      filter     Filter for model
*/
class GenerateModels extends \Strukt\Console\Command{

	public function execute(Input $in, Output $out){

		$module = $in->get("module");
		// $filter = $in->get("filter");

		$message = null;

		try{

			try{

				$registry = Registry::getSingleton();

				$em = $registry->get("app.em");

				$em->getConnection()
					->getDatabasePlatform()
					->registerDoctrineTypeMapping('set', 'string');

				$em->getConnection()
					->getDatabasePlatform()
					->registerDoctrineTypeMapping('enum', 'string');

				// fetch metadata
				$driver = new DatabaseDriver($em->getConnection()->getSchemaManager());

				$appName = end(explode("/", reset(glob(sprintf("%s*", Env::get("rel_appsrc_dir"))))));
				// $driver->setNamespace(sprintf("%s\\%sModule\\Model\\", $appName, $module));
				$driver->setNamespace(sprintf("%s\\", $appName));
				// print_r($appName);

				$em->getConfiguration()->setMetadataDriverImpl($driver);
				$cmf = new DisconnectedClassMetadataFactory($em);
				$cmf->setEntityManager($em); 

				$classes = $driver->getAllClassNames();
				$metadata = $cmf->getAllMetadata(); 

				//Ensure DoctrineMigrationVersions table is not generated as model
				foreach($metadata as $key=>$meta){

					$name = str_replace(sprintf("%s\\", $appName), "", $meta->name);

					if($name == "DoctrineMigrationVersions")
						unset($metadata[$key]);
				}

				// print_r($metadata);exit;

				$generator = new EntityGenerator();
				$generator->setClassToExtend(Entity::class);
				$cme = new ClassMetadataExporter();
				$exporter = $cme->getExporter("annotation", Env::get("rel_appsrc_dir"));
				$metadata = MetadataFilter::filter($metadata, $filter);
				$exporter->setMetadata($metadata);
				$exporter->setEntityGenerator($generator);
				$exporter->setOverwriteExistingFiles(true);
				$exporter->export();
			}
			catch(\Exception $e){

				$message[] = $e->getMessage();
			}

			if(empty($message)){

				$generator = new EntityGenerator();
				$generator->setClassToExtend(Entity::class);
				$generator->setGenerateAnnotations(true);
				$generator->setGenerateStubMethods(true);
				$generator->setRegenerateEntityIfExists(true);
				$generator->setUpdateEntityIfExists(true);
				// $generator->setNumSpaces($numspaces);
				$generator->setBackupExisting(false);
				// $generator->setClassToExtend($extend);
				$generator->generate($metadata, Env::get("rel_appsrc_dir"));
			}
		}
		catch(\Exception $f){

			$message[] = $f->getMessage();

			exit($f);
		}

		if(!is_null($message))
			throw new \Exception(implode("***", $message));
			
		$out->add("Models generated successfully.");
	}
}