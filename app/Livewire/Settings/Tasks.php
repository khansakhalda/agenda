<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Task;
use App\Models\Participant;

class Tasks extends Component
{
    /* ===== Sidebar: Participants ===== */
    /** @var \Illuminate\Support\Collection */
    public $participants;
    public string $participantName = '';
    public ?int $editingParticipantId = null;
    public string $editingParticipantName = '';

    /* ===== Listing & state ===== */
    public $tasks = [];
    public $completedTasks = [];
    public $flashMessage = '';
    public $sortBy = 'manual'; // 'manual' | 'title' | 'starred'

    /* ===== Inline edit ===== */
    public $editingTaskId = null;
    public $editingTitle = '';
    public $editingDescription = '';

    /* ===== Modal Buat Tugas ===== */
    public bool $showCreateModal = false;
    public array $newTask = [
        'title' => '',
        'description' => '',
        'participants' => [], // array of ['id'=>..,'name'=>..]
    ];

    /* ===================== Lifecycle ===================== */
    public function mount(): void
    {
        $this->showCreateModal = false;
        $this->reloadParticipants();
        $this->loadTasks();
    }

    /* ===================== Load & Sort ===================== */
    public function setSort(string $sort): void
    {
        $this->sortBy = $sort;
        $this->loadTasks();
    }

public function loadTasks(): void
{
    $q = Task::query()->where('is_completed', false);

    switch ($this->sortBy) {
        case 'title':
            $q->orderBy('title');
            break;
        case 'starred':
            $q->orderByDesc('is_starred')->orderByDesc('updated_at');
            break;
        case 'manual':
        default:
            $q->orderByDesc('created_at');
    }

    // (boleh tanpa with di daftar tugas aktif)
    $this->tasks = $q->get();

    // Eager load participants untuk tugas selesai
    $this->completedTasks = Task::with('participants')
        ->where('is_completed', true)
        ->orderByDesc('completed_at')
        ->get();
}


    /* ===================== Modal Buat Tugas ===================== */
    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->newTask = ['title' => '', 'description' => '', 'participants' => []];
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    /** Autocomplete partisipan: dipakai dropdown di modal tugas */
    public function searchCalendarParticipants(string $term): array
    {
        $term = trim($term);
        if ($term === '') return [];

        return Participant::where('name', 'like', "%{$term}%")
            ->orderBy('name')
            ->limit(8)
            ->get(['id','name'])
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
            ->toArray();
    }

    /** Ghost suggestion pertama: dipakai ketika user mengetik (TAB untuk accept) */
    public function getFirstSuggestion(string $query): string
    {
        $query = trim($query);
        if ($query === '') return '';

        $p = Participant::where('name', 'like', $query.'%')
            ->orderBy('name')
            ->first();

        return $p ? $p->name : '';
    }

    /** Simpan tugas baru */
    public function createTask(): void
    {
        $data = $this->validate([
            'newTask.title'       => 'required|string|max:255',
            'newTask.description' => 'nullable|string',
            'newTask.participants'=> 'array',
        ])['newTask'];

        $task = Task::create([
            'title'        => trim($data['title']),
            'description'  => trim($data['description'] ?? ''),
            'is_starred'   => false,
            'is_completed' => false,
            'completed_at' => null,
        ]);

        // relasi partisipan
        $ids = [];
        foreach ($data['participants'] ?? [] as $p) {
            if (isset($p['id'])) {
                $ids[] = (int) $p['id'];
            } elseif (!empty($p['name'])) {
                $pp = Participant::firstOrCreate(['name' => trim($p['name'])]);
                $ids[] = $pp->id;
            }
        }
        if ($ids) {
            $task->participants()->sync($ids);
        }

        $this->reloadParticipants();

        $this->showCreateModal = false;
        $this->newTask = ['title' => '', 'description' => '', 'participants' => []];

        $this->dispatch('toast', type:'success', title:'Berhasil', text:'Tugas baru dibuat.');
        $this->loadTasks();
    }

    /* ===================== Partisipan di kartu ===================== */
    public function getFirstSuggestionForCard(string $query): string
    {
        $p = Participant::where('name', 'like', $query.'%')->orderBy('name')->first();
        return $p ? $p->name : '';
    }

    public function attachParticipant(int $taskId, string $name): void
    {
        $name = trim($name);
        if ($name === '') return;

        $task = Task::find($taskId);
        if (!$task) return;

        $p = Participant::firstOrCreate(['name' => $name]);
        $task->participants()->syncWithoutDetaching([$p->id]);
        $this->reloadParticipants();

        $this->dispatch('toast', type:'success', title:'Partisipan Ditambahkan', text:'Ditambahkan ke tugas.');
    }

    public function detachParticipant(int $taskId, int $participantId): void
    {
        if ($task = Task::find($taskId)) {
            $task->participants()->detach($participantId);
            $this->reloadParticipants();
            $this->dispatch('toast', type:'info', title:'Partisipan Dihapus', text:'Dihapus dari tugas.');
        }
    }

    /* ===================== Actions tugas ===================== */
    public function toggleStar(int $id): void
    {
        if ($t = Task::find($id)) {
            $t->is_starred = !$t->is_starred;
            $t->save();
            $this->dispatch('toast', type:'success', title:'Diperbarui', text:'Status bintang diubah.');
            $this->loadTasks();
        }
    }

    public function markAsDone(int $id): void
    {
        if ($t = Task::find($id)) {
            $t->is_completed = true;
            $t->completed_at = now();
            $t->save();
            $this->dispatch('toast', type:'success', title:'Tugas Selesai', text:'Tugas ditandai selesai.');
            $this->loadTasks();
        }
    }

    public function deleteTask(int $id): void
    {
        $this->markAsDone($id);
    }

    public function destroyTask(int $id): void
    {
        Task::destroy($id);
        $this->dispatch('toast', type:'success', title:'Dihapus', text:'Tugas dihapus permanen.');
        $this->loadTasks();
    }

    public function clearCompleted(): void
    {
        Task::where('is_completed', true)->delete();
        $this->dispatch('toast', type:'success', title:'Bersih', text:'Tugas selesai dihapus.');
        $this->loadTasks();
    }

    public function restoreTask(int $id): void
    {
        if ($t = Task::find($id)) {
            $t->is_completed = false;
            $t->completed_at = null;
            $t->save();
            $this->dispatch('toast', type:'success', title:'Dipulihkan', text:'Tugas dipulihkan.');
            $this->loadTasks();
        }
    }

    /* ===================== Inline edit ===================== */
    public function startEditing(int $id): void
    {
        if ($t = Task::find($id)) {
            $this->editingTaskId      = $t->id;
            $this->editingTitle       = $t->title;
            $this->editingDescription = $t->description ?? '';
        }
    }

    public function saveEditing(): void
    {
        if ($t = Task::find($this->editingTaskId)) {
            $t->title = trim($this->editingTitle);
            $t->description = trim($this->editingDescription);
            $t->save();
            $this->dispatch('toast', type:'success', title:'Tersimpan', text:'Perubahan disimpan.');
        }

        $this->editingTaskId = null;
        $this->editingTitle = '';
        $this->editingDescription = '';
        $this->loadTasks();
    }

    public function cancelEditing(): void
    {
        $this->editingTaskId = null;
        $this->editingTitle = '';
        $this->editingDescription = '';
    }

    /* ===================== Participants (Sidebar) ===================== */
    public function reloadParticipants(): void
    {
        $this->participants = Participant::orderBy('name')->get();
    }

// Tambah partisipan dari sidebar
public function addParticipant(): void
{
    $name = trim($this->participantName);
    if ($name === '') return;

    Participant::firstOrCreate(['name' => $name]);
    $this->participantName = '';
    $this->reloadParticipants();

    // >>> toast sukses
    $this->dispatch('toast',
        type: 'success',
        title: 'Partisipan Ditambahkan',
        text: 'Partisipan baru berhasil disimpan.'
    );
}

// Masuk mode edit tetap sama
public function editParticipant(int $id): void
{
    $this->editingParticipantId = $id;
    $this->editingParticipantName = Participant::find($id)?->name ?? '';
}

// Simpan perubahan nama partisipan
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

    // >>> toast sukses
    $this->dispatch('toast',
        type: 'success',
        title: 'Perubahan Disimpan',
        text: 'Nama partisipan berhasil diperbarui.'
    );
}

    public function cancelEditParticipant(): void
    {
        $this->editingParticipantId = null;
        $this->editingParticipantName = '';
    }

// Hapus partisipan
public function deleteParticipant(int $id): void
{
    if ($p = Participant::find($id)) {
        $p->delete();
        $this->reloadParticipants();

        // >>> toast info/sukses
        $this->dispatch('toast',
            type: 'success',
            title: 'Partisipan Dihapus',
            text: 'Data partisipan telah dihapus.'
        );
    }
}

    /* ===================== Render ===================== */
    public function render()
    {
        return view('livewire.settings.tasks', [
            'tasks'               => $this->tasks,
            'completedTasks'      => $this->completedTasks,
            'sortBy'              => $this->sortBy,
            'showCreateModal'     => $this->showCreateModal,
            'participants'        => $this->participants,
            'editingParticipantId'=> $this->editingParticipantId,
            'editingParticipantName' => $this->editingParticipantName,
            'showMiniCalendar'    => false,
            'showStats'           => false,
        ])->title('Tugas | Agenda App');
    }
}
