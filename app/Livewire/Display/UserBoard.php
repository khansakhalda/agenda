<?php

namespace App\Livewire\Display;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Collection;

class UserBoard extends Component
{
    // batches
    public array $previousEvents = [];
    public array $currentEvents  = [];
    public array $nextEvents     = [];

    // window query (opsional)
    public int $windowPastDays = 7;
    public int $windowNextDays = 30;

    protected function fetchActiveEvents(): Collection
    {
        $now = now();

        return Event::query()
            ->with(['participants:id,name']) // <<-- eager load peserta (anti N+1)
            ->where('is_completed', false)
            ->when($this->windowPastDays > 0, fn ($q) =>
                $q->whereDate('end_date', '>=', $now->clone()->subDays($this->windowPastDays)->toDateString())
            )
            ->when($this->windowNextDays > 0, fn ($q) =>
                $q->whereDate('start_date', '<=', $now->clone()->addDays($this->windowNextDays)->toDateString())
            )
            ->get()
            ->filter(function ($e) {
                return $e->start_date_time && $e->end_date_time
                    && $e->end_date_time->gte($e->start_date_time);
            });
    }

    protected function mapForView(Collection $events): array
    {
        return $events->map(function ($e) {
            return [
                'id'          => $e->id,
                'title'       => $e->title,
                'description' => $e->description ?: null,
                // format agar bulan singkatan EN (Jan, Feb, dst.)
                'start'       => $e->start_date_time?->format('d M Y H:i'),
                'end'         => $e->end_date_time?->format('d M Y H:i'),
                'location'    => $e->location ?? null,
                'raw_start'   => $e->start_date_time,
                'raw_end'     => $e->end_date_time,

                // === peserta untuk display ===
                'participants'       => $e->participants?->pluck('name')->values()->all() ?? [],
                'participants_count' => $e->participants?->count() ?? 0,
            ];
        })->values()->all();
    }

    protected function loadData(): void
    {
        $now    = now();
        $events = $this->fetchActiveEvents();

        // CURRENT
        $current = $events->filter(
            fn ($e) => $e->start_date_time->lte($now) && $e->end_date_time->gt($now)
        )->sortBy('start_date_time');
        $this->currentEvents = $this->mapForView($current);

        // NEXT batch (paling awal setelah sekarang)
        $firstStart = $events
            ->filter(fn ($e) => $e->start_date_time->gt($now))
            ->min('start_date_time');

        $nextBatch = collect();
        if ($firstStart) {
            $nextBatch = $events->filter(
                fn ($e) => $e->start_date_time->equalTo($firstStart)
            )->sortBy('title');
        }
        $this->nextEvents = $this->mapForView($nextBatch);

        // PREVIOUS batch (paling akhir sebelum/tepat sekarang)
        $lastEnd = $events
            ->filter(fn ($e) => $e->end_date_time->lte($now))
            ->max('end_date_time');

        $prevBatch = collect();
        if ($lastEnd) {
            $prevBatch = $events->filter(
                fn ($e) => $e->end_date_time->equalTo($lastEnd)
            )->sortBy('title');
        }
        $this->previousEvents = $this->mapForView($prevBatch);
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function render()
    {
        // auto-roll via wire:poll di Blade
        $this->loadData();

        return view('livewire.display.user-board')
            ->title('Display Agenda');
    }
}
