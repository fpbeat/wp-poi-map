<?php

namespace WpPoiMap\Tools;

use WpPoiMap\Registry;
use WpPoiMap\Utils\Arr;

class Fenom
{
    /**
     * @var string
     */
    const COMPILED_DIRECTORY = 'cache/compiled';

    /**
     * @var array
     */
    private static $instance = [];

    /**
     * @var \Fenom
     */
    private $fenom;

    /**
     * Fenom constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->fenom = \Fenom::factory(Registry::instance()['views_dir'], Registry::instance()['dir'] . DIRECTORY_SEPARATOR . self::COMPILED_DIRECTORY, $options);
        $this->registerFenomModifications();
    }

    /**
     * @param array $options
     * @return self
     */
    public static function instance(array $options = []): self
    {
        $hash = Arr::getHash($options);

        if (!isset(self::$instance[$hash])) {
            self::$instance[$hash] = new self($options);
        }

        return self::$instance[$hash];
    }

    /**
     * @return void
     */
    private function registerFenomModifications(): void
    {
        $this->fenom->addModifier('default', function ($variable, $default = '') {
            return empty($variable) ? $default : $variable;
        });

        $this->fenom->addBlockFunction('t', function (array $params, $text) {
            $text = trim($text);

            return pll__($text);
        });
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $method, array $params)
    {
        try {
            return call_user_func_array([$this->fenom, $method], $params);
        } catch (\Exception $e) {
            // suppress errors while "fetching" template
            if (in_array($method, ['fetch', 'display'])) {
                return '';
            }

            throw $e;
        }
    }
}