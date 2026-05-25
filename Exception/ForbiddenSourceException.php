<?php

namespace MrAuGir\Thumbnail\Exception;

/**
 * Thrown when a requested source is rejected for security reasons:
 * disallowed URL scheme, host outside the allow-list, or payload too large.
 */
class ForbiddenSourceException extends \Exception
{

}
