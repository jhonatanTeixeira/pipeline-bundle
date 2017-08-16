<?php

namespace Vox\PipelineBundle\Pipeline;

interface ArgumentableInterface
{
    public function extractArguments(PipelineContext $context): array;
}
