<?php

namespace Vox\PipelineBundle\Service;

use Doctrine\Common\EventSubscriber;
use InvalidArgumentException;
use Vox\PipelineBundle\Pipeline\PipelineContext;
use Vox\PipelineBundle\Pipeline\PipelineRunner;
use Vox\PipelineBundle\Pipeline\RunnerInterface;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @var PipelineRunner
     */
    private $pipelineRunner;
    
    /**
     * @var array
     */
    private $subscribedEvents;
    
    public function __construct(RunnerInterface $pipelineRunner, array $subscribedEvents)
    {
        $this->pipelineRunner   = $pipelineRunner;
        $this->subscribedEvents = $subscribedEvents;
    }
    
    public function getSubscribedEvents(): array
    {
        return $this->subscribedEvents;
    }
    
    public function __call($name, $arguments)
    {
        if (!in_array($name, $this->subscribedEvents)) {
            throw new InvalidArgumentException(
                "no event $name is registered for this this class: " . implode(',', $this->subscribedEvents)
            );
        }
        
        $context = new PipelineContext();
        $context->set('event', $arguments[0]);
        
        $this->pipelineRunner->run($context);
    }
}
