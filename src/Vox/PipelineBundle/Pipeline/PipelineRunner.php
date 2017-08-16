<?php

namespace Vox\PipelineBundle\Pipeline;

use Vox\PipelineBundle\Exception\CannotHandleContextException;
use Vox\PipelineBundle\Exception\ShouldStopPropagationException;

class PipelineRunner
{
    private $pipes = [];
    
    public function addPipe(callable $pipe)
    {
        $this->pipes[] = $pipe;
    }
    
    public function run(PipelineContext $context)
    {
        foreach ($this->pipes as $pipe) {
            try {
                $arguments = [$context];
                
                if ($pipe instanceof CheckableInterface) {
                    if (!call_user_func([$pipe, 'shouldCall'], $context)) {
                        continue;
                    }
                }
                
                if ($pipe instanceof ArgumentableInterface) {
                    $arguments = call_user_func([$pipe, 'extractArguments'], $context);
                }
                
                $pipe(...$arguments);
                
                if ($context->isPropagationStoped()) {
                    return;
                }
            } catch (CannotHandleContextException $ex) {
                continue;
            } catch (ShouldStopPropagationException $ex) {
                return;
            }
        }
    }
}
