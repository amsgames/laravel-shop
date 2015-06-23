<?php

use App;
use Log;
use Shop;
use Amsgames\LaravelShop;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShopTest extends TestCase
{

	/**
	 * Tests shop class static methods.
	 */
	public function testStaticMethods()
	{
	    $this->assertEquals(Shop::format(1.99), '$1.99');
	}

	/**
	 * Tests shop class constants
	 */
	public function testConstants()
	{
	    $this->assertTrue(Amsgames\LaravelShop\LaravelShop::QUANTITY_RESET);
	}

}