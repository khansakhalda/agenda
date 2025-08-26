<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use Carbon\Carbon;
use App\Models\Participant;
use Illuminate\Support\Collection;

class CalendarAdmin extends Component
{
    protected $layout = 'layouts.app';

    // Calendar state
    public $currentMonth;
    public $currentYear;
    public $currentDate;
    public $calendarView = 'month';

    // Mini Calendar
    public $miniCalendarMonth;
    public $miniCalendarYear;
    public $lastClickTime = 0;
    public $lastClickedDate = '';

    // Modal state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $isSubmitting = false;

    // Event form
    public $title = '';
    public $description = '';
    public $startDate;
    public $startTime;
    public $endDate;
    public $endTime;
    public $allDay = false;
    public $color = '#3B82F6';

    // Edit modal
    public $editingEventId = null;
    public $currentModalEventIndex = 0;
    public $eventsInCurrentModalSlot;
    public $modalSlotDate;
    public $modalSlotHour;
    public bool $fromMore = false;
    public $currentEvent = null;

    // Participants management
    public $newParticipantName = '';
    public $editingParticipantId = null;
    public $editingParticipantName = '';
    public $eventParticipants = [];
    public $newEventParticipant = '';
    public $selectedEvent = null;
    public $searchParticipant = '';
    public $selectedParticipants = [];


    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'startDate' => 'required|date',
        'startTime' => 'nullable|date_format:H:i',
        'endDate' => 'required|date|after_or_equal:startDate',
        'endTime' => 'nullable|date_format:H:i',
        'color' => 'required|string',
    ];

    public function mount()
    {
        $this->currentDate = $this->currentDate ?: now()->format('Y-m-d');
    }

    protected $queryString = [
        'calendarView' => ['except' => 'month'],
        'currentDate' => ['except' => ''],
    ];

    private function initializeCalendar()
    {
        $now = now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
        $this->currentDate = $now->format('Y-m-d');
        $this->miniCalendarMonth = $now->month;
        $this->miniCalendarYear = $now->year;
    }

    public function getTimeOptions()
    {
        $times = [];
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 15) {
                $times[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }
        return $times;
    }

    // Mini Calendar Methods
    public function previousMiniMonth()
    {
        $miniDate = Carbon::create($this->miniCalendarYear, $this->miniCalendarMonth, 1)->subMonth();
        $this->miniCalendarMonth = $miniDate->month;
        $this->miniCalendarYear = $miniDate->year;
    }

    public function nextMiniMonth()
    {
        $miniDate = Carbon::create($this->miniCalendarYear, $this->miniCalendarMonth, 1)->addMonth();
        $this->miniCalendarMonth = $miniDate->month;
        $this->miniCalendarYear = $miniDate->year;
    }

    public function selectMiniCalendarDate($date)
    {
        $currentTime = now()->timestamp * 1000;
        $isDoubleClick = ($this->lastClickedDate === $date && ($currentTime - $this->lastClickTime) < 500);

        $this->lastClickTime = $currentTime;
        $this->lastClickedDate = $date;

        $selectedDate = Carbon::parse($date);
        $this->currentDate = $date;
        $this->currentMonth = $selectedDate->month;
        $this->currentYear = $selectedDate->year;

        if ($isDoubleClick) {
            $this->calendarView = 'day';
        }

        $this->dispatch('refreshCalendar');
    }

    // Calendar Navigation
    public function previousPeriod()
    {
        $carbonDate = Carbon::parse($this->currentDate);

        switch ($this->calendarView) {
            case 'month':
                $carbonDate->subMonth();
                break;
            case 'week':
                $carbonDate->subWeek();
                break;
            case 'day':
                $carbonDate->subDay();
                break;
        }

        $this->updateCurrentDate($carbonDate);
    }

    public function nextPeriod()
    {
        $carbonDate = Carbon::parse($this->currentDate);

        switch ($this->calendarView) {
            case 'month':
                $carbonDate->addMonth();
                break;
            case 'week':
                $carbonDate->addWeek();
                break;
            case 'day':
                $carbonDate->addDay();
                break;
        }

        $this->updateCurrentDate($carbonDate);
    }

    private function updateCurrentDate($carbonDate)
    {
        $this->currentDate = $carbonDate->format('Y-m-d');
        $this->currentMonth = $carbonDate->month;
        $this->currentYear = $carbonDate->year;
        $this->dispatch('refreshCalendar');
    }

    public function goToToday()
    {
        $this->currentDate = now()->format('Y-m-d');
    }

    public function goToPrevious()
    {
        $date = Carbon::parse($this->currentDate);

        if ($this->calendarView === 'month') {
            $this->currentDate = $date->subMonth()->format('Y-m-d');
        } elseif ($this->calendarView === 'week') {
            $this->currentDate = $date->subWeek()->format('Y-m-d');
        } else {
            $this->currentDate = $date->subDay()->format('Y-m-d');
        }
    }

    public function goToNext()
    {
        $date = Carbon::parse($this->currentDate);

        if ($this->calendarView === 'month') {
            $this->currentDate = $date->addMonth()->format('Y-m-d');
        } elseif ($this->calendarView === 'week') {
            $this->currentDate = $date->addWeek()->format('Y-m-d');
        } else {
            $this->currentDate = $date->addDay()->format('Y-m-d');
        }
    }

    public function setView($view)
    {
        $this->calendarView = $view;
    }

    // Event Creation Modal - FIXED
    public function openCreateModal($eventId = null, $date = null, $hour = null)
    {
        try {
            $this->resetForm();
            $this->resetErrorBag();

            $this->showCreateModal = true;
            $this->showEditModal = false;
            $this->isSubmitting = false;
            $this->eventParticipants = [];

            if ($date) {
                $this->startDate = $date;
                $this->endDate = $date;
            } else {
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
            }

            if ($hour !== null) {
                $this->startTime = sprintf('%02d:00', $hour);
                $this->endTime = sprintf('%02d:00', ($hour + 1) % 24);
                $this->allDay = false;
            } else {
                $currentHour = now()->hour;
                $this->startTime = sprintf('%02d:00', $currentHour);
                $this->endTime = sprintf('%02d:00', ($currentHour + 1) % 24);
            }
        } catch (\Exception $e) {
            \Log::error('Error opening create modal: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membuka modal');
        }
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->isSubmitting = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function createEvent()
    {
        if ($this->isSubmitting)
            return;

        $this->isSubmitting = true;

        try {
            // Custom validation untuk waktu
            $rules = $this->rules;
            if (!$this->allDay) {
                $rules['startTime'] = 'required|date_format:H:i';
                $rules['endTime'] = 'required|date_format:H:i';
            }

            $this->validate($rules);
            $this->eventParticipants = [];

            // Validasi waktu jika bukan sepanjang hari
            if (!$this->allDay && $this->startDate === $this->endDate) {
                $startDateTime = Carbon::parse($this->startDate . ' ' . $this->startTime);
                $endDateTime = Carbon::parse($this->endDate . ' ' . $this->endTime);

                if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                    $this->addError('endTime', 'Waktu selesai harus setelah waktu mulai.');
                    $this->isSubmitting = false;
                    return;
                }
            }

            $event = Event::create([
                'title' => $this->title,
                'description' => $this->description,
                'start_date' => $this->startDate,
                'start_time' => $this->allDay ? null : $this->startTime,
                'end_date' => $this->endDate,
                'end_time' => $this->allDay ? null : $this->endTime,
                'all_day' => $this->allDay,
                'color' => $this->color,
                'type' => 'meeting',
            ]);

            if (!empty($this->selectedParticipants)) {
                $event->participants()->attach($this->selectedParticipants);
            }

            $this->closeCreateModal();
            session()->flash('success', 'Acara berhasil dibuat!');
            $this->dispatch('refreshCalendar');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSubmitting = false;
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error creating event: ' . $e->getMessage());
            $this->addError('general', 'Terjadi kesalahan saat membuat acara');
            $this->isSubmitting = false;
        }

        $this->isSubmitting = false;
    }

    public function openEditModal($eventId = null, $date = null, $hour = null)
    {
        try {
            $this->resetForm();
            $this->resetErrorBag();
            $this->fromMore = false;
            $this->showCreateModal = false;
            $this->showEditModal = true;
            $this->editingEventId = $eventId;
            $this->currentModalEventIndex = 0;
            $this->modalSlotDate = $date;
            $this->modalSlotHour = $hour;

            if ($eventId) {
                $this->loadEventForEditing($eventId);
                $this->selectedEvent = Event::with('participants')->find($eventId);
                $this->selectedParticipants = $this->selectedEvent
                    ? $this->selectedEvent->participants->pluck('id')->toArray()
                    : [];
            } elseif ($date) {
                $this->loadEventsForSlot($date, $hour);
            } else {
                $this->prepareNewEvent();
            }
        } catch (\Exception $e) {
            \Log::error('Error opening edit modal: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membuka modal');
        }
    }

    public function openMoreEventsModal($date, $hour = null)
    {
        try {
            $this->resetForm();
            $this->resetErrorBag();

            $this->fromMore = true;
            $this->modalSlotDate = $date;
            $this->modalSlotHour = $hour;

            if ($hour !== null) {
                // Untuk specific hour di week view
                $cellDateTime = Carbon::parse($date)->setHour($hour);
                $dayEvents = Event::whereDate('start_date', $date)
                    ->where('all_day', false)
                    ->get();

                $this->eventsInCurrentModalSlot = $dayEvents->filter(function ($event) use ($cellDateTime) {
                    $eventStart = Carbon::parse($event->start_date . ' ' . $event->start_time);
                    $eventEnd = Carbon::parse($event->end_date . ' ' . $event->end_time);
                    $slotStart = $cellDateTime->copy();
                    $slotEnd = $cellDateTime->copy()->addHour();

                    return $eventStart->lt($slotEnd) && $eventEnd->gt($slotStart);
                })->skip(3)->values();
            } else {
                // Untuk monthly view
                $allEvents = Event::whereDate('start_date', $date)
                    ->orderBy('all_day', 'desc')
                    ->orderBy('start_time')
                    ->get();

                $this->eventsInCurrentModalSlot = $allEvents->skip(3)->values();
            }

            if ($this->eventsInCurrentModalSlot->count() > 0) {
                $this->currentModalEventIndex = 0;
                $firstEvent = $this->eventsInCurrentModalSlot->first();
                $this->editingEventId = $firstEvent->id;
                $this->populateFormFromEvent($firstEvent);

                $this->showEditModal = true;
                $this->showCreateModal = false;
            }

        } catch (\Exception $e) {
            \Log::error('Error opening more events modal: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membuka modal');
        }
    }

    private function loadEventForEditing($eventId)
    {
        $event = Event::with('participants')->find($eventId);
        if (!$event) {
            $this->closeEditModal();
            return;
        }

        $this->populateFormFromEvent($event);
        $this->loadEventsInModalSlot($event->start_date, $event->start_time, $eventId);

    }

    private function loadEventsForSlot($date, $hour)
    {
        $this->startDate = $date;
        $this->endDate = $date;

        if ($hour !== null) {
            $this->startTime = sprintf('%02d:00', $hour);
            $this->endTime = sprintf('%02d:00', ($hour + 1) % 24);
            $this->loadEventsInModalSlot($date, sprintf('%02d:00', $hour));
        } else {
            $this->loadEventsInModalSlot($date, null);
        }

        if ($this->eventsInCurrentModalSlot->count() > 0) {
            $this->loadFirstEventInSlot();
        } else {
            $this->prepareNewEventForSlot($hour);
        }
    }

    private function loadFirstEventInSlot()
    {
        $firstEvent = $this->eventsInCurrentModalSlot->first();
        $this->editingEventId = $firstEvent->id;
        $this->populateFormFromEvent($firstEvent);
    }

    private function prepareNewEventForSlot($hour)
    {
        $this->editingEventId = null;
        if ($hour !== null) {
            $this->startTime = sprintf('%02d:00', $hour);
            $this->endTime = sprintf('%02d:00', ($hour + 1) % 24);
        } else {
            $this->setDefaultTimes();
        }
    }

    private function prepareNewEvent()
    {
        $this->setDefaultTimes();
        $this->loadEventsInModalSlot($this->startDate, $this->startTime);
    }

    private function setDefaultTimes()
    {
        $currentTime = now();
        if ($currentTime->minute > 15) {
            $currentTime->addHour()->startOfHour();
        } else {
            $currentTime->startOfHour();
        }

        $this->startTime = $currentTime->format('H:i');
        $this->endTime = $currentTime->copy()->addHour()->format('H:i');
        $this->startDate = $this->currentDate;
        $this->endDate = $this->currentDate;
    }

    private function populateFormFromEvent($event)
    {
        $this->title = $event->title;
        $this->description = $event->description;
        $this->startDate = $event->start_date;
        $this->startTime = $event->start_time;
        $this->endDate = $event->end_date;
        $this->endTime = $event->end_time;
        $this->allDay = $event->all_day;
        $this->color = $event->color ?? '#3B82F6';
        $this->selectedParticipants = $event->participants->pluck('id')->toArray();
    }

    private function loadEventsInModalSlot($date, $time, $excludeEventId = null)
    {
        $query = Event::whereDate('start_date', $date);

        if ($excludeEventId) {
            $query->where('id', '!=', $excludeEventId);
        }

        if (!$time) {
            // For all day or "lainnya" events
            $allEvents = $query->orderBy('start_time')->get();
            $this->eventsInCurrentModalSlot = $allEvents->skip(3)->values();
        } else {
            // For specific hour
            $hour = Carbon::parse($time)->hour;
            $startOfHour = sprintf('%02d:00:00', $hour);
            $endOfHour = sprintf('%02d:59:59', $hour);

            $this->eventsInCurrentModalSlot = $query
                ->where('all_day', false)
                ->whereBetween('start_time', [$startOfHour, $endOfHour])
                ->orderBy('start_time')
                ->get();
        }

        // Add current event if editing
        if ($excludeEventId) {
            $currentEvent = Event::with('participants')->find($excludeEventId);
            if ($currentEvent) {
                $this->eventsInCurrentModalSlot = $this->eventsInCurrentModalSlot->prepend($currentEvent)->values();
            }
        }

        $this->currentModalEventIndex = 0;
        $this->findCurrentEventIndex();
    }

    private function findCurrentEventIndex()
    {
        if ($this->editingEventId) {
            $index = $this->eventsInCurrentModalSlot->search(function ($event) {
                return $event->id == $this->editingEventId;
            });
            $this->currentModalEventIndex = $index !== false ? $index : 0;
        }
    }

    public function navigateModalEvent($direction)
    {
        if (!$this->fromMore || $this->eventsInCurrentModalSlot->count() <= 1) {
            return;
        }

        if ($direction === 'prev' && $this->currentModalEventIndex > 0) {
            $this->currentModalEventIndex--;
        } elseif ($direction === 'next' && $this->currentModalEventIndex < $this->eventsInCurrentModalSlot->count() - 1) {
            $this->currentModalEventIndex++;
        }

        // INI YANG PENTING - update form dengan event yang baru
        $currentEvent = $this->eventsInCurrentModalSlot[$this->currentModalEventIndex];
        $this->editingEventId = $currentEvent->id;
        $this->populateFormFromEvent($currentEvent);
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingEventId = null;
        $this->currentModalEventIndex = 0;
        $this->eventsInCurrentModalSlot = collect();
        $this->modalSlotDate = null;
        $this->modalSlotHour = null;
        $this->selectedEvent = null;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function updateEvent()
    {
        if ($this->isSubmitting)
            return;

        $this->isSubmitting = true;

        try {
            // Custom validation untuk waktu
            $rules = $this->rules;
            if (!$this->allDay) {
                $rules['startTime'] = 'required|date_format:H:i';
                $rules['endTime'] = 'required|date_format:H:i';
            }

            $this->validate($rules);

            // Validasi waktu jika bukan sepanjang hari
            if (!$this->allDay && $this->startDate === $this->endDate) {
                $startDateTime = Carbon::parse($this->startDate . ' ' . $this->startTime);
                $endDateTime = Carbon::parse($this->endDate . ' ' . $this->endTime);

                if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                    $this->addError('endTime', 'Waktu selesai harus setelah waktu mulai.');
                    $this->isSubmitting = false;
                    return;
                }
            }

            $event = Event::find($this->editingEventId);
            if ($event) {
                $event->update([
                    'title' => $this->title,
                    'description' => $this->description,
                    'start_date' => $this->startDate,
                    'start_time' => $this->allDay ? null : $this->startTime,
                    'end_date' => $this->endDate,
                    'end_time' => $this->allDay ? null : $this->endTime,
                    'all_day' => $this->allDay,
                    'color' => $this->color,
                ]);

                $event->participants()->sync($this->selectedParticipants);

                $this->closeEditModal();
                session()->flash('success', 'Acara berhasil diperbarui!');
                $this->dispatch('refreshCalendar');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSubmitting = false;
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error updating event: ' . $e->getMessage());
            $this->addError('general', 'Terjadi kesalahan saat memperbarui acara');
            $this->isSubmitting = false;
        }

        $this->isSubmitting = false;
    }

    public function deleteEvent()
    {
        try {
            $event = Event::find($this->editingEventId);
            if ($event) {
                $event->delete();

                // Pastikan semua state event dibersihkan
                $this->editingEventId = null;
                $this->currentModalEventIndex = 0;
                $this->eventsInCurrentModalSlot = collect();
                $this->selectedEvent = null;

                $this->closeEditModal();
                session()->flash('success', 'Acara berhasil dihapus!');
                $this->dispatch('refreshCalendar');
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting event: ' . $e->getMessage());
            $this->addError('general', 'Terjadi kesalahan saat menghapus acara');
        }
    }

    public function quickCreateEvent($eventType, $date)
    {
        $eventTitles = [
            'meeting' => 'Rapat',
            'call' => 'Panggilan',
            'deadline' => 'Deadline',
            'review' => 'Review',
            'training' => 'Pelatihan',
        ];

        try {
            Event::create([
                'title' => $eventTitles[$eventType] ?? 'Acara Baru',
                'description' => '',
                'start_date' => $date,
                'start_time' => now()->format('H:i'),
                'end_date' => $date,
                'end_time' => now()->addHour()->format('H:i'),
                'all_day' => false,
                'color' => '#3B82F6',
                'type' => $eventType,
            ]);

            session()->flash('success', 'Acara berhasil ditambahkan!');
            $this->dispatch('refreshCalendar');
        } catch (\Exception $e) {
            \Log::error('Error creating quick event: ' . $e->getMessage());
            session()->flash('error', 'Gagal menambahkan acara');
        }
    }

    private function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->startDate = $this->currentDate ?? now()->format('Y-m-d');
        $this->startTime = now()->format('H:i');
        $this->endDate = $this->currentDate ?? now()->format('Y-m-d');
        $this->endTime = now()->addHour()->format('H:i');
        $this->allDay = false;
        $this->color = '#3B82F6';
        $this->selectedParticipants = [];
        $this->newEventParticipant = '';
    }

    public function addParticipant()
    {
        $name = trim($this->newParticipantName);

        // Reset error lama
        $this->resetErrorBag('participants');

        // Jangan proses kalau kosong
        if (empty($name)) {
            return;
        }

        // Cari atau buat baru di database (case-insensitive)
        $participant = Participant::firstOrCreate([
            'name' => ucfirst(strtolower($name))
        ]);

        // Cek apakah sudah ada di daftar terpilih
        if (in_array($participant->id, $this->selectedParticipants)) {
            $this->addError('participants', 'Partisipan ini sudah ada.');
            return;
        }

        // Kalau belum ada → tambahkan
        $this->selectedParticipants[] = $participant->id;

        // Reset input setelah sukses
        $this->newParticipantName = '';

        // Refresh daftar partisipan di sidebar
        $this->participants = Participant::orderBy('name')->get();
    }


    public function addParticipantToEvent()
    {
        if (empty($this->newEventParticipant))
            return;

        // Cari atau buat partisipan
        $participant = Participant::firstOrCreate(['name' => $this->newEventParticipant]);

        if ($this->selectedEvent) {
            // Untuk Edit Event
            $this->selectedEvent->participants()->syncWithoutDetaching([$participant->id]);
            $this->selectedEvent->refresh();
            $this->eventParticipants = $this->selectedEvent->participants->toArray();
        } else {
            // Untuk Create Event
            $this->eventParticipants[] = [
                'id' => $participant->id,
                'name' => $participant->name
            ];
        }

        $this->newEventParticipant = '';
    }

    public function startEditParticipant($participantId)
    {
        $participant = Participant::find($participantId);
        if ($participant) {
            $this->editingParticipantId = $participantId;
            $this->editingParticipantName = $participant->name;
        }
    }

    public function updateParticipant()
    {
        if (empty(trim($this->editingParticipantName)) || !$this->editingParticipantId) {
            $this->cancelEditParticipant();
            return;
        }

        try {
            $participant = Participant::find($this->editingParticipantId);
            if ($participant) {
                $participant->update(['name' => trim($this->editingParticipantName)]);
                session()->flash('success', 'Partisipan berhasil diperbarui!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui partisipan');
        }

        $this->cancelEditParticipant();
    }



    public function cancelEditParticipant()
    {
        $this->editingParticipantId = null;
        $this->editingParticipantName = '';
    }

    public function removeParticipant($id)
    {
        $this->selectedParticipants = array_filter($this->selectedParticipants, fn($p) => $p != $id);
    }

    public function deleteParticipant($id)
    {
        try {
            $participant = Participant::find($id);
            if ($participant) {
                $participant->delete();
                session()->flash('success', 'Partisipan berhasil dihapus!');
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting participant: ' . $e->getMessage());
            session()->flash('error', 'Gagal menghapus partisipan.');
        }
    }

    public function removeParticipantFromEvent($participantId)
    {
        if ($this->selectedEvent) {
            // Untuk Edit Event
            $this->selectedEvent->participants()->detach($participantId);
            $this->selectedEvent->refresh();
            $this->eventParticipants = $this->selectedEvent->participants->toArray();
        } else {
            // Untuk Create Event
            $this->eventParticipants = array_filter($this->eventParticipants, function ($p) use ($participantId) {
                return $p['id'] !== $participantId;
            });
        }
    }

    public function addEventParticipant()
    {
        if (empty(trim($this->newEventParticipant))) {
            return;
        }

        try {
            // Check if participant exists, if not create new one
            $participant = Participant::firstOrCreate([
                'name' => trim($this->newEventParticipant)
            ]);

            if (!in_array($participant->id, $this->selectedParticipants)) {
                $this->selectedParticipants[] = $participant->id;
            }

            $this->newEventParticipant = '';
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan partisipan');
        }
    }

    public function addSelectedParticipant($participantId)
    {
        if ($participantId && !in_array($participantId, $this->selectedParticipants)) {
            $this->selectedParticipants[] = (int) $participantId;
        }
    }

    public function removeSelectedParticipant($index)
    {
        if (isset($this->selectedParticipants[$index])) {
            unset($this->selectedParticipants[$index]);
            $this->selectedParticipants = array_values($this->selectedParticipants);
        }
    }

    public function getParticipantsProperty()
    {
        return Participant::orderBy('name')->get();
    }

    public function updatedSearchParticipant()
    {
        $this->searchResults = Participant::where('name', 'like', '%' . $this->searchParticipant . '%')
            ->orderBy('name')
            ->take(5)
            ->get();
    }

    public function getFirstSuggestion($query)
    {
        $participant = Participant::where('name', 'like', $query . '%')->first();
        return $participant ? $participant->name : '';
    }

    public function addParticipantFromInput($name)
    {
        if (empty(trim($name))) {
            return;
        }

        $participant = Participant::firstOrCreate([
            'name' => trim($name),
        ]);

        // Kalau sudah ada di selectedParticipants → kasih error
        if (in_array($participant->id, $this->selectedParticipants)) {
            $this->addError('participant_error', 'Partisipan ini sudah ada.');
            return;
        }

        $this->resetErrorBag('participant_error'); // clear error kalau berhasil

        $this->selectedParticipants[] = $participant->id;
        $this->newParticipantName = '';

        $this->participants = Participant::orderBy('name')->get();
    }

    private function resetSearch()
    {
        $this->searchParticipant = '';
        $this->searchResults = [];
    }

    public function addParticipantFromSearch($id)
    {
        if (!in_array($id, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $id;
        }
        $this->reset(['searchParticipant', 'searchResults']);
    }

    protected function attachParticipant($participant)
    {
        if ($this->selectedEvent) {
            $this->selectedEvent->participants()->syncWithoutDetaching([$participant->id]);
            $this->selectedEvent->refresh();
            $this->eventParticipants = $this->selectedEvent->participants->toArray();
        } else {
            $exists = collect($this->eventParticipants)->contains(fn($p) => $p['id'] == $participant->id);
            if (!$exists) {
                $this->eventParticipants[] = ['id' => $participant->id, 'name' => $participant->name];
            }
        }
    }

    // Statistics
    public function getStatsProperty()
    {
        $today = now()->format('Y-m-d');
        $thisMonthStart = now()->startOfMonth()->format('Y-m-d');
        $thisMonthEnd = now()->endOfMonth()->format('Y-m-d');

        return [
            'total' => Event::count(),
            'today' => Event::whereDate('start_date', $today)->count(),
            'thisMonth' => Event::whereBetween('start_date', [$thisMonthStart, $thisMonthEnd])->count(),
            'upcoming' => Event::where('start_date', '>', $today)->count(),
        ];
    }

    // Calendar data
    public function getCalendarDataProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $events = Event::with('participants')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $eventsByDate = $events->groupBy(function ($event) {
            return Carbon::parse($event->start_date)->format('Y-m-d');
        });

        return [
            'events' => $eventsByDate,
            'periodLabel' => $this->generatePeriodLabel(),
        ];
    }

    private function getDateRange()
    {
        switch ($this->calendarView) {
            case 'month':
                $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
                $endOfMonth = $startOfMonth->copy()->endOfMonth();
                return [
                    $startOfMonth->copy()->startOfWeek()->format('Y-m-d'),
                    $endOfMonth->copy()->endOfWeek()->format('Y-m-d')
                ];

            case 'week':
                $weekStart = Carbon::parse($this->currentDate)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                return [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')];

            case 'day':
                return [$this->currentDate, $this->currentDate];

            default:
                return [now()->format('Y-m-d'), now()->format('Y-m-d')];
        }
    }

    private function generatePeriodLabel()
    {
        $carbonDate = Carbon::parse($this->currentDate);

        switch ($this->calendarView) {
            case 'month':
                return $carbonDate->translatedFormat('F Y');

            case 'week':
                $weekStart = $carbonDate->startOfWeek();
                $weekEnd = $carbonDate->copy()->endOfWeek();
                return $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M Y');

            case 'day':
                return $carbonDate->translatedFormat('l, d F Y');

            default:
                return '';
        }
    }

    public function render()
    {
        return view('livewire.calendar-admin', [
            'stats' => $this->stats,
            'calendarData' => $this->calendarData,
            'periodLabel' => $this->calendarData['periodLabel'],
            'participants' => $this->participants,
        ]);
    }
}