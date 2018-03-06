<?php

namespace Vox\PipelineBundle\Pipeline;

use Symfony\Component\EventDispatcher\Event;

interface RunnerInterface
{
    public function addPipe(callable $pipe);
    
    public function run(Event $context);
    
    public function __invoke(Event $context);
}
