<?php

namespace Blugen\Enum;

enum ClassNameSuffix: string
{
    case INPUT = 'Input';
    case OUTPUT = 'Output';
    case PARAMS = 'Params';
    case DEFINITION = 'Definition';
}
