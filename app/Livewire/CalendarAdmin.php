<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use Carbon\Carbon;

class CalendarAdmin extends Component
{
    protected $layout = 'layouts.app';
    public $currentMonth;
    public $currentYear;
    public $currentDate;
    public $calendarView = 'month';

    public $showCreateModal = false;

    // Create Event Modal Properties
    public $title = '';
    public $description = '';
    public $startDate;
    public $startTime;
    public $endDate;
    public $endTime;
    public $allDay = false;
    public $color = '#3B82F6';
    // Properti untuk Edit Event Modal
    public $showEditModal = false;
    public $editingEventId = null;
    public $currentModalEventIndex = 0;
    public $eventsInCurrentModalSlot;

    public $isSubmitting = false;

    public function getRules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate' => 'required|date',
            'startTime' => $this->allDay ? 'nullable' : 'required|date_format:H:i',
            'endDate' => 'required|date|after_or_equal:startDate',
            'endTime' => $this->allDay ? 'nullable' : 'required|date_format:H:i',
            'color' => 'required|string',
        ];
    }

    protected function after_start_time_if_same_day($attribute, $value, $fail)
    {
        if (!$this->allDay && $this->startDate === $this->endDate) {
            if (Carbon::parse($this->startTime)->greaterThanOrEqualTo(Carbon::parse($value))) {
                $fail('The end time must be after the start time if the event is on the same day.');
            }
        }
    }

    public function getTimeOptions()
    {
        $times = [];
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 15) {
                $timeString = sprintf('%02d:%02d', $hour, $minute);
                $times[] = $timeString;
            }
        }
        return $times;
    }

    private function getNearestThirtyMinuteTime($time = null)
    {
        $currentTime = $time ? Carbon::parse($time) : now();

        $minutes = $currentTime->minute;
        if ($minutes < 15) {
            $currentTime->minute(0)->second(0);
        } elseif ($minutes < 45) {
            $currentTime->minute(30)->second(0);
        } else {
            $currentTime->addHour()->minute(0)->second(0);
        }

        return $currentTime->format('H:i');
    }

    private function validateThirtyMinuteIntervals()
    {
        $errors = [];

        if ($this->startTime) {
            $startMinutes = Carbon::createFromFormat('H:i', $this->startTime)->minute;
            if (!in_array($startMinutes, [0, 15])) {
                $errors['startTime'] = 'Start time must be in 15-minute intervals (e.g., 09:00, 09:15).';
            }
        }

        if ($this->endTime) {
            $endMinutes = Carbon::createFromFormat('H:i', $this->endTime)->minute;
            if (!in_array($endMinutes, [0, 15])) {
                $errors['endTime'] = 'End time must be in 15-minute intervals (e.g., 10:00, 10:15).';
            }
        }

        return $errors;
    }

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->currentDate = now()->format('Y-m-d');
        $this->eventsInCurrentModalSlot = collect();
        $this->resetForm();
    }

    // --- Calendar Navigation (tetap sama) ---
    public function previousPeriod()
    {
        $carbonDate = Carbon::parse($this->currentDate);
        if ($this->calendarView === 'month') {
            $carbonDate->subMonth();
        } elseif ($this->calendarView === 'week') {
            $carbonDate->subWeek();
        } elseif ($this->calendarView === 'day') {
            $carbonDate->subDay();
        }
        $this->currentDate = $carbonDate->format('Y-m-d');
        $this->currentMonth = $carbonDate->month;
        $this->currentYear = $carbonDate->year;
        $this->dispatch('refreshCalendar');
    }

    public function nextPeriod()
    {
        $carbonDate = Carbon::parse($this->currentDate);
        if ($this->calendarView === 'month') {
            $carbonDate->addMonth();
        } elseif ($this->calendarView === 'week') {
            $carbonDate->addWeek();
        } elseif ($this->calendarView === 'day') {
            $carbonDate->addDay();
        }
        $this->currentDate = $carbonDate->format('Y-m-d');
        $this->currentMonth = $carbonDate->month;
        $this->currentYear = $carbonDate->year;
        $this->dispatch('refreshCalendar');
    }

    public function goToToday()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->currentDate = now()->format('Y-m-d');
        $this->dispatch('refreshCalendar');
    }

    public function setView($view)
    {
        $this->calendarView = $view;
        $this->goToToday();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->isSubmitting = false;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->isSubmitting = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function refreshCurrentTime()
    {
        if (!$this->showEditModal && !$this->editingEventId) {
            $currentTime = now();
            if ($currentTime->minute > 15) {
                $currentTime->addHour()->startOfHour();
            } else {
                $currentTime->startOfHour();
            }

            $this->startTime = $currentTime->format('H:i');
            $this->endTime = $currentTime->copy()->addHour()->format('H:i');
            $this->startDate = now()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        }
    }

    public function createEvent()
    {
        // Prevent multiple submissions
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            // Validasi dasar
            $this->validate($this->getRules());

            // Validasi interval 30 menit
            if (!$this->allDay) {
                $timeErrors = $this->validateThirtyMinuteIntervals();
                if (!empty($timeErrors)) {
                    $this->isSubmitting = false;
                    foreach ($timeErrors as $field => $message) {
                        $this->addError($field, $message);
                    }
                    return;
                }
            }

            Event::create([
                'title' => $this->title,
                'description' => $this->description,
                'start_date' => $this->startDate,
                'start_time' => $this->allDay ? '00:00:00' : $this->startTime . ':00',
                'end_date' => $this->endDate,
                'end_time' => $this->allDay ? '23:59:59' : $this->endTime . ':00',
                'all_day' => $this->allDay,
                'color' => $this->color,
            ]);

            $this->closeCreateModal();
            session()->flash('message', 'Event created successfully!');
            $this->dispatch('refreshCalendar');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSubmitting = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            session()->flash('error', 'Error creating event: ' . $e->getMessage());
        }
    }

    public function openEditModal($eventId = null, $date = null, $hour = null)
    {
        $this->showCreateModal = false; // Ensure create modal is closed
        $this->isSubmitting = false;
        $this->eventsInCurrentModalSlot = collect();
        $this->currentModalEventIndex = 0;

        if ($eventId) {
            $event = Event::find($eventId);
            if (!$event) {
                session()->flash('error', 'Event not found.');
                return;
            }
            $this->eventsInCurrentModalSlot = collect([$event]);
            $this->loadEventForModal($event);

        } elseif ($date && $hour !== null) {
            $selectedDateTime = Carbon::parse($date)->setHour($hour)->startOfHour();
            $nextHour = $selectedDateTime->copy()->addHour();

            $eventsInSlot = Event::whereDate('start_date', $selectedDateTime->format('Y-m-d'))
                ->where(function ($query) use ($selectedDateTime, $nextHour) {
                    $query->where(function ($q) use ($selectedDateTime, $nextHour) {
                        $q->whereTime('start_time', '<', $nextHour->format('H:i:s'))
                            ->whereTime('end_time', '>', $selectedDateTime->format('H:i:s'));
                    })->orWhere('all_day', true);
                })
                ->orderBy('start_time')
                ->get();

            $timedEventsInSlot = $eventsInSlot->filter(function ($event) use ($selectedDateTime, $nextHour) {
                if ($event->all_day)
                    return false;
                $eventStart = $event->start_date_time;
                $eventEnd = $event->end_date_time;
                return ($eventStart->lt($nextHour) && $eventEnd->gt($selectedDateTime));
            })->values();

            $allDayEventsInSlot = $eventsInSlot->filter(fn($event) => $event->all_day)->values();
            $this->eventsInCurrentModalSlot = $allDayEventsInSlot->merge($timedEventsInSlot);

            if ($this->eventsInCurrentModalSlot->isEmpty()) {
                $this->resetForm();
                $this->startDate = $date;
                $this->startTime = $selectedDateTime->format('H:i');
                $this->endDate = $date;
                $this->endTime = $nextHour->format('H:i');
                $this->showCreateModal = true;
                return;
            }

            $this->loadEventForModal($this->eventsInCurrentModalSlot->first());
        }

        $this->showEditModal = true;
    }

    private function loadEventForModal($event)
    {
        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->startDate = $event->start_date->format('Y-m-d');
        $this->startTime = Carbon::parse($event->start_time)->format('H:i');
        $this->endDate = $event->end_date->format('Y-m-d');
        $this->endTime = Carbon::parse($event->end_time)->format('H:i');
        $this->allDay = $event->all_day;
        $this->color = $event->color;
    }

    public function navigateModalEvent($direction)
    {
        if (!$this->eventsInCurrentModalSlot instanceof \Illuminate\Support\Collection || $this->eventsInCurrentModalSlot->isEmpty()) {
            return;
        }

        if ($direction === 'prev') {
            $this->currentModalEventIndex = max(0, $this->currentModalEventIndex - 1);
        } elseif ($direction === 'next') {
            $this->currentModalEventIndex = min($this->eventsInCurrentModalSlot->count() - 1, $this->currentModalEventIndex + 1);
        }

        $event = $this->eventsInCurrentModalSlot->get($this->currentModalEventIndex);
        if ($event) {
            $this->loadEventForModal($event);
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
        $this->editingEventId = null;
        $this->eventsInCurrentModalSlot = collect();
        $this->currentModalEventIndex = 0;
    }

    public function updateEvent()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        if (is_null($this->editingEventId)) {
            $this->isSubmitting = false;
            session()->flash('error', 'No event selected for update.');
            return;
        }

        try {
            $this->validate($this->getRules());

            $event = Event::find($this->editingEventId);
            if (!$event) {
                $this->isSubmitting = false;
                session()->flash('error', 'Event not found.');
                $this->closeEditModal();
                return;
            }

            $startTime = $this->allDay ? '00:00:00' : $this->startTime . ':00';
            $endTime = $this->allDay ? '23:59:59' : $this->endTime . ':00';

            $event->update([
                'title' => $this->title,
                'description' => $this->description,
                'start_date' => $this->startDate,
                'start_time' => $startTime,
                'end_date' => $this->endDate,
                'end_time' => $endTime,
                'all_day' => $this->allDay
            ]);

            $this->closeEditModal();
            session()->flash('message', 'Event updated successfully!');
            $this->dispatch('refreshCalendar');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSubmitting = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            session()->flash('error', 'Error updating event: ' . $e->getMessage());
            \Log::error('Event update error: ' . $e->getMessage());
        }
    }

    public function deleteEvent()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        if (is_null($this->editingEventId)) {
            $this->isSubmitting = false;
            session()->flash('error', 'No event selected for deletion.');
            return;
        }

        try {
            $event = Event::find($this->editingEventId);
            if ($event) {
                $event->delete();
                session()->flash('message', 'Event deleted successfully!');
                $this->closeEditModal();
                $this->dispatch('refreshCalendar');
            } else {
                $this->isSubmitting = false;
                session()->flash('error', 'Event not found.');
            }
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            session()->flash('error', 'Error deleting event: ' . $e->getMessage());
            \Log::error('Event deletion error: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->startDate = now()->format('Y-m-d');
        $currentTime = now();
        if ($currentTime->minute > 15) {
            $currentTime->addHour()->startOfHour();
        } else {
            $currentTime->startOfHour();
        }
        $this->startTime = $this->getNearestThirtyMinuteTime();
        $this->endDate = now()->format('Y-m-d');
        $startTimeCarbon = Carbon::createFromFormat('H:i', $this->startTime);
        $this->endTime = $startTimeCarbon->addHour()->format('H:i');
        $this->allDay = false;
        $this->color = '#3B82F6';
        $this->editingEventId = null;
        $this->eventsInCurrentModalSlot = collect();
        $this->currentModalEventIndex = 0;
        $this->isSubmitting = false;
    }

    public function getCalendarData()
    {
        try {
            $events = collect();
            $periodLabel = '';

            if ($this->calendarView === 'month') {
                $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                $startOfGrid = $startOfMonth->copy()->startOfWeek();
                $endOfGrid = $endOfMonth->copy()->endOfWeek();

                $events = Event::whereBetween('start_date', [
                    $startOfGrid->format('Y-m-d'),
                    $endOfGrid->format('Y-m-d')
                ])->orderBy('start_time')->get()->groupBy(function ($event) {
                    return $event->start_date->format('Y-m-d');
                });

                $periodLabel = Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');

            } elseif ($this->calendarView === 'week') {
                $startOfWeek = Carbon::parse($this->currentDate)->startOfWeek();
                $endOfWeek = Carbon::parse($this->currentDate)->endOfWeek();

                $events = Event::whereBetween('start_date', [
                    $startOfWeek->format('Y-m-d'),
                    $endOfWeek->format('Y-m-d')
                ])->orderBy('start_time')->get()->groupBy(function ($event) {
                    return $event->start_date->format('Y-m-d');
                });

                $periodLabel = $startOfWeek->format('M D') . ' - ' . $endOfWeek->format('M D, Y');

            } elseif ($this->calendarView === 'day') {
                $currentDay = Carbon::parse($this->currentDate);

                $events = Event::whereDate('start_date', $currentDay->format('Y-m-d'))
                    ->orderBy('start_time')->get();

                $periodLabel = $currentDay->format('l, F d, Y');
            }

            return [
                'startPeriod' => isset($startOfGrid) ? $startOfGrid : Carbon::parse($this->currentDate)->startOfMonth()->startOfWeek(),
                'endPeriod' => isset($endOfGrid) ? $endOfGrid : Carbon::parse($this->currentDate)->endOfMonth()->endOfWeek(),
                'events' => $events,
                'periodLabel' => $periodLabel,
            ];
        } catch (\Exception $e) {
            session()->flash('error', 'Error fetching calendar data: ' . $e->getMessage());
            \Log::error("Error in getCalendarData (Admin): " . $e->getMessage());
            return [
                'startPeriod' => Carbon::now()->startOfMonth()->startOfWeek(),
                'endPeriod' => Carbon::now()->endOfMonth()->endOfWeek(),
                'events' => collect(),
                'periodLabel' => 'Error Loading Calendar',
            ];
        }
    }

    public function getEventStats()
    {
        try {
            $currentMonthCarbon = Carbon::create($this->currentYear, $this->currentMonth, 1);

            return [
                'total' => Event::count(),
                'today' => Event::whereDate('start_date', today())->count(),
                'thisMonth' => Event::whereMonth('start_date', $currentMonthCarbon->month)
                    ->whereYear('start_date', $currentMonthCarbon->year)
                    ->count(),
                'upcoming' => Event::where('start_date', '>', today())->count(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting event stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'today' => 0,
                'thisMonth' => 0,
                'upcoming' => 0,
            ];
        }
    }

    public function render()
    {
        $calendarData = $this->getCalendarData();
        $stats = $this->getEventStats();

        return view('livewire.calendar-admin', [
            'calendarData' => $calendarData,
            'stats' => $stats,
            'periodLabel' => $calendarData['periodLabel'],
            'calendarView' => $this->calendarView,
        ]);
    }
}