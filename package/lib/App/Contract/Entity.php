<?php

namespace App\Contract;

use Strukt\Contract\AbstractCore;

abstract class Entity extends AbstractCore{
	
	public function toArray(){

		return $this->core()->get("app.service.normalizer")->toArray($this);
	}

	public function save(){

		$em = $this->core()->get("app.em");
		$em->persist($this);
		$em->flush();
	}
}