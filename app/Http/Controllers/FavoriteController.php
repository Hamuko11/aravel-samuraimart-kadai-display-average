<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    //storeはお気に入り登録する処理＝＞attach（紐づける対象）のidを中間テーブルに渡す
    public function store($product_id)
    {
        Auth::user()->favorite_products()->attach($product_id);
        return back();
    }

    public function destroy($product_id)
    {
        Auth::user()->favorite_products()->detach($product_id);
        return back();
    }
}
