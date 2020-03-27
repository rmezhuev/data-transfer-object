<?php

namespace RMezhuev\DTO;

use Illuminate\Support\Arr;
use ReflectionClass;
use RMezhuev\DTO\Exceptions\DataObjectException;
use Symfony\Component\PropertyInfo\Type;

trait Validatable
{
    /**
     * Maps php gettype() values to the property extractor types
     * @var array
     */
    private $typesMap = [
        "boolean" => Type::BUILTIN_TYPE_BOOL,
        "integer" => Type::BUILTIN_TYPE_INT,
        "double" => Type::BUILTIN_TYPE_FLOAT,
        "string" => Type::BUILTIN_TYPE_STRING,
        "array" => Type::BUILTIN_TYPE_ARRAY,
        "object" => Type::BUILTIN_TYPE_OBJECT,
        "NULL" => Type::BUILTIN_TYPE_NULL,
    ];

    /**
     * Check if given property is nullable
     * @param $property
     * @return bool
     */
    private function isNullable($property): bool
    {
        $types = $this->magicExtractor->getTypes(static::class, $property);

        return optional(Arr::first($types))->isNullable();
    }

    /**
     * Asserts that all required props are given
     * @param array $parameters
     * @throws DataObjectException
     */
    private function assertRequiredPropsPresent(array $parameters)
    {
        foreach (array_keys($this->properties) as $property) {
            $types = $this->magicExtractor->getTypes(static::class, $property);

            if (optional(Arr::first($types))->isNullable() === false && !Arr::exists($parameters, $property)) {
                throw new DataObjectException("Property '{$property}' is required");
            }
        }
    }

    /**
     * Asserts that all given properties are supported by object
     * @param $parameter
     * @throws DataObjectException
     */
    private function assertSupportedProp($parameter): void
    {
        if (!array_key_exists($parameter, $this->properties)) {
            throw new DataObjectException("Unknown property {$parameter}");
        }
    }

    /**
     * Asserts that given property value type is supported by object
     * @param $parameter
     * @param $value
     * @throws DataObjectException
     */
    private function assertSupportedValueType($parameter, $value)
    {
        $types = collect($this->magicExtractor->getTypes(static::class, $parameter))
            ->filter(function (Type $type) use ($value) {
                $paramType = gettype($value);

                $match = $this->typesMap[$paramType] == $type->getBuiltinType();

                if ($type->getBuiltinType() == Type::BUILTIN_TYPE_OBJECT) {
                    $match = is_object($value)
                        ? (new ReflectionClass($value))->getShortName() == $type->getClassName()
                        : false;
                }

                return $match;
            });

        if ($this->isNullable($parameter) && is_null($value)) {
            $types->push(new Type(Type::BUILTIN_TYPE_NULL));
        }

        if (!$types->count()) {
            throw new DataObjectException("Unsupported type for {$parameter} value");
        }
    }
}
