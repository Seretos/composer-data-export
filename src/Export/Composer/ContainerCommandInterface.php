<?php
namespace Export\Composer;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ContainerCommandInterface{
	public function setContainer(ContainerInterface $container);
	/**
	 * @return ContainerInterface
	 */
	public function getContainer();
}