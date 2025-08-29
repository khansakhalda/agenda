<div wire:poll class="min-h-screen bg-gray-50">
  <div class="flex min-h-screen">

    {{-- Sidebar: Sembunyikan mini calendar di halaman Tasks --}}
    @include('partials.sidebar', ['showMiniCalendar' => false])

    {{-- Main Content --}}
    <div class="flex-1 p-6">
      <div class="flex justify-between items-center mb-5">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Daftar Tugas</h2>

{{-- Buat Tugas + Sort --}}
<div class="flex items-center space-x-4">
  {{-- Tombol ke Calendar Admin (disamakan dengan Buat Tugas) --}}
  <a
    href="{{ route('calendar.admin') }}"
    wire:navigate
    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
  >
    <span class="inline-flex items-center gap-2">
      Calendar
    </span>
  </a>

  {{-- Tombol Buat Tugas (tetap) --}}
  <button
    wire:click="openCreateModal"
    wire:loading.attr="disabled"
    wire:target="openCreateModal,createTask"
    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
    <span wire:loading.remove wire:target="openCreateModal">Buat Tugas</span>
    <span wire:loading wire:target="openCreateModal">Memuat...</span>
  </button>

  {{-- Dropdown Urutkan (tetap) --}}
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
            @if ($flashMessage)
              @if (is_array($flashMessage))
                this.fire(@js($flashMessage));
              @else
                this.fire({ type:'success', title:'Berhasil', text:@js($flashMessage) });
              @endif
            @endif
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

      {{-- Task List (tanpa konsep waktu) --}}
      @if ($sortBy === 'starred')
        @php
          $starred = $tasks->filter(fn($t) => (bool)($t->is_starred ?? false));
          $nonstar = $tasks->reject(fn($t) => (bool)($t->is_starred ?? false));
        @endphp

        <div class="mb-8">
          <h3 class="text-gray-700 font-semibold text-lg mb-4">Berbintang</h3>
          <div class="flex flex-col space-y-4">
            @forelse ($starred as $task)
              @include('livewire.settings.task-card', ['task' => $task])
            @empty
              <p class="text-gray-500 italic">Belum ada tugas berbintang.</p>
            @endforelse
          </div>
        </div>

        <div class="mb-10 space-y-4">
          <h3 class="text-gray-700 font-semibold text-lg mb-4">Tidak berbintang</h3>
          @forelse ($nonstar as $task)
            @include('livewire.settings.task-card', ['task' => $task])
          @empty
            <p class="text-gray-500 italic">Tidak ada tugas non-bintang.</p>
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

{{-- ================== Modal Buat Tugas (khusus halaman Tasks) ================== --}}
@if ($showCreateModal)
  <div
    x-data="{}"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[9998] flex items-center justify-center"
    aria-modal="true" role="dialog">

    {{-- backdrop --}}
    <div class="absolute inset-0 bg-black/40" @click="$wire.closeCreateModal()"></div>

    {{-- container (disamakan dgn modal acara) --}}
    <div class="relative z-[9999] w-[92vw] max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
      {{-- Header --}}
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <h3 class="text-base font-semibold text-gray-900">Buat Tugas</h3>
        <button class="h-8 w-8 grid place-items-center rounded-md text-slate-500 hover:bg-slate-100"
                @click="$wire.closeCreateModal()" aria-label="Tutup">×</button>
      </div>

      {{-- Body (scrollable seperti modal acara) --}}
      <div class="px-5 py-4 max-h-[75vh] overflow-y-auto">
        <form wire:submit.prevent="createTask" class="space-y-4">
          {{-- Judul --}}
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas</label>
            <input type="text" wire:model.defer="newTask.title" placeholder="Judul tugas"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('newTask.title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>

          {{-- Deskripsi --}}
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea rows="4" wire:model.defer="newTask.description" placeholder="Deskripsi tugas"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            @error('newTask.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
          </div>

{{-- Partisipan (disamakan dengan modal acara) --}}
<div
  x-data="{
    // query diketik user
    query: '',
    // saran dropdown
    sug: [],
    // ghost suggestion satu kandidat terdepan
    suggestion: '',
    lastQuery: '',
    // daftar yg terpilih — tetap pakai payload newTask.participants
    picked: @js($newTask['participants'] ?? []),

    async updateSuggestion(){
      const q = (this.query||'').trim();
      this.lastQuery = q;

      // ghost (first suggestion)
      if(q){
        const res = await $wire.getFirstSuggestion(q);
        if(this.lastQuery === q) this.suggestion = res || '';
      } else {
        this.suggestion = '';
      }

      // dropdown list
      if(q){
        this.sug = await $wire.searchCalendarParticipants(q);
      } else {
        this.sug = [];
      }
    },

    pick(p){
      // p bisa {id,name} atau {name}
      const key = (x) => (x.id ?? 'name:'+x.name);
      if(!this.picked.find(x => key(x) === key(p))){
        this.picked.push({ id: p.id ?? null, name: p.name });
        $wire.set('newTask.participants', this.picked);
      }
      this.query=''; this.suggestion=''; this.sug=[];
    },

    enter(){
      const name = (this.query||'').trim();
      if(!name) return;
      this.pick({ name });
    },

    acceptGhost(){
      if(this.suggestion && this.suggestion.startsWith(this.query)){
        this.query = this.suggestion; this.suggestion='';
      }
    },

    remove(i){
      this.picked.splice(i,1);
      $wire.set('newTask.participants', this.picked);
    }
  }"
  x-init="$watch('query', () => updateSuggestion())"
  class="mt-2 relative"
>
  <label class="block text-sm font-medium text-gray-700 mb-1">Partisipan</label>

  {{-- Input + ghost suggestion --}}
  <div class="relative">
    <input type="text"
           x-model="query"
           @keydown.tab.prevent="acceptGhost()"
           @keydown.enter.prevent="enter()"
           placeholder="Ketik nama partisipan…"
           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

    {{-- Ghost text (di atas input, abu-abu) --}}
    <div class="absolute top-0 left-0 px-3 py-2 text-gray-400 pointer-events-none">
      <template x-if="suggestion && suggestion !== query">
        <span x-text="query + suggestion.substring(query.length)"></span>
      </template>
    </div>

    {{-- Dropdown suggestions --}}
    <div class="absolute z-10 mt-1 w-full bg-white border rounded-lg shadow max-h-56 overflow-y-auto"
         x-show="sug.length">
      <template x-for="p in sug" :key="p.id ?? p.name">
        <button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50"
                @click="pick(p)" x-text="p.name"></button>
      </template>
    </div>
  </div>
<p id="help-partisipan" class="mt-2 text-xs text-slate-500">
  Jika belum ada, tekan <strong>Enter</strong> untuk menambah.
</p>
  {{-- Chips terpilih (DI BAWAH input, sama seperti modal acara) --}}
  <div class="flex flex-wrap gap-2 mt-3">
    <template x-for="(p,i) in picked" :key="(p.id||p.name)+'-'+i">
      <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs flex items-center">
        <span x-text="p.name"></span>
        <button type="button" class="ml-2 text-red-500 hover:text-red-700" @click="remove(i)">×</button>
      </span>
    </template>
  </div>
</div>


          {{-- Footer (diseragamkan) --}}
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="$wire.closeCreateModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
              Batal
            </button>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="createTask"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
              <span wire:loading.remove wire:target="createTask">Buat</span>
              <span wire:loading wire:target="createTask">Menyimpan...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endif
{{-- ================== /Modal Buat Tugas ================== --}}

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
