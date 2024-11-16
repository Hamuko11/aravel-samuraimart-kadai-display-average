<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\MajorCategory;
use App\Models\Product;

class WebController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        //カテゴリーカラムを配列で取得する（pluck）
        //$major_category_names = Category::pluck('major_category_name')->unique();
        $major_categories = MajorCategory::all();
        //新着商品を４つ取得する
        $recently_products = Product::orderBy('created_at', 'desc')->take(4)->get();
        $recommend_products = Product::where('recommend_flag', true)->take(3)->get();
        return view('web.index', compact('major_categories', 'categories', 'recently_products', 'recommend_products'));
    }
}
