<?php
namespace Export\Composer\Service;

use Composer\Composer;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\BasePackage;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;

class ExportService {
    private $platformRepository;
    private $phpVersion;
    /**
     * @var CompletePackageInterface[]
     */
    private $packages;
    private $factory;
    /**
     * @var VersionSelector
     */
    private $versionSelector;

    /**
     * @var InstallationManager
     */
    private $installationManager;

    public function __construct (ExportFactory $factory, PlatformRepository $repository) {
        $this->factory = $factory;
        $this->platformRepository = $repository;
        $this->phpVersion = $this->platformRepository->findPackage('php', '*')
                                                     ->getVersion();
        $this->packages = [];

        foreach ($this->platformRepository->getPackages() as $package) {
            $this->packages[$package->getName()] = $package;
        }
    }

    public function load (IOInterface $io, Composer $composer) {
        $repos = $composer->getRepositoryManager()
                          ->getLocalRepository();

        if ($repos instanceof CompositeRepository) {
            $repos = $repos->getRepositories();
        } elseif (!is_array($repos)) {
            $repos = [$repos];
        }

        /* @var $repos RepositoryInterface[] */

        foreach ($repos as $repo) {
            foreach ($repo->getPackages() as $package) {
                $this->packages[$package->getName()] = $package;
            }
        }

        $this->packages[$composer->getPackage()
                                 ->getName()] = $composer->getPackage();

        $this->versionSelector = $this->factory->createVersionSelector($this->factory->createPool($composer));
        $this->installationManager = $this->factory->createInstallationManager($io, $composer);
    }

    public function createJsonArray (Composer $composer) {
        $result = [];
        $result['packages'] = [];

        $jsonLinks = [];
        foreach ($this->packages as $package) {
            $jsonLinks = array_merge($jsonLinks, $this->convertLinks($package->getRequires(), $package));
            $jsonLinks = array_merge($jsonLinks, $this->convertLinks($package->getDevRequires(), $package, true));
            $jsonLinks = array_merge($jsonLinks, $this->convertLinks($package->getSuggests(), $package));
        }

        $result['links'] = $jsonLinks;

        $installed = $this->buildUsedPackagesArray($jsonLinks);

        foreach ($this->packages as $package) {
            if (in_array($package->getName(), $installed)) {
                if (is_object($package)) {
                    $result['packages'][] = $this->convertPackage($package, $composer);
                }
            }
        }

        $result['root'] = $composer->getPackage()
                                   ->getName();

        return $result;
    }

    private function buildUsedPackagesArray ($jsonLinks) {
        $installed = array_map(function ($item) {
            return $item['source'];
        },
            $jsonLinks);

        $installed = array_merge($installed,
                                 array_map(function ($item) {
                                     return $item['target'];
                                 },
                                     $jsonLinks));

        return array_unique($installed);
    }

    private function convertPackage (CompletePackageInterface $package, Composer $composer) {
        $path = $this->installationManager->getInstallPath($package);

        if (!file_exists($path)) {
            $path = null;
        }

        $json = [
            'name' => $package->getName(),
            'version' => $package->getPrettyVersion(),
            'release' => $package->getReleaseDate(),
            'authors' => $package->getAuthors(),
            'description' => $package->getDescription(),
            'homepage' => $package->getHomepage(),
            'keywords' => $package->getKeywords(),
            'sourceType' => $package->getSourceType(),
            'sourceUrl' => $package->getSourceUrl(),
            'type' => $package->getType(),
            'license' => $package->getLicense(),
            'autoload' => $package->getAutoload(),
            'devAutoload' => $package->getDevAutoload(),
            'path' => $path
        ];

        $latestPackage = $this->findLatestPackage($package, $composer);
        if (is_object($latestPackage)) {
            $json['availableVersion'] = $latestPackage->getPrettyVersion();
        } else {
            $json['availableVersion'] = $json['version'];
        }

        return $json;
    }

    /**
     * @param Link[]                   $links
     * @param CompletePackageInterface $package
     * @param bool                     $development
     *
     * @return array
     */
    private function convertLinks ($links, CompletePackageInterface $package, $development = false) {
        $json = [];
        foreach ($links as $key => $link) {
            if (is_object($link)) {
                $json[] = ['source' => $link->getSource(),
                           'target' => $link->getTarget(),
                           'constraint' => $link->getPrettyConstraint(),
                           'development' => $development,
                           'suggest' => false];
            } else {
                $json[] = ['source' => $package->getName(),
                           'target' => $key,
                           'development' => false,
                           'suggest' => true];
            }
        }

        return $json;
    }

    /**
     * Given a package, this finds the latest package matching it
     *
     * @param  CompletePackageInterface $package
     * @param  Composer                 $composer
     *
     * @return PackageInterface|null
     */
    private function findLatestPackage (CompletePackageInterface $package, Composer $composer) {
        // find the latest version allowed in this pool
        $name = $package->getName();
        $stability = $composer->getPackage()
                              ->getMinimumStability();
        $flags = $composer->getPackage()
                          ->getStabilityFlags();
        if (isset($flags[$name])) {
            $stability = array_search($flags[$name], BasePackage::$stabilities, true);
        }

        $bestStability = $stability;
        if ($composer->getPackage()
                     ->getPreferStable()
        ) {
            $bestStability = $package->getStability();
        }

        $targetVersion = null;
        if (0 === strpos($package->getVersion(), 'dev-')) {
            $targetVersion = $package->getVersion();
        }

        return $this->versionSelector->findBestCandidate($name, $targetVersion, $this->phpVersion, $bestStability);
    }
}