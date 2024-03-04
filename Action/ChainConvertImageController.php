<?php

namespace MrAuGir\Thumbnail\Action;

use MrAuGir\Thumbnail\Command\ConvertImageCommand;
use Symfony\Component\HttpFoundation\Request;

class ChainConvertImageController
{
    public function __construct(
        private readonly InputFactory $inputFactory,
        private readonly ConvertImageCommand $convertImageCmd
    )
    {
    }

    public function __invoke(Request $request, string $chain, string $path): void
    {
        $input = $this->inputFactory->createConvertChainFromRequest($chain,$path);
    }
}