<?php

namespace Vox\PipelineBundle\Pipeline;

use Psr\Log\LoggerInterface as PsrLogger;
use Symfony\Component\Stopwatch\Stopwatch;

class PipelineLogger implements LoggerInterface
{
    /**
     * @var PsrLogger
     */
    private $logger;
    
    /**
     * @var Stopwatch
     */
    private $stopWatch;
    
    public function __construct(PsrLogger $logger, Stopwatch $stopWatch = null)
    {
        $this->logger    = $logger;
        $this->stopWatch = $stopWatch;
    }
    
    public function start(callable $pipe, array $arguments)
    {
        $name = $this->getPipeName($pipe);
        
        if ($this->stopWatch) {
            $this->stopWatch->start($name, 'pipelines');
        }
        
        $this->logger->debug("calling pipline item $name", $arguments);
    }
    
    public function finish(callable $pipe, array $arguments)
    {
        $name = $this->getPipeName($pipe);
        
        if ($this->stopWatch) {
            $this->stopWatch->stop($name);
        }
        
        $this->logger->debug("called pipeline item $name", $arguments);
    }
    
    public function logError(callable $pipe, array $arguments)
    {
        $this->logger->error($this->getPipeName($pipe), $arguments);
    }
    
    private function getPipeName(callable $pipe)
    {
        return is_object($pipe)
            ? get_class($pipe) . ' ' . spl_object_hash($pipe)
            : implode('::', $pipe);
    }
}
