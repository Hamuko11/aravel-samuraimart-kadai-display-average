<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShoppingCart;
use Illuminate\Pagination\LengthAwarePaginator;
use Gloudemans\Shoppingcart\Facades\Cart;
//↑ショッピングカートのパッケージ
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //Auth::userで自身の情報を変数に代入してビューに渡す。
    public function mypage()
    {
        $user = Auth::user();
        return view('users.mypage', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $user = Auth::user();

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $user = Auth::user();

        //<条件式>?<条件が真の場合>:<条件が偽の場合>の三項演算を使用し、更新するかしないかで分岐させている
        $user->name = $request->input('name') ? $request->input('name') : $user->name;
        $user->email = $request->input('email') ? $request->input('email') : $user->email;
        $user->postal_code = $request->input('postal_code') ? $request->input('postal_code') : $user->postal_code;
        $user->address = $request->input('address') ? $request->input('address') : $user->address;
        $user->phone = $request->input('phone') ? $request->input('phone') : $user->phone;
        $user->update();

        return to_route('mypage');
    }

    //パスワードの同一確認とアップデート
    public function update_password(Request $request)
    {
        $validatedData = $request->validate([
            'password' => 'required|confirmed',
        ]);

        $user = Auth::user();

        if ($request->input('password') == $request->input('password_confirmation')) {
            $user->password = bcrypt($request->input('password'));
            $user->update();
        } else {
            return to_route('mypage.edit_password');
        }
        return to_route('mypage');
    }
    //パスワードの変更画面を表示するアクション
    public function edit_password()
    {
        return view('users.edit_password');
    }

    public function favorite()
    {
        //認証ユーザがお気に入りした商品一覧を取得し変数に代入しビューに渡す
        $user = Auth::user();
        $favorite_products = $user->favorite_products;
        return view('users.favorite', compact('favorite_products'));
    }

    //退会用の画面の設定 deleteメソッドで論理削除ができる
    public function destroy(Request $request)
    {
        Auth::user()->delete();
        return redirect('/');
    }
    //注文履歴の一覧を表示する(先ほど作成したShoppingCart::getCurrentUserOrders()を呼び出す)
    public function cart_history_index(Request $request)
    {
        $page = $request->page !== null ? $request->page : 1;
        $user_id = Auth::user()->id;
        $billings = ShoppingCart::getCurrentUserOrders($user_id);
        $total = count($billings);
        $billings = new LengthAwarePaginator(array_slice($billings, ($page - 1) * 15, 15), $total, 15, $page, array('path' => $request->url()));

        return view('users.cart_history_index', compact('billings', 'total'));
    }

    //注文番号を指定して注文データを取得する（LaravelShoppingcartのライブラリを使用）
    public function cart_history_show(Request $request)
    {
        //アイテム番号を$numに入れる
        $num = $request->num;
        $user_id = Auth::user()->id;
        //shoppingcart テーブルで、指定したユーザー（instance）とアイテム番号（number）に一致するレコードを検索して最初の一致レコードを変数に格納。
        $cart_info = DB::table('shoppingcart')->where('instance', $user_id)->where('number', $num)->get()->first();
        //以下３行で過去に購入したカート情報をライブラリ経由で取り出すため、以下のrestoreメソッド呼び$cart_contentsに格納
        //ただしrestoreで呼ぶとShoppingcartテーブルからデータが消えるためstoreで再度保存する
        Cart::instance($user_id)->restore($cart_info->identifier);
        $cart_contents = Cart::content();
        Cart::instance($user_id)->store($cart_info->identifier);
        Cart::destroy();
        //storeで戻したときにcodeカラムやnumberカラムなどの一部データが復元できない制約のため、updateで書き戻しを行う
        DB::table('shoppingcart')->where('instance', $user_id)
            ->where('number', null)
            ->update(
                [
                    'code' => $cart_info->code,
                    'number' => $num,
                    'price_total' => $cart_info->price_total,
                    'qty' => $cart_info->qty,
                    'buy_flag' => $cart_info->buy_flag,
                    'updated_at' => $cart_info->updated_at
                ]
            );
        return view('users.cart_history_show', compact('cart_contents', 'cart_info'));
    }
}
