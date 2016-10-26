<?php
use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\Pool;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\CompositeRepository;
use Export\Composer\Service\ExportFactory;

class ExportFactoryTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ExportFactory
     */
    private $factory;

    protected function setUp () {
        parent::setUp();
        $this->factory = new ExportFactory();
    }

    /**
     * @test
     */
    public function createPool () {
        /**
         * @var $mockComposer PHPUnit_Framework_MockObject_MockObject|Composer
         */
        $mockComposer = $this->getMockBuilder(Composer::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $mockRootPackage = $this->getMockBuilder(RootPackageInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $mockRepository = $this->getMockBuilder(CompositeRepository::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $mockComposer->expects($this->any())
                     ->method('getPackage')
                     ->will($this->returnValue($mockRootPackage));
        $mockComposer->expects($this->any())
                     ->method('getRepositoryManager')
                     ->will($this->returnValue($mockRepository));

        $mockRootPackage->expects($this->any())
                        ->method('getMinimumStability')
                        ->will($this->returnValue('stable'));
        $mockRootPackage->expects($this->any())
                        ->method('getStabilityFlags')
                        ->will($this->returnValue([]));

        $mockRepository->expects($this->any())
                       ->method('getRepositories')
                       ->will($this->returnValue([]));

        $pool = $this->factory->createPool($mockComposer);

        $this->assertInstanceOf(Pool::class, $pool);
    }

    /**
     * @test
     */
    public function createVersionSelector () {
        /**
         * @var $mockPool PHPUnit_Framework_MockObject_MockObject|Pool
         */
        $mockPool = $this->getMockBuilder(Pool::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $selector = $this->factory->createVersionSelector($mockPool);

        $this->assertInstanceOf(VersionSelector::class, $selector);
    }

    /**
     * @test
     */
    public function createInstallationManager () {
        /**
         * @var $mockIo       PHPUnit_Framework_MockObject_MockObject|IOInterface
         * @var $mockComposer PHPUnit_Framework_MockObject_MockObject|Composer
         */
        $mockIo = $this->getMockBuilder(IOInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $mockComposer = $this->getMockBuilder(Composer::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $mockConfig = $this->getMockBuilder(Config::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockComposer->expects($this->any())
                     ->method('getConfig')
                     ->will($this->returnValue($mockConfig));

        $mockConfig->expects($this->any())
                   ->method('get')
                   ->will($this->returnValue('config'));

        $manager = $this->factory->createInstallationManager($mockIo, $mockComposer);
    }

    /**
     * @test
     */
    public function saveJsonFile () {
        $this->factory->saveJsonFile('tmp.json', ['test']);
    }
}