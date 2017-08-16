<?php

namespace Vox\PipelineBundle\DependencyInjection;

use Exception;
use Vox\PipelineBundle\Pipeline\PipelineRunner;
use Vox\PipelineBundle\Pipeline\Service\DoctrineSubscriber;
use Vox\PipelineBundle\Pipeline\Service\KernelSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class VoxPipelineExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->loadServices($config, $container);
    }

    private function loadServices($config, ContainerBuilder $container)
    {
        $pipelinesConf = $config['pipelines'];

        foreach ($pipelinesConf as $name => $pipelineConf) {
            $type = $pipelineConf['type'];

            $pipelineRunnerDefinition = $container->register($name, PipelineRunner::class);

            $tags      = [];
            $class     = $pipelineConf['class'] ?? null;
            $arguments = [new Reference($name)];

            switch ($type) {
                case 'kernel-subscriber':
                    $typeClass = KernelSubscriber::class;
                    $tags[]    = 'kernel.event_listener';
                    break;
                case 'doctrine-subscriber':
                    $typeClass = DoctrineSubscriber::class;
                    $tags[]    = 'doctrine.event_subscriber';
                    break;
                case 'service':
                    $typeClass = null;
                    $tags      = $pipelineConf['tags'] ?? [];
                    break;
                default:
                    throw new Exception("Invalid pipeline type $type");
            }

            if ($typeClass) {
                if (!isset($pipelineConf['subscribedEvents'])) {
                    throw new Exception('if you chose an event subscriber you need to register the events to subscribe to using option subscribedEvents');
                }

                $arguments[] = $pipelineConf['subscribedEvents'];
            }

            if ($class || $typeClass) {
                $className         = $class ?: $typeClass;
                $wrapperDefinition = new Definition($className, $arguments);

                foreach ($tags as $tag) {
                    $wrapperDefinition->addTag($tag);
                }

                $container->setDefinition($className, $wrapperDefinition);
            }

            foreach ($pipelineConf['services'] as $service) {
                $pipelineRunnerDefinition->addMethodCall('addPipe', [new Reference($service)]);
            }
        }
    }
}
