{{-- resources/views/partials/task-completed.blade.php --}}
<div class="mt-6" x-data="{ openCompleted: true }">
  <div class="flex items-center justify-between mb-3">
    <button type="button"
            @click="openCompleted = !openCompleted"
            class="flex items-center space-x-2 text-gray-700 hover:text-black font-medium">
      <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
           :class="openCompleted ? 'rotate-90' : ''"
           viewBox="0 0 24 24" fill="currentColor">
        <path d="M8.25 4.5l7.5 7.5-7.5 7.5" />
      </svg>
      <span>Selesai ({{ $completedTasks->count() }})</span>
    </button>

    @if($completedTasks->count() > 0)
      <button
        @click="$dispatch('confirm',{
          title:'Hapus semua yang selesai?',
          text:'Semua tugas di bagian Selesai akan dihapus permanen.',
          confirmText:'Ya',
          cancelText:'Tidak',
          method:'clearCompleted',
          args:[]
        })"
        class="text-sm px-3 py-1.5 rounded-md border text-red-600 border-red-200 hover:bg-red-50">
        Hapus semua yang selesai
      </button>
    @endif
  </div>

  <div x-show="openCompleted"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 -translate-y-1"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-1"
       class="space-y-3">
    @forelse($completedTasks as $c)
      @php
        /** @var \App\Models\Event|null $__event */
        $__event = \App\Models\Event::with('participants')->find($c->id);
        $__participants = $__event?->participants ?? collect();
      @endphp

      <div class="flex items-center justify-between group p-4 border rounded-xl bg-gray-100 transition shadow-sm opacity-80 hover:shadow-md">
        {{-- Kiri: Checkbox + konten --}}
        <div class="flex items-start w-0 flex-1 space-x-3">
          {{-- Restore --}}
          <button type="button" wire:click="restoreTask({{ $c->id }})"
                  class="relative cursor-pointer flex items-center flex-shrink-0 mt-0.5">
            <div class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center shadow-sm">
              <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </button>

          <div class="flex-1 overflow-hidden">
            <div class="font-semibold text-gray-600 line-through break-words whitespace-normal w-full leading-tight">
              {{ $c->title }}
            </div>
            <div class="text-sm text-gray-500 mt-1 line-clamp-2 leading-snug">
              {{ $c->description ?: 'Tanpa deskripsi' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">
              Selesai: {{ optional(\Carbon\Carbon::parse($c->completed_at))->translatedFormat('D, j M') ?? '-' }}
            </div>

            {{-- Partisipan --}}
            <div class="flex flex-wrap gap-1 mt-2">
              @foreach($__participants as $p)
                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">{{ $p->name }}</span>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Delete permanen (konfirmasi) --}}
        <div class="opacity-0 group-hover:opacity-100 transition-all duration-200">
          <button type="button"
                  @click="$dispatch('confirm',{
                    title:'Hapus permanen?',
                    text:'Tugas ini akan dihapus selamanya.',
                    confirmText:'Ya',
                    cancelText:'Tidak',
                    method:'destroyTask',
                    args:[{{ $c->id }}]
                  })"
                  class="text-red-500 hover:text-red-700 transition-colors duration-200" title="Hapus permanen">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>
      </div>
    @empty
      <p class="text-gray-400 text-sm">Tidak ada tugas selesai.</p>
    @endforelse
  </div>
</div>
