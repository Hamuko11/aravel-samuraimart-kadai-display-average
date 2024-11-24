<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    //注文内容の確認ページ（CartControllのindexと同じ)
    public function index()
    {
        $cart = Cart::instance(Auth::user()->id)->content();

        $total = 0;
        $has_carriage_cost = false;
        $carriage_cost = 0;

        foreach ($cart as $c) {
            $total += $c->qty * $c->price;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        if ($has_carriage_cost) {
            $total += env('CARRIAGE');
            $carriage_cost = env('CARRIAGE');
        }

        return view('checkout.index', compact('cart', 'total', 'carriage_cost'));
    }
    //StripeAPIに支払い情報を送信し決済ページにリダイレクトさせる
    public function store(Request $request)
    {
        $cart = Cart::instance(Auth::user()->id)->content();

        $has_carriage_cost = false;

        foreach ($cart as $product) {
            if ($product->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $line_items = [];

        foreach ($cart as $product) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => $product->qty,
            ];
        }

        if ($has_carriage_cost) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => '送料',
                    ],
                    'unit_amount' => env('CARRIAGE'),
                ],
                'quantity' => 1,
            ];
        }
        //stripe-phpライブラリが提供するSessionクラスのcreate()メソッドでStripeに送信する支払い情報をセッションとして作成する
        $checkout_session = Session::create([
            //支払い対象の商品（商品と送料）
            'line_items' => $line_items,
            //支払いモード（一回払い）
            'mode' => 'payment',
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.index'),
        ]);
        //作成したセッションのURLを以下のように指定するとstripe側でセッションが所持する情報を取得し、適切なページを作成表示してくれる
        return redirect($checkout_session->url);
    }

    public function success()
    {
        //購入時にshoppingartテーブルに新規レコードを追加する際に必要なidの値を作成するためレコードを取得している
        //ショッピングカートの中身のデータを変数$numberに保存
        $user_shoppingcarts = DB::table('shoppingcart')->get();
        $number = DB::table('shoppingcart')->where('instance', Auth::user()->id)->count();

        //カートの数を取得
        $count = $user_shoppingcarts->count();

        //新しくDBに登録するカートのデータ用にカートIDと取得したナンバーIDを一つ増やす
        $count += 1;
        $number += 1;

        $cart = Cart::instance(Auth::user()->id)->content();

        $price_total = 0;
        $qty_total = 0;
        $has_carriage_cost = false;

        foreach ($cart as $c) {
            $price_total += $c->qty * $c->price;
            $qty_total += $c->qty;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        if ($has_carriage_cost) {
            $price_total += env('CARRIAGE');
        }

        //ユーザーIDを使ってカートの商品情報をデータベースに保存
        Cart::instance(Auth::user()->id)->store($count);

        //カートの中身を数0から取得したカートの個数に更新して、購入済みにフラグも変更する
        //DB::table('shoppingcart')でDB内のshoppingcartテーブルにアクセス
        //その後whereを使用してユーザーIDと上記のinstanceに保存した$countを使ってカートデータを更新

        DB::table('shoppingcart')->where('instance', Auth::user()->id)
            ->where('number', null)
            ->update(
                [
                    //ランダムなコードを作る
                    'code' => substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10),
                    'number' => $number,
                    'price_total' => $price_total,
                    'qty' => $qty_total,
                    'buy_flag' => true,
                    'updated_at' => date("Y/m/d H:i:s")
                ]
            );

        //カートを空にする
        Cart::instance(Auth::user()->id)->destroy();

        return view('checkout.success');
    }
}
