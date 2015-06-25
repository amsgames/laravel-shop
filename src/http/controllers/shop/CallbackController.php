<?php

namespace Amsgames\LaravelShop\Http\Controllers\Shop;

use Validator;
use Shop;
use Illuminate\Http\Request;
use Amsgames\LaravelShop\Http\Controllers\Controller;

class CallbackController extends Controller
{
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function process(Request $request, $status, $id, $token)
    {
        $validator = Validator::make(
            [
                'id'        => $id,
                'status'    => $status,
                'token'     => $token,
            ],
            [
                'id'        => 'required|exists:' . config('shop.order_table') . ',id',
                'status'    => 'required|in:success,fail',
                'token'     => 'required|exists:' . config('shop.transaction_table') . ',token,order_id,' . $id,
            ]
        );

        if ($validator->fails()) {
            abort(404);
        }

        $order = call_user_func(config('shop.order') . '::find', $id);

        $transaction = $order->transactions()->where('token', $token)->first();

        Shop::callback($order, $transaction, $status, $request->all());

        $transaction->token = null;

        $transaction->save();

        return redirect()->route(config('shop.callback_redirect_route'), ['order' => $order->id]);
    }
}
