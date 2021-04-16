<?php
declare(strict_types=1);
namespace erickcomp\Enumerator;

//if (!defined('ERICKCOMP_ENUMERATOR_SKIP_ENUM_COMPILING')) {
//    define('ERICKCOMP_ENUMERATOR_SKIP_ENUM_COMPILING', false);
//}

/**
* @property string $data_type Must be compatible with constant(integer, string, float/double or array)
* @property bool $allow_same_values_for_different_identifiers
*/
trait BaseEnum
{
    // Options
    //private static $allow_same_values_for_different_identifiers = false;
    //private static $allow_string_values = true;
    //private static $allow_array_values = true;
    
    /** @var */
    //private static $data_type = ''; //
    private static $_default_allow_same_values_for_different_identifiers = true;
    
    // Value
    private $value;
    private $value_label;
    
    /**
     * Private constructor. Validate range of assingned value. It's private so no one can misassing any value.
     * @param mixed $const_val
     */
    final private function __construct($const_label, $const_val)
    {
        foreach (static::enumValues() as $ek => $ev) {
            if ($const_label === $ek && $const_val === $ev) {
                $this->value = $ev;
                $this->value_label = $ek;

                return;
            }
        }

        throw new \UnexpectedValueException(" Value \'$const_val\' could not be found on ENUM " . static::class);
    }
    
    final public static function fromValue($val) : array
    {
        static::compile();

        $ret = [];
        
        $enum_values = static::enumValues();
        $enum_objects = static::enumObjects();
        
        foreach ($enum_values as $k => $v) {
            if ($v === $val) {
                $ret[] = $enum_objects[$k];
            }
        }
        
        return $ret;
    }
    
    final public static function firstFromValue($val)
    {
        static::compile();

        foreach (static::enumValues() as $k => $v) {
            if ($v === $val) {
                return static::enumObjects()[$k];
            }
        }
        
        return null;
    }
    
    /**
     * Returns interval value.
     * @return mixed
     */
    final public function value()
    {
        return $this->value;
    }
    
    final public function label()
    {
        return $this->value_label;
    }

    /**
     * Functor behaviour. It's an alias for EnumBase::getValue()
     * @see Enum::getValue()
     * @return mixed
     */
    final public function __invoke()
    {
        return $this->value();
    }
    
    /**
     * Returns the identifier of the internal value
     * @return string
     */
    final public function __toString(): string
    {
        return $this->label().':'.json_encode($this->value(), JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * It's an alias for EnumBase::__toString()
     * @see Enum::__toString()
     * @return string
     */
    final public function toString(): string
    {
        return $this->__toString();
    }

    private static function enumValues()
    {
        static::compile();

        static $enum_values = null;
        
        if ($enum_values === null) {
            $reflection_object = new \ReflectionClass(static::class);
            $enum_values = $reflection_object->getConstants();
        }
        
        return $enum_values;
    }
    
    /**
     * Returns the only allowed instances of this class
     * @return mixed[]
     */
    private static function enumObjects()
    {
        static::compile();
        static $enum_objects = null;
        
        $vals = [];
        if ($enum_objects === null) {
            foreach (static::enumValues() as $key => $val) {
                $vals[$key] = new static($key, $val);
            }
            
            $enum_objects = $vals;
        }
        
        return $enum_objects;
    }
    
    final public static function compile()
    {
        static $compiled = null;
        static $must_compile = null;
        
        if ($compiled) {
            return;
        }
        
        if ($must_compile === null) {
            $must_compile = true;
            
            if (defined('ERICKCOMP_ENUMERATOR_SKIP_ENUM_COMPILING')) {
                $must_compile = \ERICKCOMP_ENUMERATOR_SKIP_ENUM_COMPILING;
            }            
        }

        //pt-BR: O que não precisa ser compilado, compilado está. =P
        if (!$must_compile) {
            $compiled = true;

            return;
        }
        
        $make_parse_error = function ($msg) {
            $ref = new \ReflectionClass(static::class);
            $line = $ref->getStartLine();
            $file = $ref->getFileName();
            
            $error = new \ParseError($msg);
            $ref = new \ReflectionClass(\ParseError::class);
            
            $file_prop = $ref->getProperty('file');
            $file_prop->setAccessible(true);
            $file_prop->setValue($error, $file);
            
            $line_prop = $ref->getProperty('line');
            $line_prop->setAccessible(true);
            $line_prop->setValue($error, $line);
            
            return $error;
        };
        
        $reflection_object = new \ReflectionClass(static::class);
        
        if (!$reflection_object->isFinal()) {
            throw $make_parse_error("All Enum classes must be marked as final");
        }
        
        $object_constants = $reflection_object->getReflectionConstants();
        
        // Default value for option
        //$allow_same_values_for_different_identifiers = static::$_default_allow_same_values_for_different_identifiers;
        //
        //if (isset(static::$allow_same_values_for_different_identifiers)) {
        //    $allow_same_values_for_different_identifiers = static::$allow_same_values_for_different_identifiers;
        //}
        $allow_same_values_for_different_identifiers = 
            static::$allow_same_values_for_different_identifiers
            ?? static::$_default_allow_same_values_for_different_identifiers
        ;
        
        $prevs = [];
        $prev_val = null;
        $prev_datatype = null;

        foreach ($object_constants as $reflection_constant) {
            $ek = $reflection_constant->getName();
            $ev = $reflection_constant->getValue();
            
            if (!$reflection_constant->isPrivate()) {
                if ($reflection_constant->isPublic()) {
                    $accessibilty = 'public';
                } else {
                    $accessibilty = 'protected';
                }
                
                $msg = "All enum constants must be private. Enum member '$ek' is declared as '$accessibilty'";
                throw $make_parse_error($msg);
            }
            
            $ev_type = gettype($ev);
            if ($ev_type !== static::$data_type) {
                $msg = "All enum constants must be of type '".static::$data_type."'. Enum member '$ek' is of the type '$ev_type'";
                
                throw $make_parse_error($msg);
            }
            
            if ($allow_same_values_for_different_identifiers === false) {
                $ev_key = in_array(gettype($ev), ['integer', 'string']) ? $ev : json_encode($ev, JSON_PRESERVE_ZERO_FRACTION);
                
                if (count($prevs) > 0) {
                    if (array_key_exists($ev_key, $prevs)) {
                        $echo_ev = json_encode($ev, JSON_PRESERVE_ZERO_FRACTION);

                        $msg = "Enum member '$ek' has same value as enum member '$prevs[$ev_key]': '" . $echo_ev;
                        throw $make_parse_error($msg);
                    }
                }
                $prevs[$ev_key] = $ek;
            }
        }

        $compiled = true;
    }
    
    public static function validate()
    {
        return static::compile();
    }
    
    /**
     * Returns the fly-weight object corresponding to the informed constant name.
     * E.g.:<code>
     *
     * final class MyEnum
     * {
     *      use erickcomp\Enumerator\Enum;
     *
     *      private const A = 1;
     *      private const B = 2;
     * }
     *
     * MyEnum::A();
     * </code>
     *
     * MyEnum::A() will return THE(one and only) object instance of corresponding A constant value. A fly-weight.
     * It's useful when enforcing types and strict comparing comparing objects.
     * @param string $const_name
     * @param array $args Must be empty or will raise a \BadMethodCallException
     * @throws \LogicException
     * @throws \BadMethodCallException
     * @return mixed
     */
    final public static function __callStatic(string $const_name, array $args = [])
    {
        static::compile();

        if (!array_key_exists($const_name, static::enumValues())) {
            $msg = "Enum value '$const_name' could not be found on ENUM " . static::class;
            throw new \LogicException($msg);
        }

        if (count($args) !== 0) {
            $msg = "Function " . static::class . '::' . $const_name . "() does not accept any arguments";
            throw new \BadMethodCallException($msg);
        }

        return static::enumObjects()[$const_name];
    }
    
    /**
     * Raises \LogicException when one tries to clone this object.
     * @return string[]
     */
    final public function __clone()
    {
        $msg = static::class." objects cannot be cloned. Use the static methods with the name of enum constant to get an instance";
        throw new \LogicException($msg);
    }
    
    /**
     * Provides serializing features. One needs only implement interface
     * on class.
     * @return string[]
     */
    public function jsonSerialize()
    {
        return [
            'label' => $this->value_label,
            'value' => $this->value
        ];
    }
}
