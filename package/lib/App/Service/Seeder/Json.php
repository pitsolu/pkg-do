<?php

namespace App\Service\Seeder;

use Strukt\Fs;
use Strukt\Type\Json as JsonUtil;
use Strukt\Contract\AbstractCore;

class Json extends AbstractCore{

	public static function load($path){

		$em = self::core()->get("app.em");

		$conn = $em->getConnection();

		$pathinfo = pathinfo($path);

		$fileName = $pathinfo['filename'];

		if(!Fs::isFile($path))
			throw new \Exception(sprintf("File [%s.json] not found!", $fileName));

		$rawContent = Fs::cat($path);

		if(empty($rawContent))
			throw new \Exception(sprintf("File [%s] is empty!", $fileName));

		if(!JsonUtil::isJson($rawContent))
			throw new \Exception("Invalid JSON content!");

		$content = json_decode($rawContent, 1);

		$contentKeys = array_keys($content);

		if(in_array("relations", $contentKeys)){

			$hasCouples = in_array("couples", array_keys($content["relations"]));

			if($hasCouples){

				$couples = reset($content["relations"]["couples"]);
				$relations = array_keys($couples);

				$leftRel = reset($relations);
				$leftRelFld = $couples[$leftRel];
				$rightRel = next($relations);
				$rightRelFld = $couples[$rightRel];

				foreach($content["data"] as $entry){

					//left
					$sql = str_replace(array("_REL", "_FLD"), 
										array($leftRel, $leftRelFld), 
										"SELECT id FROM _REL WHERE _FLD = :_FLD");

					$stmt = $conn->prepare($sql);
					$stmt->bindValue($leftRelFld, $entry["to"]);

					if(!$stmt->execute())
						throw new \Exception("Failed to execute [LEFT] sql relation!");

					$id = reset($stmt->fetch());

					//right
					$sql = str_replace(array("_REL", "_FLD"), 
										array($rightRel, $rightRelFld),
										'SELECT id FROM _REL WHERE _FLD IN (?)');

					$stmt = $conn->executeQuery($sql,
					    array($entry["add"]),
					    array(\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
					);

					if(!$stmt->execute())
						throw new \Exception("Failed to execute [RIGHT] sql relation!");

					$rs = $stmt->fetchAll();

					foreach($rs as $row)
						$data[] = array(

							sprintf("%s_id", $leftRel)=>$id,
							sprintf("%s_id", $rightRel)=>$row["id"]
						);

					unset($content["data"]);

					$content["data"] = $data;
				}
			}

			if(!$hasCouples){

				$relations = array_keys($content["relations"]);

				foreach($content["data"] as $seqKey=>$entry){

					foreach($content["relations"] as $relation=>$field){

						$sql = str_replace(array("_REL", "_FLD"), 
											array($relation, $field), 
											"SELECT id FROM _REL WHERE _FLD = :_FLD");

						$stmt = $conn->prepare($sql);
						$stmt->bindValue($field, $entry[$relation]);

						if(!$stmt->execute())
							throw new \Exception("Failed to execute [LEFT] sql relation!");

						$id = reset($stmt->fetch());

						unset($content["data"][$seqKey][$relation]);

						$content["data"][$seqKey][sprintf("%s_id", $relation)] = $id;
					}
				}		
			}
		}

		foreach($content["data"] as $row){

			// $row["token"] = \App\Util\Hash::random();

			if(!empty($content["generic"]))
				foreach($content["generic"] as $key=>$val)
					if(!in_array($key, array_keys($row)))
						if($val == "_")
							$row[$key] = (new \DateTime())->format("Y-m-d");
						else
							$row[$key] = $val;

			//setting.json
			// if(in_array("value", array_keys($row)))
				// if(is_array($row["value"]))
					// $row["value"] = json_encode($row["value"]);

			$conn->insert($content["table"], $row);
		}
	}
}