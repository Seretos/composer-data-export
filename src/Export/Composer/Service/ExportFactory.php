<?php
namespace Export\Composer\Service;

use Composer\Composer;
use Composer\DependencyResolver\Pool;
use Composer\Installer\InstallationManager;
use Composer\Installer\LibraryInstaller;
use Composer\Installer\MetapackageInstaller;
use Composer\Installer\PearInstaller;
use Composer\Installer\PluginInstaller;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\CompositeRepository;

class ExportFactory {
    public function createPool (Composer $composer) {
        $pool = new Pool($composer->getPackage()
                                  ->getMinimumStability(),
                         $composer->getPackage()
                                  ->getStabilityFlags());
        $pool->addRepository(new CompositeRepository($composer->getRepositoryManager()
                                                              ->getRepositories()));

        return $pool;
    }

    public function createVersionSelector (Pool $pool) {
        return new VersionSelector($pool);
    }

    public function saveJsonFile ($file, $jsonArr) {
        $json = new JsonFile($file);
        $json->write($jsonArr);
//        $fp = fopen($file, 'w');
//        fwrite($fp, json_encode($jsonArr, JSON_FORCE_OBJECT));
//        fclose($fp);
    }

    public function createInstallationManager (IOInterface $io, Composer $composer) {
        $installation = new InstallationManager();
        $installation->addInstaller(new LibraryInstaller($io, $composer, null));
        $installation->addInstaller(new PearInstaller($io, $composer, 'pear-library'));
        $installation->addInstaller(new PluginInstaller($io, $composer));
        $installation->addInstaller(new MetapackageInstaller($io));

        return $installation;
    }
}