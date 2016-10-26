<?php

use Composer\Composer;
use Composer\IO\IOInterface;
use Export\Composer\Command\JsonExportCommand;
use Export\Composer\Service\ExportFactory;
use Export\Composer\Service\ExportService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JsonExportCommandTest extends PHPUnit_Framework_TestCase {
    /**
     * @var JsonExportCommand
     */
    private $command;
    /**
     * @var ReflectionClass
     */
    private $commandReflection;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $mockContainer;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Composer
     */
    private $mockComposer;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|IOInterface
     */
    private $mockIo;

    protected function setUp () {
        parent::setUp();
        $this->command = new JsonExportCommand();
        $this->commandReflection = new ReflectionClass(JsonExportCommand::class);
        $this->mockContainer = $this->getMockBuilder(ContainerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->mockComposer = $this->getMockBuilder(Composer::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->mockIo = $this->getMockBuilder(IOInterface::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->assertSame(null, $this->command->getContainer());
        $this->command->setComposer($this->mockComposer);
        $this->command->setContainer($this->mockContainer);

        $this->command->setIO($this->mockIo);

        $this->assertSame($this->mockContainer, $this->command->getContainer());
    }

    /**
     * @test
     */
    public function configure_method () {
        $method = $this->commandReflection->getMethod('configure');
        $method->setAccessible(true);

        $method->invokeArgs($this->command, []);

        $definition = $this->command->getDefinition();

        $this->assertSame(1, count($definition->getArguments()));
        $this->assertInstanceOf(InputArgument::class, $definition->getArguments()['output']);
        $this->assertSame('output', $definition->getArguments()['output']->getName());
        $this->assertSame('output file for composer data json result',
                          $definition->getArguments()['output']->getDescription());
        $this->assertSame('composer-data.json', $definition->getArguments()['output']->getDefault());
        $this->assertSame('json-export', $this->command->getName());
        $this->assertSame('export the informations about the current composer project in an json format',
                          $this->command->getDescription());
    }

    /**
     * @test
     */
    public function execute_method () {
        $method = $this->commandReflection->getMethod('execute');
        $method->setAccessible(true);

        $mockInput = $this->getMockBuilder(InputInterface::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $mockOutput = $this->getMockBuilder(OutputInterface::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $mockService = $this->getMockBuilder(ExportService::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $mockFactory = $this->getMockBuilder(ExportFactory::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $mockFactory->expects($this->at(0))
                    ->method('saveJsonFile')
                    ->with('my.file', ['success']);

        $mockInput->expects($this->at(0))
                  ->method('getArgument')
                  ->with('output')
                  ->will($this->returnValue('my.file'));

        $this->mockContainer->expects($this->at(0))
                            ->method('get')
                            ->with('json.export.service')
                            ->will($this->returnValue($mockService));
        $this->mockContainer->expects($this->at(1))
                            ->method('get')
                            ->with('json.export.factory')
                            ->will($this->returnValue($mockFactory));

        $mockService->expects($this->at(0))
                    ->method('load')
                    ->with($this->mockIo, $this->mockComposer);
        $mockService->expects($this->at(1))
                    ->method('createJsonArray')
                    ->with($this->mockComposer)
                    ->will($this->returnValue(['success']));

        $method->invokeArgs($this->command, [$mockInput, $mockOutput]);
    }
}