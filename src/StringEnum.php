<?php
declare(strict_types=1);
namespace erickcomp\Enumerator;

if (($dir = __DIR__) != '/') {
    $dir .= DIRECTORY_SEPARATOR;
}

require_once "{$dir}BaseEnum.php";
unset($dir);

trait StringEnum
{
    use BaseEnum {
        BaseEnum::value as private _value;
        BaseEnum::__invoke as private ___invoke;
    }

    private static $data_type = 'string';

    public function value() : string
    {
        return $this->_value();
    }
    
    public function __invoke() : string
    {
        return $this->___invoke();
    }
}
