<?php

declare(strict_types=1);

namespace Phenix\Analyzer;

enum Type: string
{
    case CLIENT = 'client';
    case SERVER = 'server';
    case SHARED = 'shared';
    case UNKNOWN = 'unknown';
}