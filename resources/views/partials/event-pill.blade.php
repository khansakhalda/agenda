@php
  use Carbon\Carbon;
@endphp

@props([
  // Model Event (wajib)
  'event',

  // Tampilkan jam di depan judul? (mis. Month view)
  'showTime' => false,

  // Tampilkan badge jumlah partisipan?
  'showParticipants' => true,

  // Warna tone (untuk masa depan). Saat ini default biru.
  'tone' => 'blue',

  // Kelas tambahan opsional
  'class' => '',
])

@php
  // Kelas dasar untuk "pill"
  $toneMap = [
    'blue'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'border' => 'border-blue-200', 'badgeText' => 'text-blue-700', 'badgeBorder' => 'border-blue-200'],
    'emerald'=> ['bg' => 'bg-emerald-100','text' => 'text-emerald-800','border'=> 'border-emerald-200','badgeText'=>'text-emerald-700','badgeBorder'=>'border-emerald-200'],
    'amber'  => ['bg' => 'bg-amber-100',  'text' => 'text-amber-900',  'border'=> 'border-amber-200', 'badgeText'=>'text-amber-800',  'badgeBorder'=>'border-amber-200'],
  ];

  $t = $toneMap[$tone] ?? $toneMap['blue'];
  $pc = $event->participants ? $event->participants->count() : 0;

  // Jam "HH:mm" bila dibutuhkan
  $timePrefix = '';
  if ($showTime && !$event->all_day) {
    // event->start_time bisa "06:00" / "06:00:00" / null
    $st = $event->start_time ? substr($event->start_time, 0, 5) : null;
    $timePrefix = $st ? (Carbon::createFromFormat('H:i', $st)->format('H:i').' ') : '';
  }
@endphp

<div
  {{ $attributes->merge([
      'class' => "px-1.5 py-0.5 rounded text-xs font-medium border {$t['bg']} {$t['text']} {$t['border']} cursor-pointer truncate hover:shadow-md transition-shadow z-10 relative flex items-center gap-1 ".$class
  ]) }}
  wire:click.stop="openEditModal({{ $event->id }})"
  title="{{ $timePrefix }}{{ $event->title }}"
>
  <span class="truncate">
    {{ $timePrefix }}{{ $event->title }}
  </span>

  @if($showParticipants && $pc > 0)
    <span class="ml-auto text-[10px] px-1 rounded bg-white/70 {{ $t['badgeBorder'] }} {{ $t['badgeText'] }} shrink-0">
      {{ $pc }} org
    </span>
  @endif
</div>
