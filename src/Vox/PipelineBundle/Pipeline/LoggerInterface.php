<?php

namespace Vox\PipelineBundle\Pipeline;

interface LoggerInterface
{
    public function start(callable $pipe, array $arguments);
    
    public function finish(callable $pipe, array $arguments);
    
    public function logError(callable $pipe, array $arguments);
}
