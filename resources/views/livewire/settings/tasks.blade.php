{{-- resources/views/livewire/settings/tasks.blade.php --}}
<div wire:poll class="min-h-screen bg-gray-50">
  <div class="flex min-h-screen">

    {{-- Sidebar: Sembunyikan mini calendar di halaman Tasks --}}
    @include('partials.sidebar', ['showMiniCalendar' => false])

    {{-- Main Content --}}
    <div class="flex-1 p-6">
      <div class="flex justify-between items-center mb-5">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Daftar Tugas</h2>

        {{-- Buat Acara + Sort --}}
        <div class="flex items-center space-x-4">
          <button
            wire:click="openCreateModal"
            wire:loading.attr="disabled"
            wire:target="openCreateModal,createEvent"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="openCreateModal">Buat Acara</span>
            <span wire:loading wire:target="openCreateModal">Memuat...</span>
          </button>

          {{-- Dropdown Urutkan (Alpine) --}}
          @include('partials.sort-dropdown')
        </div>
      </div>

      {{-- ======================= TOAST NOTIFIKASI (INDONESIA) ======================= --}}
      <div
        x-data="{
          show:false, type:'success', title:'', text:'', duration:3200, t:null,
          palette:{
            success:{ ring:'ring-emerald-200', icon:'#059669', bar:'bg-blue-600' },
            info:{ ring:'ring-blue-200', icon:'#2563eb', bar:'bg-blue-600' },
            warning:{ ring:'ring-amber-200', icon:'#d97706', bar:'bg-amber-500' },
            error:{ ring:'ring-red-200', icon:'#dc2626', bar:'bg-red-600' },
          },
          fire(p){
            clearTimeout(this.t);
            this.type = p?.type ?? 'success';
            this.title = p?.title ?? '';
            this.text  = p?.text  ?? '';
            this.duration = p?.duration ?? 3200;
            this.show = true;
            this.t = setTimeout(() => this.show = false, this.duration);
          },
          init(){
            // Dukung flashMessage dari server (bisa string atau array {type,title,text})
            @if ($flashMessage)
              @if (is_array($flashMessage))
                this.fire(@js($flashMessage));
              @else
                this.fire({ type:'success', title:'Berhasil', text:@js($flashMessage) });
              @endif
            @endif

            // Dukung event Livewire/Alpine: window.dispatchEvent(new CustomEvent('toast', {detail:{...}}))
            window.addEventListener('toast', e => this.fire(e.detail || {}));
          }
        }"
        class="fixed top-6 right-6 z-50"
        aria-live="polite"
      >
        <div
          x-show="show"
          x-transition.opacity.duration.200ms
          class="pointer-events-auto w-80 rounded-xl border bg-white/95 shadow-2xl ring-1 ring-black/5 backdrop-blur overflow-hidden"
          :class="palette[type].ring"
        >
          <div class="flex gap-3 p-3.5">
            <div class="mt-0.5 shrink-0">
              <template x-if="type==='success'">
                <svg class="h-5 w-5" :style="`color:${palette[type].icon}`" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
              </template>
              <template x-if="type==='info'">
                <svg class="h-5 w-5" :style="`color:${palette[type].icon}`" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M11 9h2V7h-2v2zm0 8h2v-6h-2v6zm1-16C6.48 1 2 5.48 2 11s4.48 10 10 10 10-4.48 10-10S17.52 1 12 1z"/>
                </svg>
              </template>
              <template x-if="type==='warning'">
                <svg class="h-5 w-5" :style="`color:${palette[type].icon}`" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                </svg>
              </template>
              <template x-if="type==='error'">
                <svg class="h-5 w-5" :style="`color:${palette[type].icon}`" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5v6.25h-1.5V7.5h1.5zm0 8.75v1.5h-1.5v-1.5h1.5z"/>
                </svg>
              </template>
            </div>

            <div class="min-w-0">
              <p class="text-sm font-semibold text-slate-900" x-text="title"></p>
              <p class="mt-0.5 text-sm text-slate-600" x-text="text"></p>
            </div>

            <button type="button"
                    class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100"
                    @click="show=false" aria-label="Tutup">×</button>
          </div>

          <div class="h-1 bg-slate-200">
            <div class="h-full" :class="palette[type].bar"
                 :style="show ? `animation: toastProgress ${duration}ms linear forwards` : ''"></div>
          </div>
        </div>

        <style>
          @keyframes toastProgress { from { width: 100%; } to { width: 0%; } }
        </style>
      </div>
      {{-- ===================== /TOAST ===================== --}}

      {{-- Task List --}}
      @if ($sortBy === 'date' || $sortBy === 'starred')
        <div class="mb-8">
          <h3 class="text-gray-700 font-semibold text-lg mb-4">
            {{ $sortBy === 'starred' ? 'Berbintang' : 'Segera' }}
          </h3>
          <div class="flex flex-col space-y-4">
            @forelse ($soonTasks as $task)
              @include('livewire.settings.task-card', ['task' => $task])
            @empty
              <p class="text-gray-500 italic">
                {{ $sortBy === 'starred' ? 'Belum ada tugas berbintang.' : 'Tidak ada tugas mendatang.' }}
              </p>
            @endforelse
          </div>
        </div>

        <div class="mb-10 space-y-4">
          <h3 class="text-gray-700 font-semibold text-lg mb-4">
            {{ $sortBy === 'starred' ? 'Tidak berbintang' : 'Lampau' }}
          </h3>
          @forelse ($pastTasks as $task)
            @include('livewire.settings.task-card', ['task' => $task])
          @empty
            <p class="text-gray-500 italic">
              {{ $sortBy === 'starred' ? 'Tidak ada tugas non-bintang.' : 'Tidak ada tugas lampau.' }}
            </p>
          @endforelse
        </div>
      @else
        <div class="flex flex-col space-y-4 mb-10">
          @forelse ($tasks as $task)
            @include('livewire.settings.task-card', ['task' => $task])
          @empty
            <p class="text-center text-gray-500">Belum ada tugas.</p>
          @endforelse
        </div>
      @endif

      {{-- Bagian: Selesai --}}
      @include('partials.task-completed')

      {{-- Modal Buat Acara (pakai partial yang sama dengan calendar admin) --}}
      @include('partials.modal-create')
    </div>
  </div>

  {{-- ======================= MODAL KONFIRMASI (INDONESIA) ======================= --}}
  <div
    x-data="{
      open:false, title:'', text:'', confirmText:'Ya', cancelText:'Tidak', method:null, args:[],
      ask(p){ this.title=p?.title||'Hapus data?'; this.text=p?.text||'Apakah Anda yakin ingin menghapus? Tindakan ini tidak bisa dibatalkan.'; this.confirmText=p?.confirmText||'Ya'; this.cancelText=p?.cancelText||'Tidak'; this.method=p?.method||null; this.args=p?.args||[]; this.open=true; },
      confirm(){ if(this.method){ $wire.call(this.method, ...this.args); } this.open=false; },
      init(){ window.addEventListener('confirm', e => this.ask(e.detail||{})); }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9998] flex items-center justify-center"
  >
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

    <div x-show="open" x-transition class="relative z-[9999] w-[92vw] max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 p-5">
      <div class="flex items-start gap-3">
        <div class="mt-0.5">
          <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5v6.25h-1.5V7.5h1.5zm0 8.75v1.5h-1.5v-1.5h1.5z"/>
          </svg>
        </div>
        <div class="min-w-0">
          <h3 class="text-base font-semibold text-slate-900" x-text="title"></h3>
          <p class="mt-1 text-sm text-slate-600" x-text="text"></p>
        </div>
        <button class="ml-auto h-8 w-8 grid place-items-center rounded-md text-slate-500 hover:bg-slate-100"
                @click="open=false" aria-label="Tutup">×</button>
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <button type="button" class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50"
                @click="open=false" x-text="cancelText"></button>
        <button type="button" class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700"
                @click="confirm" x-text="confirmText"></button>
      </div>
    </div>
  </div>
  {{-- ===================== /MODAL KONFIRMASI ===================== --}}
</div>
