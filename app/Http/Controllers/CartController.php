<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //ユーザーIDを元にこれまで追加したカートの中身を変数$cartに代入して保存
        //また合計金額を計算して$totalに保存しています。
        $cart = Cart::instance(Auth::user()->id)->content();
        //購入した商品のうち１つでも送料ありがあるか判断し、ある場合のみ$has_carriage_costフラグをtrueにする
        $total = 0;
        $has_carriage_cost = false;
        $carriage_cost = 0;

        foreach ($cart as $c) {
            $total += $c->qty * $c->price;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        //上記ifにてtrueになった場合のみ$totalに送料を追加する
        //別途送料も表記するので$carriage_costを設定しておく
        if ($has_carriage_cost) {
            $total += env('CARRIAGE');
            $carriage_cost = env('CARRIAGE');
        }

        return view('carts.index', compact('cart', 'total', 'carriage_cost'));
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //ユーザーIDをもとにカートのデータを作成し、add関数を使って送信されたデータを元に商品を追加する
        Cart::instance(Auth::user()->id)->add(
            [
                'id' => $request->id,
                'name' => $request->name,
                'qty' => $request->qty,
                'price' => $request->price,
                'weight' => $request->weight,
                'options' => [
                    'image' => $request->image,
                    'carriage' => $request->carriage,
                ]
            ]
        );
        //商品をカートに追加したあとそのまま商品の個別ページにリダイレクトする
        return to_route('products.show', $request->get('id'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //購入済みかを確認するためのカラム(buy_flag)をshoppingcartテーブルに追加している
    public function destroy(Request $request)
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

        return to_route('carts.index');
    }
}
