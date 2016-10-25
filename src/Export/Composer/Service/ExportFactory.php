<?php
namespace Export\Composer\Service;

use Composer\Composer;
use Composer\DependencyResolver\Pool;
use Composer\Repository\CompositeRepository;
use Composer\Package\Version\VersionSelector;

class ExportFactory{
	public function createPool(Composer $composer){
		$pool = new Pool($composer->getPackage()->getMinimumStability(), $composer->getPackage()->getStabilityFlags());
		$pool->addRepository(new CompositeRepository($composer->getRepositoryManager()->getRepositories()));
		return $pool;
	}
	
	public function createVersionSelector(Pool $pool){
		return new VersionSelector($pool);
	}
	
	public function saveJsonFile($file, $jsonArr){
		$fp = fopen($file, 'w');
		fwrite($fp, json_encode($jsonArr,JSON_FORCE_OBJECT));
		fclose($fp);
	}
}