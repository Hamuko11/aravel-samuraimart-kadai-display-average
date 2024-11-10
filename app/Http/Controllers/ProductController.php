<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
//商品登録時にカテゴリ選択できるようにする
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    //
    {
        $keyword = $request->keyword;
        //因数にRequest $requestを追加することで渡されたindexアクションを使用できるようになる
        //また受け取った値は$request=>categoryのように値の名前で使用できる
        //次に受け取った値で処理分岐
        //値を受け取っている場合（カテゴリ絞り込みの場合）
        if ($request->category !== null) {
            //受け取ったカテゴリidを持つ商品データの取得
            $products = Product::where('category_id', $request->category)->sortable()->paginate(15);
            //該当商品のカウント
            $total_count = Product::Where('category_id', $request->category)->count();
            //カテゴリ名の取得
            $category = Category::find($request->category);
        } elseif ($keyword !== null) {
            //受け取った名前を持つ商品データの取得
            //部分一致('name', 'like', "%{$keyword}%")、完全一致('name', '=', $keyword)
            $products = Product::where('name', 'like', "%{$keyword}%")->sortable()->paginate(15);
            //該当商品のカウント
            $total_count = $products->total();
            //カテゴリ名の取得
            $category = null;
        } else {
            $products = Product::sortable()->paginate(15);
            $total_count = "";
            $category = null;
        }
        //最初に変数を定義しておくまた15商品ごとにページを区切る
        //$products = Product::paginate(15);
        //↑意味はデータからすべての商品データを取得して変数に代入
        $categories = Category::all();
        $major_category_names = Category::pluck('major_category_name')->unique();
        //↑の意味は全カテゴリからmajor_category_nameカラムのみを取得し、uniqueで重複を削除している
        return view('products.index', compact('products', 'category', 'categories', 'major_category_names', 'total_count', 'keyword'));
        //↑第二引数にコントローラからビューに渡す変数を指定する
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //商品登録時にカテゴリを選べるようにする
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Productモデルの変数を$product=new Product()で作成する
        //そのあとフォームから送信されたデータが格納されている$request変数の中から各項目をそれぞれのカラムに保存して
        //$product->saveでデータベースに保存する
        $product = new Product();
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->category_id = $request->input('category_id');
        $product->save();
        //redirect=to_route
        return to_route('products.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //商品のデータが保存されているstoreで定義した商品情報が保存されている変数を渡す
        //追加でレビューも渡す
        $reviews = $product->reviews()->get();
        return view('products.show', compact('product', 'reviews'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //edit.bladeで受け取ったデータを更新する
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->category_id = $request->input('category_id');
        $product->update();
        //updatedaだとデータのupdate_atが更新されるsaveだと更新されない

        return to_route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return to_route('products.index');
    }
}
