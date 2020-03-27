<?php

namespace RMezhuev\DTO\Tests\TestDoubles;

use RMezhuev\DTO\DataObject;

/**
 * @property string $name
 * @property string $email
 * @property string|integer|null $age
 * @property array|null $phone
 * @property CustomType|null $personDetails
 */
class TestDTO extends DataObject
{
    protected $snakeOnSerialize = false;
}



