<?php

namespace Vox\PipelineBundle\Pipeline;

class PipelineContext
{
    private $data = [];
    
    private $stopPropagation = false;
    
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
    
    public function stopPropagation()
    {
        $this->stopPropagation = true;
    }
    
    public function isPropagationStoped(): bool
    {
        return $this->stopPropagation;
    }
}
