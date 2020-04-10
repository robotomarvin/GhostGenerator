<?php declare(strict_types=1);

namespace InfyOm\Generator\Common;

class EBoundTo
{

    public const NONE = 'none';
    public const PROJECT = 'project';
    public const PROJECT_TYPE = 'project_type';

    public const ALL = [
        self::NONE,
        self::PROJECT,
        self::PROJECT_TYPE,
    ];

}
