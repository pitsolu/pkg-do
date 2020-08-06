<?php

namespace App\Command\Doctrine\Migration;

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
* generate:migration  Doctrine Generate Migration
* 
* Usage:
*	
*      generate:migration [<name>] 
*
* Arguments:
*
*      name     (Optional) Name of migration preferrably with number appended 
*               will generate concatenated datetime if left blank
*/
class GenerateMigration extends \Strukt\Console\Command{

	public function execute(Input $in, Output $out){

		$name = trim($in->get("name"));
		if(empty($name))
			$name = date("Ymdhis");

		$message = null;

		try{

			$tplMigration = Fs::cat(Env::get("migration_sgf"));

			$class = Templator::create($tplMigration, array("ver"=>$name));

			\Strukt\Fs::touchWrite(sprintf("%s/Version%s.php", 
											Env::get("migration_home"), 
											ucfirst($name)), 
									$class);
		}
		catch(\Exception $e){

			$message = $e->getMessage();
		}

		if(!is_null($message))
			throw new \Exception($message);
			
		$out->add("Migration generated successfully.");
	}
}