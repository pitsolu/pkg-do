<?php

namespace App\Command\Doctrine\Migration;

use Strukt\Console\Input;
use Strukt\Console\Output;
use Strukt\Console\Color;
use Strukt\Env;
use Strukt\Core\Registry;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
* migrate:exec  Doctrine Execute Migration
* 
* Usage:
*	
*      migrate:exec [<version>] 
*
* Arguments:
*
*      version     (Optional) The version number (YYYYMMDDHHMMSS) or alias (first, prev, next, 
*								latest) to migrate to. default:latest
*/
class MigrateExec extends \Strukt\Console\Command{

	public function execute(Input $in, Output $out){

		$versionAlias = $in->get("version");

		if(empty($versionAlias))
			$versionAlias = "latest";

		$message = null;

		try{

			$registry = Registry::getInstance();

			$em = $registry->get("app.em");

			$outputWriter = new OutputWriter(function($msg) use ($out){

				$formatter = new OutputFormatter();
        		$formatter->setDecorated(true);

				$out->add($formatter->format($msg));
			});

			$conf = new Configuration($em->getConnection(), $outputWriter);
			$conf->setMigrationsNamespace(Env::get("migration_ns"));
			$conf->setMigrationsDirectory(Env::get("migration_home"));

			$version = $conf->resolveVersionAlias($versionAlias);

	        if ($version === null){

	            if($versionAlias == 'prev')	                
	                throw new \Exception("Already at first version");

	            if($versionAlias == 'next')
	            	throw new \Exception("Already at latest version.");

	            throw new \Exception(sprintf("Unknown version: %s", $versionAlias));
	        }

			$executedMigrations = $conf->getMigratedVersions();
        	$availableMigrations = $conf->getAvailableVersions();
        	$executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);

	        if (!empty($executedUnavailableMigrations)) {

	            $out->add(sprintf(Color::write("yellow","WARNING! You have %s previously executed migrations in the database that are not registered migrations.'"),
	                count($executedUnavailableMigrations)
	            ));

	            foreach ($executedUnavailableMigrations as $executedUnavailableMigration) {

	                $out->add(sprintf(Color::write("yellow","    >> %s (").Color::write("yellow", "%s)"),
	                    $conf->getDateTime($executedUnavailableMigration),
	                    $executedUnavailableMigration
	                ));
	            }

	            $question = 'Are you sure you wish to continue? (y/n)';
	            $ans = $in->getInput($question);

	            if(!in_array(trim(strtolower($ans)), array("y","yes", "")))
	            	throw new Exception("Migration cancelled");
	        }

			$dryRun = false;
			$timeAllqueries = true;

			$migration = new Migration($conf);
			$result = $migration->migrate($version, $dryRun, $timeAllqueries, function() use ($in){

	            $question = 'WARNING! You are about to execute a database migration'
			                . ' that could result in schema changes and data lost.'
			                . ' Are you sure you wish to continue? (y/n)';

	            $ans = $in->getInput($question);

	            $continue = false;
	            if(in_array(trim(strtolower($ans)), array("y","yes", "")))
	            	$continue = true;

	            return $continue;
	        });
		}
		catch(\Exception $e){

			$message = $e->getMessage();
		}

		if(!is_null($message))
			throw new \Exception($message);
	}
}