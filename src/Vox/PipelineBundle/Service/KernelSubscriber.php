<?php

namespace Vox\PipelineBundle\Pipeline\Service;

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
    private $subscribedEvents;
    
    public function __construct(PipelineRunner $pipelineRunner, array $subscribedEvents)
    {
        $this->pipelineRunner   = $pipelineRunner;
        $this->subscribedEvents = $subscribedEvents;
    }
    
    public static function getSubscribedEvents(): array
    {
        $events = [];
        
        foreach ($this->subscribedEvents as $event) {
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
