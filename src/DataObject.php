<?php

namespace RMezhuev\DTO;

use ArrayAccess;
use Hartmann\PropertyInfo\Extractor\PhpDocMagicExtractor;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use RMezhuev\DTO\Exceptions\DataObjectException;

/**
 * Immutable DTO with serialization support.
 */
abstract class DataObject implements Arrayable, Jsonable, ArrayAccess
{
    use ArrayAccessible, Validatable, Serializable;

    /**
     * All supported properties described by PhpDoc for DTO class.
     * @var array
     */
    private $properties = [];

    /**
     * Initialized properties via object construction.
     * @var array
     */
    private $initializedProperties = [];

    /**
     * Extracts types for object properties from phpdoc
     * @var PhpDocMagicExtractor
     */
    private $magicExtractor;

    /**
     * Getter for DTO properties
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return Arr::get($this->properties, $name);
    }

    /**
     * Setter for DTO properties
     * @param $name
     * @param $value
     * @throws DataObjectException
     */
    public function __set($name, $value)
    {
        throw new DataObjectException('Object is immutable');
    }

    /**
     * DataObject constructor.
     * @param array $parameters
     * @throws DataObjectException
     */
    public function __construct(array $parameters = [])
    {
        $this->magicExtractor = new PhpDocMagicExtractor();

        $this->properties = collect($this->magicExtractor->getProperties(static::class))
            ->flip()
            ->map(function () {
            })
            ->toArray();

        $this->assertRequiredPropsPresent($parameters);

        foreach ($parameters as $parameter => $value) {

            $this->assertSupportedProp($parameter);
            $this->assertSupportedValueType($parameter, $value);

            $this->properties[$parameter] = $value;

            array_push($this->initializedProperties, $parameter);
        }
    }

    /**
     * Set partial serialization for next serialisation only
     * @return $this
     */
    public function partial(): self
    {
        $this->isPartial = true;

        return $this;
    }

}
