@props([
  'title',
  'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-end justify-between gap-4']) }}>
  <div>
    <h2 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900">
      {{ $title }}
    </h2>

    @if($subtitle)
      <p class="mt-1 text-sm text-gray-600">
        {{ $subtitle }}
      </p>
    @endif
  </div>

  @if(trim($slot))
    <div>
      {{ $slot }}
    </div>
  @endif
</div>