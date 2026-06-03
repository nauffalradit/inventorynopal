@extends('layouts.app')

@section('content')
    <h1>Edit Barang</h1>
    <div class="card">
        <form method="post" action="{{ route('products.update', $product) }}">
            @method('PUT')
            @include('products._form')
        </form>
    </div>
@endsection
