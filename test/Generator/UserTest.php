<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\AccessorGenerator\Generator;

use Hostnet\Component\AccessorGenerator\Generator\fixtures\Address;
use Hostnet\Component\AccessorGenerator\Generator\fixtures\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetAddressEmpty()
    {
        $user = new User();
        self::assertNull($user->getAddress());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testGetAddressTooManyArguments()
    {
        $user = new User();
        $user->getAddress(1);
    }

    public function testSetAddress()
    {
        $user    = new User();
        $address = new Address();

        $user->setAddress($address);
        self::assertSame($address, $user->getAddress());

        $user->setAddress(null);
        self::assertNull($user->getAddress());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetAddressTooManyArguments()
    {
        $user    = new User();
        $address = new Address();
        $user->setAddress($address, 1);
    }
}
