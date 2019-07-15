<?php

namespace App\Command\Doctrine\Seeder;

use Strukt\Console\Input;
use Strukt\Console\Output;

/**
* seeder:xls  Seed DB from XLS file. (Ensure file name is only table name)
* 
* Usage:
*	
*      seeder:xls <pathtofile>
*
* Arguments:
*
*      pathtofile    Path to XLS file
*/
class SeederXls extends \Strukt\Console\Command{

	public function execute(Input $in, Output $out){

		$pathtofile = $in->get('pathtofile');

		\App\Service\Seeder\Xls::load($pathtofile);

		$out->add(sprintf("File %s seeded successfully!", $pathtofile));
	}
}