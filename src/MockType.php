<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus;

enum MockType
{

    case INT;

    case STRING;

    case BOOL;

    case FLOAT;

    case OBJECT;

    case ARRAY;

    case OBJECT_ARRAY;

    case ENUM;
}
