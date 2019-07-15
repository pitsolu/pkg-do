<?php

namespace App\Provider;

use Strukt\Contract\AbstractProvider;
use Strukt\Contract\AbstractService;
use Strukt\Contract\ProviderInterface;

class EntityManagerAdapter extends AbstractProvider implements ProviderInterface{

	public function register(){

		/**
		* Doctrine Adapter - da
		*/
		$this->core()->set("app.da", new class extends AbstractService{

			/**
			* Adaptor for Doctrine\ORM\EntityManager::getRepository
			*
			* @param string $alias
			*
			* @throws \Exception
			*
			* @return \Doctrine\ORM\EntityRepository
			*/
			public function repo($alias){

				$ns = $this->getNs($alias);

				return $this->em()->getRepository($ns);
			}

			/**
			* Adaptor for Doctrine\ORM\EntityManager::createQuery
			*
			* @param string $dql
			*
			* @throws \Exception
			*
			* @return Doctrine\ORM\Query
			*/
			public function query($dql){

				$gotMatches = preg_match_all("/\~\w+/", $dql, $matches);

				foreach(reset($matches) as $alias)
					$names[] = $this->getNs(trim($alias, "~"));

				$dql = str_replace(reset($matches), $names, $dql);

				return $this->em()->createQuery($dql);
			}

			/**
			* Adaptor for Doctrine\ORM\EntityManager::find
			*
			* @param string  $alias
			* @param integer $id
			*
			* @throws \Exception
			*
			* @return object|null
			*/
			public function find($alias, $id){

				$ns = $this->getNs($alias);

				return $this->em()->find($ns, $id);
			}
		});
	}
}