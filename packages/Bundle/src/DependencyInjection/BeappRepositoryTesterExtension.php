<?php

namespace Beapp\RepositoryTesterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BeappRepositoryTesterExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../resources/config'));
        $fileLoader->load('services.yml');
    }
}
