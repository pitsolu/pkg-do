<?php

namespace App\Contract;

use Symfony\Component\Inflector\Inflector;
use Strukt\Contract\AbstractCore;
use Strukt\Type\Str;
use Strukt\Raise;


abstract class Entity extends AbstractCore{
	
	public function toArray(){

		return $this->core()->get("app.service.normalizer")->toArray($this);
	}

	public function save(){

		$em = $this->core()->get("app.em");
		$em->persist($this);
		$em->flush();
	}

	public function __call($func, $args){

		$da = $this->core()->get("app.da");

		$funcName = new Str($func);

		if($funcName->startsWith("get")){

			$ns = new Str(get_class($this));
			$nsArr = $ns->toLower()->split("\\");
			$field = (string)end($nsArr);

			$foreignCls = (string)$funcName->replaceFirst("get","");

			$foreignSigCls = new Str(Inflector::singularize($foreignCls));

			$repo = $da->repo($foreignSigCls);

			$params = array($field=>$this->getId());

			if($foreignSigCls->equals($foreignCls))
				return $repo->findOneBy($params);
			
			return $repo->findBy($params);
		}

		new Raise(sprintf("Entity ignored call [%s]!", $func));
	}
}