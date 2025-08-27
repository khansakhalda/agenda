<div class="h-screen w-full relative flex flex-col items-center justify-center
            bg-gradient-to-br from-[#e8f1ff] via-[#eaf7ff] to-[#d9eaff]
            overflow-hidden pb-10">

    {{-- Background decoration --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-40
                    bg-gradient-to-br from-blue-400 to-sky-300"></div>
        <div class="absolute -bottom-16 -right-16 h-80 w-80 rounded-full blur-3xl opacity-30
                    bg-gradient-to-tr from-sky-400 to-blue-300"></div>
    </div>

    {{-- Logo --}}
    <div class="relative mb-4">
        <img src="{{ asset('images/kominfo.png') }}" alt="Kominfo Banyumas" class="h-14 w-auto">
    </div>

    {{-- Login Card --}}
    <div class="relative w-full max-w-md">
        <div class="rounded-2xl border border-slate-200/70 bg-white/90 backdrop-blur-xl
                    shadow-2xl ring-1 ring-white/20 overflow-hidden transition-all duration-300">

            {{-- Header --}}
            <div class="px-6 pt-4 pb-2 text-center">
                <h1 class="text-lg font-semibold text-slate-900">Masuk</h1>
                <p class="mt-0.5 text-sm text-slate-500">Gunakan email dan kata sandi Anda</p>
                <x-auth-session-status class="mt-1 text-center" :status="session('status')" />
            </div>

            {{-- Body --}}
            <div class="px-6 py-4">
                <form wire:submit.prevent="login" class="space-y-3">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <flux:input
                            wire:model.defer="email"
                            :label="__('Email')"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="masukkan alamat email Anda"
                            class="w-full focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-offset-0"
                        />
                        @error('email')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <flux:input
                            wire:model.defer="password"
                            :label="__('Kata sandi')"
                            type="password"
                            required
                            autocomplete="current-password"
                            :placeholder="__('masukkan kata sandi Anda')"
                            viewable
                            class="w-full focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-offset-0"
                            wire:keydown.enter.prevent="login"
                        />
                        @error('password')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember me --}}
                    <div class="pt-1">
                        <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                wire:model="remember"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm text-slate-700">Ingat saya</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-3">
                        <flux:button
                            variant="primary"
                            type="submit"
                            class="w-full h-11 text-base bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 shadow-md text-white"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>{{ __('Masuk') }}</span>
                            <span wire:loading>{{ __('Memproses...') }}</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="fixed bottom-0 inset-x-0 z-10">
        <div class="bg-white/95 border-t border-slate-200 shadow-[0_-6px_20px_-8px_rgba(2,6,23,0.2)]">
            <div class="mx-auto max-w-screen-2xl px-4 py-2.5 text-center">
                <p class="text-xs font-medium text-blue-600">
                    © {{ date('Y') }} Dinkominfo Banyumas. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    {{-- Toast Popup (Success/Fail) --}}
    <div
        x-data="{
            show:false, type:'info', title:'', text:'',
            fire(payload){
                this.type  = payload?.type  ?? 'info';
                this.title = payload?.title ?? '';
                this.text  = payload?.text  ?? '';
                this.show  = true;
                setTimeout(() => this.show = false, 3200);
            },
            init(){
                @if (session('toast'))
                    this.fire(@js(session('toast')));
                @endif
                window.addEventListener('toast', e => this.fire(e.detail || {}));
            }
        }"
        class="fixed top-4 right-4 z-[9999]"
        aria-live="polite"
    >
        <div
            x-show="show"
            x-transition.opacity.duration.200ms
            class="pointer-events-auto w-80 rounded-xl border bg-white/95 shadow-2xl ring-1 ring-black/5 backdrop-blur overflow-hidden"
            :class="{
                'border-emerald-200': type==='success',
                'border-red-200': type==='error',
                'border-blue-200': type==='info'
            }"
        >
            <div class="flex gap-3 p-3.5">
                {{-- Icon --}}
                <div class="mt-0.5">
                    <template x-if="type==='success'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </template>
                    <template x-if="type==='error'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5v6.25h-1.5V7.5h1.5zm0 8.75v1.5h-1.5v-1.5h1.5z"/>
                        </svg>
                    </template>
                    <template x-if="type==='info'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 9h2V7h-2v2zm0 8h2v-6h-2v6zm1-16C6.48 1 2 5.48 2 11s4.48 10 10 10 10-4.48 10-10S17.52 1 12 1z"/>
                        </svg>
                    </template>
                </div>

                {{-- Texts --}}
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900" x-text="title"></p>
                    <p class="mt-0.5 text-sm text-slate-600" x-text="text"></p>
                </div>

                {{-- Close --}}
                <button
                    type="button"
                    class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100"
                    @click="show=false"
                    aria-label="Close"
                >×</button>
            </div>

            {{-- Progress bar --}}
            <div class="h-1 bg-slate-200">
                <div class="h-full bg-blue-600"
                     :style="show ? 'animation: toastProgress 3200ms linear forwards' : ''"></div>
            </div>
        </div>
    </div>

    <style>
        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%; }
        }
    </style>
</div>
