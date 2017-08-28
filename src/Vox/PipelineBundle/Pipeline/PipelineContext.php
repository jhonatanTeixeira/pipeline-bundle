<?php

namespace Vox\PipelineBundle\Pipeline;

use Symfony\Component\EventDispatcher\Event;

class PipelineContext extends Event
{
    private $data = [];
    
    public function get($name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }
    
    public function set($name, $value)
    {
        $this->data[$name] = $value;
        
        return $this;
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}
