<div x-data="notificationSystem()" x-init="init()" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none" style="max-width: 400px;">
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-slate-800 shadow-[0_5px_30px_rgba(0,0,0,0.3)] ring-1 ring-white/10"
             :class="{ 'border-l-4 border-primary': notification.data.icon === 'box', 'border-l-4 border-secondary': notification.data.icon === 'check-circle' }">
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <template x-if="notification.data.icon === 'user-plus'">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                        </template>
                        <template x-if="notification.data.icon === 'shopping-cart'">
                            <svg class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                        </template>
                        <template x-if="notification.data.icon === 'check-circle' || notification.data.icon === 'shield-check'">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </template>
                        <template x-if="notification.data.icon === 'box'">
                            <svg class="h-6 w-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                        </template>
                    </div>
                    
                    <div class="ml-3 w-0 flex-1 pt-0.5" @click="notification.data.url ? window.location.href = notification.data.url : null" :class="{'cursor-pointer hover:opacity-80': notification.data.url}">
                        <p class="text-sm font-bold text-white" x-text="notification.data.title"></p>
                        <p class="mt-1 text-sm text-slate-300" x-text="notification.data.message"></p>
                    </div>
                    
                    <div class="ml-4 flex flex-shrink-0">
                        <button type="button" @click="markAsRead(notification.id)" class="inline-flex rounded-md bg-slate-800 text-slate-400 hover:text-white focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Progress bar -->
            <div class="h-1 bg-slate-700 w-full overflow-hidden">
                <div class="h-full bg-slate-500 transition-all duration-[6000ms] ease-linear" :style="'width: ' + (notification.progress || 0) + '%'"></div>
            </div>
        </div>
    </template>
</div>

<script>
function notificationSystem() {
    return {
        notifications: [],
        checkedIds: new Set(),
        
        init() {
            // Check right away if logged in
            this.fetchNotifications();
            
            // Poll every 15 seconds
            setInterval(() => {
                this.fetchNotifications();
            }, 15000);
        },
        
        fetchNotifications() {
            // Only try if not on welcome page specifically or if we know we are logged in.
            // A simple fetch will just 401 if not logged in, but we handle it gracefully.
            fetch('/notifications/unread', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if(res.ok) return res.json();
                throw new Error("Not authenticated");
            })
            .then(data => {
                if (data.notifications) {
                    data.notifications.forEach(notif => {
                        if (!this.checkedIds.has(notif.id)) {
                            this.checkedIds.add(notif.id);
                            this.addNotification(notif);
                        }
                    });
                }
            })
            .catch(e => {
                // Not logged in or error, quietly ignore
            });
        },
        
        addNotification(notif) {
            const index = this.notifications.push({
                ...notif,
                show: true,
                progress: 100
            }) - 1;
            
            // Trigger animation for progress bar
            setTimeout(() => {
                this.notifications[index].progress = 0;
            }, 50);

            // Auto dismiss after 6 seconds
            setTimeout(() => {
                this.markAsRead(notif.id, true);
            }, 6000);
        },
        
        markAsRead(id, isAuto = false) {
            const notif = this.notifications.find(n => n.id === id);
            if (notif) {
                notif.show = false; // Trigger hide animation
            }
            
            // Tell server
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            }).catch(e => {});
        }
    }
}
</script>
