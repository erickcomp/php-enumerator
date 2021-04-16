<?php
declare(strict_types=1);
namespace erickcomp\Enumerator;

if (($dir = __DIR__) != '/') {
    $dir .= DIRECTORY_SEPARATOR;
}

require_once "{$dir}BaseEnum.php";
unset($dir);

trait ArrayEnum
{
    use BaseEnum {
        BaseEnum::value as private _value;
        BaseEnum::__invoke as private ___invoke;
    }

    private static $data_type = 'array';

    public function value() : array
    {
        return $this->_value();
    }
    
    public function __invoke() : array
    {
        return $this->___invoke();
    }
}
