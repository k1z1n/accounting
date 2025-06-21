@props([
  'code',           // код валюты, например "BTC"
  'size' => 'w-4',  // ширина/высота иконки
])

@if($code)
    @php
        $path = public_path("images/coins/{$code}.svg");
        $url  = asset("images/coins/{$code}.svg");
    @endphp

    @if(file_exists($path))
        <img
            src="{{ $url }}"
            alt="{{ $code }}"
            {{ $attributes->merge(['class' => "{$size} h-4"]) }}
        >
    @else
        <span {{ $attributes->merge(['class' => 'text-white text-xs']) }}>
      {{ $code }}
    </span>
    @endif
@endif
