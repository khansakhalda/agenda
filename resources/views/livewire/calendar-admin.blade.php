<div wire:poll class="min-h-screen bg-gray-50">
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Collection;
    @endphp

    <div class="flex">
        {{-- START: Sidebar Admin --}}
        <div class="w-72 bg-white shadow-lg p-4">
            {{-- Calendar Stats --}}
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-3">Calendar Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <span class="text-sm text-gray-600">Total</span>
                        <span class="font-bold text-xl">{{ $stats['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-sm text-gray-600">Today</span>
                        <span class="font-bold text-xl text-blue-600">{{ $stats['today'] }}</span>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-600">
                    <div>This month:</div>
                    <div class="font-bold text-base">{{ $stats['thisMonth'] }} events</div>
                    <div class="mt-2">Upcoming:</div>
                    <div class="font-bold text-base">{{ $stats['upcoming'] }} events</div>
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
                            class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border-gray-300 rounded-md hover:bg-blue-700">Today</button>
                        <button wire:click="previousPeriod" class="p-1 hover:bg-gray-100 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button wire:click="nextPeriod" class="p-1 hover:bg-gray-100 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        <span class="text-lg font-semibold text-gray-800 ml-3">{{ $periodLabel }}</span></svg>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <div class="flex bg-gray-100 rounded-lg p-0.5">
                        <button wire:click="setView('month')"
                            class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Month</button>
                        <button wire:click="setView('week')"
                            class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Week</button>
                        <button wire:click="setView('day')"
                            class="px-3 py-1 text-xs font-medium rounded-md {{ $calendarView === 'day' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Day</button>
                    </div>
                    <button
                        class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Export</button>
                    <button wire:click="openCreateModal" wire:loading.attr="disabled"
                        wire:target="openCreateModal,createEvent"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="openCreateModal">Create Event</span>
                        <span wire:loading wire:target="openCreateModal">Loading...</span>
                    </button>
                </div>
            </div>

            {{-- START: Calendar Grid Container --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if($calendarView === 'month')
                    {{-- START: MONTH VIEW --}}
                    <div class="grid grid-cols-7 bg-gray-50">
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Sunday</div>
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Monday</div>
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Tuesday</div>
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Wednesday</div>
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Thursday</div>
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Friday</div>
                        <div class="p-3 text-center font-medium text-gray-900 text-sm">Saturday</div>
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

                            <div class="min-h-28 border border-gray-200 p-1.5 {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : '' }} {{ $isToday ? 'bg-blue-50' : '' }}"
                                data-date="{{ $currentDay->format('Y-m-d') }}">
                                <div class="flex justify-between items-start mb-1">
                                    <span
                                        class="text-xs font-medium {{ !$isCurrentMonth ? 'text-gray-400' : 'text-gray-900' }} {{ $isToday ? 'text-blue-600 font-bold' : '' }}">
                                        {{ $currentDay->day }}
                                    </span>
                                </div>

                                <div class="space-y-0.5">
                                    @foreach($dayEvents->take(3) as $event)
                                        @php
                                            $colorClasses = [
                                                'meeting' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'deadline' => 'bg-red-100 text-red-800 border-red-200',
                                                'call' => 'bg-green-100 text-green-800 border-green-200',
                                                'review' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'training' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            ];
                                            $eventClass = $colorClasses[$event->type] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                        @endphp

                                        <div wire:click="openEditModal({{ $event->id }})"
                                            class="px-1.5 py-0.5 rounded text-xs font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow"
                                            title="{{ $event->title }}">
                                            {{ $event->all_day ? 'All Day' : $event->start_date_time->format('H:i') . ' ' }}
                                            {{ $event->title }}
                                        </div>
                                    @endforeach

                                    @if($dayEvents->count() > 3)
                                        <div class="text-xs text-gray-500 px-1.5 pt-0.5">
                                            +{{ $dayEvents->count() - 3 }} more
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @php $currentDay->addDay(); @endphp
                        @endwhile
                    </div>
                    {{-- END: MONTH VIEW --}}

                @elseif($calendarView === 'week')
                    {{-- START: WEEK VIEW --}}
                    <div class="grid border-t border-l border-gray-200" style="grid-template-columns: 80px repeat(7, 1fr);">
                        {{-- Header Kolom "All Day" --}}
                        <div class="grid-item-header bg-gray-50 border-b border-r border-gray-200">
                            <div
                                class="h-24 p-2 text-center text-sm font-medium text-gray-900 flex items-end justify-center">
                                All Day</div>
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
                                    <span class="font-bold text-xl">{{ $currentDayInWeekHeader->format('D') }}</span>
                                    <span class="text-3xl font-bold {{ $dayClass }}">{{ $currentDayInWeekHeader->day }}</span>
                                    <span class="text-xs text-gray-500">{{ $currentDayInWeekHeader->format('M') }}</span>
                                </div>
                            </div>
                            @php $currentDayInWeekHeader->addDay(); @endphp
                        @endfor

                        {{-- Konten Grid Waktu --}}
                        @foreach(range(0, 23) as $hour)
                            {{-- Kolom Jam (untuk setiap baris jam) --}}
                            <div class="grid-item-time bg-gray-50 border-b border-r border-gray-200">
                                <div class="h-12 flex items-center justify-center text-xs text-gray-500">
                                    {{ Carbon::createFromTime($hour, 0)->format('H:i') }}
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

                                    $allDayEventsForSlot = ($hour === 0) ? $dayEvents->filter(fn($event) => $event->all_day) : collect();

                                    $eventsInHour = $dayEvents->filter(function ($event) use ($cellDateTime) {
                                        if ($event->all_day)
                                            return false;
                                        $eventStart = $event->start_date_time;
                                        $eventEnd = $event->end_date_time;
                                        return ($eventStart->lt($cellDateTime->copy()->addHour()) && $eventEnd->gt($cellDateTime));
                                    })->sortBy('start_time');

                                    $countEventsInHour = $eventsInHour->count();
                                    $displayLimit = 2; // Batasi jumlah event yang ditampilkan per slot
                                @endphp
                                <div class="grid-item-cell h-12 border-b border-r border-gray-200 p-0.5 relative group">
                                    @foreach($eventsInHour->take($displayLimit) as $index => $event)
                                        @php
                                            $colorClasses = [
                                                'meeting' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'deadline' => 'bg-red-100 text-red-800 border-red-200',
                                                'call' => 'bg-green-100 text-green-800 border-green-200',
                                                'review' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'training' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            ];
                                            $eventClass = $colorClasses[$event->type] ?? 'bg-gray-100 text-gray-800 border-gray-200';

                                            $eventStart = $event->start_date_time;
                                            $eventEnd = $event->end_date_time;
                                            $currentHourStart = $cellDateTime->copy()->startOfHour();
                                            $currentHourEnd = $currentHourStart->copy()->addHour();

                                            $slotDurationMinutes = $currentHourEnd->diffInMinutes($currentHourStart);
                                            $eventStartInSlot = $eventStart->max($currentHourStart);
                                            $eventEndInSlot = $eventEnd->min($currentHourEnd);
                                            $actualDurationInSlot = $eventEndInSlot->diffInMinutes($eventStartInSlot);

                                            $topOffset = ($eventStartInSlot->minute / $slotDurationMinutes) * 100;
                                            $height = ($actualDurationInSlot / $slotDurationMinutes) * 100;

                                            $dynamicWidth = 100;
                                            $dynamicLeft = 0;
                                            if ($countEventsInHour > 1 && $displayLimit > 0) {
                                                $visibleCount = min($countEventsInHour, $displayLimit);
                                                $dynamicWidth = 100 / $visibleCount;
                                                $dynamicLeft = $index * $dynamicWidth;
                                            }
                                        @endphp
                                        <div wire:click.stop="openEditModal({{ $event->id }})"
                                            class="absolute px-1 py-0.5 rounded-sm text-xs font-medium border {{ $eventClass }} overflow-hidden hover:shadow-md transition-shadow"
                                            title="{{ $event->title }}"
                                            style="top: {{ $topOffset }}%; height: {{ $height }}%; left: {{ $dynamicLeft }}%; width: {{ $dynamicWidth }}%;">
                                            {{ $event->start_date_time->format('H:i') }} - {{ $event->title }}
                                        </div>
                                    @endforeach

                                    @if($countEventsInHour > $displayLimit)
                                        <div wire:click.stop="openEditModal(null, '{{ Carbon::parse($this->currentDate)->format('Y-m-d') }}', {{ $hour }})"
                                            class="absolute bottom-0 right-0 text-gray-500 text-xs px-1 hover:underline cursor-pointer">
                                            +{{ $countEventsInHour - $displayLimit }} more
                                        </div>
                                    @endif
                                </div>
                                @php $currentDayInWeekRow->addDay(); @endphp
                            @endfor
                        @endforeach
                    </div>
                    {{-- END: WEEK VIEW --}}
                    {{-- START: Day View --}}
                @else
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="bg-gray-50 border-b p-6 text-center">
                                <div class="text-sm font-medium text-blue-600 uppercase">
                                    {{ $calendarData['periodLabel'] }}
                                </div>
                            </div>

                            <div class="overflow-y-auto" style="max-height: 600px;">
                                {{-- Baris utama Day View, selalu render kolom jam --}}
                                @for($hour = 0; $hour < 24; $hour++)
                                    @php
                                        $currentHourStart = Carbon::parse($this->currentDate)->copy()->setHour($hour)->startOfHour();
                                        $currentHourEnd = $currentHourStart->copy()->addHour();

                                        $eventsInHour = $calendarData['events']->filter(function ($event) use ($currentHourStart, $currentHourEnd) {
                                            return $event->start_date_time->lt($currentHourEnd) && $event->end_date_time->gt($currentHourStart);
                                        })->sortBy('start_time');

                                        $countEventsInHour = $eventsInHour->count();
                                        $displayLimit = 2; // Batasi jumlah event yang ditampilkan per slot
                                    @endphp

                                    <div class="flex border-b border-gray-100">
                                        <div class="w-20 p-4 text-right text-xs text-gray-500 bg-gray-50 border-r">
                                            {{ Carbon::createFromTime($hour, 0)->format('H:i') }}
                                        </div>

                                        <div class="flex-1 p-4 min-h-16 relative group"
                                            wire:click="openEditModal(null, '{{ Carbon::parse($this->currentDate)->format('Y-m-d') }}', {{ $hour }})">
                                            @if ($hour === 0)
                                                @php
                                                    $allDayEvents = $calendarData['events']->filter(fn($event) => $event->all_day);
                                                @endphp
                                                @if($allDayEvents->count() > 0)
                                                    <div class="pb-2 mb-2 border-b border-gray-200">
                                                        <h4 class="text-xs font-semibold text-gray-700 mb-1">All Day Events:</h4>
                                                        <div class="space-y-1">
                                                            @foreach($allDayEvents as $event)
                                                                <div wire:click.stop="openEditModal({{ $event->id }})"
                                                                    class="px-2 py-1 rounded text-xs font-medium border {{ $eventClass }} cursor-pointer truncate hover:shadow-md transition-shadow"
                                                                    title="{{ $event->title }}">
                                                                    {{ $event->title }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            @foreach($eventsInHour->take($displayLimit) as $index => $event)
                                                @php
                                                    $colorClasses = [
                                                        'meeting' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                        'deadline' => 'bg-red-100 text-red-800 border-red-200',
                                                        'call' => 'bg-green-100 text-green-800 border-green-200',
                                                        'review' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                        'training' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                    ];
                                                    $eventClass = $colorClasses[$event->type] ?? 'bg-gray-100 text-gray-800 border-gray-200';

                                                    $eventStart = $event->start_date_time;
                                                    $eventEnd = $event->end_date_time;
                                                    $slotDurationMinutes = $currentHourEnd->diffInMinutes($currentHourStart);
                                                    $eventStartInSlot = $eventStart->max($currentHourStart);
                                                    $eventEndInSlot = $eventEnd->min($currentHourEnd);
                                                    $actualDurationInSlot = $eventEndInSlot->diffInMinutes($eventStartInSlot);

                                                    $topOffset = ($eventStartInSlot->minute / $slotDurationMinutes) * 100;
                                                    $height = ($actualDurationInSlot / $slotDurationMinutes) * 100;

                                                    $dynamicWidth = 100;
                                                    $dynamicLeft = 0;
                                                    if ($countEventsInHour > 1 && $displayLimit > 0) {
                                                        $visibleCount = min($countEventsInHour, $displayLimit);
                                                        $dynamicWidth = 100 / $visibleCount;
                                                        $dynamicLeft = $index * $dynamicWidth;
                                                    }
                                                @endphp
                                                <div wire:click.stop="openEditModal({{ $event->id }})"
                                                    class="absolute px-1 py-0.5 rounded-sm text-xs font-medium border {{ $eventClass }} overflow-hidden hover:shadow-md transition-shadow"
                                                    title="{{ $event->title }}"
                                                    style="
                                                                                                                                                                                                                                                                                                                                                                                                                                                    top: {{ $topOffset }}%;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    height: {{ $height }}%;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    left: {{ $dynamicLeft }}%;
                                                                                                                                                                                                                                                                                                                                                                                                                                                    width: {{ $dynamicWidth }}%;
                                                                                                                                                                                                                                                                                                                                                                                                                                                ">
                                                    {{ $event->start_date_time->format('H:i') }} - {{ $event->title }}
                                                </div>
                                            @endforeach

                                            @if($countEventsInHour > $displayLimit)
                                                <div wire:click.stop="openEditModal(null, '{{ Carbon::parse($this->currentDate)->format('Y-m-d') }}', {{ $hour }})"
                                                    class="absolute bottom-0 right-0 text-gray-500 text-xs px-1 hover:underline cursor-pointer">
                                                    +{{ $countEventsInHour - $displayLimit }} more
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    {{-- END: Day View --}}
                @endif
            {{-- END: @if($calendarView === 'month') / @elseif($calendarView === 'week') / @else --}}
        </div>
        {{-- END: Calendar Grid Container --}}
    </div>
    {{-- END: Main Calendar Area --}}
</div>

@if($showCreateModal)
    <div
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Create New Event</h3>
                <button wire:click="closeCreateModal" class="text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="createEvent" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" wire:model="title" placeholder="Event title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" placeholder="Event description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" wire:model="allDay" class="mr-2">
                    <label class="text-sm text-gray-700">All day</label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" wire:model="startDate"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    @if(!$allDay)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <select wire:model="startTime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select start time</option>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" wire:model="endDate"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    @if(!$allDay)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <select wire:model="endTime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select end time</option>
                                @foreach($this->getTimeOptions() as $time)
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endforeach
                            </select>
                            @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" wire:click="closeCreateModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="createEvent"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="createEvent">Create</span>
                        <span wire:loading wire:target="createEvent" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($showEditModal)
    <div
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Edit Event</h3>
                <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Kontrol Navigasi Event di Modal --}}
            @if(isset($eventsInCurrentModalSlot) && $eventsInCurrentModalSlot instanceof Collection && $eventsInCurrentModalSlot->count() > 1)
                <div class="flex items-center justify-between mb-4">
                    <button wire:click="navigateModalEvent('prev')" @if($currentModalEventIndex === 0) disabled @endif
                        class="px-2 py-1 bg-gray-100 rounded-md text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span class="text-sm text-gray-600">{{ $currentModalEventIndex + 1 }} of
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" wire:model="title" placeholder="Event title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" placeholder="Event description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" wire:model="allDay" class="mr-2">
                    <label class="text-sm text-gray-700">All day</label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" wire:model="startDate"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    @if(!$allDay)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <select wire:model="startTime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select start time</option>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" wire:model="endDate"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    @if(!$allDay)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <select wire:model="endTime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select end time</option>
                                @foreach($this->getTimeOptions() as $time)
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endforeach
                            </select>
                            @error('endTime') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" wire:click="deleteEvent" wire:loading.attr="disabled" wire:target="deleteEvent"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
                        <span wire:loading.remove wire:target="deleteEvent">Delete</span>
                        <span wire:loading wire:target="deleteEvent">Deleting...</span>
                    </button>
                    <button type="button" wire:click="closeEditModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="updateEvent"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-900 disabled:opacity-50">
                        <span wire:loading.remove wire:target="updateEvent">Update</span>
                        <span wire:loading wire:target="updateEvent">Updating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        Livewire.on('refreshCalendar', () => { initializeDragAndDrop(); });

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