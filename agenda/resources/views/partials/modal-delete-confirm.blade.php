<div
  x-data="{
    open:false, title:'', text:'', confirmText:'Ya', cancelText:'Tidak', method:null, args:[],
    ask(p){ 
      this.title  = p?.title  || 'Hapus Acara?'; 
      this.text   = p?.text   || 'Acara ini akan dihapus permanen. Tindakan tidak bisa dibatalkan.'; 
      this.confirmText = p?.confirmText || 'Ya, Hapus'; 
      this.cancelText  = p?.cancelText  || 'Batal'; 
      this.method = p?.method || null; 
      this.args   = p?.args   || []; 
      this.open   = true; 
    },
    confirm(){ if(this.method){ $wire.call(this.method, ...this.args); } this.open=false; },
    init(){ window.addEventListener('confirm', e => this.ask(e.detail||{})); }
  }"
  x-show="open"
  x-cloak
  class="fixed inset-0 z-[9998] flex items-center justify-center"
>
  {{-- backdrop --}}
  <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

  {{-- card --}}
  <div x-show="open" x-transition
       class="relative z-[9999] w-[92vw] max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 p-5">
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
      <button type="button"
              class="px-4 py-2 text-sm rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50"
              @click="open=false" x-text="cancelText"></button>
      <button type="button"
              class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700"
              @click="confirm" x-text="confirmText"></button>
    </div>
  </div>
</div>
