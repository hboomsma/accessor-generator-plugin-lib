<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\AccessorGenerator\Generator;

use Hostnet\Component\AccessorGenerator\Generator\fixtures\Cart;
use Hostnet\Component\AccessorGenerator\Generator\fixtures\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testSetCart()
    {
        $cart     = new Cart();
        $customer = new Customer();

        $customer->setCart($cart);
        self::assertSame($cart, $customer->getCart());
    }

    /**
     * @expectedException \Doctrine\ORM\EntityNotFoundException
     */
    public function testGetCartEmpty()
    {
        $customer = new Customer();
        $customer->getCart();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testGetCartTooManyArguments()
    {
        $customer = new Customer();
        $customer->getCart(1);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testSetCartTooManyArguments()
    {
        $cart     = new Cart();
        $customer = new Customer();
        $customer->setCart($cart, 2);
    }
}
