<?php

namespace MrAuGir\Thumbnail\Action\Output;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ConvertChainOutput
{
    public function jsonPathsOutPut(iterable $paths): JsonResponse
    {

        return new JsonResponse($paths);
    }
}