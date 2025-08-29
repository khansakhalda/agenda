{{-- Display Screen: auto refresh tiap 15 detik --}}
<div class="h-screen w-screen overflow-hidden relative" wire:poll.15s>

  {{-- LOGO (top-left) --}}
  <div class="absolute top-3 left-5 z-20">
    <div class="h-16 w-16 md:h-20 md:w-20 rounded-full bg-gradient-to-br from-white/90 to-sky-100/80
                backdrop-blur-md border border-white/60 shadow-[0_10px_25px_rgba(2,6,23,0.12)] grid place-items-center">
      <img src="{{ asset('images/kominfo.png') }}" alt="Kominfo Logo"
           class="h-12 w-12 md:h-16 md:w-16 object-contain drop-shadow-sm">
    </div>
  </div>

  {{-- CLOCK (top-right) — diperkecil --}}
  <div id="clock" wire:ignore
       class="absolute top-3 right-5 z-20 flex flex-col items-center justify-center
              bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg
              px-4 py-2 shadow-lg shadow-blue-900/30 border border-white/20">
    <div id="clock-day" class="text-xs md:text-sm font-semibold tracking-wide"></div>
    <div id="clock-time" class="text-2xl md:text-4xl font-extrabold tracking-widest mt-0.5 drop-shadow-lg"></div>
  </div>

  {{-- BACKGROUND --}}
  <div class="pointer-events-none fixed inset-0 z-0">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-sky-100 to-blue-200"></div>
    <div class="absolute inset-0 opacity-[0.12]
      [background-image:linear-gradient(to_right,rgba(0,0,0,0.08)_1px,transparent_1px),
                         linear-gradient(to_bottom,rgba(0,0,0,0.06)_1px,transparent_1px)]
      [background-size:28px_28px]"></div>
  </div>

  {{-- CONTENT: top besar, jarak lega --}}
  <div class="relative z-10 h-full w-full grid grid-rows-[68vh_minmax(0,1fr)] gap-6 px-4 md:px-8 lg:px-12 py-4">

    {{-- ===== GRID ATAS: acara sedang berlangsung ===== --}}
    @php $cCount = count($currentEvents); @endphp
    <section
      x-data="{
        idx: 0, count: {{ $cCount }}, timer: null,
        start(){ if(this.count>1){ this.timer=setInterval(()=>{ this.idx=(this.idx+1)%this.count }, 2000) } },
        stop(){ if(this.timer){ clearInterval(this.timer); this.timer=null } },
        go(i){ this.idx=i; this.stop(); this.start(); }
      }"
      x-init="start()"
      class="relative rounded-2xl bg-gradient-to-br from-white/90 to-sky-50/80 backdrop-blur-md
             border border-white/40 shadow-[0_18px_44px_rgba(2,6,23,0.12)] overflow-hidden"
    >
      <div class="absolute inset-0">
        @if($cCount === 0)
          <div class="h-full w-full grid place-items-center">
            <p class="text-xl md:text-2xl font-semibold text-slate-600">
              Tidak ada acara yang sedang berlangsung.
            </p>
          </div>
        @else
          @foreach($currentEvents as $i => $ev)
            <div
              x-show="idx === {{ $i }}"
              x-transition:enter="transition ease-out duration-500"
              x-transition:enter-start="opacity-0 translate-y-3"
              x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-400"
              x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 -translate-y-3"
              class="absolute inset-0 flex items-center justify-center px-6 md:px-10"
            >
              <div class="text-center leading-tight text-slate-900">
                <div class="text-[clamp(36px,6.8vw,86px)] font-extrabold">{{ $ev['title'] }}</div>
                <div class="mt-3 text-[clamp(18px,2.6vw,34px)] font-semibold text-slate-700">
                  {{ $ev['start'] }} — {{ $ev['end'] }}
                </div>
                @if(($ev['participants_count'] ?? 0) > 0)
                  <div class="mt-4 flex justify-center">
                    @include('partials.participants-facepile', [
                      'list'  => $ev['participants'] ?? [],
                      'max'   => 12,
                      'label' => 'Partisipan'
                    ])
                  </div>
                @endif
              </div>
            </div>
          @endforeach

          {{-- indikator swipe (dot saja) --}}
          <div class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col gap-4" x-show="count > 1" x-cloak>
            @for($i=0; $i<$cCount; $i++)
              <button
                class="indicator-dot"
                :class="idx === {{ $i }} ? 'indicator-active' : ''"
                @click="go({{ $i }})"
                aria-label="Slide {{ $i+1 }}"
              ></button>
            @endfor
          </div>
        @endif
      </div>
    </section>

    {{-- ===== GRID BAWAH: list auto-scroll (jalan hanya jika >1 event) ===== --}}
    <section class="rounded-2xl overflow-hidden
                    bg-gradient-to-br from-white/85 via-sky-50/80 to-blue-50/70
                    backdrop-blur-md border border-white/40
                    shadow-[0_12px_30px_rgba(2,6,23,0.12)]">
      @php $tCount = count($upcomingToday); @endphp

      @if ($tCount === 0)
        <div class="h-full w-full grid place-items-center text-slate-500">
          Tidak ada acara selanjutnya hari ini.
        </div>
      @else
        <div class="h-full w-full relative overflow-hidden">
          {{-- tambahkan class vlist hanya jika >1 --}}
          <div class="h-full absolute inset-0 {{ $tCount > 1 ? 'vlist' : '' }}">
            <ul class="divide-y divide-white/40">
              @foreach ($upcomingToday as $ev)
                <li class="px-5 py-3 flex items-start gap-3">
                  <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-900 text-sm md:text-base truncate">{{ $ev['title'] }}</div>
                    <div class="text-xs md:text-sm font-medium text-slate-700">
                      {{ $ev['start'] }} — {{ $ev['end'] }}
                    </div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-1">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 8, 'label' => 'Partisipan'])
                      </div>
                    @endif
                  </div>
                </li>
              @endforeach

              {{-- duplikasi untuk loop mulus: hanya saat >1 --}}
              @if($tCount > 1)
                @foreach ($upcomingToday as $ev)
                  <li class="px-5 py-3 flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                      <div class="font-semibold text-slate-900 text-sm md:text-base truncate">{{ $ev['title'] }}</div>
                      <div class="text-xs md:text-sm font-medium text-slate-700">
                        {{ $ev['start'] }} — {{ $ev['end'] }}
                      </div>
                      @if(($ev['participants_count'] ?? 0) > 0)
                        <div class="mt-1">
                          @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 8, 'label' => 'Partisipan'])
                        </div>
                      @endif
                    </div>
                  </li>
                @endforeach
              @endif
            </ul>
          </div>
        </div>
      @endif
    </section>
  </div>

  {{-- CLOCK SCRIPT --}}
  <script>
    function updateClock() {
      const now = new Date();
      const days = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
      const months = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];
      document.getElementById('clock-day').textContent =
        `${days[now.getDay()]}, ${now.getDate().toString().padStart(2,'0')} ${months[now.getMonth()]} ${now.getFullYear()}`;
      document.getElementById('clock-time').textContent =
        `${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}:${now.getSeconds().toString().padStart(2,'0')}`;
    }
    updateClock(); setInterval(updateClock, 1000);
  </script>

  <style>
    [x-cloak]{ display:none !important; }

    /* Swipe indicators */
    .indicator-dot{
      height: 18px; width: 18px; border-radius: 9999px;
      border: 2px solid rgba(96,165,250,.9);
      box-shadow: 0 2px 10px rgba(59,130,246,.25);
      background: radial-gradient(circle at 50% 50%, rgba(255,255,255,.95) 45%, rgba(255,255,255,0) 46%);
      transition: all .22s ease; backdrop-filter: blur(2px);
    }
    .indicator-dot:hover,.indicator-dot:focus{
      outline: none; transform: scale(1.06);
      border-color: rgba(37,99,235,.95);
      box-shadow: 0 6px 18px rgba(37,99,235,.26);
    }
    .indicator-active{
      border-color: transparent;
      background-image:
        radial-gradient(closest-side, rgba(15,23,42,.92) 42%, rgba(15,23,42,.92) 42%),
        linear-gradient(135deg, #38bdf8, #6366f1);
      background-origin: border-box; background-clip: content-box, border-box;
      box-shadow: 0 10px 24px rgba(37,99,235,.32), 0 0 0 6px rgba(99,102,241,.18);
      animation: ping-soft 1.6s cubic-bezier(0,0,.2,1) infinite;
    }
    @keyframes ping-soft{
      0%{ box-shadow:0 10px 24px rgba(37,99,235,.32),0 0 0 6px rgba(99,102,241,.22) }
      70%{ box-shadow:0 10px 24px rgba(37,99,235,.28),0 0 0 14px rgba(99,102,241,0) }
      100%{ box-shadow:0 10px 24px rgba(37,99,235,.32),0 0 0 6px rgba(99,102,241,.18) }
    }

    /* Auto-scroll list bawah (berjalan hanya jika ada class .vlist) */
    @keyframes vlist { 0%{ transform: translateY(0) } 100%{ transform: translateY(-50%) } }
    .vlist{ animation: vlist 18s linear infinite; will-change: transform; }
  </style>
</div>
