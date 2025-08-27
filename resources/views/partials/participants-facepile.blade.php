@php
  /** @var array $list */
  $list = $list ?? [];
  $max  = $max  ?? 8;
  $label = $label ?? 'Partisipan';

  $visible = array_slice($list, 0, $max);
  $extra   = max(count($list) - count($visible), 0);

  $palette = [
    'bg-gradient-to-br from-sky-400 to-blue-600 text-white',
    'bg-gradient-to-br from-emerald-400 to-teal-600 text-white',
    'bg-gradient-to-br from-amber-400 to-orange-600 text-white',
    'bg-gradient-to-br from-fuchsia-400 to-purple-600 text-white',
    'bg-gradient-to-br from-rose-400 to-red-600 text-white',
    'bg-gradient-to-br from-cyan-400 to-indigo-600 text-white',
  ];

  $initials = function ($name) {
    $parts = collect(\Illuminate\Support\Str::of($name)->squish()->explode(' '));
    return $parts->take(2)->map(fn($p) => mb_substr($p, 0, 1))->join('');
  };
@endphp

<div class="flex items-center gap-2 select-none">
  <div class="flex -space-x-2">
    @foreach ($visible as $i => $name)
      <div class="h-6 w-6 md:h-7 md:w-7 rounded-full ring-2 ring-white grid place-items-center text-[10px] md:text-[11px] font-bold shadow
                  {{ $palette[$i % count($palette)] }}" title="{{ $name }}">
        {{ $initials($name) }}
      </div>
    @endforeach

    @if ($extra > 0)
      <div class="h-6 w-6 md:h-7 md:w-7 rounded-full ring-2 ring-white bg-slate-300 text-slate-800 grid place-items-center text-[10px] md:text-[11px] font-bold shadow"
           title="{{ $extra }} lainnya">
        +{{ $extra }}
      </div>
    @endif
  </div>

  <span class="text-[11px] md:text-xs text-slate-700 font-medium">
    {{ count($list) }} {{ strtolower($label) }}
  </span>
</div>
