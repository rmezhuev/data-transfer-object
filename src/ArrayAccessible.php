<?php

namespace RMezhuev\DTO;

use RMezhuev\DTO\Exceptions\DataObjectException;

trait ArrayAccessible
{
    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->properties);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->properties[$offset];
    }

    /**
     * @param $offset
     * @param $value
     * @throws DataObjectException
     */
    public function offsetSet($offset, $value)
    {
        throw new DataObjectException('Object is immutable');
    }

    /**
     * @param $offset
     * @throws DataObjectException
     */
    public function offsetUnset($offset)
    {
        throw new DataObjectException('Object is immutable');
    }
}
