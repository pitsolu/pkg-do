<?php

namespace App\Command\Doctrine\Seeder;

use Strukt\Console\Input;
use Strukt\Console\Output;

/**
* seeder:json  Seed DB from JSON file. (Ensure file name is only table name)
* 
* Usage:
*	
*      seeder:json <pathtofile>
*
* Arguments:
*
*      pathtofile    Path to XLS file
*/
class SeederJson extends \Strukt\Console\Command{

	public function execute(Input $in, Output $out){

		$path = $in->get('pathtofile');

		\App\Service\Seeder\Json::load($path);

		$out->add(sprintf("File %s seeded successfully!", $path));
	}
}