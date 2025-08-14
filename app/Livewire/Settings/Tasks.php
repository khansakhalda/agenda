<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class Tasks extends Component
{
    // Listing & state
    public $tasks = [];
    public $pastTasks = [];
    public $soonTasks = [];
    public $completedTasks = [];
    public $flashMessage = '';
    public $sortBy = 'manual';

    // Inline edit
    public $editingTaskId = null;
    public $editingTitle = '';
    public $editingDescription = '';

    // Per-task schedule map (untuk input di kartu)
    public $taskData = [];   // [id => ['start_date','start_time','end_date','end_time']]
    public $allDay = [];     // [id => bool]

    // Create Event modal state
    public $showCreateModal = false;
    public $title = '';
    public $description = '';
    public $startDate;
    public $startTime;
    public $endDate;
    public $endTime;
    public $allDayEvent = false;

    /* ---------- Helpers waktu ---------- */

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

    /* ---------- Modal Create ---------- */

    public function openCreateModal()
    {
        // Default
        $this->reset(['title', 'description', 'allDayEvent']);
        $this->startDate = now()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');

        $this->startTime = $this->nearestThirty();
        $this->endTime   = Carbon::createFromFormat('H:i', $this->startTime)->addHour()->format('H:i');

        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    public function createEvent()
    {
        // Validasi input
        $this->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate'   => 'required|date',
            'startTime'   => $this->allDayEvent ? 'nullable' : 'required|date_format:H:i',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'endTime'     => $this->allDayEvent ? 'nullable' : 'required|date_format:H:i',
        ]);

        // Simpan ke DB (kolom tanggal & jam terpisah)
        $startTime = $this->allDayEvent ? '00:00:00' : $this->startTime . ':00';
        $endTime   = $this->allDayEvent ? '23:59:59' : $this->endTime . ':00';

        Event::create([
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

        // Tutup modal & reset form
        $this->showCreateModal = false;
        $this->reset(['title','description','startDate','startTime','endDate','endTime','allDayEvent']);

        $this->flashMessage = 'Event created successfully!';
        $this->loadTasks();
    }

    /* ---------- Lifecycle & Sorting ---------- */

    public function mount()
    {
        $this->loadTasks();
    }

    public function setSort($sort)
    {
        $this->sortBy = $sort;
        $this->loadTasks();
    }

    public function loadTasks()
    {
        // Ambil semua event
        $base = Event::query();

        // Daftar aktif (belum completed)
        $activeQuery = (clone $base)->when(
            Schema::hasColumn('events', 'is_completed'),
            fn($q) => $q->where('is_completed', false)
        );

        // SORTING — gunakan kolom nyata (bukan accessor)
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

        // Completed
        $this->completedTasks = Schema::hasColumn('events', 'is_completed')
            ? (clone $base)
                ->where('is_completed', true)
                ->orderByDesc('completed_at')
                ->get()
                ->map(function ($event) {
                    return (object) [
                        'id'               => $event->id,
                        'title'            => $event->title,
                        'description'      => $event->description ?? '',
                        'completed_at'     => optional($event->completed_at)?->toDateTimeString(),
                        'completed_human'  => optional($event->completed_at)?->diffForHumans(),
                    ];
                })
            : collect();

        // Map daftar aktif → objek kartu (pakai accessor start_date_time / end_date_time)
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

        // Kategorisasi task aktif
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

        // Mapping taskData & allDay (untuk inline schedule)
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

    /* ---------- Actions (aktif <-> completed) ---------- */

    public function toggleStar($id)
    {
        if ($event = Event::find($id)) {
            $event->is_starred = ! $event->is_starred;
            $event->save();

            $this->flashMessage = 'Star status updated!';
            $this->loadTasks();
        }
    }

    // centang kiri pada kartu aktif → Completed (jangan destroy)
    public function markAsDone($id)
    {
        if ($event = Event::find($id)) {
            $event->is_completed = true;
            $event->completed_at = now();
            $event->save();

            $this->flashMessage = 'Task completed!';
            $this->loadTasks();
        }
    }

    // ikon tong sampah pada kartu aktif → Completed juga
    public function deleteTask($id)
    {
        if ($event = Event::find($id)) {
            $event->is_completed = true;
            $event->completed_at = now();
            $event->save();

            $this->flashMessage = 'Task moved to Completed!';
            $this->loadTasks();
        }
    }

    // tombol trash pada kartu Completed → hapus permanen
    public function destroyTask($id)
    {
        Event::destroy($id);
        $this->flashMessage = 'Task permanently deleted!';
        $this->loadTasks();
    }

    // tombol Clear all di bagian Completed
    public function clearCompleted()
    {
        Event::where('is_completed', true)->delete();
        $this->flashMessage = 'All completed tasks deleted!';
        $this->loadTasks();
    }

    // tombol centang di Completed → kembalikan ke aktif
    public function restoreTask($id)
    {
        if ($event = Event::find($id)) {
            $event->is_completed = false;
            $event->completed_at = null;
            $event->save();

            $this->flashMessage = 'Task restored!';
            $this->loadTasks();
        }
    }

    /* ---------- Inline edit ---------- */

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

            $this->flashMessage = 'Task updated!';
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

    /* ---------- Update schedule pada kartu ---------- */

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

            $this->flashMessage = 'Task schedule updated!';
            $this->loadTasks();
        }
    }

    public function updatedEditingTitle($value) {}
    public function updatedEditingDescription($value) {}

    /* ---------- Render ---------- */

    public function render()
    {
        return view('livewire.settings.tasks', [
            'tasks'           => $this->tasks,
            'soonTasks'       => $this->soonTasks,
            'pastTasks'       => $this->pastTasks,
            'completedTasks'  => $this->completedTasks,
            'sortBy'          => $this->sortBy,
            'stats'           => $this->getStats(),
            'taskData'        => $this->taskData,
            'allDay'          => $this->allDay,
            'showCreateModal' => $this->showCreateModal,
        ]);
    }

    protected function getStats()
    {
        // Semua pakai kolom asli (bukan accessor)
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
