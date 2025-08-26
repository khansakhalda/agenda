{{-- components/notification.blade.php --}}
<div x-data="{ 
        notifications: [],
        addNotification(type, message, duration = 4000) {
            const id = Date.now();
            this.notifications.push({ id, type, message, show: true });
            setTimeout(() => this.removeNotification(id), duration);
        },
        removeNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications[index].show = false;
                setTimeout(() => this.notifications.splice(index, 1), 300);
            }
        }
    }" @show-notification.window="addNotification($event.detail.type, $event.detail.message)"
    class="fixed top-4 right-4 z-50 space-y-2" style="pointer-events: none;">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show" x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
            :class="{
                'bg-green-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error',
                'bg-blue-500': notification.type === 'info',
                'bg-yellow-500': notification.type === 'warning'
             }">

            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <!-- Success Icon -->
                        <template x-if="notification.type === 'success'">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </template>

                        <!-- Error Icon -->
                        <template x-if="notification.type === 'error'">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </template>

                        <!-- Info Icon -->
                        <template x-if="notification.type === 'info'">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>

                        <!-- Warning Icon -->
                        <template x-if="notification.type === 'warning'">
                            <svg class="h-5 w-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L3.098 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </template>
                    </div>

                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium" :class="{
               'text-white': notification.type !== 'warning',
               'text-black': notification.type === 'warning'
           }" x-text="notification.message"></p>
                    </div>

                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="removeNotification(notification.id)"
                            class="rounded-md inline-flex focus:outline-none focus:ring-2 focus:ring-offset-2" :class="{
                                    'text-white hover:text-gray-200 focus:ring-offset-green-500 focus:ring-white': notification.type === 'success',
                                    'text-white hover:text-gray-200 focus:ring-offset-red-500 focus:ring-white': notification.type === 'error',
                                    'text-white hover:text-gray-200 focus:ring-offset-blue-500 focus:ring-white': notification.type === 'info',
                                    'text-black hover:text-gray-700 focus:ring-offset-yellow-500 focus:ring-black': notification.type === 'warning'
                                }">
                            <span class="sr-only">Close</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>