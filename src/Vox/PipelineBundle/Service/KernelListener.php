<?php

namespace Vox\PipelineBundle\Service;

use Symfony\Component\EventDispatcher\Event;
use Vox\PipelineBundle\Pipeline\RunnerInterface;

class KernelListener
{
    private $pipelineRunner;
    
    public function __construct(RunnerInterface $pipelineRunner)
    {
        $this->pipelineRunner = $pipelineRunner;
    }
    
    public function __invoke(Event $event)
    {
        $this->pipelineRunner->run($event);
    }
}
