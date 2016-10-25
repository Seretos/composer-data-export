<?php
namespace Export\Composer\Command;

use Export\Composer\ContainerCommandInterface;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JsonExportCommand extends BaseCommand implements ContainerCommandInterface{
	private $container;
	
	public function setContainer(ContainerInterface $container){
		$this->container = $container;
	}
	
	public function getContainer(){
		return $this->container;
	}
	
	protected function configure()
	{
		$this
		->setName('json-export')
		->setDefinition([new InputArgument('output', InputArgument::OPTIONAL, 'output file for composer data json result','composer-data.json')])
		->setDescription('export the informations about the current composer project in an json format');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$file = $input->getArgument('output');		
		$composer = $this->getComposer(false);
		
		$service = $this->container->get('json.export.service');

		$service->load($composer);
		$result = $service->createJsonArray($composer);
		
		$factory = $this->container->get('json.export.factory');
		$factory->saveJsonFile($file,$result);
	}
}