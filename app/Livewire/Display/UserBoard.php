<?php

namespace App\Livewire\Display;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Collection;

class UserBoard extends Component
{
    public array $currentEvents = [];   // sekarang berlangsung
    public array $upcomingToday = [];   // sisa acara hari ini (start >= now)
    public array $nextEvents    = [];   

    public int $windowPastDays = 7;
    public int $windowNextDays = 30;

    protected function fetchActiveEvents(): Collection
    {
        $now = now();

        return Event::query()
            ->with(['participants:id,name'])
            ->where('is_completed', false)
            ->when($this->windowPastDays > 0, fn ($q) =>
                $q->whereDate('end_date', '>=', $now->clone()->subDays($this->windowPastDays)->toDateString())
            )
            ->when($this->windowNextDays > 0, fn ($q) =>
                $q->whereDate('start_date', '<=', $now->clone()->addDays($this->windowNextDays)->toDateString())
            )
            ->get()
            ->filter(fn ($e) =>
                $e->start_date_time && $e->end_date_time && $e->end_date_time->gte($e->start_date_time)
            );
    }

    private function fmt($date): ?string
    {
        if (!$date) return null;
        $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $d = (int) $date->format('d');
        $m = (int) $date->format('n');
        $y = $date->format('Y');
        $hm= $date->format('H:i');
        return sprintf('%02d %s %s %s', $d, $bulan[$m], $y, $hm);
    }

    protected function mapForView(Collection $events): array
    {
        return $events->map(function ($e) {
            return [
                'id'          => $e->id,
                'title'       => $e->title,
                'description' => $e->description ?: null,
                'start'       => $this->fmt($e->start_date_time),
                'end'         => $this->fmt($e->end_date_time),
                'location'    => $e->location ?? null,
                'raw_start'   => $e->start_date_time,
                'raw_end'     => $e->end_date_time,
                'participants'       => $e->participants?->pluck('name')->values()->all() ?? [],
                'participants_count' => $e->participants?->count() ?? 0,
            ];
        })->values()->all();
    }

    protected function loadData(): void
    {
        $now    = now();
        $events = $this->fetchActiveEvents();

        // ===== CURRENT: yang sedang berlangsung (past disaring)
        $current = $events->filter(
            fn ($e) => $e->start_date_time->lte($now) && $e->end_date_time->gt($now)
        )->sortBy('start_date_time');
        $this->currentEvents = $this->mapForView($current);

$upcoming = $events->filter(function ($e) use ($now) {
        return $e->start_date_time->isSameDay($now)
            && $e->start_date_time->gte($now); // belum mulai
    })
    ->sortBy(fn ($e) => [
        $e->end_date_time->timestamp,
        $e->start_date_time->timestamp,
        mb_strtolower($e->title ?? ''),
    ]);

$this->upcomingToday = $this->mapForView($upcoming);


        // (opsional) NEXT batch paling dekat setelah sekarang (tidak dipakai UI utama)
        $firstStart = $events->filter(fn ($e) => $e->start_date_time->gt($now))->min('start_date_time');
        $nextBatch = collect();
        if ($firstStart) {
            $nextBatch = $events->filter(fn ($e) => $e->start_date_time->equalTo($firstStart))
                                ->sortBy('title');
        }
        $this->nextEvents = $this->mapForView($nextBatch);
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function render()
    {
        $this->loadData();

        return view('livewire.display.user-board')->title('Display | Agenda App');
    }
}
