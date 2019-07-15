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

			// $tplMigration = Fs::cat("tpl/sgf/database/schema/Schema/Migration/Version_.sgf");
			$tplMigration = Fs::cat(Env::get("migration_sgf"));
			        
			// print_r($tplMigration);

			$parser = new Parser(str_replace("__VER__", $name, $tplMigration));

			$config = new Configuration();
			$config->setExcludedMethodParamTypes(array(

				"string",
				"integer",
				"double",
				"float"
			));
			
			$compiler = new Compiler($parser, $config);

			$dump = sprintf("<?php\n%s", $compiler->compile());

			// print_r($dump);

			// \Strukt\Fs::touchWrite(sprintf("database/schema/Schema/Migration/Version%s.php", ucfirst($name)), $dump);

			\Strukt\Fs::touchWrite(sprintf("%s/Version%s.php", 
											Env::get("migration_home"), 
											ucfirst($name)), 
									$dump);
		}
		catch(\Exception $e){

			$message = $e->getMessage();
		}

		if(!is_null($message))
			throw new \Exception($message);
			
		$out->add("Migration generated successfully.");
	}
}