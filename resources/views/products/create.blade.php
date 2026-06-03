@extends('layouts.app')

@section('content')
    <h1>Tambah Barang</h1>
    <div class="card">
        <form method="post" action="{{ route('products.store') }}">
            @include('products._form')
        </form>
    </div>
@endsection
