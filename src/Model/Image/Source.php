<?php

namespace MrAuGir\Thumbnail\Model\Image;

enum Source : int
{
    case URL = 0;

    case ABSOLUTE = 1;

    case UNKNOW = 2;
}
