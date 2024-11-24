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
}
