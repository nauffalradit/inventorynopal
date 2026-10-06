@extends('layouts.app')

@section('content')
    <h1>Tambah Barang</h1>
    <x-card>
        <form method="post" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @include('products._form')
        </form>
    </x-card>
@endsection
