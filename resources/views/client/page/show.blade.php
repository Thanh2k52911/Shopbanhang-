@extends('client.layouts.master')

@section(
    'title',
    ($page->meta_title ?: $page->title) . ' - Cosmetic Shop'
)

@section(
    'meta_description',
    $page->meta_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($page->content ?? ''),
            160
        )
)

@section('content')
    <section class="client-page">
        <div class="client-container">
            <nav
                class="client-page__breadcrumb"
                aria-label="Breadcrumb"
            >
                <a href="{{ route('home') }}">
                    Trang chủ
                </a>

                <span aria-hidden="true">/</span>

                <span>{{ $page->title }}</span>
            </nav>

            <article class="client-page__article">
                <header class="client-page__header">
                    <p class="client-page__eyebrow">
                        Cosmetic Shop
                    </p>

                    <h1>{{ $page->title }}</h1>
                </header>

                @if ($page->thumbnail)
                    <div class="client-page__thumbnail">
                        <img
                            src="{{ asset(
                                'storage/' . $page->thumbnail
                            ) }}"
                            alt="{{ $page->title }}"
                        >
                    </div>
                @endif

                <div class="client-page__content">
                    {!! $page->content !!}
                </div>
            </article>
        </div>
    </section>
@endsection
