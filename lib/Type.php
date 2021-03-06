<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\ClassName;

class Type
{
    const TYPE_ARRAY = 'array';
    const TYPE_BOOL = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_CLASS = 'object'; 
    const TYPE_NULL = 'null';

    private $type;
    private $className;

    public static function fromArray(array $parts): Type
    {
        return self::fromString(implode('\\', $parts));
    }

    public static function fromValue($value): Type
    {
        if (is_int($value)) {
            return self::int();
        }

        if (is_string($value)) {
            return self::string();
        }

        if (is_float($value)) {
            return self::float();
        }

        if (is_array($value)) {
            return self::array();
        }

        if (is_bool($value)) {
            return self::bool();
        }

        if (null === $value) {
            return self::null();
        }

        if (is_object($value)) {
            return self::class(ClassName::fromString(get_class($value)));
        }

        return self::unknown();
    }

    public static function fromString(string $type): Type
    {
        if ('' === $type) {
            return self::unknown();
        }

        if ($type === 'string') {
            return self::string();
        }

        if ($type === 'int') {
            return self::int();
        }

        if ($type === 'float') {
            return self::float();
        }

        if ($type === 'array') {
            return self::array();
        }

        if ($type === 'bool') {
            return self::bool();
        }

        if ($type === 'mixed') {
            return self::mixed();
        }

        if ($type === 'null') {
            return self::null();
        }

        return self::class(ClassName::fromString($type));
    }

    public static function unknown()
    {
        return new self();
    }

    public static function mixed()
    {
        return new self();
    }

    public static function array()
    {
        return self::create(self::TYPE_ARRAY);
    }

    public static function null()
    {
        return self::create(self::TYPE_NULL);
    }

    public static function bool()
    {
        return self::create(self::TYPE_BOOL);
    }

    public static function string()
    {
        return self::create(self::TYPE_STRING);
    }

    public static function int()
    {
        return self::create(self::TYPE_INT);
    }

    public static function float()
    {
        return self::create(self::TYPE_FLOAT);
    }

    public static function class(ClassName $className)
    {
        $instance = new self();
        $instance->type = self::TYPE_CLASS;
        $instance->className = $className;

        return $instance;
    }

    public static function undefined()
    {
        return new self();
    }

    public function isDefined()
    {
        return null !== $this->type;
    }

    public function __toString()
    {
        return $this->className ? (string) $this->className : $this->type ?: '<unknown>';
    }

    /**
     * Return the short name of the type, whether it be a scalar or class name.
     */
    public function short(): string
    {
        if ($this->isPrimitive()) {
            return $this->type;
        }

        return $this->className->short();
    }

    public function isPrimitive(): bool
    {
        return $this->className === null;
    }

    public function isClass(): bool
    {
        return $this->className !== null;
    }

    public function primitive(): string
    {
        return $this->type;
    }

    public function className()
    {
        return $this->className;
    }

    private static function create($type)
    {
        $instance = new self();
        $instance->type = $type;

        return $instance;
    }
}
