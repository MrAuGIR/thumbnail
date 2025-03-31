<?php

namespace MrAuGir\Thumbnail\Message\Handler;

use MrAuGir\Thumbnail\Engine;
use MrAuGir\Thumbnail\Message\ThumbnailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ThumbnailMessageHandler
{
    /**
     * @param Engine $engine
     */
    public function __construct(private Engine $engine)
    {
    }
    /**
     * @param ThumbnailMessage $message
     * @return void
     */
    public function __invoke(ThumbnailMessage $message) : void
    {

    }
}