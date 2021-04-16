<?php
declare(strict_types=1);
namespace erickcomp\Enumerator;

if (($dir = __DIR__) != '/') {
    $dir .= DIRECTORY_SEPARATOR;
}

require_once "{$dir}BaseEnum.php";

/**
 * Basic Enumerator, that uses integer data type
 *
 **/
trait Enum
{
    use BaseEnum {
        BaseEnum::value as private _value;
        BaseEnum::__invoke as private ___invoke;
    }
    
    private static $data_type = 'integer';

    public function value() : int
    {
        return $this->_value();
    }
    
    public function __invoke() : int
    {
        return $this->___invoke();
    }
}
