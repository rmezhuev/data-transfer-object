# Flexible Data transfer objects

Implementation of immutable Data Transfer Object (DTO) concept for safe transferring data between architecture layers of application. Like transferring data received from API to the application core. Arrays don't provide reliable mechanism for that purpose, due to the lack of strict structure, validation, type checks and immutability. Data Transfer Object (DTO) can solve this problem remaining a simple data structure object without business logic, while at the same time providing powerful tools for validating, structuring and serializing data.

## Installation

You can install the package via composer:

```bash
composer require rmezhuev/data-transfer-object
```

## Usage

### Declaration

The package contains `RMezhuev\DTO\DataObject` class,  to create your own DTO just extend it from this class. 

```php
use RMezhuev\DTO\DataObject;

/**
 * @property string $name
 * @property string $email
 * @property string|int|null $age
 * @property array|null $phone
 * @property CustomType|null $details
 */
class PersonDto extends DataObject
{

}
```

To make the class `IDE friendly` and immutable, supported properties are declared using `phpdoc`. All properties and values are validated on object construction. For optional properties, type `null` must be added to the description of their types.

### Initialization

#### Constructor

Constructor of the object expects an associative array of properties with names matching the `phpdoc` names and with values of the supported types.
```php
$personDto = new PersonDto([
    'email' =>'john.doe@gmail.com',
    'name' => 'John Doe',
    'age' => 35,
]);
```

#### Factory methods

Most likely, you will need to initialize your DTO from different data structures depending on the context of use. To do this, you can use separate factory methods for each specific case.
```php
class PersonDto extends DataObject
{
    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->only([
                'email',
                'name',
            ])
        );
    }
    
    public static function fromApi(array $data): self
    {
        return new self([
              'email' => $data['primary_email'],
              'name' => $data['full_name'],
        ]);
        
    }
}
```

### Accessing data 

#### By magic getter or array notation

All properties are accessible through magic getter defined in base class. You can also use array brackets notation because the class also implements `Arrayable` interface as well.

```php
$personDto = new PersonDto([
    'name' => 'John Doe',
    'email' =>'john.doe@gmail.com',
]);

echo($personDto->name); //John Doe
echo($personDto->email); //john.doe@gmail.com

echo($personDto['name']); //John Doe
echo($personDto['email']); //john.doe@gmail.com
```

### Serialization

DTO supports `toArray()` and `toJson()` serialization out of the box  and implements `Illuminate\Contracts\Support\Arrayable` and `Illuminate\Contracts\Support\Jsonable` corresponding interfaces for better integration with `Laravel` framework.

#### Snake case

By default during serialization all names will be converted to the snake case so `fullName` will become `full_name`. To disable this behavior you can set `$snakeOnSerialize = false`

```php
class PersonDto extends DataObject
{
	protected $snakeOnSerialize = false;
}
```

#### Partial mode

On serialization DTO allows you to specify `partial` mode. In this case only implicitly initialized fields will be serialized, all the rest will be excluded. This mode is useful for partial updates, when you need to discern if `nullable` field was implicitly set to `null` value or wasn't set at all.
```php
$personDto = new PersonDto([
    'name' => 'John Doe',
    'email' =>'john.doe@gmail.com',
]);

$personDto->toArray();
//      Result:
//        [
//            "name" => "John Doe"
//            "email" => "john.doe@gmail.com"
//            "age" => null
//            "phone" => null
//            "details" => null
//        ]
    
$personDto->partial()->toArray();
//      Result:
//        [
//            "name" => "John Doe"
//            "email" => "john.doe@gmail.com"
//        ]
```

#### Custom serialization

Serialization methods can be easily overwritten in child class when built-in methods do not meet your needs or if you want to make it more explicit. 
```php
class PersonDto extends DataObject
{
    public static function toArray(): array
    {
        return [
            'full_name' => $this->name,
            'email' => $this->email,
            'year' => date("Y") - $this->age
        ];
    }    
}
```
## Exception handling

In addition to property type validation, on constructing data transfer object will check if all required properties are set. If not, then `RMezhuev\DTO\Exceptions\DataObjectException` will be thrown. Likewise, if you're trying to set unsupported  or change existing properties, you'll get the same exception.
## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License](https://opensource.org/licenses/MIT)  for more information.
