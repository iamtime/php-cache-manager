<?php

class ChipVN_Cache_Manager
{
    /**
     * Create new Cache instrance.
     *
     * @param  string                        $name
     * @param  array                         $options
     * @return ChipVN_Cache_Adapter_Abstract
     */
    public static function make($name = 'Session', array $options = array())
    {
        $class = self::getAdapter($name);

        return new $class($options);
    }

    /**
     * Load all adapters.
     *
     * @return string
     */
    protected static function getAdapter($name)
    {
        static $loaded = false;
        if (!$loaded) {
            foreach (glob(dirname(__FILE__).'/Adapter/*.php') as $file) {
                require_once $file;
            }
            $loaded = true;
        }

        $class = 'ChipVN_Cache_Adapter_'.ucfirst(strtolower($name));
        if (!class_exists($class)) {
            throw new Exception(sprintf('Class "%s" is not exists.', $class));
        }

        return $class;
    }
}
