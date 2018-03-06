<?php

namespace Vox\PipelineBundle\Pipeline;

use Symfony\Component\EventDispatcher\Event;

class ResponsabilityChainRunner implements RunnerInterface
{
    /**
     * @var array<callable,ChainHandlerInterface>
     */
    private $handlers;
    
    public function addPipe(callable $pipe)
    {
        if (!$pipe instanceof ChainHandlerInterface) {
            throw new \InvalidArgumentException('chain handler must implement ' . ChainHandlerInterface::class);
        }
        
        $this->handlers[] = $pipe;
    }
    
    public function run(Event $context)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($context)) {
                $arguments = [$context];
                
                if (method_exists($pipe, 'extractArguments')) {
                    $arguments = call_user_func([$pipe, 'extractArguments'], $context);
                }
                
                $handler(...$arguments);
                
                return;
            }
        }
        
        throw new \RuntimeException('no handler found to context');
    }
    
    public function __invoke(Event $context)
    {
        $this->run($context);
    }
}
