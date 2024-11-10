<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class WebController extends Controller
{
    public function index()
    {
        $categories = Category::all()->sortBy('major_category_name');
        //カテゴリーカラムを配列で取得する（pluck）
        $major_category_names = Category::pluck('major_category_name')->unique();
        return view('web.index', compact('major_category_names', 'categories'));
    }
}
