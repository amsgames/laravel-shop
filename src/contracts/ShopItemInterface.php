<?php

namespace Amsgames\LaravelShop\Contracts;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

interface ShopItemInterface
{

    /**
     * One-to-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user();

    /**
     * One-to-One relations with the cart model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cart();

    /**
     * Returns flag indicating if item has an object.
     *
     * @return bool
     */
    public function getHasObjectAttribute();

    /**
     * Returns attached object.
     *
     * @return mixed
     */
    public function getObjectAttribute();
    
    /**
     * Returns item name.
     *
     * @return string
     */
    public function getShopNameAttribute();

    /**
     * Returns shop it.
     *
     * @return mixed
     */
    public function getShopIdAttribute();

}