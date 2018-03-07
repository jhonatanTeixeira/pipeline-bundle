<?php

namespace Vox\PipelineBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Vox\PipelineBundle\Pipeline\PipelineRunner;
use Vox\PipelineBundle\Pipeline\ResponsabilityChainRunner;
use Vox\PipelineBundle\Service\DoctrineSubscriber;
use Vox\PipelineBundle\Service\KernelListener;

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
        $this->createLogger($container);

        foreach ($pipelinesConf as $name => $pipelineConf) {
            $type = $pipelineConf['type'];

            $runnerClassName = $pipelineConf['style'] == 'pipe' 
                ? PipelineRunner::class
                : ResponsabilityChainRunner::class;
            
            $pipelineRunnerDefinition = $container->register($name, $runnerClassName)
                ->setAutowired(true);

            switch ($type) {
                case 'kernel-subscriber':
                    $this->configureKernelListener($name, $pipelineConf, $container);
                    break;
                case 'doctrine-subscriber':
                    $this->configureDoctrineListener($name, $pipelineConf, $container);
                    break;
                case 'service':
                    $this->configureClass($name, $pipelineConf, $container);
                    break;
                default:
                    throw new Exception("Invalid pipeline type $type");
            }

            foreach ($pipelineConf['services'] as $service) {
                $pipelineRunnerDefinition->addMethodCall('addPipe', [new Reference($service)]);
            }
        }
    }
    
    private function createLogger(ContainerBuilder $container)
    {
        $name = \Vox\PipelineBundle\Pipeline\PipelineLogger::class;
        
        $container->autowire($name)
            ->addTag('monolog.logger', ['channel' => 'pipelines']);
    }
    
    private function configureKernelListener($name, array $pipelineConf, ContainerBuilder $container)
    {
        if (!isset($pipelineConf['subscribedEvents'])) {
            throw new Exception('if you chose an event subscriber you need to register the events to subscribe to using option subscribedEvents');
        }
        
        foreach ($pipelineConf['subscribedEvents'] as $subscribedEvent) {
            $listener = new Definition(KernelListener::class, [new Reference($name)]);
            $listener->addTag('kernel.event_listener', ['event' => $subscribedEvent, 'method' => '__invoke']);
            $container->setDefinition(sprintf('kernel.listener.%s.%s', $name, $subscribedEvent), $listener);
        }
    }
    
    private function configureDoctrineListener($name, array $pipelineConf, ContainerBuilder $container)
    {
        if (!isset($pipelineConf['subscribedEvents'])) {
            throw new Exception('if you chose an event subscriber you need to register the events to subscribe to using option subscribedEvents');
        }
        
        $subscriber = new Definition(DoctrineSubscriber::class, [new Reference($name), $pipelineConf['subscribedEvents']]);
        $subscriber->addTag('doctrine.event_subscriber');
        $container->setDefinition(sprintf('doctrine.subscriber.%s', $name), $subscriber);
    }
    
    private function configureClass($name, array $pipelineConf, ContainerBuilder $container)
    {
        if (!isset($pipelineConf['class'])) {
            return;
        }
        
        $arguments = [new Reference($name)];
        
        if (isset($pipelineConf['subscribedEvents'])) {
            $arguments[] = $pipelineConf['subscribedEvents'];
        }
        
        $wrapperDefinition = new Definition($pipelineConf['class'], $arguments);
        
        $container->setDefinition(sprintf('pipeline.service.%s.%s', $name, $pipelineConf['class']), $wrapperDefinition);
    }
}
