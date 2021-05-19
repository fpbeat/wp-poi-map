<?php

namespace WpPoiMap\Admin;

class Transport {

    const SUPPORTED = ['gpx'];

    public static function factory(array $params, $value) {
        $class = sprintf('\MapRoute\Admin\Transports\%sTransport', ucfirst($params['type']));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException;
        }

        return new $class($params, $value);
    }
}