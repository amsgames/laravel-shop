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

trait ShopItemTrait
{

    /**
     * Returns flag indicating if item has an object.
     *
     * @return bool
     */
    public function getHasObjectAttribute() 
    {
        return array_key_exists('class', $this->attributes) && !empty($this->attributes['class']);
    }

    /**
     * Returns flag indicating if the object is shoppable or not.
     *
     * @return bool
     */
    public function getIsShoppableAttribute()
    {
        return true;
    }

    /**
     * Returns attached object.
     *
     * @return mixed
     */
    public function getObjectAttribute()
    {
        return $this->hasObject ? call_user_func($this->attributes['class'] . '::find', $this->attributes['reference_id']) : null;
    }

    /**
     * Returns item name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if ($this->hasObject) return $this->object->displayName;
        return isset($this->itemName)
            ? $this->attributes[$this->itemName]
            : (array_key_exists('name', $this->attributes)
                ? $this->attributes['name']
                : ''
            );
    }

    /**
     * Returns item id.
     *
     * @return mixed
     */
    public function getShopIdAttribute()
    {
        return is_array($this->primaryKey) ? 0 : $this->attributes[$this->primaryKey];
    }

    /**
     * Returns item url.
     *
     * @return string
     */
    public function getShopUrlAttribute()
    {
        if ($this->hasObject) return $this->object->shopUrl;
        if (!property_exists($this, 'itemRouteName') && !property_exists($this, 'itemRouteParams')) return '#';
        $params = [];
        foreach (array_keys($this->attributes) as $attribute) {
            if (in_array($attribute, $this->itemRouteParams)) $params[$attribute] = $this->attributes[$attribute];
        }
        return \route($this->itemRouteName, $params);
    }

    /**
     * Returns price formatted for display.
     *
     * @return string
     */
    public function getDisplayPriceAttribute()
    {
        return preg_replace(
            [
                '/:symbol/',
                '/:price/',
                '/:currency/'
            ],
            [
                Config::get('shop.currency_symbol'),
                $this->attributes['price'],
                Config::get('shop.currency')
            ],
            Config::get('shop.display_price_format')
        );
    }


}