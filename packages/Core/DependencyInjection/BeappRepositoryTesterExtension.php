<?php

namespace Beapp\RepositoryTesterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Routing\Loader\YamlFileLoader;

class BeappRepositoryTesterExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLoader = new YamlFileLoader(new FileLocator(__DIR__ . '/../resources/config'));

        $fileLoader->load('services.yml');
    }
}
