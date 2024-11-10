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

        $total = 0;

        foreach ($cart as $c) {
            $total += $c->qty * $c->price;
        }

        return view('carts.index', compact('cart', 'total'));
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
        //ショッピングカートの中身のデータを変数$user_shoppingcartに保存
        //カートの数を取得
        $user_shoppingcarts = DB::table('shoppingcart')->where('instance', Auth::user()->id)->get();
        $count = $user_shoppingcarts->count();
        //新しくDBに登録するカートのデータ用にカートIDを一つ増やす
        $count += 1;
        //ユーザーIDを使ってカートの商品情報をデータベースに保存
        Cart::instance(Auth::user()->id)->store($count);
        //カートの中身を数0から取得したカートの個数に更新して、購入済みにフラグも変更する
        //DB::table('shoppingcart')でDB内のshoppingcartテーブルにアクセス
        //その後whereを使用してユーザーIDと上記のinstanceに保存した$countを使ってカートデータを更新
        DB::table('shoppingcart')->where('instance', Auth::user()->id)->where('number', null)->update(['number' => $count, 'buy_flag' => true]);
        //カートを空にする
        Cart::instance(Auth::user()->id)->destroy();

        return to_route('carts.index');
    }
}
