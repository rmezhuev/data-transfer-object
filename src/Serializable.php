<?php

namespace RMezhuev\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

trait Serializable
{
    /**
     * Determines if object should use partial serialization with initialized properties only.
     * This property will be reset after serialization ca to initial false state
     * @var bool
     */
    protected $isPartial = false;

    /**
     * @var bool
     */
    protected $snakeOnSerialize = true;

    /**
     * Serializes object into array
     * @return array
     */
    public function toArray(): array
    {
        $serialized = collect($this->properties);

        //partial serialization only for initialized properties
        if ($this->isPartial) {
            $serialized = $serialized->intersectByKeys(array_flip($this->initializedProperties));
        }

        $serialized = $serialized->mapWithKeys(function ($value, $property) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if ($this->snakeOnSerialize) {
                $property = Str::snake($property);
            }

            return [$property => $value];
        });

        $this->resetSerializationConditions();

        return $serialized->toArray();
    }

    /**
     * Serializes object into Json string
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Resets serialization settings to initial state
     */
    private function resetSerializationConditions(): void
    {
        $this->isPartial = false;
    }
}
