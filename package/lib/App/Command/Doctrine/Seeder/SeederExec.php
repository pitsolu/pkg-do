<?php

namespace App\Command\Doctrine\Seeder;

use Strukt\Console\Input;
use Strukt\Console\Output;
use Strukt\Core\Registry;
use Strukt\Env;

/**
* seeder:exec  Execute Database Seeder
* 
* Usage:
*	
*      seeder:exec <name> [<action>]
*
* Arguments:
*
*      name     Name of table to seed or 'all'
*	   action   (optional) up or down, default up
*/
class SeederExec extends \Strukt\Console\Command{


	public function execute(Input $in, Output $out){

		$registry = Registry::getInstance();

		$conn = $registry->get("app.em")->getConnection();

		$action = $in->get("action");
		if(empty($action))
			$action = "up";

		$name = $in->get("name");

		if($name!="all"){

			$name = implode("", array_map(function($val){

				return ucfirst($val);

			}, explode("_", $name)));

			// $seeders = glob(sprintf("database/seeder/Seed/%s*", $name));
			$seeders = glob(sprintf("%s/%s*.php", Env::get("seeder_dir"), $name));
		}
		else
			$seeders = glob(sprintf("%s/*.php", Env::get("seeder_dir")));

		foreach($seeders as $seeder){

			// $seeder = str_replace(array(".php", "database/seeder", "/"), array("", "","\\"), $seeder);

			$seeder = str_replace(array(".php", Env::get("seeder_home"), "/"), 
									array("", "","\\"), $seeder);

			list($cls, $ver) = preg_split("/(?<=[a-z])(?=[0-9]+)/i", ltrim($seeder, "\\"));

			$files[$ver] = $cls;
		}

		if(!empty($files)){

			if($action == "up")
				ksort($files);
			elseif($action == "down")
				krsort($files);

			foreach($files as $ver=>$cls){

				if(!preg_match("/.*_.*/", $cls)){ //Ignore files with underscore

					$reflCls = new \ReflectionClass(sprintf("%s%s", $cls, $ver));
					$seeder = $reflCls->newInstance();

					if($action == "up")
						$seeder->up($conn);
					elseif($action == "down")
						$seeder->down($conn);
					else
						throw new \Exception(sprintf("Invalid action: %s!", $action));
				}		
			}
		}
		else throw new \Exception(sprintf("%s seeder(s) not found!", ucfirst($name)));

		$out->add(sprintf("%s seeder(s) executed successfully!", ucfirst($name)));
	}
}