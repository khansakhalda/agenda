<divwire:poll class="min-h-screen bg-gray-50">
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Collection;
        Carbon::setLocale('id');
    @endphp

    <div class="flex">
        {{-- START: Sidebar Admin --}}
        <div class="w-72 bg-white shadow-lg p-4">
            {{-- Mini Calendar --}}
            <div class="mt-6 mb-3">
                <div class="bg-gray-50 rounded-lg p-3">
                    {{-- Mini Calendar Header --}}
                    <div class="flex justify-between items-center mb-2">
                        <button wire:click="previousMiniMonth" class="p-1 hover:bg-gray-200 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <span
                            class="text-sm font-semibold">{{ Carbon::create($miniCalendarYear, $miniCalendarMonth, 1)->translatedFormat('F Y') }}</span>
                        <button wire:click="nextMiniMonth" class="p-1 hover:bg-gray-200 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Mini Calendar Grid --}}
                    <div class="grid grid-cols-7 text-sm">
                        {{-- Day Headers --}}
                        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                            <div class="text-center text-gray-500 font-medium py-1">{{ $day }}</div>
                        @endforeach

                        {{-- Calendar Days --}}
                        @php
                            $miniStartOfMonth = Carbon::create($miniCalendarYear, $miniCalendarMonth, 1);
                            $miniEndOfMonth = $miniStartOfMonth->copy()->endOfMonth();
                            $miniStartOfGrid = $miniStartOfMonth->copy()->startOfWeek();
                            $miniEndOfGrid = $miniEndOfMonth->copy()->endOfWeek();
                            $miniCurrentDay = $miniStartOfGrid->copy();
                        @endphp

                        @while($miniCurrentDay <= $miniEndOfGrid)
                            @php
                                $isCurrentMonth = $miniCurrentDay->month == $miniStartOfMonth->month;
                                $isToday = $miniCurrentDay->isToday();
                                $isSelected = $miniCurrentDay->format('Y-m-d') === $this->currentDate;
                            @endphp
                            <button wire:click="selectMiniCalendarDate('{{ $miniCurrentDay->format('Y-m-d') }}')"
                                class="h-6 text-xs rounded hover:bg-blue-100 transition-colors {{ !$isCurrentMonth ? 'text-gray-300' : 'text-gray-700' }} {{ $isToday ? 'bg-blue-600 text-white font-bold' : '' }} {{ $isSelected && !$isToday ? 'bg-blue-200 text-blue-800 font-semibold' : '' }}">
                                {{ $miniCurrentDay->day }}
                            </button>
                            @php $miniCurrentDay->addDay(); @endphp
                        @endwhile
                    </div>
                </div>
            </div>

            {{-- Calendar Stats --}}
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-3">Statistik Kalender</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <span class="text-sm text-gray-600">Total</span>
                        <span class="font-bold text-xl">{{ $stats['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-sm text-gray-600">Hari Ini</span>
                        <span class="font-bold text-xl text-blue-600">{{ $stats['today'] }}</span>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-600">
                    <div>Bulan ini:</div>
                    <div class="font-bold text-base">{{ $stats['thisMonth'] }} acara</div>
                    <div class="mt-2">Mendatang:</div>
                    <div class="font-bold text-base">{{ $stats['upcoming'] }} acara</div>
                </div>
            </div>
            
            {{-- Participants Management --}}
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-3">Partisipan</h3>
                
                {{-- Add Participant Form --}}
                <div class="mb-3">
                    <form wire:submit.prevent="addParticipant" class="flex">
                        <input type="text" wire:model="newParticipantName" 
                            wire:keydown.enter.prevent="addParticipant"
                            placeholder="Nama partisipan baru..."
                            class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" 
                                class="px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Participants List --}}
                <div class="space-y-1 max-h-40 overflow-y-auto">
                    @forelse($participants as $participant)
                        <div class="flex items-center justify-between p-2 text-sm bg-gray-50 rounded hover:bg-gray-100 group">
                            @if($editingParticipantId === $participant->id)
                                {{-- Edit Mode --}}
                                <form wire:submit.prevent="updateParticipant" class="flex-1 flex">
                                    <input type="text" wire:model="editingParticipantName" 
                                        wire:keydown.enter.prevent="updateParticipant"
                                        wire:keydown.escape="cancelEditParticipant"
                                        class="flex-1 px-1 py-0 text-sm bg-white border border-blue-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        autofocus>
                                    <button type="submit" class="ml-1 text-green-600 hover:text-green-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="cancelEditParticipant" class="ml-1 text-gray-600 hover:text-gray-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                {{-- View Mode --}}
                                <span class="flex-1 text-gray-700">{{ $participant->name }}</span>
                                <div class="opacity-0 group-hover:opacity-100 flex space-x-1">
                                    {{-- Edit Button --}}
                                    <button wire:click="startEditParticipant({{ $participant->id }})" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    {{-- Delete Button --}}
                                    <button type="button"
                                            wire:click="deleteParticipant({{ $participant->id }})"
                                            onclick="return confirm('Hapus partisipan ini?')"
                                            class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-xs text-gray-500 text-center py-2">Belum ada partisipan</div>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- END: Sidebar Admin --}}

        {{-- START: Main Calendar Area --}}
        <div class="flex-1 p-6">
            {{-- Header Calendar Controls --}}
            <div class="flex justify-between items-center mb-5">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-1">
                        <button wire:click="goToToday"
                            class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border-gray-300 rounded-md hover:bg-blue-700">Hari
                            Ini</button>
                        <button wire:click="goToPrevious" class="p-1 hover:bg-gray-100 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button wire:click="goToNext" class="p-1 hover:bg-gray-100 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        <h2 class="text-lg font-bold text-gray-800 ml-3">
                            {{ Carbon::parse($this->currentDate)->locale('id')->translatedFormat('l, d F Y') }}
                        </h2>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <div class="flex bg-gray-100 rounded-lg p-0.5">
                        <button wire:click="setView('month')"
                            class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Bulan</button>
                        <button wire:click="setView('week')"
                            class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Minggu</button>
                        <button wire:click="setView('day')"
                            class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'day' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Hari</button>
                    </div>
                    <button
                        class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Ekspor</button>
                    <button wire:click="openCreateModal" wire:loading.attr="disabled"
                        wire:target="openCreateModal,createEvent"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="openCreateModal">Buat Acara</span>
                        <span wire:loading wire:target="openCreateModal">Memuat...</span>
                    </button>
                </div>
            </div>

            {{-- START: Calendar Grid Container --}}
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
                                <div class="border-b border-gray-200 p-4 bg-gray-50">
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
                                        if ($event->all_day) return false;

                                        $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                                        $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);

                                        return $eventStart->lt($currentHourEnd) && $eventEnd->gt($currentHourStart);
                                    })->sortBy(function ($event) {
                                        return Carbon::parse($event->start_date . ' ' . $event->start_time);
                                    });

                                    $countEventsInHour = $eventsInHour->count();
                                @endphp

                                <div class="flex border-b border-gray-200">
                                    <div class="w-20 p-4 text-right text-xs text-gray-500 bg-gray-50 border-r">
                                        {{ sprintf('%02d:00', $hour) }}
                                    </div>

                                    <div class="flex-1 p-4 min-h-16 relative group">
                                        {{-- Display events in this hour --}}
                                        @foreach($eventsInHour as $event)
                                            @php
                                                $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                                                $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);
                                                $durationMinutes = $eventStart->diffInMinutes($eventEnd);

                                                // Tinggi lebih kecil, minimal 24px, scaling lebih ramping
                                                $height = max(24, ($durationMinutes / 60) * 40);

                                                $eventClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                            @endphp
                                            <div wire:click.stop="openEditModal({{ $event->id }})"
                                                class="mb-2 px-3 py-2 rounded text-sm font-medium border {{ $eventClass }} cursor-pointer hover:shadow-md transition-shadow"
                                                style="height: {{ $height }}px;" title="{{ $event->title }}">
                                                <div class="flex justify-between items-start h-full">
                                                    <div class="flex-1">
                                                        {{-- Judul --}}
                                                        <span class="truncate font-semibold block">{{ $event->title }}</span>
                                                        
                                                        {{-- Deskripsi (jika panjang waktunya cukup) --}}
                                                        @if($event->description && $durationMinutes > 30)
                                                            <div class="text-xs opacity-75 mt-1 truncate">{{ $event->description }}</div>
                                                        @endif

                                                        {{-- Partisipan --}}
                                                        @if($event->participants && $event->participants->count() > 0)
                                                            <div class="flex flex-wrap gap-x-2 gap-y-1 mt-1 text-xs text-gray-700">
                                                                @foreach($event->participants->take(5) as $p)
                                                                    <span class="bg-white px-2 py-0.5 rounded">{{ $p->name }}</span>
                                                                @endforeach

                                                                @if($event->participants->count() > 5)
                                                                    <button wire:click.stop="openEditModal({{ $event->id }})"
                                                                            class="text-blue-600 hover:underline">
                                                                        +{{ $event->participants->count() - 5 }} lainnya
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                    {{-- Waktu acara --}}
                                                    <span class="text-xs ml-2 whitespace-nowrap">
                                                        {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Empty slot click area --}}
                                        @if($countEventsInHour === 0)
                                            <div wire:click.stop="openCreateModal(null, '{{ $this->currentDate }}', {{ $hour }})"
                                                class="absolute inset-0 hover:bg-blue-50 cursor-pointer opacity-0 hover:opacity-100 transition-opacity">
                                                <div class="flex items-center justify-center h-full">
                                                    <span class="text-xs text-gray-400 opacity-0 group-hover:opacity-100">Klik untuk menambah acara</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    {{-- END: Day View --}}
                @endif
            </div>
            {{-- END: Calendar Grid Container --}}
        </div>
        {{-- END: Main Calendar Area --}}
    </div>

    {{-- START: Create Event Modal --}}
    @if($showCreateModal)
        <div
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Buat Acara Baru</h3>
                    <button wire:click="closeCreateModal" class="text-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createEvent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                        <input type="text" wire:model="title" placeholder="Judul acara"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea wire:model="description" placeholder="Deskripsi acara" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" wire:model="allDay" class="mr-2">
                        <label class="text-sm text-gray-700">Sepanjang hari</label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" wire:model="startDate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(!$allDay)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                                <select wire:model="startTime"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih waktu mulai</option>
                                    @foreach($this->getTimeOptions() as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                                @error('startTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                            <input type="date" wire:model="endDate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(!$allDay)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                                <select wire:model="endTime"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih waktu selesai</option>
                                    @foreach($this->getTimeOptions() as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                                @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    <div x-data="{
                            query: @entangle('searchParticipant'),
                            suggestion: '',
                            async updateSuggestion() {
                                if (this.query.length > 0) {
                                    const res = await $wire.getFirstSuggestion(this.query);
                                    this.suggestion = res || '';
                                } else {
                                    this.suggestion = '';
                                }
                            }
                        }"
                        x-init="$watch('query', () => updateSuggestion())"
                        class="mt-4 relative">

                        <label class="block text-sm font-medium text-gray-700 mb-1">Partisipan</label>
                        
                        <div class="relative">
                            <!-- Input Utama -->
                            <input type="text"
                                x-model="query"
                                @keydown.tab.prevent="if(suggestion){ query = suggestion; suggestion=''; }"
                                @keydown.enter.prevent="$wire.addParticipantFromInput(query); query=''; suggestion='';"
                                placeholder="Ketik nama partisipan..."
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                            <!-- Ghost Suggestion -->
                            <div class="absolute top-0 left-0 px-3 py-2 text-gray-400 pointer-events-none">
                                <template x-if="suggestion && suggestion !== query">
                                    <span x-text="query + suggestion.substring(query.length)"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Chips Partisipan -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach(\App\Models\Participant::whereIn('id', $selectedParticipants ?? [])->get() as $p)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center">
                                    {{ $p->name }}
                                    <button type="button" wire:click="removeParticipant({{ $p->id }})"
                                            class="ml-2 text-red-500 hover:text-red-700">&times;</button>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="closeCreateModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="createEvent"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="createEvent">Buat</span>
                            <span wire:loading wire:target="createEvent" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Membuat...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- START: Edit Event Modal --}}
    @if($showEditModal)
        <div
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Edit Acara</h3>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Kontrol Navigasi Event di Modal --}}
                @if($fromMore && $eventsInCurrentModalSlot->count() > 1)
                    <div class="flex items-center justify-between mb-4">
                        <button wire:click="navigateModalEvent('prev')" @if($currentModalEventIndex === 0) disabled @endif
                            class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <span class="text-sm text-gray-600">{{ $currentModalEventIndex + 1 }} dari
                            {{ $eventsInCurrentModalSlot->count() }}</span>
                        <button wire:click="navigateModalEvent('next')"
                            @if($currentModalEventIndex === $eventsInCurrentModalSlot->count() - 1) disabled @endif
                            class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                <form wire:submit.prevent="updateEvent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                        <input type="text" wire:model="title" placeholder="Judul acara"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea wire:model="description" placeholder="Deskripsi acara" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" wire:model="allDay" class="mr-2">
                        <label class="text-sm text-gray-700">Sepanjang hari</label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" wire:model="startDate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(!$allDay)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                                <select wire:model="startTime"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih waktu mulai</option>
                                    @foreach($this->getTimeOptions() as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                                @error('startTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                            <input type="date" wire:model="endDate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if(!$allDay)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                                <select wire:model="endTime"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih waktu selesai</option>
                                    @foreach($this->getTimeOptions() as $time)
                                        <option value="{{ $time }}">{{ $time }}</option>
                                    @endforeach
                                </select>
                                @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    <div x-data="{
                            query: @entangle('searchParticipant'),
                            suggestion: '',
                            async updateSuggestion() {
                                if (this.query.length > 0) {
                                    const res = await $wire.getFirstSuggestion(this.query);
                                    this.suggestion = res || '';
                                } else {
                                    this.suggestion = '';
                                }
                            }
                        }"
                        x-init="$watch('query', () => updateSuggestion())"
                        class="mt-4 relative">

                        <label class="block text-sm font-medium text-gray-700 mb-1">Partisipan</label>
                        
                        <div class="relative">
                            <!-- Input Utama -->
                            <input type="text"
                                x-model="query"
                                @keydown.tab.prevent="if(suggestion){ query = suggestion; suggestion=''; }"
                                @keydown.enter.prevent="$wire.addParticipantFromInput(query); query=''; suggestion='';"
                                placeholder="Ketik nama partisipan..."
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                            <!-- Ghost Suggestion -->
                            <div class="absolute top-0 left-0 px-3 py-2 text-gray-400 pointer-events-none">
                                <template x-if="suggestion && suggestion !== query">
                                    <span x-text="query + suggestion.substring(query.length)"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Chips Partisipan -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach(\App\Models\Participant::whereIn('id', $selectedParticipants ?? [])->get() as $p)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center">
                                    {{ $p->name }}
                                    <button type="button" wire:click="removeParticipant({{ $p->id }})"
                                            class="ml-2 text-red-500 hover:text-red-700">&times;</button>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="closeEditModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteEvent" wire:loading.attr="disabled"
                            wire:target="deleteEvent"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
                            <span wire:loading.remove wire:target="deleteEvent">Hapus</span>
                            <span wire:loading wire:target="deleteEvent">Menghapus...</span>
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="updateEvent"
                            class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-900 disabled:opacity-50">
                            <span wire:loading.remove wire:target="updateEvent">Perbarui</span>
                            <span wire:loading wire:target="updateEvent">Memperbarui...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Livewire.on('refreshCalendar', () => { initializeDragAndDrop(); });
            Livewire.on('open-edit-modal', () => { const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.remove('hidden'); // atau kalau pakai modal library -> modal.show()
            }
        });

        document.addEventListener('livewire:update-autocomplete', event => {
            const el = document.querySelector('[x-data]');
            if (el) {
                el.__x.$data.results = event.detail.results;
            }
        });

            function initializeDragAndDrop() {
                const currentCells = document.querySelectorAll('[data-date]');
                currentCells.forEach(cell => {
                    cell.removeEventListener('dragover', handleDragOver);
                    cell.removeEventListener('dragleave', handleDragLeave);
                    cell.removeEventListener('drop', handleDrop);
                });
                const currentDraggables = document.querySelectorAll('[draggable="true"]');
                currentDraggables.forEach(event => {
                    event.removeEventListener('dragstart', handleDragStart);
                });

                currentCells.forEach(cell => {
                    cell.addEventListener('dragover', handleDragOver);
                    cell.addEventListener('dragleave', handleDragLeave);
                    cell.addEventListener('drop', handleDrop);
                });
                const draggableEvents = document.querySelectorAll('[draggable="true"]');
                draggableEvents.forEach(event => {
                    event.addEventListener('dragstart', handleDragStart);
                });
            }
            function handleDragOver(e) { e.preventDefault(); this.classList.add('bg-blue-100'); }
            function handleDragLeave(e) { e.classList.remove('bg-blue-100'); }
            function handleDrop(e) {
                e.preventDefault();
                e.classList.remove('bg-blue-100');
                const eventType = e.dataTransfer.getData('text/plain');
                const date = this.dataset.date;
                @this.call('quickCreateEvent', eventType, date);
            }
            function handleDragStart(e) { e.dataTransfer.setData('text/plain', this.dataset.type); }
            initializeDragAndDrop();
        });
    </script>
</div>