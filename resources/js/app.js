// Add this to your app.js or include as separate file

document.addEventListener("alpine:init", () => {
    Alpine.data("notifications", () => ({
        notifications: [],

        init() {
            // Listen for Livewire notification events
            this.$wire.on("show-notification", (data) => {
                this.addNotification(data.type, data.message);
            });
        },

        addNotification(type, message, duration = 5000) {
            const id = Date.now();
            const notification = {
                id,
                type,
                message,
                visible: false,
            };

            this.notifications.push(notification);

            // Show with animation
            this.$nextTick(() => {
                const notif = this.notifications.find((n) => n.id === id);
                if (notif) notif.visible = true;
            });

            // Auto remove after duration
            setTimeout(() => {
                this.removeNotification(id);
            }, duration);
        },

        removeNotification(id) {
            const index = this.notifications.findIndex((n) => n.id === id);
            if (index > -1) {
                this.notifications[index].visible = false;
                // Remove from array after animation
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                }, 300);
            }
        },

        getIcon(type) {
            const icons = {
                success: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>`,
                error: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>`,
                info: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`,
                warning: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L3.098 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>`,
            };
            return icons[type] || icons.info;
        },

        getColorClasses(type) {
            const colors = {
                success: "bg-green-500 text-white",
                error: "bg-red-500 text-white",
                info: "bg-blue-500 text-white",
                warning: "bg-yellow-500 text-black",
            };
            return colors[type] || colors.info;
        },
    }));
});
