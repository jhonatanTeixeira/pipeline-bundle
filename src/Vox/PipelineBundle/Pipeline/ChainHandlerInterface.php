<?php

namespace Vox\PipelineBundle\Pipeline;

interface ChainHandlerInterface
{
    public function canHandle(Event $context): bool;
}
