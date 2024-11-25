@extends('layouts.app')

@section('content')
<div class="row">

  <div class="col-2">
    {{--呼び出すcomponentに連想配列を作成し、コンポネントに変数を渡す--}}
    @component('components.sidebar',['categories'=>$categories,'major_categories'=>$major_categories])
    @endcomponent
  </div>

  <div class="col-9">

    <div class="container">
      @if($category !== null)
      <a href="{{route('products.index')}}">トップ</a> > <a href="#">{{$major_category->name}}</a> > {{$category->name}}
      <h1>{{$category->name}}の商品一覧{{$total_count}}件</h1>
      {{--↑カテゴリ検索↓検索リストでの検索--}}
      @elseif($keyword !== null)
      <a href="{{route('products.index')}}">トップ</a> >商品一覧
      <h1>"{{$keyword}}"の検索結果{{$total_count}}件</h1>
      @endif
    </div>
    <div>
      {{--@sortablelinkはソートリンクを追加する第一引数はソートするカラム名、第二引数はビューに表示する文字列を指定する。--}}
      Sort By
      @sortablelink('id','ID')
      @sortablelink('price','Price')
      @sortablelink('created_at','Created_At')
    </div>

    <div class="container mt-4">
      <div class="row w-100">
        @foreach($products as $product)
        <div class="col-3">
          <a href="{{route('products.show', $product)}}">
            {{--商品画像をそれぞれ表示する--}}
            @if($product->image !== "")
            <img src="{{asset($product->image)}}" class="img-thumbnail">
            @else
            <img src="{{ asset('img/dummy.png')}}" class="img-thumbnail">
            @endif
          </a>
          <div class="row">
            <div class="col-12">
              <p class="samuraimart-product-label mt-2">
                {{$product->name}}<br>

                {{-- 平均評価の星を色分けで表示 --}}
                @if ($product->averageScore)

                @for ($i = 1; $i <= 5; $i++)
                  @if ($i <=floor($product->averageScore))
                  {{-- 緑色の星 (満たされた部分) --}}
                  <span class="starfull">★</span>
                  @elseif ($i - $product->averageScore < 1 && $i> $product->averageScore)
                    {{-- 半分の星 (評価が小数の場合) --}}
                    <span class="starhalf">★</span>
                    @else
                    {{-- 灰色の星 (満たされない部分) --}}
                    <span class="starempty">★</span>
                    @endif
                    @endfor
                    {{ $product->averageScore }}

                    @else
                    <span class="reviewempty">★ ★ ★ ★ ★</span>
                    @endif
                    <br>


                    <label>￥{{$product->price}}</label>
              </p>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    {{--ページネーションの設定(カテゴリーで絞り込んだ条件を保持してページングする)--}}
    {{$products->appends(request()->query())->links()}}
  </div>
</div>
@endsection

<!--コントローラから受け取った変数$productsを$productに１つづつ渡している-->
<!--@foreach($products as $product)
  <tr>
    <td>{{$product->name}}</td>
    <td>{{$product->description}}</td>
    <td>{{$product->price}}</td>
    <td>{{$product->category_id}}</td>
    <td>
      <form action="{{route('products.destroy',$product->id)}}" method="POST">
        <a href="{{route('products.show',$product->id)}}">Show</a>
        <a href="{{route('products.edit',$product->id)}}">Edit</a>
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
      </form>
  </tr>
  @endforeach
</table>
-->