@extends('layouts.app')

@section('content')
    <h1>Edit Barang</h1>
    <x-card>
        <form method="post" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('products._form')
        </form>
    </x-card>
@endsection
