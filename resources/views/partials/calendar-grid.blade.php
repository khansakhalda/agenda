@php
  use Carbon\Carbon;
  use Illuminate\Support\Collection;
  Carbon::setLocale('id');

  /**
   * Palet warna biru variatif (keluarga biru).
   * Tiap item berisi kelas Tailwind untuk bg/border/text/ring/hover dan badge.
   */
  $PALETTE = [
    ['bg'=>'bg-blue-50','bg2'=>'bg-blue-100','text'=>'text-blue-900','border'=>'border-blue-300','ring'=>'ring-blue-200','hoverBg'=>'hover:bg-blue-200','hoverBorder'=>'hover:border-blue-400','badgeBg'=>'bg-blue-50','badgeText'=>'text-blue-700','badgeBorder'=>'border-blue-300'],
    ['bg'=>'bg-sky-50','bg2'=>'bg-sky-100','text'=>'text-sky-900','border'=>'border-sky-300','ring'=>'ring-sky-200','hoverBg'=>'hover:bg-sky-200','hoverBorder'=>'hover:border-sky-400','badgeBg'=>'bg-sky-50','badgeText'=>'text-sky-700','badgeBorder'=>'border-sky-300'],
    ['bg'=>'bg-indigo-50','bg2'=>'bg-indigo-100','text'=>'text-indigo-900','border'=>'border-indigo-300','ring'=>'ring-indigo-200','hoverBg'=>'hover:bg-indigo-200','hoverBorder'=>'hover:border-indigo-400','badgeBg'=>'bg-indigo-50','badgeText'=>'text-indigo-700','badgeBorder'=>'border-indigo-300'],
    ['bg'=>'bg-cyan-50','bg2'=>'bg-cyan-100','text'=>'text-cyan-900','border'=>'border-cyan-300','ring'=>'ring-cyan-200','hoverBg'=>'hover:bg-cyan-200','hoverBorder'=>'hover:border-cyan-400','badgeBg'=>'bg-cyan-50','badgeText'=>'text-cyan-700','badgeBorder'=>'border-cyan-300'],
  ];
  $pickColor = function($seed) use ($PALETTE) {
      $idx = is_numeric($seed) ? intval($seed) : crc32((string)$seed);
      return $PALETTE[$idx % count($PALETTE)];
  };
@endphp

<div class="bg-white rounded-lg shadow overflow-hidden" wire:poll.2s>
  @if ($calendarView === 'month')
    {{-- ====================== MONTH VIEW ====================== --}}
    <div class="grid grid-cols-7 bg-gray-50">
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Minggu</div>
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Senin</div>
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Selasa</div>
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Rabu</div>
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Kamis</div>
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Jumat</div>
      <div class="p-3 text-center font-medium text-gray-900 text-sm">Sabtu</div>
    </div>

    <div class="grid grid-cols-7">
      @php
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();
        $startOfGrid  = $startOfMonth->copy()->startOfWeek();
        $endOfGrid    = $endOfMonth->copy()->endOfWeek();
        $currentDay   = $startOfGrid->copy();
        $events       = $calendarData['events'];
      @endphp

      @while ($currentDay <= $endOfGrid)
        @php
          /** @var \Illuminate\Support\Collection $dayEvents */
          $dayEvents      = $events->get($currentDay->format('Y-m-d'), collect());
          $isCurrentMonth = $currentDay->month == $startOfMonth->month;
          $isToday        = $currentDay->isToday();
        @endphp

        <div
          class="min-h-28 border border-gray-200 p-1.5 relative group cursor-pointer hover:bg-blue-50 transition-colors {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : '' }} {{ $isToday ? 'bg-blue-50' : '' }}"
          data-date="{{ $currentDay->format('Y-m-d') }}"
          wire:click.stop="openCreateModal(null, '{{ $currentDay->format('Y-m-d') }}', 9)"
        >
          <div class="flex justify-between items-start mb-1">
            <span class="text-xs font-medium {{ !$isCurrentMonth ? 'text-gray-400' : 'text-gray-900' }} {{ $isToday ? 'text-blue-600 font-bold' : '' }}">
              {{ $currentDay->day }}
            </span>

            @if($dayEvents->isNotEmpty())
              <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                {{ $dayEvents->count() }} acara
              </span>
            @else
              <div class="opacity-0 group-hover:opacity-100 transition-opacity select-none pointer-events-none">
                <span class="text-blue-600 text-lg leading-none font-semibold">+</span>
              </div>
            @endif
          </div>

          <div class="space-y-0.5">
            @foreach ($dayEvents->take(3) as $event)
              @include('partials.event-pill', [
                'event'            => $event,
                'showTime'         => !$event->all_day,
                'showParticipants' => true,
                'class'            => ''
              ])
            @endforeach

            @if ($dayEvents->count() > 3)
              <div
                wire:click.stop="openMoreEventsModal('{{ $currentDay->format('Y-m-d') }}')"
                class="text-xs text-gray-500 px-1.5 pt-0.5 hover:text-blue-600 cursor-pointer z-10 relative">
                +{{ $dayEvents->count() - 3 }} lainnya
              </div>
            @endif
          </div>

          @if($dayEvents->isEmpty())
            <div class="absolute inset-0 hover:bg-blue-50 opacity-0 hover:opacity-30 transition-opacity pointer-events-none"></div>
          @endif
        </div>

        @php $currentDay->addDay(); @endphp
      @endwhile
    </div>

  @elseif ($calendarView === 'week')
    {{-- ====================== WEEK VIEW ====================== --}}
    @php
      $HOUR_HEIGHT    = 80;   // tinggi 1 jam (px)
      $DAY_MINUTES    = 24 * 60;
      $STACK_SHIFT    = 8;    // geser per tumpukan (px)
      $LEFT_BASE_PCT  = 2;    // % margin kiri dasar
      $WIDTH_BASE_PCT = 96;   // % lebar dasar
    @endphp

    {{-- Header hari --}}
    <div class="grid border-t border-l border-gray-200" style="grid-template-columns: 80px repeat(7, 1fr);">
      <div class="bg-gray-50 border-b border-r border-gray-200"></div>
      @php $d = Carbon::parse($this->currentDate)->startOfWeek(); @endphp
      @for ($i=0; $i<7; $i++)
        @php $isToday = $d->isToday(); @endphp
        <div class="border-b border-r border-gray-200 {{ $isToday ? 'bg-blue-50' : 'bg-gray-50' }}">
          <div class="h-24 p-2 text-center text-sm font-medium {{ $isToday ? 'text-blue-600' : 'text-gray-900' }} flex flex-col justify-end items-center">
            <span class="font-bold text-xl">{{ $d->translatedFormat('D') }}</span>
            <span class="text-3xl font-bold">{{ $d->day }}</span>
            <span class="text-xs text-gray-500">{{ $d->translatedFormat('M') }}</span>
          </div>
        </div>
        @php $d->addDay(); @endphp
      @endfor

      {{-- All-day row --}}
      <div class="bg-gray-50 border-b border-r border-gray-200 flex items-center justify-center text-xs text-gray-500">All Day</div>
      @php $d = Carbon::parse($this->currentDate)->startOfWeek(); @endphp
      @for ($i=0; $i<7; $i++)
        @php
          $dayEvents = $calendarData['events']->get($d->format('Y-m-d'), collect());
          $allDay    = $dayEvents->filter(fn($e) => (bool)$e->all_day);
        @endphp
        <div class="border-b border-r border-gray-200 p-1.5 relative">
          <div class="flex flex-wrap gap-1">
            @foreach ($allDay->take(4) as $event)
              @php
                $pc = $event->participants ? $event->participants->count() : 0;
                $c  = $pickColor($event->id ?? $event->title);
              @endphp
              <button
                type="button"
                wire:click.stop="openEditModal({{ $event->id }})"
                class="px-2 py-1 rounded text-xs font-medium
                       {{ $c['bg2'] }} {{ $c['text'] }} {{ $c['border'] }} {{ $c['ring'] }}
                       {{ $c['hoverBg'] }} {{ $c['hoverBorder'] }} hover:shadow transition-colors duration-150
                       flex items-center gap-1"
              >
                <span class="truncate max-w-[12rem]">{{ $event->title }}</span>
                @if($pc > 0)
                  <span class="text-[10px] leading-none px-1 py-0.5 rounded-full {{ $c['badgeBg'] }} {{ $c['badgeText'] }} {{ $c['badgeBorder'] }}">{{ $pc }}</span>
                @endif
              </button>
            @endforeach
            @if ($allDay->count() > 4)
              <button
                type="button"
                wire:click.stop="openMoreEventsModal('{{ $d->format('Y-m-d') }}')"
                class="text-xs text-gray-600 hover:text-blue-600"
              >+{{ $allDay->count()-4 }} lainnya</button>
            @endif
          </div>
        </div>
        @php $d->addDay(); @endphp
      @endfor

      {{-- Kolom waktu kiri --}}
      <div class="border-r border-gray-200 bg-gray-50" style="height: {{ $HOUR_HEIGHT*24 }}px;">
        @for ($h=0; $h<24; $h++)
          <div class="border-b border-gray-200 text-xs text-gray-500 flex items-start justify-end pr-2" style="height: {{ $HOUR_HEIGHT }}px;">
            {{ sprintf('%02d:00', $h) }}
          </div>
        @endfor
      </div>

      {{-- 7 kolom hari --}}
      @php $dayPtr = Carbon::parse($this->currentDate)->startOfWeek(); @endphp
      @for ($col=0; $col<7; $col++)
        @php
          $colDay = $dayPtr->copy();

          $dayAll = $calendarData['events']->get($colDay->format('Y-m-d'), collect());
          $timed  = $dayAll->filter(fn($e) => !$e->all_day)
                           ->sortBy(fn($e) => $e->start_time ? substr($e->start_time,0,5) : '00:00')
                           ->values();

          // Cluster overlap
          $clusters = [];
          $dayStart = $colDay->copy()->startOfDay();
          $dayEnd   = $colDay->copy()->endOfDay();

          foreach ($timed as $ev) {
            $evStart = Carbon::parse(($ev->start_date instanceof Carbon ? $ev->start_date->format('Y-m-d') : $ev->start_date).' '.($ev->start_time ?? '00:00'));
            $evEnd   = Carbon::parse(($ev->end_date   instanceof Carbon ? $ev->end_date->format('Y-m-d')   : $ev->end_date)  .' '.($ev->end_time   ?? '23:59'));
            if ($evEnd->lt($dayStart) || $evStart->gt($dayEnd)) continue;
            $evStart = $evStart->lt($dayStart) ? $dayStart->copy() : $evStart;
            $evEnd   = $evEnd->gt($dayEnd)     ? $dayEnd->copy()   : $evEnd;

            if (empty($clusters)) {
              $clusters[] = [['e'=>$ev,'s'=>$evStart,'t'=>$evEnd]];
            } else {
              $last = count($clusters)-1;
              $lastEndMax = collect($clusters[$last])->max(fn($x) => $x['t']);
              if ($evStart->lt($lastEndMax)) $clusters[$last][] = ['e'=>$ev,'s'=>$evStart,'t'=>$evEnd];
              else                           $clusters[]        = [['e'=>$ev,'s'=>$evStart,'t'=>$evEnd]];
            }
          }

          // Items untuk render + durasi
          $renderItems = [];
          foreach ($clusters as $cluster) {
            $stackSize = count($cluster);
            foreach ($cluster as $stackIndex => $item) {
              $minutesFromStart = $dayStart->diffInMinutes($item['s']);
              $durMinutes       = max(15, $item['s']->diffInMinutes($item['t']));
              $renderItems[] = [
                'e'       => $item['e'],
                'top'     => ($minutesFromStart / $DAY_MINUTES) * 100,
                'height'  => ($durMinutes       / $DAY_MINUTES) * 100,
                'dur'     => $durMinutes,
                'stack'   => $stackIndex,
                'stackSz' => $stackSize,
              ];
            }
          }
        @endphp

        <div class="border-r border-gray-200 relative" style="height: {{ $HOUR_HEIGHT*24 }}px;">
          {{-- Garis jam --}}
          @for ($h=0; $h<24; $h++)
            <div class="absolute left-0 right-0 border-b border-gray-200"
                 style="top: {{ ($h/24)*100 }}%; height: 0;"></div>
          @endfor

          {{-- Area klik per jam --}}
          @for ($h=0; $h<24; $h++)
            <div
              class="absolute left-0 right-0 group"
              style="top: {{ ($h/24)*100 }}%; height: {{ (1/24)*100 }}%;"
            >
              <div
                wire:click.stop="openCreateModal(null, '{{ $colDay->format('Y-m-d') }}', {{ $h }})"
                class="absolute inset-0 hover:bg-blue-50/40 cursor-pointer"
              ></div>
              <span class="absolute top-1 right-1 text-blue-600 text-lg font-semibold opacity-0 group-hover:opacity-100 select-none pointer-events-none">+</span>
            </div>
          @endfor

          {{-- Event --}}
          @foreach ($renderItems as $ri)
            @php
              /** @var \App\Models\Event $ev */
              $ev   = $ri['e'];
              $pc   = $ev->participants ? $ev->participants->count() : 0;

              $left = "calc({$LEFT_BASE_PCT}% + ".($ri['stack']*$STACK_SHIFT)."px)";
              $width= "calc({$WIDTH_BASE_PCT}% - ".($ri['stack']*$STACK_SHIFT)."px)";

              $s = Carbon::parse(($ev->start_date instanceof Carbon ? $ev->start_date->format('Y-m-d') : $ev->start_date).' '.($ev->start_time ?? '00:00'));
              $t = Carbon::parse(($ev->end_date   instanceof Carbon ? $ev->end_date->format('Y-m-d')   : $ev->end_date)  .' '.($ev->end_time   ?? '23:59'));

              // warna
              $c = $pickColor($ev->id ?? ($ri['stack'] + $ri['top']*100));
              // z-index: makin pendek durasi, makin depan
              $z = 1000 - min(999, (int) $ri['dur']);
              $z += (int) $ri['stack']; // tie-break
            @endphp
            <div
              wire:click.stop="openEditModal({{ $ev->id }})"
              class="absolute rounded-lg cursor-pointer px-2 py-1.5 text-xs
                     {{ $c['bg2'] }} {{ $c['text'] }} {{ $c['border'] }} {{ $c['ring'] }}
                     {{ $c['hoverBg'] }} {{ $c['hoverBorder'] }}
                     shadow-sm transition-all duration-150 ease-out will-change-transform
                     hover:-translate-y-0.5"
              style="
                top: calc({{ $ri['top'] }}% + 2px);
                height: calc({{ $ri['height'] }}% - 4px);
                left: {{ $left }};
                width: {{ $width }};
                min-height: 18px;
                z-index: {{ $z }};
              "
              title="{{ $ev->title }}"
            >
              <div class="flex items-center justify-between gap-2">
                <span class="font-semibold truncate">{{ $ev->title }}</span>
                @if ($pc > 0)
                  <span class="text-[10px] rounded px-1 {{ $c['badgeBg'] }} {{ $c['badgeText'] }} {{ $c['badgeBorder'] }}">{{ $pc }}</span>
                @endif
              </div>
              <div class="mt-0.5 text-[10px] {{ $c['text'] === 'text-indigo-900' ? 'text-indigo-800' : 'text-blue-800' }}">
                {{ $s->format('H:i') }} – {{ $t->format('H:i') }}
              </div>
            </div>
          @endforeach

{{-- CURRENT TIME INDICATOR (WEEK) --}}
@php $now = Carbon::now(); @endphp
@if ($colDay->isSameDay($now))
  @php
    $dayStart = $colDay->copy()->startOfDay();
    $mins     = max(0, min($DAY_MINUTES, $dayStart->diffInMinutes($now)));
    $topPct   = ($mins / $DAY_MINUTES) * 100;
  @endphp
  <div class="absolute left-0 right-0 pointer-events-none z-[5000]" style="top: {{ $topPct }}%;">
    <div class="h-0.5 bg-red-600 w-full"></div>
    <div class="w-3 h-3 rounded-full bg-red-600 border-2 border-white absolute -left-1.5 -top-1.5 shadow"></div>
  </div>
@endif
{{-- /CURRENT TIME INDICATOR (WEEK) --}}

        </div>

        @php $dayPtr->addDay(); @endphp
      @endfor
    </div>

  @else
    {{-- ====================== DAY VIEW ====================== --}}
    @php
      $HOUR_HEIGHT    = 80;
      $DAY_MINUTES    = 24 * 60;
      $STACK_SHIFT    = 8;
      $LEFT_BASE_PCT  = 2;
      $WIDTH_BASE_PCT = 96;
      $today       = Carbon::parse($this->currentDate)->toDateString();
      $eventsToday = \App\Models\Event::with('participants')
        ->whereDate('start_date','<=',$today)
        ->whereDate('end_date','>=',$today)
        ->orderByDesc('all_day')
        ->orderBy('start_time')
        ->get();

      $allDayEvents = $eventsToday->where('all_day', true);
      $timedEvents  = $eventsToday->where('all_day', false)->values();
    @endphp

    <div class="bg-white rounded-lg shadow overflow-hidden" wire:poll.2s>
      {{-- Header tanggal --}}
      <div class="bg-gray-50 border-b p-6 text-center">
        <div class="text-sm font-medium text-blue-600 uppercase">
          {{ Carbon::parse($this->currentDate)->locale('id')->translatedFormat('l, d F Y') }}
        </div>
      </div>

      {{-- All Day row --}}
      <div class="grid border-b border-gray-200" style="grid-template-columns: 80px 1fr;">
        <div class="bg-gray-50 border-r border-gray-200 flex items-center justify-center text-xs text-gray-500">All Day</div>
        <div class="p-1.5">
          <div class="flex flex-wrap gap-2">
            @forelse ($allDayEvents as $event)
              @php
                $pc = $event->participants ? $event->participants->count() : 0;
                $c  = $pickColor($event->id ?? $event->title);
              @endphp
              <button
                type="button"
                wire:click.stop="openEditModal({{ $event->id }})"
                class="px-3 py-1.5 rounded-md text-sm font-medium
                       {{ $c['bg2'] }} {{ $c['text'] }} {{ $c['border'] }} {{ $c['ring'] }}
                       {{ $c['hoverBg'] }} {{ $c['hoverBorder'] }} hover:shadow transition-colors duration-150
                       flex items-center gap-2"
              >
                <span class="truncate max-w-[16rem]">{{ $event->title }}</span>
                @if($pc > 0)
                  <span class="text-[10px] leading-none px-1.5 py-0.5 rounded-full {{ $c['badgeBg'] }} {{ $c['badgeText'] }} {{ $c['badgeBorder'] }}">{{ $pc }}</span>
                @endif
              </button>
            @empty
            @endforelse
          </div>
        </div>
      </div>

      {{-- Grid 24 jam + event --}}
      <div class="relative" style="height: {{ $HOUR_HEIGHT*24 }}px;">
        {{-- kolom waktu kiri --}}
        <div class="absolute left-0 top-0 bottom-0 w-20 bg-gray-50 border-r border-gray-200 z-[1]">
          @for ($h=0; $h<24; $h++)
            <div class="border-b border-gray-200 text-xs text-gray-500 flex items-start justify-end pr-2" style="height: {{ $HOUR_HEIGHT }}px;">
              {{ sprintf('%02d:00', $h) }}
            </div>
          @endfor
        </div>

        {{-- grid garis jam + area event --}}
        <div class="absolute left-20 right-0 top-0 bottom-0">
          @for ($h=0; $h<24; $h++)
            <div class="absolute left-0 right-0 border-b border-gray-200" style="top: {{ ($h/24)*100 }}%; height: 0;"></div>
          @endfor

          {{-- area klik per jam --}}
          @for ($h=0; $h<24; $h++)
            <div
              class="absolute left-0 right-0 group"
              style="top: {{ ($h/24)*100 }}%; height: {{ (1/24)*100 }}%;"
            >
              <div
                wire:click.stop="openCreateModal(null, '{{ $this->currentDate }}', {{ $h }})"
                class="absolute inset-0 hover:bg-blue-50/40 cursor-pointer"
              ></div>
              <span class="absolute top-1 right-1 text-blue-600 text-lg font-semibold opacity-0 group-hover:opacity-100 select-none pointer-events-none">+</span>
            </div>
          @endfor

{{-- CURRENT TIME INDICATOR (DAY) --}}
@php
  $now = Carbon::now();
  $currentDateObj = Carbon::parse($this->currentDate);
@endphp
@if ($currentDateObj->isSameDay($now))
  @php
    $dayStart = $currentDateObj->copy()->startOfDay();
    $mins     = max(0, min($DAY_MINUTES, $dayStart->diffInMinutes($now)));
    $topPct   = ($mins / $DAY_MINUTES) * 100;
  @endphp
  <div class="absolute left-0 right-0 pointer-events-none z-[5000]" style="top: {{ $topPct }}%;">
    <div class="h-0.5 bg-red-600 w-full"></div>
    <div class="w-3 h-3 rounded-full bg-red-600 border-2 border-white absolute -left-1.5 -top-1.5 shadow"></div>
  </div>
@endif
{{-- /CURRENT TIME INDICATOR (DAY) --}}

          {{-- cluster & render: MODE TUMPUK --}}
          @php
            $dayStart  = Carbon::parse($this->currentDate)->startOfDay();
            $dayEnd    = Carbon::parse($this->currentDate)->endOfDay();
            $sorted    = $timedEvents->sortBy(fn($e) => $e->start_time ? substr($e->start_time,0,5) : '00:00')->values();

            $clusters  = [];
            foreach ($sorted as $ev) {
              $evStart = Carbon::parse(($ev->start_date instanceof Carbon ? $ev->start_date->format('Y-m-d') : $ev->start_date).' '.($ev->start_time ?? '00:00'));
              $evEnd   = Carbon::parse(($ev->end_date   instanceof Carbon ? $ev->end_date->format('Y-m-d')   : $ev->end_date)  .' '.($ev->end_time   ?? '23:59'));
              if ($evEnd->lt($dayStart) || $evStart->gt($dayEnd)) continue;
              $evStart = $evStart->lt($dayStart) ? $dayStart->copy() : $evStart;
              $evEnd   = $evEnd->gt($dayEnd)     ? $dayEnd->copy()   : $evEnd;

              if (empty($clusters)) {
                $clusters[] = [['e'=>$ev,'s'=>$evStart,'t'=>$evEnd]];
              } else {
                $last = count($clusters)-1;
                $lastEndMax = collect($clusters[$last])->max(fn($x) => $x['t']);
                if ($evStart->lt($lastEndMax)) $clusters[$last][] = ['e'=>$ev,'s'=>$evStart,'t'=>$evEnd];
                else                           $clusters[]        = [['e'=>$ev,'s'=>$evStart,'t'=>$evEnd]];
              }
            }

            $renderItems = [];
            foreach ($clusters as $cluster) {
              $stackSize = count($cluster);
              foreach ($cluster as $stackIndex => $item) {
                $minutesFromStart = $dayStart->diffInMinutes($item['s']);
                $durMinutes       = max(15, $item['s']->diffInMinutes($item['t']));
                $renderItems[] = [
                  'e'       => $item['e'],
                  'top'     => ($minutesFromStart / $DAY_MINUTES) * 100,
                  'height'  => ($durMinutes       / $DAY_MINUTES) * 100,
                  'dur'     => $durMinutes,
                  'stack'   => $stackIndex,
                  'stackSz' => $stackSize,
                ];
              }
            }
          @endphp

          @foreach ($renderItems as $ri)
            @php
              /** @var \App\Models\Event $event */
              $event = $ri['e'];
              $pc    = $event->participants ? $event->participants->count() : 0;

              $left  = "calc({$LEFT_BASE_PCT}% + ".($ri['stack']*$STACK_SHIFT)."px)";
              $width = "calc({$WIDTH_BASE_PCT}% - ".($ri['stack']*$STACK_SHIFT)."px)";

              $s = Carbon::parse(($event->start_date instanceof Carbon ? $event->start_date->format('Y-m-d') : $event->start_date).' '.($event->start_time ?? '00:00'));
              $t = Carbon::parse(($event->end_date   instanceof Carbon ? $event->end_date->format('Y-m-d')   : $event->end_date)  .' '.($event->end_time   ?? '23:59'));

              // warna
              $c = $pickColor($event->id ?? ($ri['stack'] + $ri['top']*100));
              // z-index: makin pendek durasi, makin depan
              $z = 1000 - min(999, (int) $ri['dur']);
              $z += (int) $ri['stack'];
            @endphp

            <div
              wire:click.stop="openEditModal({{ $event->id }})"
              class="absolute rounded-lg cursor-pointer px-3 py-2 text-sm
                     {{ $c['bg2'] }} {{ $c['text'] }} {{ $c['border'] }} {{ $c['ring'] }}
                     {{ $c['hoverBg'] }} {{ $c['hoverBorder'] }}
                     shadow-sm transition-all duration-150 hover:-translate-y-0.5"
              style="
                left: {{ $left }};
                width: {{ $width }};
                top: calc({{ $ri['top'] }}% + 2px);
                height: calc({{ $ri['height'] }}% - 4px);
                min-height: 22px;
                z-index: {{ $z }};
              "
              title="{{ $event->title }}"
            >
              <div class="flex justify-between items-start gap-2">
                <span class="font-semibold truncate">{{ $event->title }}</span>
                <span class="ml-2 shrink-0 text-xs {{ $c['text'] === 'text-indigo-900' ? 'text-indigo-800' : 'text-blue-800' }}">{{ $s->format('H:i') }} – {{ $t->format('H:i') }}</span>
              </div>
              @if ($pc > 0)
                <div class="mt-1 text-[11px]">
                  <span class="rounded px-1 {{ $c['badgeBg'] }} {{ $c['badgeText'] }} {{ $c['badgeBorder'] }}">{{ $pc }}</span>
                </div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </div>
  @endif
</div>
