<?php

namespace Amsgames\LaravelShop\Traits;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

trait ShopUserTrait
{

    /**
     * One-to-Many relations with cart.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cart()
    {
        return $this->hasOne(Config::get('shop.cart'), 'user_id');
    }

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items()
    {
        return $this->hasMany(Config::get('shop.item'), 'user_id');
    }

    /**
     * One-to-Many relations with Order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->hasMany(Config::get('shop.order'), 'user_id');
    }

    /**
     * Returns the user ID value based on the primary key set for the table.
     *
     * @param mixed
     */
    public function getShopIdAttribute()
    {
        return is_array($this->primaryKey) ? 0 : $this->attributes[$this->primaryKey];
    }

}