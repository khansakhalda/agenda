@php
  use Carbon\Carbon;
  use Illuminate\Support\Collection;
  Carbon::setLocale('id');
@endphp

<div class="bg-white rounded-lg shadow overflow-hidden">
  @if ($calendarView === 'month')
    {{-- ===== MONTH VIEW ===== --}}
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

            {{-- badge jumlah event tanggal tsb --}}
            @if($dayEvents->isNotEmpty())
              <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                {{ $dayEvents->count() }} acara
              </span>
            @else
              <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </div>
            @endif
          </div>

          <div class="space-y-0.5">
            @foreach ($dayEvents->take(3) as $event)
              @php
                $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                $pc = $event->participants ? $event->participants->count() : 0;
              @endphp
              <div
                wire:click.stop="openEditModal({{ $event->id }})"
                class="px-1.5 py-0.5 rounded text-xs font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow z-10 relative flex items-center gap-1"
                title="{{ $event->title }}"
              >
                <span class="truncate">
                  {{ $event->all_day ? 'Sepanjang Hari' : ($event->start_time ? Carbon::parse($event->start_time)->format('H:i').' ' : '') }}
                  {{ $event->title }}
                </span>

                @if($pc > 0)
                  <span class="ml-auto text-[10px] px-1 rounded bg-white/70 border border-blue-200 text-blue-700 shrink-0">
                    {{ $pc }} org
                  </span>
                @endif
              </div>
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
    {{-- ===== WEEK VIEW ===== --}}
    <div class="grid border-t border-l border-gray-200" style="grid-template-columns: 80px repeat(7, 1fr);">
      {{-- header kiri --}}
      <div class="grid-item-header bg-gray-50 border-b border-r border-gray-200">
        <div class="h-24 p-2 text-center text-sm font-medium text-gray-900 flex items-end justify-center">
          Sepanjang Hari
        </div>
      </div>

      {{-- header hari --}}
      @php $currentDayInWeekHeader = Carbon::parse($this->currentDate)->startOfWeek(); @endphp
      @for ($i = 0; $i < 7; $i++)
        @php
          $isToday = $currentDayInWeekHeader->isToday();
          $dayClass = $isToday ? 'text-blue-600' : 'text-gray-900';
          $headerBgClass = $isToday ? 'bg-blue-50' : 'bg-gray-50';
        @endphp
        <div class="grid-item-header {{ $headerBgClass }} border-b border-r border-gray-200">
          <div class="h-24 p-2 text-center text-sm font-medium {{ $dayClass }} flex flex-col justify-end items-center">
            <span class="font-bold text-xl">{{ $currentDayInWeekHeader->translatedFormat('D') }}</span>
            <span class="text-3xl font-bold {{ $dayClass }}">{{ $currentDayInWeekHeader->day }}</span>
            <span class="text-xs text-gray-500">{{ $currentDayInWeekHeader->translatedFormat('M') }}</span>
          </div>
        </div>
        @php $currentDayInWeekHeader->addDay(); @endphp
      @endfor

      {{-- baris all day --}}
      <div class="grid-item-time bg-gray-50 border-b border-r border-gray-200">
        <div class="h-16 flex items-center justify-center text-xs text-gray-500">All Day</div>
      </div>

      @php
        $currentDayInAllDayRow = Carbon::parse($this->currentDate)->startOfWeek();
        $maxVisibleAllDay = 3;
      @endphp

      @for ($j = 0; $j < 7; $j++)
        @php
          $dayEvents    = $calendarData['events']->get($currentDayInAllDayRow->format('Y-m-d'), collect());
          $allDayEvents = $dayEvents->filter(fn($event) => $event->all_day);
        @endphp
        <div
          class="grid-item-cell h-16 border-b border-r border-gray-200 p-0.5 relative group cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden"
          wire:click.stop="openCreateModal(null, '{{ $currentDayInAllDayRow->format('Y-m-d') }}')"
        >
          <div class="space-y-0.5">
            @foreach($allDayEvents->take($maxVisibleAllDay) as $index => $event)
              @php
                $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                $pc = $event->participants ? $event->participants->count() : 0;
              @endphp
              <div
                wire:click.stop="openEditModal({{ $event->id }})"
                class="px-1.5 py-0.5 rounded-sm text-xs font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow z-10 relative flex items-center gap-1"
                title="{{ $event->title }}"
              >
                <span class="truncate">{{ $event->title }}</span>
                @if($pc > 0)
                  <span class="ml-auto text-[10px] px-1 rounded bg-white/70 border border-blue-200 text-blue-700 shrink-0">{{ $pc }}</span>
                @endif
              </div>
            @endforeach

            @if ($allDayEvents->count() > $maxVisibleAllDay)
              <div wire:click.stop="openMoreEventsModal(null, '{{ $currentDayInAllDayRow->format('Y-m-d') }}', null)"
                   class="text-xs text-gray-500 px-1.5 pt-0.5 hover:text-blue-600 cursor-pointer z-10 relative">
                +{{ $allDayEvents->count() - $maxVisibleAllDay }} lainnya
              </div>
            @endif
          </div>

          @if($allDayEvents->isEmpty())
            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute inset-0 flex items-center justify-center">
              <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
            </div>
          @endif
        </div>
        @php $currentDayInAllDayRow->addDay(); @endphp
      @endfor

      {{-- slot per jam --}}
      @foreach (range(0, 23) as $hour)
        <div class="grid-item-time bg-gray-50 border-b border-r border-gray-200">
          <div class="h-20 flex items-center justify-center text-xs text-gray-500">
            {{ sprintf('%02d:00', $hour) }}
          </div>
        </div>

        @php
          $currentDayInWeekRow = Carbon::parse($this->currentDate)->startOfWeek();
          $eventsByDate        = $calendarData['events'];
        @endphp

        @for ($j = 0; $j < 7; $j++)
          @php
            $dayEvents    = $eventsByDate->get($currentDayInWeekRow->format('Y-m-d'), collect());
            $cellDateTime = $currentDayInWeekRow->copy()->setHour($hour);

            // Filter events overlap jam ini
            $eventsInHour = $dayEvents->filter(function ($event) use ($cellDateTime) {
              if ($event->all_day) return false;

              $eventStart = $event->start_date_time
                ? $event->start_date_time->copy()
                : Carbon::parse(
                    (($event->start_date instanceof Carbon) ? $event->start_date->format('Y-m-d') : $event->start_date)
                    .' '.($event->start_time ?? '00:00:00')
                  );

              $eventEnd = $event->end_date_time
                ? $event->end_date_time->copy()
                : Carbon::parse(
                    (($event->end_date instanceof Carbon) ? $event->end_date->format('Y-m-d') : $event->end_date)
                    .' '.($event->end_time ?? '23:59:59')
                  );

              $slotStart = $cellDateTime->copy();
              $slotEnd   = $cellDateTime->copy()->addHour();

              return $eventStart->lt($slotEnd) && $eventEnd->gt($slotStart);
            })->sortBy(function ($event) {
              return $event->start_date_time
                ? $event->start_date_time
                : Carbon::parse(
                    (($event->start_date instanceof Carbon) ? $event->start_date->format('Y-m-d') : $event->start_date)
                    .' '.($event->start_time ?? '00:00:00')
                  );
            })->values();

            $countEventsInHour = $eventsInHour->count();
          @endphp

          <div
            class="grid-item-cell h-20 border-b border-r border-gray-200 p-0.5 relative group cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden"
            wire:click.stop="openCreateModal(null, '{{ $currentDayInWeekRow->format('Y-m-d') }}', {{ $hour }})"
          >
            <div class="relative h-full w-full">
              @foreach ($eventsInHour->take(3) as $index => $event)
                @php
                  $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';

                  $eventStart = $event->start_date_time
                    ? $event->start_date_time->copy()
                    : Carbon::parse(
                        (($event->start_date instanceof Carbon) ? $event->start_date->format('Y-m-d') : $event->start_date)
                        .' '.($event->start_time ?? '00:00:00')
                      );

                  $eventEnd = $event->end_date_time
                    ? $event->end_date_time->copy()
                    : Carbon::parse(
                        (($event->end_date instanceof Carbon) ? $event->end_date->format('Y-m-d') : $event->end_date)
                        .' '.($event->end_time ?? '23:59:59')
                      );

                  $slotStart = $cellDateTime->copy();
                  $slotEnd   = $cellDateTime->copy()->addHour();

                  $topPercent = $eventStart->gte($slotStart) ? ($eventStart->diffInMinutes($slotStart) / 60) * 100 : 0;

                  $eventStartInSlot = $eventStart->gte($slotStart) ? $eventStart : $slotStart;
                  $eventEndInSlot   = $eventEnd->lte($slotEnd) ? $eventEnd : $slotEnd;
                  $heightPercent    = ($eventStartInSlot->diffInMinutes($eventEndInSlot) / 60) * 100;
                  if ($topPercent + $heightPercent > 100) $heightPercent = 100 - $topPercent;

                  $visibleCount = min($countEventsInHour, 3);
                  $eventWidth   = (90 / $visibleCount) - 2;
                  $leftPercent  = ($index * (100 / $visibleCount)) + 1;

                  $pc = $event->participants ? $event->participants->count() : 0;
                @endphp

                <div
                  wire:click.stop="openEditModal({{ $event->id }})"
                  class="absolute px-1 py-0.5 rounded text-xs font-medium border {{ $eventClass }} cursor-pointer hover:shadow-md transition-shadow z-10 overflow-hidden"
                  style="
                    top: calc({{ $topPercent }}%);
                    left: {{ $leftPercent }}%;
                    width: {{ $eventWidth }}%;
                    height: {{ $heightPercent }}%;
                    min-height: 6px;
                  "
                  title="{{ $event->title }} ({{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }})"
                >
                  <div class="h-full flex items-center justify-between gap-1">
                    <span class="truncate text-[11px] font-semibold leading-tight">{{ $event->title }}</span>
                    @if($pc > 0 && $heightPercent >= 40)
                      <span class="text-[10px] px-1 rounded bg-white/70 border border-blue-200 text-blue-700 shrink-0">{{ $pc }}</span>
                    @endif
                  </div>
                </div>
              @endforeach

              @if ($eventsInHour->count() > 3)
                <div
                  wire:click.stop="openMoreEventsModal('{{ $currentDayInWeekRow->format('Y-m-d') }}', {{ $hour }})"
                  class="absolute bottom-1 right-1 text-xs text-gray-500 bg-white px-1 rounded shadow hover:text-blue-600 cursor-pointer z-20">
                  +{{ $eventsInHour->count() - 3 }} lainnya
                </div>
              @endif
            </div>

            @if ($countEventsInHour === 0)
              <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute inset-0 flex items-center justify-center">
                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </div>
            @endif
          </div>

          @php $currentDayInWeekRow->addDay(); @endphp
        @endfor
      @endforeach
    </div>

  @else
    {{-- ===== DAY VIEW ===== --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="bg-gray-50 border-b p-6 text-center">
        <div class="text-sm font-medium text-blue-600 uppercase">
          {{ Carbon::parse($this->currentDate)->locale('id')->translatedFormat('l, d F Y') }}
        </div>
      </div>

      <div class="overflow-y-auto" style="max-height: 600px;">
        @php
          $todayEvents  = $calendarData['events']->get($this->currentDate, collect());
          $allDayEvents = $todayEvents->filter(fn($event) => $event->all_day);
        @endphp

        @if ($allDayEvents->count() > 0)
          <div class="border-b border-gray-200 p-4 bg-gray-50">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Acara Sepanjang Hari:</h4>
            <div class="space-y-1">
              @foreach ($allDayEvents as $event)
                @php $eventClass = 'bg-blue-100 text-blue-800 border-blue-200'; @endphp
                <div
                  wire:click.stop="openEditModal({{ $event->id }})"
                  class="px-3 py-2 rounded text-sm font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow"
                  title="{{ $event->title }}"
                >
                  {{ $event->title }}
                </div>
              @endforeach
            </div>
          </div>
        @endif

        @for ($hour = 0; $hour < 24; $hour++)
          @php
            $currentHourStart = Carbon::parse($this->currentDate)->copy()->setHour($hour)->startOfHour();
            $currentHourEnd   = $currentHourStart->copy()->addHour();

            $eventsInHour = $todayEvents->filter(function ($event) use ($currentHourStart, $currentHourEnd) {
              if ($event->all_day) return false;

              $eventStart = $event->start_date_time
                ? $event->start_date_time->copy()
                : Carbon::parse(
                    (($event->start_date instanceof Carbon) ? $event->start_date->format('Y-m-d') : $event->start_date)
                    .' '.($event->start_time ?? '00:00:00')
                  );

              $eventEnd = $event->end_date_time
                ? $event->end_date_time->copy()
                : Carbon::parse(
                    (($event->end_date instanceof Carbon) ? $event->end_date->format('Y-m-d') : $event->end_date)
                    .' '.($event->end_time ?? '23:59:59')
                  );

              return $eventStart->lt($currentHourEnd) && $eventEnd->gt($currentHourStart);
            })->sortBy(function ($event) {
              return $event->start_date_time
                ? $event->start_date_time
                : Carbon::parse(
                    (($event->start_date instanceof Carbon) ? $event->start_date->format('Y-m-d') : $event->start_date)
                    .' '.($event->start_time ?? '00:00:00')
                  );
            });

            $countEventsInHour = $eventsInHour->count();
          @endphp

          <div class="flex border-b border-gray-200 relative h-24">
            <div class="w-20 p-4 text-right text-xs text-gray-500 bg-gray-50 border-r">
              {{ sprintf('%02d:00', $hour) }}
            </div>

            <div class="flex-1 relative">
              @foreach ($eventsInHour->take(3) as $idx => $event)
                @php
                  $eventStart = $event->start_date_time
                    ? $event->start_date_time->copy()
                    : Carbon::parse(
                        (($event->start_date instanceof Carbon) ? $event->start_date->format('Y-m-d') : $event->start_date)
                        .' '.($event->start_time ?? '00:00:00')
                      );

                  $eventEnd = $event->end_date_time
                    ? $event->end_date_time->copy()
                    : Carbon::parse(
                        (($event->end_date instanceof Carbon) ? $event->end_date->format('Y-m-d') : $event->end_date)
                        .' '.($event->end_time ?? '23:59:59')
                      );

                  $slotStart = $currentHourStart;
                  $slotEnd   = $currentHourEnd;

                  $topPercent = max(0, $eventStart->greaterThan($slotStart) ? $eventStart->diffInMinutes($slotStart) / 60 * 100 : 0);

                  $eventStartInSlot = $eventStart->greaterThan($slotStart) ? $eventStart : $slotStart;
                  $eventEndInSlot   = $eventEnd->lessThan($slotEnd) ? $eventEnd : $slotEnd;
                  $durationInSlot   = $eventStartInSlot->diffInMinutes($eventEndInSlot);
                  $heightPercent    = ($durationInSlot / 60) * 100;

                  $maxHeight      = 100 - $topPercent;
                  $heightPercent  = min($heightPercent, $maxHeight);
                  $heightPercent  = max($heightPercent, 15);

                  $visibleCount = min($countEventsInHour, 3);
                  $eventWidth   = 95 / $visibleCount;
                  $leftPercent  = $idx * (100 / $visibleCount);

                  $pc = $event->participants ? $event->participants->count() : 0;
                @endphp

                <div
                  wire:click.stop="openEditModal({{ $event->id }})"
                  class="absolute border rounded bg-blue-100 text-blue-800 border-blue-200 cursor-pointer hover:shadow-md transition-shadow px-3 py-2 text-sm z-10"
                  style="top: calc({{ $topPercent }}% + 2px);
                         left: {{ $leftPercent }}%;
                         width: {{ $eventWidth }}%;
                         height: calc({{ $heightPercent }}% - 10px);
                         margin-top: 3px;
                         margin-left: 5px;"
                >
                  <div class="flex justify-between items-start">
                    <span class="truncate font-semibold">{{ $event->title }}</span>
                    <span class="ml-2 whitespace-nowrap text-gray-700 font-medium">
                      {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}
                    </span>
                  </div>

                  @if ($event->description)
                    <div class="mt-1 text-gray-700">{{ $event->description }}</div>
                  @endif

                  @if ($event->participants && $event->participants->count() > 0)
                    <div class="flex flex-wrap gap-1 mt-2 text-xs text-gray-800">
                      @foreach($event->participants->take(3) as $p)
                        <div class="px-2 py-0.5 bg-gray-200 rounded-md">{{ $p->name }}</div>
                      @endforeach
                      @if($event->participants->count() > 3)
                        <span class="text-blue-600 font-medium">+{{ $event->participants->count() - 3 }}</span>
                      @endif
                    </div>
                  @endif
                </div>
              @endforeach

              @if ($eventsInHour->count() > 3)
                <div
                  wire:click.stop="openMoreEventsModal('{{ $this->currentDate }}', {{ $hour }})"
                  class="absolute bottom-3 right-1 text-xs text-gray-500 bg-white px-1 rounded shadow hover:text-blue-600 cursor-pointer z-20">
                  +{{ $eventsInHour->count() - 3 }} lainnya
                </div>
              @endif

              <div
                wire:click.stop="openCreateModal(null, '{{ $this->currentDate }}', {{ $hour }})"
                class="absolute inset-0 hover:bg-blue-50 cursor-pointer transition-opacity"
                style="z-index:0;"
              >
                <div class="flex items-center justify-center h-full opacity-0 hover:opacity-100 pointer-events-none">
                  <span class="text-xs text-gray-400">Klik untuk menambah acara</span>
                </div>
              </div>
            </div>
          </div>
        @endfor
      </div>
    </div>
  @endif
</div>
