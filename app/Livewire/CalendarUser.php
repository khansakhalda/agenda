<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use Carbon\Carbon;

class CalendarUser extends Component
{
    public $viewMode = 'month'; // 'month', 'week', 'day'
    public $currentDate; // Carbon instance, sumber kebenaran utama
    public $currentMonth; // Disinkronkan dari $currentDate
    public $currentYear;  // Disinkronkan dari $currentDate
    public $selectedDate; // Disinkronkan dari $currentDate (string Y-m-d)

    public $showDetailModal = false;
    public $selectedEvent = null;

    public function mount()
    {
        $this->currentDate = now();
        $this->syncDateProperties();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->syncDateProperties();
    }

    public function previousPeriod()
    {
        switch ($this->viewMode) {
            case 'month':
                $this->currentDate = $this->currentDate->copy()->subMonth();
                break;
            case 'week':
                $this->currentDate = $this->currentDate->copy()->subWeek();
                break;
            case 'day':
                $this->currentDate = $this->currentDate->copy()->subDay();
                break;
        }
        $this->syncDateProperties();
    }

    public function nextPeriod()
    {
        switch ($this->viewMode) {
            case 'month':
                $this->currentDate = $this->currentDate->copy()->addMonth();
                break;
            case 'week':
                $this->currentDate = $this->currentDate->copy()->addWeek();
                break;
            case 'day':
                $this->currentDate = $this->currentDate->copy()->addDay();
                break;
        }
        $this->syncDateProperties();
    }

    public function goToToday()
    {
        $this->currentDate = now();
        $this->syncDateProperties();
    }

    private function syncDateProperties()
    {
        $this->currentMonth = $this->currentDate->month;
        $this->currentYear = $this->currentDate->year;
        $this->selectedDate = $this->currentDate->format('Y-m-d');
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->currentDate = Carbon::parse($date);
        $this->viewMode = 'day';
        $this->syncDateProperties();
    }

    public function openDetailModal($eventId)
    {
        $event = Event::find($eventId);
        if ($event) {
            $this->selectedEvent = $event;
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedEvent = null;
    }

    public function getViewData()
    {
        switch ($this->viewMode) {
            case 'month':
                return $this->getMonthData();
            case 'week':
                return $this->getWeekData();
            case 'day':
                return $this->getDayData();
            default:
                return $this->getMonthData();
        }
    }

    private function getMonthData()
    {
        $startOfMonthActual = $this->currentDate->copy()->startOfMonth();
        $endOfMonthActual = $startOfMonthActual->copy()->endOfMonth();

        $startOfGrid = $startOfMonthActual->copy()->startOfWeek();
        $endOfGrid = $endOfMonthActual->copy()->endOfWeek();

        $events = Event::whereBetween('start_date', [
            $startOfGrid->format('Y-m-d'),
            $endOfGrid->format('Y-m-d')
        ])->get()->groupBy(function ($event) {
            return $event->start_date->format('Y-m-d');
        });

        return [
            'startOfMonth' => $startOfMonthActual,
            'endOfMonth' => $endOfMonthActual,
            'events' => $events,
            'title' => $startOfMonthActual->format('F Y'),
        ];
    }

    private function getWeekData()
    {
        try {
            $startOfWeek = $this->currentDate->copy()->startOfWeek();
            $endOfWeek = $this->currentDate->copy()->endOfWeek();

            // Pastikan $allEventsFromDb selalu didefinisikan sebagai Collection
            $allEventsFromDb = Event::whereBetween('start_date', [
                $startOfWeek->format('Y-m-d'),
                $endOfWeek->format('Y-m-d')
            ])->get(); // Ambil semua event di rentang minggu ini

            // Kemudian group events
            $eventsGroupedByDate = $allEventsFromDb->groupBy(function ($event) {
                return $event->start_date->format('Y-m-d');
            });

            $weekDays = [];
            $current = $startOfWeek->copy();

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $current->format('Y-m-d');
                $weekDays[] = [
                    'date' => $current->copy(),
                    // Pastikan selalu mendapatkan Collection (bahkan jika kosong)
                    'events' => $eventsGroupedByDate->get($dateKey, collect())
                ];
                $current->addDay();
            }

            return [
                'weekDays' => $weekDays,
                'startOfWeek' => $startOfWeek,
                'endOfWeek' => $endOfWeek,
                'events' => $eventsGroupedByDate, // ← ADD THIS LINE
                'title' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y'),
            ];
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error("Error in getWeekData: " . $e->getMessage());
            // Return empty data on error to prevent fatal crashes
            return [
                'weekDays' => [],
                'startOfWeek' => $this->currentDate->copy()->startOfWeek(),
                'endOfWeek' => $this->currentDate->copy()->endOfWeek(),
                'events' => collect(), // ← ADD THIS LINE TOO
                'title' => 'Error Loading Week Data',
            ];
        }
    }

    private function getDayData()
    {
        $date = $this->currentDate->copy();

        $events = Event::whereDate('start_date', $date->format('Y-m-d'))
            ->orderBy('start_time')
            ->get();

        return [
            'date' => $date,
            'events' => $events,
            'title' => $date->format('l, F j, Y'),
        ];
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
        $viewData = $this->getViewData();
        $stats = $this->getEventStats();

        return view('livewire.calendar-user', [
            'viewData' => $viewData,
            'viewMode' => $this->viewMode,
            'stats' => $stats,
        ]);
    }
}