<?php
declare(strict_types=1);
namespace erickcomp\Enumerator;

if (($dir = __DIR__) != '/') {
    $dir .= DIRECTORY_SEPARATOR;
}

require_once "{$dir}DoubleEnum.php";
unset($dir);

use erickcomp\Enumerator\BaseEnum;

trait FloatEnum
{
    use DoubleEnum;
}
