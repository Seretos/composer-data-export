<?php
namespace Export\Composer\Console;

use Composer\Console\Application as BaseApplication;
use Export\Composer\Command\JsonExportCommand;
use Export\Composer\ContainerCommandInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Application extends BaseApplication {
    private $container;

    public function __construct () {
        $this->container = new ContainerBuilder();

        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__.'/../../../../Resources/config/'));
        $loader->load('services.yml');

        parent::__construct();
        $this->setDefaultCommand('json-export');
    }

    /**
     *
     * @return ContainerInterface
     */
    public function getContainer () {
        return $this->container;
    }

    public function find ($name) {
        $command = parent::find($name);
        if ($command instanceof ContainerCommandInterface) {
            $command->setContainer($this->container);
        }

        return $command;
    }

    protected function getDefaultCommands () {
        //return array_merge(parent::getDefaultCommands(), [new JsonExportCommand()]);
        return [new JsonExportCommand()];
    }
}