<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Event;
use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class Tasks extends Component
{
    /* ===== Sidebar: Mini Calendar & Date ===== */
    public int $miniCalendarMonth;
    public int $miniCalendarYear;
    public string $currentDate;

    /* ===== Sidebar: Participants ===== */
    /** @var \Illuminate\Support\Collection */
    public $participants;                 // Eloquent collection untuk sidebar
    public string $participantName = '';  // input tambah
    public ?int $editingParticipantId = null;
    public string $editingParticipantName = '';

    /* ===== Listing & state ===== */
    public $tasks = [];
    public $pastTasks = [];
    public $soonTasks = [];
    public $completedTasks = [];
    public $flashMessage = ''; // tetap ada kalau suatu saat butuh flash awal
    public $sortBy = 'manual';

    /* ===== Inline edit ===== */
    public $editingTaskId = null;
    public $editingTitle = '';
    public $editingDescription = '';

    /* ===== Per-task schedule map ===== */
    public $taskData = [];   // [id => ['start_date','start_time','end_date','end_time']]
    public $allDay = [];     // [id => bool]

    /* ===== Create Event modal state ===== */
    public $showCreateModal = false;
    public $title = '';
    public $description = '';
    public $startDate;
    public $startTime;
    public $endDate;
    public $endTime;
    public $allDayEvent = false;

    /* ===== Partisipan untuk MODAL ===== */
    public string $searchParticipant = '';
    public array $selectedParticipants = []; // list id peserta terpilih
    public array $searchResults = [];

    public function getFirstSuggestionForCard(string $query): string
    {
        $p = Participant::where('name', 'like', $query.'%')
            ->orderBy('name')
            ->first();

        return $p ? $p->name : '';
    }

    public function attachParticipant(int $taskId, string $name): void
    {
        // Normalisasi: trim, satukan spasi, case-insensitive compare
        $norm = trim(preg_replace('/\s+/', ' ', $name));
        if ($norm === '') return;

        $event = Event::find($taskId);
        if (!$event) return;

        // Cari existing tanpa peduli kapitalisasi
        $existing = Participant::whereRaw('LOWER(name) = ?', [mb_strtolower($norm)])->first();
        $participant = $existing ?: Participant::create(['name' => $norm]);

        // Pasang tanpa menduplikasi relasi
        $event->participants()->syncWithoutDetaching([$participant->id]);

        // Pastikan sidebar langsung mutakhir
        $this->reloadParticipants();

        // Notifikasi
        $this->dispatch('toast', type:'success', title:'Partisipan Ditambahkan', text:'Partisipan ditambahkan ke tugas.');
    }

    public function detachParticipant(int $taskId, int $participantId): void
    {
        if ($event = Event::find($taskId)) {
            $event->participants()->detach($participantId);

            // Segarkan sidebar juga
            $this->reloadParticipants();

            // Notifikasi
            $this->dispatch('toast', type:'info', title:'Partisipan Dihapus', text:'Partisipan dihapus dari tugas.');
        }
    }

    /* ===================== Helpers waktu ===================== */
    public function getTimeOptions()
    {
        $times = [];
        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 15) {
                $times[] = sprintf('%02d:%02d', $h, $m);
            }
        }
        return $times;
    }

    private function nearestThirty($time = null)
    {
        $t = $time ? Carbon::parse($time) : now();
        $min = (int) $t->minute;
        if ($min < 15) { $t->minute(0)->second(0); }
        elseif ($min < 45) { $t->minute(30)->second(0); }
        else { $t->addHour()->minute(0)->second(0); }
        return $t->format('H:i');
    }

    /* ===================== Modal Create ===================== */
    public function openCreateModal()
    {
        $this->reset(['title', 'description', 'allDayEvent']);
        $this->startDate = now()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');

        $this->startTime = $this->nearestThirty();
        $this->endTime   = Carbon::createFromFormat('H:i', $this->startTime)->addHour()->format('H:i');

        // reset state partisipan MODAL
        $this->resetErrorBag();
        $this->searchParticipant = '';
        $this->selectedParticipants = [];
        $this->searchResults = [];

        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    public function createEvent()
    {
        $this->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate'   => 'required|date',
            'startTime'   => $this->allDayEvent ? 'nullable' : 'required|date_format:H:i',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'endTime'     => $this->allDayEvent ? 'nullable' : 'required|date_format:H:i',
        ]);

        $startTime = $this->allDayEvent ? '00:00:00' : $this->startTime . ':00';
        $endTime   = $this->allDayEvent ? '23:59:59' : $this->endTime . ':00';

        $event = Event::create([
            'title'         => $this->title,
            'description'   => $this->description,
            'start_date'    => $this->startDate,
            'start_time'    => $startTime,
            'end_date'      => $this->endDate,
            'end_time'      => $endTime,
            'all_day'       => (bool) $this->allDayEvent,
            'is_completed'  => false,
            'completed_at'  => null,
        ]);

        // attach partisipan yang dipilih di MODAL
        if (!empty($this->selectedParticipants)) {
            $event->participants()->attach($this->selectedParticipants);
        }

        $this->showCreateModal = false;
        $this->reset([
            'title','description','startDate','startTime','endDate','endTime','allDayEvent',
            'searchParticipant','selectedParticipants','searchResults'
        ]);

        // Notifikasi
        $this->dispatch('toast', type:'success', title:'Berhasil Dibuat', text:'Acara telah berhasil dibuat.');

        $this->loadTasks();
    }

    /* ===================== Lifecycle ===================== */
    public function mount()
    {
        $today = Carbon::now();
        $this->miniCalendarMonth = (int) $today->format('m');
        $this->miniCalendarYear  = (int) $today->format('Y');
        $this->currentDate       = $today->toDateString();

        $this->reloadParticipants(); // sidebar
        $this->loadTasks();
    }

    private function reloadParticipants(): void
    {
        $this->participants = Participant::orderBy('name')->get();
    }

    /* ===================== Sorting & Load Tasks ===================== */
    public function setSort($sort)
    {
        $this->sortBy = $sort;
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $base = Event::query();

        $activeQuery = (clone $base)->when(
            Schema::hasColumn('events', 'is_completed'),
            fn($q) => $q->where('is_completed', false)
        );

        switch ($this->sortBy) {
            case 'date':
                $activeQuery->orderBy('start_date', 'asc')->orderBy('start_time', 'asc');
                break;
            case 'starred':
                $activeQuery->orderByDesc('is_starred')->orderBy('updated_at', 'desc');
                break;
            case 'title':
                $activeQuery->orderBy('title', 'asc');
                break;
            case 'manual':
            default:
                $activeQuery->orderByDesc('created_at');
                break;
        }

        $events = $activeQuery->get();

        $this->completedTasks = Schema::hasColumn('events', 'is_completed')
            ? (clone $base)->where('is_completed', true)->orderByDesc('completed_at')->get()
            : collect();

        $allTasks = $events->map(function ($event) {
            $end = optional($event->end_date_time);
            $isOverdue = $end?->lt(now());

            return (object) [
                'id'               => $event->id,
                'title'            => $event->title,
                'description'      => $event->description ?? '',
                'formatted_date'   => optional($event->start_date_time)?->format('j F Y, H:i') ?? '-',
                'is_starred'       => (bool) ($event->is_starred ?? false),
                'is_overdue'       => $isOverdue,
                'overdue_text'     => $isOverdue ? $end->diffForHumans(null, false) : null,
                'start_date_time'  => optional($event->start_date_time)?->toDateTimeString(),
                'end_date_time'    => optional($event->end_date_time)?->toDateTimeString(),
            ];
        });

        if ($this->sortBy === 'date') {
            $this->soonTasks = $allTasks->filter(fn ($t) =>
                isset($t->end_date_time) && Carbon::parse($t->end_date_time)->gte(now())
            )->values();

            $this->pastTasks = $allTasks->filter(fn ($t) =>
                isset($t->end_date_time) && Carbon::parse($t->end_date_time)->lt(now())
            )->values();

            $this->tasks = [];
        } elseif ($this->sortBy === 'starred') {
            $this->soonTasks = $allTasks->filter(fn ($t) => $t->is_starred)->values();
            $this->pastTasks = $allTasks->filter(fn ($t) => ! $t->is_starred)->values();
            $this->tasks = [];
        } else {
            $this->tasks = $allTasks;
            $this->soonTasks = [];
            $this->pastTasks = [];
        }

        $this->taskData = $allTasks->mapWithKeys(function ($task) {
            return [$task->id => [
                'start_date' => $task->start_date_time ? Carbon::parse($task->start_date_time)->format('Y-m-d') : '',
                'start_time' => $task->start_date_time ? Carbon::parse($task->start_date_time)->format('H:i') : '',
                'end_date'   => $task->end_date_time ? Carbon::parse($task->end_date_time)->format('Y-m-d') : '',
                'end_time'   => $task->end_date_time ? Carbon::parse($task->end_date_time)->format('H:i') : '',
            ]];
        })->toArray();

        $this->allDay = $events->mapWithKeys(fn ($e) => [$e->id => (bool) $e->all_day])->toArray();
    }

    /* ===================== Actions (aktif <-> completed) ===================== */
    public function toggleStar($id)
    {
        if ($event = Event::find($id)) {
            $event->is_starred = ! $event->is_starred;
            $event->save();

            $this->dispatch('toast', type:'success', title:'Status Bintang Diubah', text:'Tugas diperbarui.');
            $this->loadTasks();
        }
    }

    public function markAsDone($id)
    {
        if ($event = Event::find($id)) {
            $event->is_completed = true;
            $event->completed_at = now();
            $event->save();

            $this->dispatch('toast', type:'success', title:'Tugas Selesai', text:'Tugas telah ditandai selesai.');
            $this->loadTasks();
        }
    }

    public function deleteTask($id)
    {
        if ($event = Event::find($id)) {
            $event->is_completed = true;
            $event->completed_at = now();
            $event->save();

            $this->dispatch('toast', type:'info', title:'Dipindahkan', text:'Tugas dipindahkan ke bagian Selesai.');
            $this->loadTasks();
        }
    }

    public function destroyTask($id)
    {
        Event::destroy($id);

        $this->dispatch('toast', type:'success', title:'Berhasil Dihapus', text:'Tugas dihapus secara permanen.');
        $this->loadTasks();
    }

    public function clearCompleted()
    {
        Event::where('is_completed', true)->delete();

        $this->dispatch('toast', type:'success', title:'Bersih!', text:'Semua tugas selesai telah dihapus.');
        $this->loadTasks();
    }

    public function restoreTask($id)
    {
        if ($event = Event::find($id)) {
            $event->is_completed = false;
            $event->completed_at = null;
            $event->save();

            $this->dispatch('toast', type:'success', title:'Dipulihkan', text:'Tugas berhasil dipulihkan.');
            $this->loadTasks();
        }
    }

    /* ===================== Inline edit ===================== */
    public function startEditing($id)
    {
        if ($task = Event::find($id)) {
            $this->editingTaskId      = $task->id;
            $this->editingTitle       = $task->title;
            $this->editingDescription = $task->description ?? '';
        }
    }

    public function saveEditing()
    {
        if ($task = Event::find($this->editingTaskId)) {
            $task->title       = trim($this->editingTitle);
            $task->description = trim($this->editingDescription);
            $task->save();

            $this->dispatch('toast', type:'success', title:'Berhasil Diedit', text:'Perubahan telah disimpan.');
        }

        $this->editingTaskId = null;
        $this->editingTitle = '';
        $this->editingDescription = '';

        $this->loadTasks();
    }

    public function cancelEditing()
    {
        $this->editingTaskId = null;
        $this->editingTitle = '';
        $this->editingDescription = '';
    }

    /* ===================== Update schedule pada kartu ===================== */
    public function updateSchedule($taskId, $startDate, $startTime, $endDate, $endTime)
    {
        if ($task = Event::find($taskId)) {
            $isAllDay = (bool) ($this->allDay[$taskId] ?? false);

            $task->start_date = $startDate;
            $task->end_date   = $endDate;

            if ($isAllDay) {
                $task->start_time = '00:00:00';
                $task->end_time   = '23:59:59';
            } else {
                $task->start_time = $startTime . ':00';
                $task->end_time   = $endTime . ':00';
            }

            $task->all_day = $isAllDay;
            $task->save();

            $this->dispatch('toast', type:'success', title:'Jadwal Diperbarui', text:'Waktu tugas berhasil diubah.');
            $this->loadTasks();
        }
    }

    /* ===================== Participants (SIDEBAR) ===================== */
    public function addParticipant(): void
    {
        $name = trim($this->participantName);
        if ($name === '') return;

        Participant::firstOrCreate(['name' => ucfirst(strtolower($name))]);
        $this->participantName = '';
        $this->reloadParticipants();
    }

    public function editParticipant(int $id): void
    {
        $this->editingParticipantId = $id;
        $this->editingParticipantName = Participant::find($id)?->name ?? '';
    }

    public function updateParticipant(): void
    {
        if (!$this->editingParticipantId) return;
        $name = trim($this->editingParticipantName);
        if ($name === '') { $this->cancelEditParticipant(); return; }

        if ($p = Participant::find($this->editingParticipantId)) {
            $p->update(['name' => $name]);
        }
        $this->cancelEditParticipant();
        $this->reloadParticipants();
    }

    public function cancelEditParticipant(): void
    {
        $this->editingParticipantId = null;
        $this->editingParticipantName = '';
    }

    public function deleteParticipant(int $id): void
    {
        if ($p = Participant::find($id)) {
            $p->delete();
            $this->reloadParticipants();
        }
    }

    /* ===== Participants (MODAL) – dipanggil dari Blade ===== */
    public function getFirstSuggestion($query)
    {
        $p = Participant::where('name', 'like', $query.'%')->orderBy('name')->first();
        return $p ? $p->name : '';
    }

    public function addParticipantFromInput($name)
    {
        $name = trim($name);
        if ($name === '') return;

        $p = Participant::firstOrCreate(['name' => $name]);
        if (!in_array($p->id, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $p->id;
        }
        $this->searchParticipant = '';
        $this->searchResults = [];
    }

    public function addSelectedParticipant($participantId)
    {
        $participantId = (int) $participantId;
        if ($participantId && !in_array($participantId, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $participantId;
        }
        $this->searchParticipant = '';
        $this->searchResults = [];
    }

    public function removeSelectedParticipant($index)
    {
        if (isset($this->selectedParticipants[$index])) {
            unset($this->selectedParticipants[$index]);
            $this->selectedParticipants = array_values($this->selectedParticipants);
        }
    }

    public function updatedSearchParticipant()
    {
        if (trim($this->searchParticipant) === '') {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Participant::where('name', 'like', '%'.$this->searchParticipant.'%')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
            ->toArray();
    }

    /* ===================== Mini Calendar (sidebar) ===================== */
    public function prevMiniMonth(): void
    {
        $d = Carbon::create($this->miniCalendarYear, $this->miniCalendarMonth, 1)->subMonth();
        $this->miniCalendarYear  = (int) $d->format('Y');
        $this->miniCalendarMonth = (int) $d->format('m');
    }

    public function nextMiniMonth(): void
    {
        $d = Carbon::create($this->miniCalendarYear, $this->miniCalendarMonth, 1)->addMonth();
        $this->miniCalendarYear  = (int) $d->format('Y');
        $this->miniCalendarMonth = (int) $d->format('m');
    }

    public function selectMiniCalendarDate(string $date): void
    {
        $this->currentDate = $date;
    }

    /* ===================== Render ===================== */
    public function render()
    {
        return view('livewire.settings.tasks', [
            'tasks'               => $this->tasks,
            'soonTasks'           => $this->soonTasks,
            'pastTasks'           => $this->pastTasks,
            'completedTasks'      => $this->completedTasks,
            'sortBy'              => $this->sortBy,
            // 'stats'             => $this->getStats(), // tidak perlu di halaman Tasks

            'taskData'            => $this->taskData,
            'allDay'              => $this->allDay,
            'showCreateModal'     => $this->showCreateModal,

            // untuk partial sidebar
            'miniCalendarYear'    => $this->miniCalendarYear,
            'miniCalendarMonth'   => $this->miniCalendarMonth,
            'currentDate'         => $this->currentDate,
            'participants'        => $this->participants,
            'editingParticipantId'=> $this->editingParticipantId,
            'editingParticipantName' => $this->editingParticipantName,

            // turn OFF these sections on Tasks sidebar
            'showMiniCalendar'    => false,
            'showStats'           => false,
        ]);
    }

    protected function getStats()
    {
        return [
            'total'     => Event::count(),
            'today'     => Event::whereDate('start_date', today())->count(),
            'thisMonth' => Event::whereMonth('start_date', today()->month)
                                ->whereYear('start_date', today()->year)
                                ->count(),
            'upcoming'  => Event::where(function ($q) {
                $q->where('start_date', '>', today())
                  ->orWhere(function ($q2) {
                      $q2->whereDate('start_date', today())
                         ->whereTime('start_time', '>=', now()->format('H:i:s'));
                  });
            })->count(),
        ];
    }
}
