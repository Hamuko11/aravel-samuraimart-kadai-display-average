@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-2">
    @component('components.sidebar', ['categories' => $categories, 'major_categories' => $major_categories])
    @endcomponent
  </div>

  <div class="col-9">
    <h1>おすすめ商品</h1>
    <div class="row">

      @foreach($recommend_products as $recommend_product)
      <div class="col-4">
        <a href="{{route('products.show',$recommend_product)}}">
          @if($recommend_product->image!=="")
          <img src="{{asset($recommend_product->image)}}" class="img-thumbnail">
          @else
          <img src="{{asset('img\dummy.png')}}" class="img-thumbnail">
          @endif
        </a>
        <div class="row">
          <div class="col-12">
            <p class="samuraimart-product-label mt-2">

              {{ $recommend_product->name }}<br>


              {{-- 平均スコアの星表示 --}}

              @if ($recommend_product->averageScore)
              @for ($i = 1; $i <= 5; $i++)
                @if ($i <=floor($recommend_product->averageScore))
                <span class="starfull">★</span>
                @elseif ($i - $recommend_product->averageScore < 1)
                  <span class="starhalf">★</span>
                  @else
                  <span class="starempty">★</span>
                  @endif
                  @endfor
                  <span class="small">{{ $recommend_product->averageScore }}</span>
                  @else
                  <span class="reviewempty">★ ★ ★ ★ ★</span>
                  @endif

                  <br>




                  <label>￥{{$recommend_product->price}}</label>
            </p>
          </div>
        </div>
      </div>


      @endforeach

    </div>

    <div class="d-flex justify-content-between">
      <h1>新着商品</h1>
      {{--商品ページでIDソートしたURLの末尾がsort=id&direction=ascなことから
      同様のパラメータを渡せばいいと発見する　複数パラメータの渡し方はroute('ルート名','パラメータ名=>値')--}}
      <a href="{{route('products.index',['sort'=>'id','direction'=>'desc'])}}">もっとみる</a>
    </div>
    <div class="row">
      @foreach($recently_products as $recently_product)
      <div class="col-3">
        <a href="{{route('products.show',$recently_product)}}">
          @if($recently_product->image!=="")
          <img src="{{ asset($recently_product->image) }}" class="img-thumbnail">
          @else
          <img src="{{asset('img/dummy.png')}}" class="img-thumbnail">
          @endif
        </a>
        <div class="row">
          <div class="col-12">
            <p class="samuraimart-product-label mt-2">
              {{$recently_product->name}}<br>

              {{-- 平均スコアの星表示 --}}

              @if ($recently_product->averageScore)
              @for ($i = 1; $i <= 5; $i++)
                @if ($i <=floor($recently_product->averageScore))
                <span class="starfull">★</span>
                @elseif ($i - $$recently_product->averageScore < 1)
                  <span class="starhalf">★</span>
                  @else
                  <span class="starempty">★</span>
                  @endif
                  @endfor
                  <span class="small">{{ $recently_product->averageScore }}</span>
                  @else
                  <span class="reviewempty">★ ★ ★ ★ ★</span>
                  @endif
                  <br>

                  <label>￥{{$recently_product->price}}</label>
            </p>
          </div>
        </div>
      </div>
      @endforeach
    </div>

  </div>
</div>
@endsection