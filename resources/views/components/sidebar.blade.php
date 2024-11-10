{{--まずカテゴリーの大分類（$major_category_names）をforeach文でループ処理して表示します。
さらに、大分類に属するカテゴリーをforeach文でループ処理します。そうすることで、大分類ごとに各カテゴリーを並べて表示することができます。--}}
<div class="container">
  @foreach($major_category_names as $major_category_name)
  <h2>{{$major_category_name}}</h2>
  @foreach($categories as $category)
  @if($category->major_category_name === $major_category_name)
  <label class="samuraimart-sidebar-category-label"><a href="{{route('products.index',['category'=>$category->id])}}">{{$category->name}}</a></label>
  @endif
  @endforeach
  @endforeach
</div>