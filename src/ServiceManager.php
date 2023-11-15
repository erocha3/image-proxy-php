<?php

namespace ImageProxyPHP;

use InvalidArgumentException;

class ServiceManager
{
    protected $services = [];

    public function set($key, $object)
    {
        $this->services[$key] = $object;
    }

    public function get($key)
    {
        if (!isset($this->services[$key])) {
            throw new InvalidArgumentException("No service registered for key: {$key}");
        }

        return $this->services[$key];
    }
}