<div wire:poll class="min-h-screen bg-gray-50">
    @php
        use Carbon\Carbon; 
    @endphp
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left side - Calendar info and navigation -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <h1 class="text-xl font-semibold text-gray-900">
                            @if($viewMode === 'month')
                                {{ $viewData['title'] }}
                            @elseif($viewMode === 'week')
                                {{ $viewData['title'] }}
                            @else
                                {{ $viewData['title'] }}
                            @endif
                        </h1>
                    </div>

                    <div class="flex items-center space-x-1">
                        <button wire:click="previousPeriod" class="p-2 hover:bg-gray-100 rounded-full">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button wire:click="nextPeriod" class="p-2 hover:bg-gray-100 rounded-full">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                        <button wire:click="goToToday"
                            class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100">
                            Today
                        </button>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ now()->format('g:i A') }}
                    </div>
                </div>

                <!-- Right side - View modes -->
                <div class="flex items-center space-x-2">
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button wire:click="setViewMode('month')"
                            class="px-3 py-1 text-sm font-medium rounded-md {{ $viewMode === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            Month
                        </button>
                        <button wire:click="setViewMode('week')"
                            class="px-3 py-1 text-sm font-medium rounded-md {{ $viewMode === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2">
                                </path>
                            </svg>
                            Week
                        </button>
                        <button wire:click="setViewMode('day')"
                            class="px-3 py-1 text-sm font-medium rounded-md {{ $viewMode === 'day' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707">
                                </path>
                            </svg>
                            Day
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($viewMode === 'month')
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Calendar Header -->
                <div class="grid grid-cols-7 bg-gray-50 border-b">
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Sunday</div>
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Monday</div>
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Tuesday</div>
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Wednesday</div>
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Thursday</div>
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Friday</div>
                    <div class="p-4 text-center text-sm font-medium text-gray-900">Saturday</div>
                </div>

                <!-- Calendar Body -->
                <div class="grid grid-cols-7">
                    @php
                        $startOfMonth = $viewData['startOfMonth'];
                        $endOfMonth = $viewData['endOfMonth'];
                        $startOfWeek = $startOfMonth->copy()->startOfWeek();
                        $endOfWeek = $endOfMonth->copy()->endOfWeek();
                        $currentDay = $startOfWeek->copy();
                        $events = $viewData['events'];
                    @endphp

                    @while($currentDay <= $endOfWeek)
                        @php
                            $dayEvents = $events->get($currentDay->format('Y-m-d'), collect());
                            $isCurrentMonth = $currentDay->month == $this->currentMonth;
                            $isToday = $currentDay->isToday();
                        @endphp

                        <div class="min-h-32 border border-gray-200 p-3 cursor-pointer hover:bg-gray-50 {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : '' }} {{ $isToday ? 'bg-blue-50' : '' }}"
                            wire:click="selectDate('{{ $currentDay->format('Y-m-d') }}')">
                            <div class="flex justify-between items-start mb-2">
                                <span
                                    class="text-sm font-medium {{ !$isCurrentMonth ? 'text-gray-400' : 'text-gray-900' }} {{ $isToday ? 'text-blue-600 font-bold' : '' }}">
                                    {{ $currentDay->day }}
                                </span>
                            </div>

                            <!-- Events for this day -->
                            <div class="space-y-1">
                                @foreach($dayEvents->take(3) as $event)
                                    @php
                                        $colorClasses = [
                                            'meeting' => 'bg-blue-500 text-white',
                                            'deadline' => 'bg-red-500 text-white',
                                            'call' => 'bg-green-500 text-white',
                                            'review' => 'bg-yellow-500 text-white',
                                            'training' => 'bg-purple-500 text-white',
                                        ];
                                        $eventClass = $colorClasses[$event->type] ?? 'bg-blue-500 text-white';
                                    @endphp

                                    <div class="px-2 py-1 rounded text-xs font-medium {{ $eventClass }} truncate"
                                        title="{{ $event->title }}">
                                        {{ $event->all_day ? '' : Carbon::parse($event->start_time)->format('g:i A') }}
                                        {{ $event->title }}
                                    </div>
                                @endforeach

                                @if($dayEvents->count() > 3)
                                    <div class="text-xs text-gray-500 px-2">
                                        +{{ $dayEvents->count() - 3 }} more
                                    </div>
                                @endif
                            </div>
                        </div>

                        @php $currentDay->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        @elseif($viewMode === 'week')
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Week Header -->
                <div class="grid grid-cols-8 bg-gray-50 border-b">
                    <div class="p-4"></div> <!-- Time column -->
                    @foreach($viewData['weekDays'] as $day)
                        @php $isToday = $day['date']->isToday(); @endphp
                        <div class="p-4 text-center">
                            <div class="text-xs font-medium text-gray-500 uppercase">
                                {{ $day['date']->format('D') }}
                            </div>
                            <div class="text-lg font-semibold {{ $isToday ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $day['date']->day }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Week Body with Time Slots -->
                <div class="overflow-y-auto" style="max-height: 600px;">
                    @for($hour = 0; $hour < 24; $hour++)
                        <div class="grid grid-cols-8 border-b border-gray-100">
                            <!-- Time Column -->
                            <div class="p-4 text-right text-xs text-gray-500 bg-gray-50 border-r">
                                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                            </div>

                            <!-- Day Columns -->
                            @foreach($viewData['weekDays'] as $day)
                                <div class="p-2 min-h-16 border-r border-gray-100 relative">
                                    @foreach($day['events'] as $event)
                                        @if(Carbon::parse($event->start_time)->hour == $hour)
                                            @php
                                                $colorClasses = [
                                                    'meeting' => 'bg-blue-500 text-white',
                                                    'deadline' => 'bg-red-500 text-white',
                                                    'call' => 'bg-green-500 text-white',
                                                    'review' => 'bg-yellow-500 text-white',
                                                    'training' => 'bg-purple-500 text-white',
                                                ];
                                                $eventClass = $colorClasses[$event->type] ?? 'bg-blue-500 text-white';
                                            @endphp
                                            <div
                                                class="absolute left-1 right-1 top-1 px-2 py-1 rounded text-xs font-medium {{ $eventClass }} truncate z-10">
                                                {{ $event->title }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Day Header -->
                <div class="bg-gray-50 border-b p-6 text-center">
                    <div class="text-sm font-medium text-blue-600 uppercase">
                        {{ $viewData['date']->format('l') }}
                    </div>
                    <div class="text-4xl font-bold text-blue-600 mt-1">
                        {{ $viewData['date']->day }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">
                        {{ $viewData['date']->format('F Y') }}
                    </div>
                </div>

                <!-- Day Schedule -->
                <div class="overflow-y-auto" style="max-height: 600px;">
                    @if($viewData['events']->count() > 0)
                        @for($hour = 0; $hour < 24; $hour++)
                            @php
                                $hourEvents = $viewData['events']->filter(function ($event) use ($hour) {
                                    return Carbon::parse($event->start_time)->hour == $hour;
                                });
                            @endphp

                            <div class="flex border-b border-gray-100">
                                <!-- Time Column -->
                                <div class="w-20 p-4 text-right text-xs text-gray-500 bg-gray-50 border-r">
                                    {{ Carbon::createFromTime($hour, 0)->format('g:i A') }}
                                </div>

                                <!-- Event Column -->
                                <div class="flex-1 p-4 min-h-16">
                                    @foreach($hourEvents as $event)
                                        @php
                                            $colorClasses = [
                                                'meeting' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'deadline' => 'bg-red-100 text-red-800 border-red-200',
                                                'call' => 'bg-green-100 text-green-800 border-green-200',
                                                'review' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'training' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            ];
                                            $eventClass = $colorClasses[$event->type] ?? 'bg-blue-100 text-blue-800 border-blue-200';
                                        @endphp
                                        <div class="mb-2 p-3 rounded-lg border {{ $eventClass }}">
                                            <div class="font-medium">{{ $event->title }}</div>
                                            @if($event->description)
                                                <div class="text-sm mt-1 opacity-75">{{ $event->description }}</div>
                                            @endif
                                            <div class="text-xs mt-2 opacity-75">
                                                {{ Carbon::parse($event->start_time)->format('g:i A') }} -
                                                {{ Carbon::parse($event->end_time)->format('g:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endfor
                    @else
                        <div class="p-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium mb-2">No events scheduled</h3>
                            <p class="text-sm">You have a free day!</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>