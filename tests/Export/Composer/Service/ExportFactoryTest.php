<?php
use Export\Composer\Service\ExportFactory;
use Composer\Composer;
use Composer\Package\RootPackageInterface;
use Composer\Package\BasePackage;
use Composer\Repository\RepositoryInterface;
use Composer\DependencyResolver\Pool;
use Composer\Repository\CompositeRepository;
use Composer\Package\Version\VersionSelector;
use SebastianBergmann\CodeCoverage\Report\Html\Facade;

class ExportFactoryTest extends PHPUnit_Framework_TestCase{
	private $factory;
	
	protected function setUp(){
		parent::setUp();
		$this->factory = new ExportFactory();
	}
	
	/**
	 * @test
	 */
	public function createPool(){
		$mockComposer = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();
		$mockRootPackage = $this->getMockBuilder(RootPackageInterface::class)->disableOriginalConstructor()->getMock();
		$mockRepository = $this->getMockBuilder(CompositeRepository::class)->disableOriginalConstructor()->getMock();
		
		$mockComposer->expects($this->any())->method('getPackage')->will($this->returnValue($mockRootPackage));
		$mockComposer->expects($this->any())->method('getRepositoryManager')->will($this->returnValue($mockRepository));

		$mockRootPackage->expects($this->any())->method('getMinimumStability')->will($this->returnValue('stable'));
		$mockRootPackage->expects($this->any())->method('getStabilityFlags')->will($this->returnValue([]));
		
		$mockRepository->expects($this->any())->method('getRepositories')->will($this->returnValue([]));
		
		$pool = $this->factory->createPool($mockComposer);
		
		$this->assertInstanceOf(Pool::class, $pool);
	}
	
	/**
	 * @test
	 */
	public function createVersionSelector(){
		$mockPool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
		$selector = $this->factory->createVersionSelector($mockPool);
		
		$this->assertInstanceOf(VersionSelector::class, $selector);
	}
	
	/**
	 * @test
	 */
	public function saveJsonFile(){
		$this->factory->saveJsonFile('tmp.json', ['test']);
	}
}