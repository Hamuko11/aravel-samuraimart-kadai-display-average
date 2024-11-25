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
        $recently_products = Product::orderBy('created_at', 'desc')->take(4)->with('reviews')->get();
        $recommend_products = Product::where('recommend_flag', true)->take(3)->with('reviews')->get();

        // 平均スコアを各商品に設定
        foreach ($recently_products as $recently_product) {
            $recently_product->averageScore = $recently_product->reviews->avg('score')
                ? round($recently_product->reviews->avg('score') * 2) / 2
                : null;
        }

        foreach ($recommend_products as $recommend_product) {
            $recommend_product->averageScore = $recommend_product->reviews->avg('score')
                ? round($recommend_product->reviews->avg('score') * 2) / 2
                : null;
        }

        return view('web.index', compact(
            'major_categories',
            'categories',
            'recently_products',
            'recommend_products'
        ));
    }
}
