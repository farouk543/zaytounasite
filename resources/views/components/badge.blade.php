@props(['variant' => 'default'])

@php
$map = [
  'default' => 'bg-gray-100 text-gray-700 ring-gray-200',
  'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  'info'    => 'bg-sky-50 text-sky-700 ring-sky-200',
  'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
  'dark'    => 'bg-gray-900 text-white ring-gray-900/10',
];
$cls = $map[$variant] ?? $map['default'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$cls]) }}>
  {{ $slot }}
</span>