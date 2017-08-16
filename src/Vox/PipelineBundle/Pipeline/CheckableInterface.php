<?php

namespace Vox\PipelineBundle\Pipeline;

interface CheckableInterface
{
    public function shouldCall(PipelineContext $context): bool;
}
