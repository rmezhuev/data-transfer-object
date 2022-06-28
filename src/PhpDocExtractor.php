<?php

namespace RMezhuev\DTO;

use InvalidArgumentException;
use LogicException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Util\PhpDocTypeHelper;

class PhpDocExtractor implements PropertyTypeExtractorInterface, PropertyListExtractorInterface
{
    private $docBlocks = [];
    private $docBlockFactory;
    private $phpDocTypeHelper;

    public function __construct(DocBlockFactoryInterface $docBlockFactory = null)
    {
        if (!class_exists(DocBlockFactory::class)) {
            throw new LogicException(sprintf('Unable to use the "%s" class as the "phpdocumentor/reflection-docblock" package is not installed.', __CLASS__));
        }

        $this->docBlockFactory = $docBlockFactory ?: DocBlockFactory::createInstance();
        $this->phpDocTypeHelper = new PhpDocTypeHelper();
    }

    public function getTypes(string $class, $property, array $context = []): ?array
    {
        /** @var $docBlock DocBlock */
        [$docBlock] = $this->getDocBlock($class);

        if (!$docBlock || !in_array($property, $this->getProperties($class), true)) {
            return null;
        }

        $properties = $this->getMagicProperties($docBlock);

        foreach ($properties as $prop) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Property $prop */
            if ($prop->getVariableName() === $property) {
                if ($prop->getType() !== null) {
                    return $this->phpDocTypeHelper->getTypes($prop->getType());
                }

                return null;
            }
        }

        return null;
    }

    public function getProperties(string $class, array $context = []): ?array
    {
        /** @var $docBlock DocBlock */
        [$docBlock, $phpDoc] = $this->getDocBlock($class);

        if (!$docBlock) {
            return null;
        }

        $propertyTags = $this->getMagicProperties($docBlock);

        $properties = [];
        foreach ($propertyTags as $property) {
            $properties[] = $property->getVariableName();
        }

        usort($properties, static function ($a, $b) use ($phpDoc) {
            return strpos($phpDoc, $a) > strpos($phpDoc, $b);
        });

        return $properties;
    }

    private function getMagicProperties(DocBlock $docBlock): array
    {
        return array_merge(
            $docBlock->getTagsByName('property'),
            $docBlock->getTagsByName('property-read'),
            $docBlock->getTagsByName('property-write')
        );
    }


    private function getDocBlock(string $class): array
    {
        $propertyHash = sprintf('%s', $class);

        if (isset($this->docBlocks[$propertyHash])) {
            return $this->docBlocks[$propertyHash];
        }

        $data = [null, null];

        try {
            $reflectionClass = new ReflectionClass($class);

            if ($docBlock = $this->getDocBlockFromClass($reflectionClass)) {
                $data = [
                    $docBlock,
                    $reflectionClass->getDocComment() ?: null,
                ];
            }
        } catch (ReflectionException $exception) {
        }

        return $this->docBlocks[$propertyHash] = $data;
    }


    private function getDocBlockFromClass(ReflectionClass $reflection): ?DocBlock
    {
        try {
            return $this->docBlockFactory->create($reflection);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}
