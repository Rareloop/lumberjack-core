<?php

namespace Rareloop\Lumberjack\Test\Unit\Concerns;

use PHPUnit\Framework\Assert;

trait ArraySubsetAsserts
{
    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|\ArrayAccess $subset
     * @param array|\ArrayAccess $array
     * @param bool               $checkForObjectIdentity
     * @param string             $message
     */
    public function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (!(is_array($subset) || $subset instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('Subset must be an array or ArrayAccess');
        }

        if (!(is_array($array) || $array instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException('Array must be an array or ArrayAccess');
        }

        foreach ($subset as $key => $value) {
            Assert::assertArrayHasKey($key, $array, $message);

            if (
                (is_array($value) || $value instanceof \ArrayAccess) &&
                (is_array($array[$key]) || $array[$key] instanceof \ArrayAccess)
            ) {
                $this->assertArraySubset($value, $array[$key], $checkForObjectIdentity, $message);
            } else {
                if ($checkForObjectIdentity) {
                    Assert::assertSame($value, $array[$key], $message);
                } else {
                    Assert::assertEquals($value, $array[$key], $message);
                }
            }
        }
    }
}
