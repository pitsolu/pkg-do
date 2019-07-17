<?php

namespace App\Contract;

use Strukt\Core\Registry;

abstract class AbstractCommand extends \Strukt\Console\Command{

	public function core(){

		return Registry::getInstance();
	}

	protected function get($alias, Array $args = null){

		$core = $this->core()->get("core");

		if(!empty($args))
			return $core->getNew($alias, $args);

		return $core->get($alias);
	}
}