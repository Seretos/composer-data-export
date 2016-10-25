<?php

use Export\Composer\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Composer\Repository\PlatformRepository;
use Export\Composer\Service\ExportService;
use Export\Composer\Service\ExportFactory;
use Export\Composer\ContainerCommandInterface;
use Export\Composer\Command\JsonExportCommand;

class ApplicationTest extends PHPUnit_Framework_TestCase{
	/**
	 * 
	 * @var Application
	 */
	private $application;
	
	protected function setUp(){
		parent::setUp();
		$this->application = new Application();
		
		$container = $this->application->getContainer();
		
		$this->assertInstanceOf(ContainerInterface::class, $container);
		$this->assertTrue($container->has('platform.repository'));
		$this->assertTrue($container->has('json.export.factory'));
		$this->assertTrue($container->has('json.export.service'));
		$this->assertInstanceOf(PlatformRepository::class, $container->get('platform.repository'));
		$this->assertInstanceOf(ExportFactory::class, $container->get('json.export.factory'));
		$this->assertInstanceOf(ExportService::class, $container->get('json.export.service'));
	}
	
	/**
	 * @test
	 */
	public function find_method(){
		$command = $this->application->find('json-export');
		
		$this->assertInstanceOf(ContainerCommandInterface::class, $command);
		$this->assertInstanceOf(JsonExportCommand::class, $command);
		$this->assertSame($this->application->getContainer(), $command->getContainer());
	}
}