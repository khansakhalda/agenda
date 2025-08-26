    @php
      use Carbon\Carbon;
      use Illuminate\Support\Collection;
      Carbon::setLocale('id');
    @endphp

<div class="bg-white rounded-lg shadow overflow-hidden">
                @if($calendarView === 'month')
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
                        $endOfMonth = $startOfMonth->copy()->endOfMonth();
                        $startOfGrid = $startOfMonth->copy()->startOfWeek();
                        $endOfGrid = $endOfMonth->copy()->endOfWeek();
                        $currentDay = $startOfGrid->copy();
                        $events = $calendarData['events'];
                      @endphp

                      @while($currentDay <= $endOfGrid)
                        @php
                          $dayEvents = $events->get($currentDay->format('Y-m-d'), collect());
                          $isCurrentMonth = $currentDay->month == $startOfMonth->month;
                          $isToday = $currentDay->isToday();
                        @endphp

                        <div class="min-h-28 border border-gray-200 p-1.5 relative group cursor-pointer hover:bg-blue-50 transition-colors {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : '' }} {{ $isToday ? 'bg-blue-50' : '' }}"
                            data-date="{{ $currentDay->format('Y-m-d') }}"
                            wire:click.stop="openCreateModal(null, '{{ $currentDay->format('Y-m-d') }}', 9)">

                            <div class="flex justify-between items-start mb-1">
                                <span
                                    class="text-xs font-medium {{ !$isCurrentMonth ? 'text-gray-400' : 'text-gray-900' }} {{ $isToday ? 'text-blue-600 font-bold' : '' }}">
                                    {{ $currentDay->day }}
                                </span>
                                @if($dayEvents->isEmpty())
                                  <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                      <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor"
                                          viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"></path>
                                      </svg>
                                  </div>
                                @endif
                            </div>

                            <div class="space-y-0.5">
                                @foreach($dayEvents->take(3) as $event)
                                  @php
                                    $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                  @endphp

                                  <div wire:click.stop="openEditModal({{ $event->id }})"
                                      class="px-1.5 py-0.5 rounded text-xs font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow z-10 relative"
                                      title="{{ $event->title }}">
                                      {{ $event->all_day ? 'Sepanjang Hari' : ($event->start_time ? Carbon::parse($event->start_time)->format('H:i') . ' ' : '') }}
                                      {{ $event->title }}
                                  </div>
                                @endforeach

                                {{-- FIX: Perbaiki parameter untuk "lainnya" --}}
                                @if($dayEvents->count() > 3)
                                  <div wire:click.stop="openMoreEventsModal('{{ $currentDay->format('Y-m-d') }}')"
                                      class="text-xs text-gray-500 px-1.5 pt-0.5 hover:text-blue-600 cursor-pointer z-10 relative">
                                      +{{ $dayEvents->count() - 3 }} lainnya
                                  </div>
                                @endif
                            </div>

                            @if($dayEvents->isEmpty())
                              <div
                                  class="absolute inset-0 hover:bg-blue-50 opacity-0 hover:opacity-30 transition-opacity pointer-events-none">
                              </div>
                            @endif
                        </div>
                        @php $currentDay->addDay(); @endphp
                      @endwhile
                  </div>
                  {{-- END: MONTH VIEW --}}

                @elseif($calendarView === 'week')
                  {{-- START: IMPROVED WEEK VIEW WITH FIXED EVENT STACKING --}}
                  <div class="grid border-t border-l border-gray-200" style="grid-template-columns: 80px repeat(7, 1fr);">
                      {{-- Header Kolom "All Day" --}}
                      <div class="grid-item-header bg-gray-50 border-b border-r border-gray-200">
                          <div
                              class="h-24 p-2 text-center text-sm font-medium text-gray-900 flex items-end justify-center">
                              Sepanjang Hari
                          </div>
                      </div>

                      {{-- Header Hari-hari Minggu --}}
                      @php $currentDayInWeekHeader = Carbon::parse($this->currentDate)->startOfWeek(); @endphp
                      @for ($i = 0; $i < 7; $i++)
                        @php
                          $isToday = $currentDayInWeekHeader->isToday();
                          $dayClass = $isToday ? 'text-blue-600' : 'text-gray-900';
                          $headerBgClass = $isToday ? 'bg-blue-50' : 'bg-gray-50';
                        @endphp
                        <div class="grid-item-header {{ $headerBgClass }} border-b border-r border-gray-200">
                            <div
                                class="h-24 p-2 text-center text-sm font-medium {{ $dayClass }} flex flex-col justify-end items-center">
                                <span class="font-bold text-xl">{{ $currentDayInWeekHeader->translatedFormat('D') }}</span>
                                <span class="text-3xl font-bold {{ $dayClass }}">{{ $currentDayInWeekHeader->day }}</span>
                                <span
                                    class="text-xs text-gray-500">{{ $currentDayInWeekHeader->translatedFormat('M') }}</span>
                            </div>
                        </div>
                        @php $currentDayInWeekHeader->addDay(); @endphp
                      @endfor

                      {{-- All Day Events Row --}}
                      <div class="grid-item-time bg-gray-50 border-b border-r border-gray-200">
                          <div class="h-16 flex items-center justify-center text-xs text-gray-500">
                              All Day
                          </div>
                      </div>

                      @php 
                                                                                                                                                                                                              $currentDayInAllDayRow = Carbon::parse($this->currentDate)->startOfWeek();
                        $maxVisibleAllDay = 3;
                      @endphp

                          @for ($j = 0; $j < 7; $j++)
                            @php
                              $dayEvents = $calendarData['events']->get($currentDayInAllDayRow->format('Y-m-d'), collect());
                              $allDayEvents = $dayEvents->filter(fn($event) => $event->all_day);
                            @endphp
                                <div class="grid-item-cell h-16 border-b border-r border-gray-200 p-0.5 relative group cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden" wire:click.stop="openCreateModal(null, '{{ $currentDayInAllDayRow->format('Y-m-d') }}')">
                                    <div class="space-y-0.5">
                                            @foreach($allDayEvents->take($maxVisibleAllDay) as $index => $event)
                                              @php
                                                $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                              @endphp

                                              <div wire:click.stop="openEditModal({{ $event->id }})"
                                                  class="px-1.5 py-0.5 rounded-sm text-xs font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow z-10 relative"
                                                  title="{{ $event->title }}">
                                                  {{ $event->title }}
                                              </div>
                                            @endforeach

                                            @if($allDayEvents->count() > $maxVisibleAllDay)
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

                          {{-- Hourly Time Slots with FIXED EVENT DISPLAY --}}
                          @foreach(range(0, 23) as $hour)
                            <div class="grid-item-time bg-gray-50 border-b border-r border-gray-200">
                                <div class="h-20 flex items-center justify-center text-xs text-gray-500">
                                    {{ sprintf('%02d:00', $hour) }}
                                </div>
                            </div>

                            @php
                              $currentDayInWeekRow = Carbon::parse($this->currentDate)->startOfWeek();
                              $eventsByDate = $calendarData['events'];
                            @endphp

                            @for ($j = 0; $j < 7; $j++)
                              @php
                                $dayEvents = $eventsByDate->get($currentDayInWeekRow->format('Y-m-d'), collect());
                                $cellDateTime = $currentDayInWeekRow->copy()->setHour($hour);

                                // Ambil events yang overlap dengan jam ini
                                $eventsInHour = $dayEvents->filter(function ($event) use ($cellDateTime) {
                                  if ($event->all_day)
                                    return false;

                                  $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                                  $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);
                                  $slotStart = $cellDateTime->copy();
                                  $slotEnd = $cellDateTime->copy()->addHour();

                                  return $eventStart->lt($slotEnd) && $eventEnd->gt($slotStart);
                                })->sortBy(function ($event) {
                                  return Carbon::parse($event->start_date . ' ' . $event->start_time);
                                })->values();

                                $countEventsInHour = $eventsInHour->count();
                              @endphp

                              <div class="grid-item-cell h-20 border-b border-r border-gray-200 p-0.5 relative group cursor-pointer hover:bg-blue-50 transition-colors overflow-hidden"
                                  wire:click.stop="openCreateModal(null, '{{ $currentDayInWeekRow->format('Y-m-d') }}', {{ $hour }})">

                                  {{-- PERBAIKAN: Event Display dengan Positioning Side by Side --}}
                                  <div class="relative h-full w-full">
                                      @foreach($eventsInHour->take(3) as $index => $event)
                                        @php
                                          $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                          $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                                          $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);

                                          // Hitung posisi dan ukuran berdasarkan waktu
                                          $slotStart = $cellDateTime->copy();
                                          $slotEnd = $cellDateTime->copy()->addHour();

                                          // Posisi vertikal dalam slot (0-100%)
                                          if ($eventStart->gte($slotStart)) {
                                            $topPercent = ($eventStart->diffInMinutes($slotStart) / 60) * 100;
                                          } else {
                                            $topPercent = 0;
                                          }

                                          // Tinggi berdasarkan durasi dalam slot ini
                                          $eventStartInSlot = $eventStart->gte($slotStart) ? $eventStart : $slotStart;
                                          $eventEndInSlot = $eventEnd->lte($slotEnd) ? $eventEnd : $slotEnd;
                                          $heightPercent = ($eventStartInSlot->diffInMinutes($eventEndInSlot) / 60) * 100;
                                          if ($topPercent + $heightPercent > 100) {
                                            $heightPercent = 100 - $topPercent; // Pastikan tidak overflow
                                          }

                                          // Lebar dan posisi horizontal untuk side by side
                                          $totalEvents = min($countEventsInHour, 3);
                                          $eventWidth = (90 / $totalEvents) - 2; // -2 untuk margin
                                          $leftPercent = ($index * (100 / $totalEvents)) + 1; // +1 untuk margin
                                        @endphp

                                        <div wire:click.stop="openEditModal({{ $event->id }})"
                                            class="absolute px-1 py-0.5 rounded text-xs font-medium border {{ $eventClass }} cursor-pointer hover:shadow-md transition-shadow z-10 overflow-hidden"
                                            style="
                                                top: calc{{ $topPercent }}%;
                                                left: {{ $leftPercent }}%;
                                                width: {{ $eventWidth }}%;
                                                height: {{ $heightPercent }}%;
                                                min-height: 6px;
                                            "
                                            title="{{ $event->title }} ({{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }})">

                                            <div class="h-full flex flex-col justify-center">
                                                <span class="truncate text-xs font-semibold leading-tight">{{ $event->title }}</span>
                                                @if ($countEventsInHour <= 2 && $heightPercent >= 50)
                                                  <span class="text-xs opacity-75 leading-tight">{{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}</span>
                                                @elseif ($countEventsInHour <= 1 && $heightPercent >= 25)
                                                  <span class="text-xs opacity-75 leading-tight">{{ $eventStart->format('H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                      @endforeach

                                      {{-- Show "lainnya" jika ada lebih dari 3 events --}}
                                      @if($eventsInHour->count() > 3)
                                        <div wire:click.stop="openMoreEventsModal('{{ $currentDayInWeekRow->format('Y-m-d') }}', {{ $hour }})"
                                            class="absolute bottom-1 right-1 text-xs text-gray-500 bg-white px-1 rounded shadow hover:text-blue-600 cursor-pointer z-20">
                                            +{{ $eventsInHour->count() - 3 }} lainnya
                                        </div>
                                      @endif
                                  </div>


                                  {{-- Empty slot indicator --}}
                                  @if($countEventsInHour === 0)
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
                  {{-- END: WEEK VIEW --}}
                @else
                  {{-- START: Day View - FIXED VERSION --}}
                  <div class="bg-white rounded-lg shadow overflow-hidden">
                      <div class="bg-gray-50 border-b p-6 text-center">
                          <div class="text-sm font-medium text-blue-600 uppercase">
                              {{ Carbon::parse($this->currentDate)->locale('id')->translatedFormat('l, d F Y') }}
                          </div>
                      </div>

                      <div class="overflow-y-auto" style="max-height: 600px;">
                          @php
                            $todayEvents = $calendarData['events']->get($this->currentDate, collect());
                            $allDayEvents = $todayEvents->filter(fn($event) => $event->all_day);
                          @endphp

                          {{-- All Day Events Section --}}
                          @if($allDayEvents->count() > 0)
                            <div class="border-b border-gray-200 p-4 bg-gray-50 ">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Acara Sepanjang Hari:</h4>
                                <div class="space-y-1">
                                    @foreach($allDayEvents as $event)
                                      @php
                                        $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                      @endphp
                                      <div wire:click.stop="openEditModal({{ $event->id }})"
                                          class="px-3 py-2 rounded text-sm font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow"
                                          title="{{ $event->title }}">
                                          {{ $event->title }}
                                      </div>
                                    @endforeach
                                </div>
                            </div>
                          @endif

                          {{-- Hourly Time Slots --}}
                          @for($hour = 0; $hour < 24; $hour++)
                            @php
                              $currentHourStart = Carbon::parse($this->currentDate)->copy()->setHour($hour)->startOfHour();
                              $currentHourEnd = $currentHourStart->copy()->addHour();

                              $eventsInHour = $todayEvents->filter(function ($event) use ($currentHourStart, $currentHourEnd) {
                                if ($event->all_day)
                                  return false;

                                $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                                $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);

                                return $eventStart->lt($currentHourEnd) && $eventEnd->gt($currentHourStart);
                              })->sortBy(function ($event) {
                                return Carbon::parse($event->start_date . ' ' . $event->start_time);
                              });

                              $countEventsInHour = $eventsInHour->count();
                            @endphp

                            <div class="flex border-b border-gray-200 relative h-24">
                                <div class="w-20 p-4 text-right text-xs text-gray-500 bg-gray-50 border-r">
                                    {{ sprintf('%02d:00', $hour) }}
                                </div>

                                <div class="flex-1 relative">
                                    {{-- Display events in this hour --}}
                                    @foreach($eventsInHour->take(3) as $event)
                                      @php
                                        $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                                        $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);
                                        $slotStart = $currentHourStart;
                                        $slotEnd = $currentHourEnd;

                                        $topPercent = max(0, $eventStart->greaterThan($slotStart) ? $eventStart->diffInMinutes($slotStart) / 60 * 100 : 0);

                                        $eventStartInSlot = $eventStart->greaterThan($slotStart) ? $eventStart : $slotStart;
                                        $eventEndInSlot = $eventEnd->lessThan($slotEnd) ? $eventEnd : $slotEnd;
                                        $durationInSlot = $eventStartInSlot->diffInMinutes($eventEndInSlot);
                                        $heightPercent = ($durationInSlot / 60) * 100;

                                        $maxHeight = 100 - $topPercent;
                                        $heightPercent = min($heightPercent, $maxHeight);
                                        $heightPercent = max($heightPercent, 15);

                                        $totalEvents = min($countEventsInHour, 3);
                                        $eventWidth = 95 / $totalEvents;
                                        $leftPercent = $loop->index * (100 / $totalEvents);
                                      @endphp

                                      <div wire:click.stop="openEditModal({{ $event->id }})"
                                          class="absolute border rounded bg-blue-100 text-blue-800 border-blue-200 cursor-pointer hover:shadow-md transition-shadow px-3 py-2 text-sm z-10"
                                          style="top: calc({{ $topPercent }}% + 2px);
                                                  left: {{ $leftPercent }}%;
                                                  width: {{ $eventWidth }}%;
                                                  height: calc({{ $heightPercent }}% - 10px);
                                                  margin-top: 3px;
                                                  margin-left: 5px;">

                                          <div class="flex justify-between items-start">
                                              <span class="truncate font-semibold">{{ $event->title }}</span>
                                              <span class="ml-2 whitespace-nowrap text-gray-700 font-medium">
                                                  {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}
                                              </span>
                                          </div>

                                          @if($event->description)
                                            <div class="mt-1 text-gray-700">{{ $event->description }}</div>
                                          @endif

                                          @if($event->participants && $event->participants->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-2 text-xs text-gray-800">
                                                @foreach($event->participants->take(3) as $p)
                                                  <div class="px-2 py-0.5 bg-gray-200 rounded-md">
                                                      {{ $p->name }}
                                                  </div>
                                                @endforeach
                                                @if($event->participants->count() > 3)
                                                  <span class="text-blue-600 font-medium">
                                                      +{{ $event->participants->count() - 3 }}
                                                  </span>
                                                @endif
                                            </div>
                                          @endif
                                      </div>
                                    @endforeach

                                    @if($eventsInHour->count() > 3)
                                      <div wire:click.stop="openMoreEventsModal('{{ $this->currentDate }}', {{ $hour }})"
                                          class="absolute bottom-3 right-1 text-xs text-gray-500 bg-white px-1 rounded shadow hover:text-blue-600 cursor-pointer z-20">
                                          +{{ $eventsInHour->count() - 3 }} lainnya
                                      </div>
                                    @endif

                                    {{-- Empty slot click area --}}
                                    <div wire:click.stop="openCreateModal(null, '{{ $this->currentDate }}', {{ $hour }})"
                                        class="absolute inset-0 hover:bg-blue-50 cursor-pointer transition-opacity"
                                        style="z-index:0;">
                                        <div class="flex items-center justify-center h-full opacity-0 hover:opacity-100 pointer-events-none">
                                            <span class="text-xs text-gray-400">Klik untuk menambah acara</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          @endfor
                      </div>
                  </div>
                  {{-- END: Day View --}}
                @endif
            </div>