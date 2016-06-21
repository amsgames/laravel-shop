<?php

namespace Amsgames\LaravelShop\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Event fired when an order has been completed.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */
class OrderCompleted
{
	use SerializesModels;

	/**
     * Order ID.
     * @var int
     */
	public $id;

	/**
     * Create a new event instance.
     *
     * @param int $id Order ID.
     *
     * @return void
     */
	public function __construct($id)
	{
		$this->id = $id;
	}
}