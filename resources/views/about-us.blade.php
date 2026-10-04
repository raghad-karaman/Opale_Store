@extends('layouts.app')

@section('title', 'Hakkımızda - Opale Store')

@section('content')
<div class="container my-5">
    <h1 class="mb-4 text-center">Opale Store Hakkında</h1>
    <div class="row">
        <div class="col-md-6">
            <img src="{{ asset('images/logo/3.png') }}" alt="Opale Store Hakkında" class="img-fluid rounded">
        </div>
        <div class="col-md-6">
            <p>
                <strong>Opale Store</strong>'a hoş geldiniz! Moda, yaşam stili ve güzellik alanında özenle seçilmiş ürünlerimizle
                sizlere ilham vermek ve en iyi alışveriş deneyimini sunmak için buradayız.
            </p>
            <p>
                Kuruluşumuzun temelinde kalite, şıklık ve müşteri memnuniyeti yer almaktadır. Geniş ürün yelpazemiz ile
                hem klasik hem de trend parçaları bir araya getirerek her zevke hitap etmeyi amaçlıyoruz.
            </p>
            <p>
                Opale Store ekibi olarak, siz değerli müşterilerimize güvenilir ve keyifli bir alışveriş deneyimi sunmak için
                titizlikle çalışıyoruz.
            </p>
            <p>
                Bizi tercih ettiğiniz için teşekkür ederiz.
            </p>
            <p>
                <strong>Opale Store – Işıltını Keşfet!</strong>
            </p>
        </div>
    </div>
</div>
@endsection