<?php

namespace Vox\PipelineBundle\Pipeline;

use Symfony\Component\EventDispatcher\Event;
use Vox\PipelineBundle\Exception\CannotHandleContextException;
use Vox\PipelineBundle\Exception\ShouldStopPropagationException;

class PipelineRunner implements RunnerInterface
{
    private $pipes = [];
    
    public function addPipe(callable $pipe)
    {
        $this->pipes[] = $pipe;
    }
    
    public function run(Event $context)
    {
        foreach ($this->pipes as $pipe) {
            try {
                $arguments = [$context];
                
                if (method_exists($pipe, 'shouldCall')) {
                    if (!call_user_func([$pipe, 'shouldCall'], $context)) {
                        continue;
                    }
                }
                
                if (method_exists($pipe, 'extractArguments')) {
                    $arguments = call_user_func([$pipe, 'extractArguments'], $context);
                }
                
                $pipe(...$arguments);
                
                if ($context->isPropagationStopped()) {
                    return;
                }
            } catch (CannotHandleContextException $ex) {
                continue;
            } catch (ShouldStopPropagationException $ex) {
                return;
            }
        }
    }
    
    public function __invoke(Event $context)
    {
        $this->run($context);
    }
}
