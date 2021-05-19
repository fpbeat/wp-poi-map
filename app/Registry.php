<?php

namespace WpPoiMap;

class Registry implements \ArrayAccess
{
    /**
     * @var Registry|null
     */
    static private $instance = NULL;

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @return Registry
     */
    public static function instance(): self
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @throws \Exception
     */
    public function __clone()
    {
        throw new \Exception('Cloning not allowed');
    }

    /**
     * @param string $key
     * @param $value
     */
    public function __set(string $key, $value): void
    {
        $this->add($key, $value);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * @param string|array $name
     * @param null $item
     * @param bool $overwrite
     */
    public function add($name, $item = NULL, $overwrite = TRUE): void
    {
        if (is_array($name) && is_null($item)) {
            foreach ($name as $key => $value) {
                $this->add($key, $value, $overwrite);
            }
        } else {
            if ($overwrite) {
                $this->vars[$name] = $item;
            } else {
                if (!$this->exists($name)) {
                    $this->vars[$name] = $item;
                }
            }
        }

    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = NULL)
    {
        if ($this->exists($name)) {
            return $this->vars[$name];
        }

        return $default;
    }

    /**
     * @param string $name
     */
    public function remove(string $name): void
    {
        if ($this->exists($name)) {
            unset($this->vars[$name]);
        }
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->vars = [];
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->vars;
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->add($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return $this->exists($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
