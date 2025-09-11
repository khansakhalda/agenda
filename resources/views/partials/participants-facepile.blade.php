@php
  /** @var array $list */
  $list  = $list ?? [];
  $class = $class ?? 'text-base md:text-2xl text-slate-700 font-semibold'; 
  // default: grid atas
@endphp

@if (count($list) > 0)
  <span class="{{ $class }}">
    {{ implode(' | ', $list) }}
  </span>
@endif
