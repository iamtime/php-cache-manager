<?php

class ChipVN_Cache_Manager
{
    /**
     * Create new Cache instrance.
     *
     * @param  string                        $adapter
     * @param  array                         $options
     * @return ChipVN_Cache_Adapter_Abstract
     */
    public static function make($adapter = 'Session', array $options = array())
    {
        $prefix = 'ChipVN_Cache_Adapter_';
        foreach (array($prefix.'Abstract', $class = $prefix.ucfirst($adapter)) as $name) {
            if (!class_exists($name, false)) {
                require self::getClassFile($name);
            }
        }

        return new $class($options);
    }

    /**
     * Gets class file.
     *
     * @param  string $class
     * @return string
     */
    protected static function getClassFile($class)
    {
        return strtr($class, array(
            'ChipVN' => dirname(dirname(__FILE__)),
            '_'      => DIRECTORY_SEPARATOR,
        )).'.php';
    }
}
