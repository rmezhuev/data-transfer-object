<?php

namespace RMezhuev\DTO;

use RMezhuev\DTO\Exceptions\DataObjectException;

trait ArrayAccessible
{
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->properties);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->properties[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new DataObjectException('Object is immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new DataObjectException('Object is immutable');
    }
}
