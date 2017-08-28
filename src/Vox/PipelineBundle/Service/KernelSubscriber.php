<?php

namespace Vox\PipelineBundle\Service;

use Vox\PipelineBundle\Pipeline\PipelineContext;
use Vox\PipelineBundle\Pipeline\PipelineRunner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KernelSubscriber implements EventSubscriberInterface
{
    /**
     * @var PipelineRunner
     */
    private $pipelineRunner;

    /**
     * @var array
     */
    private static $subscribedEvents = [];
    
    public function __construct(PipelineRunner $pipelineRunner, array $subscribedEvents)
    {
        $this->pipelineRunner   = $pipelineRunner;
        self::$subscribedEvents[spl_object_hash($this)] = $subscribedEvents;
    }
    
    public static function getSubscribedEvents(): array
    {
        $events = [];
        
        foreach (self::$subscribedEvents[spl_object_hash($this)] as $event) {
            $events[$event][] = 'runPipeline';
        }
        
        return $events;
    }
    
    public function runPipeline($event)
    {
        $context = new PipelineContext();
        $context->set('event', $event);
        
        $this->pipelineRunner->run($context);
    }
}
