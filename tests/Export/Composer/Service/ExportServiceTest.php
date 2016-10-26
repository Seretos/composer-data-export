<?php

use Composer\Composer;
use Composer\DependencyResolver\Pool;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\BasePackage;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Export\Composer\Service\ExportFactory;
use Export\Composer\Service\ExportService;

class ExportServiceTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ExportService
     */
    private $service;
    /**
     * @var ReflectionClass
     */
    private $serviceReflection;
    /**
     * @var ExportFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFactory;
    /**
     * @var PlatformRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRepository;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|CompletePackageInterface
     */
    private $mockPhpPackage;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject[]|CompletePackageInterface[]
     */
    private $mockPackages;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Composer
     */
    private $mockComposer;

    protected function setUp () {
        parent::setUp();
        $this->mockFactory = $this->getMockBuilder(ExportFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->mockRepository = $this->getMockBuilder(PlatformRepository::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();
        $this->mockComposer = $this->getMockBuilder(Composer::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->mockPhpPackage = $this->createMockPackage('php', '7.0.9');

        $this->mockPackages = [];
        $this->mockPackages['php'] = $this->createMockPackage('php', '7.0.9');
        $this->mockPackages['php-ext'] = $this->createMockPackage('php-ext', '2.3.1');

        $this->mockRepository->expects($this->at(0))
                             ->method('findPackage')
                             ->with('php', '*')
                             ->will($this->returnValue($this->mockPhpPackage));

        $this->mockRepository->expects($this->at(1))
                             ->method('getPackages')
                             ->will($this->returnValue($this->mockPackages));

        $this->serviceReflection = new ReflectionClass(ExportService::class);
        $this->service = new ExportService($this->mockFactory, $this->mockRepository);
    }

    /**
     * @test
     */
    public function load_withCompositeRepository () {
        $mockRepositoryManager = $this->getMockBuilder(RepositoryManager::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $mockCompositeRepository = $this->getMockBuilder(CompositeRepository::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $mockRootPackage = $this->createMockPackage('root', '1.2.3');
        $mockPool = $this->getMockBuilder(Pool::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $mockVersionSelector = $this->getMockBuilder(VersionSelector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        /* @var $mockIO IOInterface|PHPUnit_Framework_MockObject_MockObject */
        $mockIO = $this->getMockBuilder(IOInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $mockInstallationManager = $this->getMockBuilder(InstallationManager::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->mockComposer->expects($this->at(0))
                           ->method('getRepositoryManager')
                           ->will($this->returnValue($mockRepositoryManager));
        $this->mockComposer->expects($this->at(1))
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));
        $this->mockComposer->expects($this->at(2))
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));

        $mockRepositoryManager->expects($this->at(0))
                              ->method('getLocalRepository')
                              ->will($this->returnValue($mockCompositeRepository));

        $mockCompositeRepository->expects($this->at(0))
                                ->method('getRepositories')
                                ->will($this->returnValue([]));

        $this->mockFactory->expects($this->at(0))
                          ->method('createPool')
                          ->with($this->mockComposer)
                          ->will($this->returnValue($mockPool));
        $this->mockFactory->expects($this->at(1))
                          ->method('createVersionSelector')
                          ->with($mockPool)
                          ->will($this->returnValue($mockVersionSelector));
        $this->mockFactory->expects($this->at(2))
                          ->method('createInstallationManager')
                          ->with($mockIO, $this->mockComposer)
                          ->will($this->returnValue($mockInstallationManager));

        $this->service->load($mockIO, $this->mockComposer);
    }

    /**
     * @test
     */
    public function load_withOneRepositoryInterface () {
        $mockRepositoryManager = $this->getMockBuilder(RepositoryManager::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $mockRepository = $this->getMockBuilder(RepositoryInterface::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $mockRootPackage = $this->createMockPackage('root', '1.2.3');
        $mockPool = $this->getMockBuilder(Pool::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $mockVersionSelector = $this->getMockBuilder(VersionSelector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        /* @var $mockIO IOInterface|PHPUnit_Framework_MockObject_MockObject */
        $mockIO = $this->getMockBuilder(IOInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $mockInstallationManager = $this->getMockBuilder(InstallationManager::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->mockComposer->expects($this->at(0))
                           ->method('getRepositoryManager')
                           ->will($this->returnValue($mockRepositoryManager));
        $this->mockComposer->expects($this->at(1))
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));
        $this->mockComposer->expects($this->at(2))
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));

        $mockRepositoryManager->expects($this->at(0))
                              ->method('getLocalRepository')
                              ->will($this->returnValue($mockRepository));

        $mockRepository->expects($this->at(0))
                       ->method('getPackages')
                       ->will($this->returnValue([$this->createMockPackage('test1', '1.0.0')]));

        $this->mockFactory->expects($this->at(0))
                          ->method('createPool')
                          ->with($this->mockComposer)
                          ->will($this->returnValue($mockPool));
        $this->mockFactory->expects($this->at(1))
                          ->method('createVersionSelector')
                          ->with($mockPool)
                          ->will($this->returnValue($mockVersionSelector));
        $this->mockFactory->expects($this->at(2))
                          ->method('createInstallationManager')
                          ->with($mockIO, $this->mockComposer)
                          ->will($this->returnValue($mockInstallationManager));

        $this->service->load($mockIO, $this->mockComposer);
    }

    /**
     * @test
     */
    public function load_withTwoRepositoryInterfaces () {
        $mockRepositoryManager = $this->getMockBuilder(RepositoryManager::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $mockRepository = $this->getMockBuilder(RepositoryInterface::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $mockRepository2 = $this->getMockBuilder(RepositoryInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $mockRootPackage = $this->createMockPackage('root', '1.2.3');
        $mockPool = $this->getMockBuilder(Pool::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $mockVersionSelector = $this->getMockBuilder(VersionSelector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        /* @var $mockIO IOInterface|PHPUnit_Framework_MockObject_MockObject */
        $mockIO = $this->getMockBuilder(IOInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $mockInstallationManager = $this->getMockBuilder(InstallationManager::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->mockComposer->expects($this->at(0))
                           ->method('getRepositoryManager')
                           ->will($this->returnValue($mockRepositoryManager));
        $this->mockComposer->expects($this->at(1))
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));
        $this->mockComposer->expects($this->at(2))
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));

        $mockRepositoryManager->expects($this->at(0))
                              ->method('getLocalRepository')
                              ->will($this->returnValue([$mockRepository, $mockRepository2]));

        $mockRepository->expects($this->at(0))
                       ->method('getPackages')
                       ->will($this->returnValue([$this->createMockPackage('test1', '1.0.0')]));
        $mockRepository2->expects($this->at(0))
                        ->method('getPackages')
                        ->will($this->returnValue([$this->createMockPackage('test2', '1.0.0')]));

        $this->mockFactory->expects($this->at(0))
                          ->method('createPool')
                          ->with($this->mockComposer)
                          ->will($this->returnValue($mockPool));
        $this->mockFactory->expects($this->at(1))
                          ->method('createVersionSelector')
                          ->with($mockPool)
                          ->will($this->returnValue($mockVersionSelector));
        $this->mockFactory->expects($this->at(2))
                          ->method('createInstallationManager')
                          ->with($mockIO, $this->mockComposer)
                          ->will($this->returnValue($mockInstallationManager));

        $this->service->load($mockIO, $this->mockComposer);
    }

    /**
     * @test
     */
    public function createJsonArray () {
        $packagesProperty = $this->serviceReflection->getProperty('packages');
        $versionSelectorProperty = $this->serviceReflection->getProperty('versionSelector');
        $installationManagerProperty = $this->serviceReflection->getProperty('installationManager');
        $packagesProperty->setAccessible(true);
        $versionSelectorProperty->setAccessible(true);
        $installationManagerProperty->setAccessible(true);

        $mockVersionSelector = $this->getMockBuilder(VersionSelector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $mockInstallationManager = $this->getMockBuilder(InstallationManager::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $mockRootPackage = $this->createMockPackage('root', '1.1', [], [], [], RootPackageInterface::class);

        $mockPackage1 = $this->createMockPackage('test',
                                                 '1.0',
                                                 $this->createMockDependencies('test', ['test2']),
                                                 $this->createMockDependencies('test', ['test3']));
        $mockPackage2 = $this->createMockPackage('test2', '1.1', [], [], ['test3' => 'test3']);
        $mockPackage3 = $this->createMockPackage('test3', 'dev-1.1');

        $mockRootPackage->expects($this->any())
                        ->method('getMinimumStability')
                        ->will($this->returnValue('stability'));
        $mockRootPackage->expects($this->any())
                        ->method('getStabilityFlags')
                        ->will($this->returnValue(['test' => BasePackage::STABILITY_DEV]));
        $mockRootPackage->expects($this->any())
                        ->method('getPreferStable')
                        ->will($this->returnValue(true));

        $mockVersionSelector->expects($this->at(0))
                            ->method('findBestCandidate')
                            ->with('test', null, '7.0.9', null)
                            ->will($this->returnValue($mockRootPackage));

        $packagesProperty->setValue($this->service,
                                    ['test' => $mockPackage1, 'test2' => $mockPackage2, 'test3' => $mockPackage3]);
        $versionSelectorProperty->setValue($this->service, $mockVersionSelector);
        $installationManagerProperty->setValue($this->service, $mockInstallationManager);

        $this->mockComposer->expects($this->any())
                           ->method('getPackage')
                           ->will($this->returnValue($mockRootPackage));

        $this->service->createJsonArray($this->mockComposer);
    }

    private function createMockDependencies ($name, $dependencies = []) {
        $mockRequires = [];
        foreach ($dependencies as $require) {
            $mockLink = $this->getMockBuilder(Link::class)
                             ->disableOriginalConstructor()
                             ->getMock();
            $mockLink->expects($this->any())
                     ->method('getSource')
                     ->will($this->returnValue($name));
            $mockLink->expects($this->any())
                     ->method('getTarget')
                     ->will($this->returnValue($require));
            $mockLink->expects($this->any())
                     ->method('getPrettyConstraint')
                     ->will($this->returnValue('constraint'));
            $mockRequires[] = $mockLink;
        }

        return $mockRequires;
    }

    private function createMockPackage ($name,
                                        $version,
                                        $requires = [],
                                        $devRequires = [],
                                        $suggests = [],
                                        $type = CompletePackageInterface::class) {
        $mockPackage = $this->getMockBuilder($type)
                            ->disableOriginalConstructor()
                            ->getMock();

        $mockPackage->expects($this->any())
                    ->method('getName')
                    ->will($this->returnValue($name));
        $mockPackage->expects($this->any())
                    ->method('getVersion')
                    ->will($this->returnValue($version));

        $mockPackage->expects($this->any())
                    ->method('getRequires')
                    ->will($this->returnValue($requires));
        $mockPackage->expects($this->any())
                    ->method('getDevRequires')
                    ->will($this->returnValue($devRequires));
        $mockPackage->expects($this->any())
                    ->method('getSuggests')
                    ->will($this->returnValue($suggests));
        $mockPackage->expects($this->any())
                    ->method('getAuthors')
                    ->will($this->returnValue(['Arne von Appen']));
        $mockPackage->expects($this->any())
                    ->method('getPrettyVersion')
                    ->will($this->returnValue($version));
        $mockPackage->expects($this->any())
                    ->method('getReleaseDate')
                    ->will($this->returnValue(null));
        $mockPackage->expects($this->any())
                    ->method('getDescription')
                    ->will($this->returnValue('test'));
        $mockPackage->expects($this->any())
                    ->method('getHomepage')
                    ->will($this->returnValue('http://www.google.de'));
        $mockPackage->expects($this->any())
                    ->method('getKeywords')
                    ->will($this->returnValue([]));
        $mockPackage->expects($this->any())
                    ->method('getSourceType')
                    ->will($this->returnValue('git'));
        $mockPackage->expects($this->any())
                    ->method('getSourceUrl')
                    ->will($this->returnValue('https://www.github.com/'));
        $mockPackage->expects($this->any())
                    ->method('getType')
                    ->will($this->returnValue('libary'));
        $mockPackage->expects($this->any())
                    ->method('getLicense')
                    ->will($this->returnValue('MIT'));
        $mockPackage->expects($this->any())
                    ->method('getAutoload')
                    ->will($this->returnValue([]));
        $mockPackage->expects($this->any())
                    ->method('getDevAutoload')
                    ->will($this->returnValue([]));

        return $mockPackage;
    }
}