@php
  /** @var array $list */
  $list = $list ?? [];
@endphp

@if (count($list) > 0)
  <span class="text-base md:text-2xl text-slate-700 font-semibold">
    {{ implode(' | ', $list) }}
  </span>
@endif