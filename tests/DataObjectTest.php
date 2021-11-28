<?php

namespace RMezhuev\DTO\Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use RMezhuev\DTO\Exceptions\DataObjectException;
use RMezhuev\DTO\Tests\TestDoubles\CustomType;
use RMezhuev\DTO\Tests\TestDoubles\TestDTO;
use RMezhuev\DTO\Tests\TestDoubles\TestSnakeDTO;
use stdClass;

class DataObjectTest extends TestCase
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function test_should_throw_exception_on_unknown_properties()
    {
        $this->expectException(DataObjectException::class);

        new TestDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'unknown' => $this->faker->userName,
        ]);
    }

    public function test_should_throw_exception_if_required_property_was_not_provided()
    {
        $this->expectException(DataObjectException::class);

        new TestDTO([
            'name' => $this->faker->name,
        ]);
    }

    public function test_should_throw_exception_if_property_with_unsupported_internal_type()
    {
        $this->expectException(DataObjectException::class);

        new TestDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'age' => $this->faker->randomFloat(),
        ]);
    }

    public function test_should_throw_exception_if_property_with_unsupported_class_type()
    {
        $this->expectException(DataObjectException::class);

        new TestDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'personDetails' => new stdClass(),
        ]);
    }

    public function test_should_serialize_dto()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'age' => $this->faker->randomNumber(),
            'phone' => [$this->faker->phoneNumber, $this->faker->phoneNumber],
            'personDetails' => new CustomType(),
        ];

        $dto = new TestDTO($data);

        $this->assertEquals($data, $dto->toArray());
        $this->assertEquals(json_encode($data), $dto->toJson());
    }

    public function test_should_support_partial_serialisation()
    {
        $data = [
            'name' => $this->faker->unique()->name,
            'email' => $this->faker->unique()->email,
        ];

        $dataWithNullables = array_merge($data, [
            'age' => null,
            'phone' => null,
            'personDetails' => null,
        ]);

        $dto = new TestDTO($data);

        $this->assertEquals($data, $dto->partial()->toArray());
        $this->assertEquals($dataWithNullables, $dto->toArray());
    }

    public function test_should_be_array_accessible()
    {
        $data = [
            'name' => $this->faker->unique()->name,
            'email' => $this->faker->unique()->email,
        ];

        $dto = new TestDTO($data);

        $this->assertEquals($data['name'], $dto['name']);
        $this->assertEquals($data['email'], $dto['email']);
    }

    public function test_should_allow_null_values_for_nullable()
    {
        $dto = new TestDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'age' => null,
            'personDetails' => null,
        ]);

        $this->assertNull($dto->age);
        $this->assertNull($dto->personDetails);
    }

    public function test_should_throw_exception_on_set_property_attempt()
    {
        $this->expectException(DataObjectException::class);

        $dto = new TestDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
        ]);

        switch ($this->faker->numberBetween(1, 3)) {
            case 1:
                $dto->email = $this->faker->email;
                break;
            case 2:
                $dto['email'] = $this->faker->email;
                break;
            case 3:
                unset($dto['email']);
                break;
        }
    }

    public function test_should_use_snake_case_for_serialization()
    {
        $personDetails = new CustomType();

        $dto = new TestSnakeDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'personDetails' => $personDetails,
        ]);

        $this->assertEquals($personDetails, $dto->toArray()['person_details']);
    }

    public function test_should_preserve_snake_for_partial()
    {
        $personDetails = new CustomType();

        $dto = new TestSnakeDTO([
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'personDetails' => $personDetails,
        ]);

        $this->assertEquals($personDetails, $dto->partial()->toArray()['person_details']);
    }
}
