<?php

namespace App\Command\Doctrine\Seeder;

use Strukt\Console\Input;
use Strukt\Console\Output;
use Strukt\Generator\Parser;
use Strukt\Generator\Annotation\Standard as StandardAnnotation;
use Strukt\Generator\Compiler\Configuration;
use Strukt\Generator\Compiler\Runner as Compiler;
use Strukt\Env;
use Strukt\Fs;
use Strukt\Templator;

/**
* generate:seeder  Generate Seeder
* 
* Usage:
*
*      generate:seeder <name>
*
* Arguments:
*
*      name     name of seeder should also preferrable be name of table
*/
class GenerateSeeder extends \Strukt\Console\Command{

	public function execute(Input $in, Output $out){

		$name = trim($in->get("name"));

		$message = null;

		try{

			$tplMigration = Fs::cat(Env::get("seeder_sgf"));
			        
			$name = sprintf("%s%s", implode("", array_map(function($val){

				return ucfirst($val);

			}, explode("_", $name))), date("YmdHis"));

			$class = Templator::create($tplMigration, array("NameVer"=>$name));

			Fs::touchWrite(sprintf("%s/%s.php", Env::get("seeder_dir"), $name), $class);
		}
		catch(\Exception $e){

			$message = $e->getMessage();
		}

		if(!is_null($message))
			throw new \Exception($message);
			
		$out->add("Seeder generated successfully.");
	}
}