{{-- Display Screen: auto refresh tiap 15 detik --}}
<div class="h-screen w-screen overflow-hidden relative" wire:poll.15s>

  {{-- ===== LOGO (top-left) ===== --}}
  <div class="absolute top-3 left-5 z-20">
    <div class="h-16 w-16 md:h-20 md:w-20 rounded-full bg-gradient-to-br from-white/90 to-sky-100/80
                backdrop-blur-md border border-white/60 shadow-[0_10px_25px_rgba(2,6,23,0.12)] grid place-items-center">
      <img src="{{ asset('images/kominfo.png') }}" alt="Kominfo Logo"
           class="h-12 w-12 md:h-16 md:w-16 object-contain drop-shadow-sm">
    </div>
  </div>

  {{-- ===== CLOCK (top-right) ===== --}}
  <div id="clock" wire:ignore
       class="absolute top-3 right-5 z-20 flex flex-col items-center justify-center
              bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl
              px-5 py-3 shadow-lg shadow-blue-900/30 border border-white/20">
    <div id="clock-day" class="text-sm md:text-base font-semibold tracking-wide"></div>
    <div id="clock-time" class="text-3xl md:text-5xl font-extrabold tracking-widest mt-1 drop-shadow-lg"></div>
  </div>

  {{-- ===== BACKGROUND ===== --}}
  <div class="pointer-events-none fixed inset-0 z-0">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-sky-100 to-blue-200"></div>
    <div class="absolute inset-0 opacity-[0.15]
      [background-image:linear-gradient(to_right,rgba(0,0,0,0.1)_1px,transparent_1px),
                         linear-gradient(to_bottom,rgba(0,0,0,0.08)_1px,transparent_1px)]
      [background-size:28px_28px]"></div>
  </div>

  {{-- ===== CONTENT (heights are fixed with vh) ===== --}}
  <div class="relative z-10 h-full w-full flex flex-col px-4 md:px-8 lg:px-12 py-3 md:py-4 gap-3">

    {{-- ========= NEXT (fixed 13vh) ========= --}}
    <section class="h-[13vh] shrink-0 rounded-xl bg-gray-100/60 backdrop-blur-sm
                   border border-gray-200/40 shadow-inner animate-fade-in
                   flex items-center justify-center px-3 md:px-4">
      @php $nCount = count($nextEvents); @endphp

      @if($nCount === 0)
        <p class="text-sm md:text-base text-gray-400 font-semibold">Tidak ada acara mendatang.</p>

      @elseif($nCount === 1)
        {{-- Single --}}
        <div class="w-full max-w-5xl text-center leading-tight">
          <div class="text-base md:text-lg font-extrabold text-slate-900">{{ $nextEvents[0]['title'] }}</div>
          <div class="mt-0.5 text-xs md:text-sm font-semibold text-slate-700">{{ $nextEvents[0]['start'] }} — {{ $nextEvents[0]['end'] }}</div>
          @if(($nextEvents[0]['participants_count'] ?? 0) > 0)
            <div class="mt-0.5 flex items-center justify-center">
              @include('partials.participants-facepile', [
                'list' => $nextEvents[0]['participants'],
                'max'  => 8,
                'label'=> 'Partisipan'
              ])
            </div>
          @endif
        </div>

      @else
        {{-- Banyak item: teks di-scroll, tinggi card tetap --}}
        <div class="relative w-full max-w-5xl h-full overflow-hidden">
          <div class="absolute inset-0">
            <div class="animate-vscroll space-y-2 md:space-y-3 py-2">
              @foreach($nextEvents as $ev)
                <div class="flex items-start gap-3 md:gap-4 text-left px-1 md:px-2">
                  <div class="w-[5px] md:w-[6px] self-stretch rounded-full bg-gradient-to-b from-slate-300 to-slate-400 opacity-70"></div>
                  <div class="leading-tight">
                    <div class="text-base md:text-lg font-extrabold text-slate-900">{{ $ev['title'] }}</div>
                    <div class="text-xs md:text-sm font-semibold text-slate-700">{{ $ev['start'] }} — {{ $ev['end'] }}</div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-0.5">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 8])
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
              {{-- duplikasi untuk loop halus --}}
              @foreach($nextEvents as $ev)
                <div class="flex items-start gap-3 md:gap-4 text-left px-1 md:px-2">
                  <div class="w-[5px] md:w-[6px] self-stretch rounded-full bg-gradient-to-b from-slate-300 to-slate-400 opacity-70"></div>
                  <div class="leading-tight">
                    <div class="text-base md:text-lg font-extrabold text-slate-900">{{ $ev['title'] }}</div>
                    <div class="text-xs md:text-sm font-semibold text-slate-700">{{ $ev['start'] }} — {{ $ev['end'] }}</div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-0.5">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 8])
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </section>

    {{-- ========= CURRENT (fixed 64vh) ========= --}}
    <section class="h-[64vh] shrink-0 rounded-2xl bg-gradient-to-br from-white/90 to-sky-50/80 backdrop-blur-md
                   border border-white/40 shadow-[0_16px_40px_rgba(2,6,23,0.12)] px-6 md:px-10">
      @php $cCount = count($currentEvents); @endphp

      <div class="relative w-full max-w-6xl mx-auto h-full overflow-hidden">
        @if($cCount === 0)
          <div class="absolute inset-0 grid place-items-center">
            <p class="text-lg md:text-xl text-slate-600">Tidak ada acara yang sedang berlangsung.</p>
          </div>

        @elseif($cCount === 1)
          {{-- Single, center --}}
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center leading-tight px-2">
              <div class="font-extrabold text-slate-900 text-[clamp(32px,6vw,80px)] md:text-[clamp(48px,6.5vw,96px)]">
                {{ $currentEvents[0]['title'] }}
              </div>
              <div class="mt-3 font-semibold text-slate-800 text-[clamp(16px,2.3vw,32px)]">
                {{ $currentEvents[0]['start'] }} — {{ $currentEvents[0]['end'] }}
              </div>
              @if($currentEvents[0]['location'])
                <div class="mt-1 text-slate-600 text-[clamp(14px,1.6vw,22px)]">📍 {{ $currentEvents[0]['location'] }}</div>
              @endif
              @if(($currentEvents[0]['participants_count'] ?? 0) > 0)
                <div class="mt-2 flex justify-center">
                  @include('partials.participants-facepile', ['list' => $currentEvents[0]['participants'], 'max' => 10])
                </div>
              @endif
            </div>
          </div>

        @else
          {{-- Banyak item: judul besar bergilir --}}
          <div class="absolute inset-0">
            <div class="animate-vscroll-current">
              @foreach($currentEvents as $ev)
                <div class="h-[64vh] flex items-center text-left pl-6 md:pl-10 pr-4 md:pr-6">
                  <div class="leading-tight">
                    <div class="relative pl-8 md:pl-10">
                      <div class="absolute left-0 top-0 bottom-0 w-[6px] rounded-full
                                  bg-gradient-to-b from-blue-500 to-indigo-600"></div>
                      <div class="font-extrabold text-slate-900
                                  text-[clamp(32px,6vw,80px)] md:text-[clamp(48px,6.5vw,96px)]">
                        {{ $ev['title'] }}
                      </div>
                    </div>
                    <div class="mt-1 font-semibold text-slate-700 text-[clamp(16px,2.3vw,32px)]">
                      {{ $ev['start'] }} — {{ $ev['end'] }}
                    </div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-2">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 12])
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach

              {{-- duplicate untuk loop mulus --}}
              @foreach($currentEvents as $ev)
                <div class="h-[64vh] flex items-center text-left pl-6 md:pl-10 pr-4 md:pr-6">
                  <div class="leading-tight">
                    <div class="relative pl-8 md:pl-10">
                      <div class="absolute left-0 top-0 bottom-0 w-[6px] rounded-full
                                  bg-gradient-to-b from-blue-500 to-indigo-600"></div>
                      <div class="font-extrabold text-slate-900
                                  text-[clamp(32px,6vw,80px)] md:text-[clamp(48px,6.5vw,96px)]">
                        {{ $ev['title'] }}
                      </div>
                    </div>
                    <div class="mt-1 font-semibold text-slate-700 text-[clamp(16px,2.3vw,32px)]">
                      {{ $ev['start'] }} — {{ $ev['end'] }}
                    </div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-2">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 12])
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </section>

    {{-- ========= PREVIOUS (fixed 13vh) ========= --}}
    <section class="h-[13vh] shrink-0 rounded-xl bg-gray-100/60 backdrop-blur-sm
                   border border-gray-200/40 shadow-inner animate-fade-in
                   flex items-center justify-center px-3 md:px-4">
      @php $pCount = count($previousEvents); @endphp

      @if($pCount === 0)
        <p class="text-sm md:text-base text-gray-400 font-semibold">Tidak ada acara sebelumnya.</p>

      @elseif($pCount === 1)
        <div class="w-full max-w-5xl text-center opacity-70 leading-tight">
          <div class="text-base md:text-lg font-extrabold text-slate-900">{{ $previousEvents[0]['title'] }}</div>
          <div class="mt-0.5 text-xs md:text-sm font-semibold text-slate-700">{{ $previousEvents[0]['start'] }} — {{ $previousEvents[0]['end'] }}</div>
          @if(($previousEvents[0]['participants_count'] ?? 0) > 0)
            <div class="mt-0.5 flex justify-center">
              @include('partials.participants-facepile', ['list' => $previousEvents[0]['participants'], 'max' => 8])
            </div>
          @endif
        </div>

      @else
        <div class="relative w-full max-w-5xl h-full overflow-hidden opacity-70">
          <div class="absolute inset-0">
            <div class="animate-vscroll space-y-2 md:space-y-3 py-2">
              @foreach($previousEvents as $ev)
                <div class="flex items-start gap-3 md:gap-4 text-left px-1 md:px-2">
                  <div class="w-[5px] md:w-[6px] self-stretch rounded-full bg-gradient-to-b from-slate-300 to-slate-400 opacity-70"></div>
                  <div class="leading-tight">
                    <div class="text-base md:text-lg font-extrabold text-slate-900">{{ $ev['title'] }}</div>
                    <div class="text-xs md:text-sm font-semibold text-slate-700">{{ $ev['start'] }} — {{ $ev['end'] }}</div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-0.5">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 8])
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
              @foreach($previousEvents as $ev)
                <div class="flex items-start gap-3 md:gap-4 text-left px-1 md:px-2">
                  <div class="w-[5px] md:w-[6px] self-stretch rounded-full bg-gradient-to-b from-slate-300 to-slate-400 opacity-70"></div>
                  <div class="leading-tight">
                    <div class="text-base md:text-lg font-extrabold text-slate-900">{{ $ev['title'] }}</div>
                    <div class="text-xs md:text-sm font-semibold text-slate-700">{{ $ev['start'] }} — {{ $ev['end'] }}</div>
                    @if(($ev['participants_count'] ?? 0) > 0)
                      <div class="mt-0.5">
                        @include('partials.participants-facepile', ['list' => $ev['participants'], 'max' => 8])
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </section>

  </div>

  {{-- ===== CLOCK SCRIPT ===== --}}
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
  updateClock();
  setInterval(updateClock, 1000);
  </script>

  {{-- ===== STYLES ===== --}}
  <style>
    @keyframes vscroll { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); } }
    .animate-vscroll { animation: vscroll linear infinite; animation-duration: 18s; }

    @keyframes vscrollCurrent { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); } }
    .animate-vscroll-current { animation: vscrollCurrent linear infinite; animation-duration: 20s; }

    @keyframes fade-in {from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:none;}}
    .animate-fade-in { animation: fade-in .5s ease-out both; }
  </style>
</div>
