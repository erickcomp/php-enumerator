<?php
declare(strict_types=1);
namespace erickcomp\Enumerator;

if (($dir = __DIR__) != '/') {
    $dir .= DIRECTORY_SEPARATOR;
}

require_once "{$dir}Enum.php";
unset($dir);

trait IntEnum
{
    use Enum;
}
