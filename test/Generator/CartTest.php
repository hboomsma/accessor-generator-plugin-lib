<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\AccessorGenerator\Generator;

use Hostnet\Component\AccessorGenerator\Generator\fixtures\Cart;
use Hostnet\Component\AccessorGenerator\Generator\fixtures\Customer;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testSetCustomer()
    {
        $cart     = new Cart();
        $customer = new Customer();

        $cart->setCustomer($customer);
        self::assertSame($customer, $cart->getCustomer());
    }

    public function testGetCustomerEmpty()
    {
        $cart = new Cart();
        self::assertNull($cart->getCustomer());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testGetCustomerTooManyArguments()
    {
        $cart = new Cart();
        $cart->getCustomer(1);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testSetCustomerTooManyArguments()
    {
        $cart     = new Cart();
        $customer = new Customer();
        $cart->setCustomer($customer, 2);
    }
}
