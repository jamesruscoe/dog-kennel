<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useNotificationStore } from '@/stores/useNotificationStore';
import { useTenantRoute } from '@/composables/useTenantRoute';
import type { SharedProps } from '@/types/kennel';

const page = usePage<SharedProps>();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);
const company = computed(() => page.props.company);

const role = computed(() => auth.value.user?.role);
const isStaff = computed(() => role.value === 'staff' || role.value === 'admin');
const isAdmin = computed(() => role.value === 'admin' || role.value === 'super_admin');

const stripeWarning = computed(() => company.value && !company.value.stripe_ready);

const notificationStore = useNotificationStore();
const unreadCount = computed(() => page.props.unread_notifications_count ?? 0);

const tenantRoute = useTenantRoute();

// Flash toast auto-dismiss
const flashVisible = ref(false);
watch(() => flash.value, (f) => {
    if (f.success || f.error) {
        flashVisible.value = true;
        setTimeout(() => { flashVisible.value = false; }, 4000);
    }
}, { immediate: true });

// Mobile sidebar toggle
const sidebarOpen = ref(false);

// ── Navigation definitions ─────────────────────────────────────────────────
const staffNavBase = [
    { label: 'Dashboard',   href: () => tenantRoute('staff.dashboard'),      icon: 'home' },
    { label: 'Owners',      href: () => tenantRoute('staff.owners.index'),   icon: 'users' },
    { label: 'Dogs',        href: () => tenantRoute('staff.dogs.index'),     icon: 'dog' },
    { label: 'Bookings',    href: () => tenantRoute('staff.bookings.index'), icon: 'calendar-days' },
    { label: 'Care Logs',   href: () => tenantRoute('staff.care-logs.index'), icon: 'clipboard-list' },
    { label: 'Calendar',    href: () => tenantRoute('staff.calendar.index'), icon: 'calendar' },
    { label: 'Settings',    href: () => tenantRoute('staff.settings.edit'),  icon: 'cog' },
    { label: 'Staff Users', href: () => tenantRoute('staff.users.index'),    icon: 'user-shield' },
];

const adminNavExtra = [
    { label: 'Finance', href: () => tenantRoute('staff.finance.index'), icon: 'banknotes' },
];

const ownerNav = [
    { label: 'Dashboard', href: () => tenantRoute('owner.dashboard'),      icon: 'home' },
    { label: 'Bookings',  href: () => tenantRoute('owner.bookings.index'), icon: 'calendar-days' },
    { label: 'My Dogs',   href: () => tenantRoute('owner.dogs.index'),     icon: 'dog' },
];

const navItems = computed(() =>
    isStaff.value
        ? (isAdmin.value ? [...staffNavBase, ...adminNavExtra] : staffNavBase)
        : ownerNav
);

function isActiveRoute(href: string): boolean {
    const current = window.location.pathname;
    const target = new URL(href, window.location.origin).pathname;
    return current === target || current.startsWith(target + '/');
}

function logout() {
    if (company.value) {
        router.post(tenantRoute('tenant.logout'));
    } else {
        router.post(route('logout'));
    }
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-zinc-50 dark:bg-zinc-950">
        <!-- ── Mobile sidebar overlay ────────────────────────────────────────── -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- ── Sidebar ───────────────────────────────────────────────────────── -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-zinc-900 transition-transform duration-200 lg:relative lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Brand -->
            <div class="flex h-16 shrink-0 items-center px-6 border-b border-zinc-700">
                <svg class="h-7 w-7 text-indigo-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-lg font-bold text-white">Kennel Manager</span>
            </div>

            <!-- Role label -->
            <div class="px-4 py-3 border-b border-zinc-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">
                    {{ isStaff ? 'Staff Portal' : 'Owner Portal' }}
                </span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-0.5">
                    <li v-for="item in navItems" :key="item.label">
                        <Link
                            :href="item.href()"
                            :class="[
                                'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                isActiveRoute(item.href())
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-zinc-300 hover:bg-zinc-800 hover:text-white',
                            ]"
                            @click="sidebarOpen = false"
                        >
                            <!-- Home icon -->
                            <svg v-if="item.icon === 'home'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <!-- Users icon -->
                            <svg v-else-if="item.icon === 'users'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Dog icon -->
                            <svg v-else-if="item.icon === 'dog'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <!-- Calendar icon -->
                            <svg v-else-if="item.icon === 'calendar' || item.icon === 'calendar-days'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <!-- Clipboard icon -->
                            <svg v-else-if="item.icon === 'clipboard-list'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <!-- Cog icon -->
                            <svg v-else-if="item.icon === 'cog'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Shield icon -->
                            <svg v-else-if="item.icon === 'user-shield'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <!-- Banknotes icon (Finance) -->
                            <svg v-else-if="item.icon === 'banknotes'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                            <!-- Fallback dot -->
                            <span v-else class="h-1.5 w-1.5 rounded-full bg-current" />

                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- User info + logout -->
            <div class="shrink-0 border-t border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ auth.user?.name }}</p>
                        <p class="truncate text-xs text-zinc-400">{{ auth.user?.email }}</p>
                    </div>
                    <button
                        type="button"
                        class="ml-2 rounded-md p-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors"
                        title="Sign out"
                        @click="logout"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ── Main area ─────────────────────────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Stripe not configured banner -->
            <div
                v-if="stripeWarning"
                class="flex shrink-0 items-center justify-center gap-2 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-700 px-4 py-2 text-xs font-medium text-amber-700 dark:text-amber-400"
            >
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span v-if="isStaff">Stripe is not configured. Bookings are disabled until payment is set up.</span>
                <span v-else>This company is not currently accepting bookings.</span>
            </div>
            <!-- Top bar -->
            <header class="flex h-16 shrink-0 items-center justify-between border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 sm:px-6">
                <!-- Mobile hamburger -->
                <button
                    type="button"
                    class="rounded-md p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 lg:flex-none" />

                <!-- Right: Notifications + user shortcut -->
                <div class="flex items-center gap-3">
                    <Link :href="tenantRoute('notifications.index')" class="relative rounded-md p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span
                            v-if="unreadCount > 0"
                            class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
                        >
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </span>
                    </Link>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>

    <!-- ── Flash toast ───────────────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="flashVisible && (flash.success || flash.error)"
                :class="[
                    'fixed bottom-4 right-4 z-50 flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg text-sm font-medium',
                    flash.success ? 'bg-green-600 text-white' : 'bg-red-600 text-white',
                ]"
            >
                <svg v-if="flash.success" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ flash.success ?? flash.error }}
            </div>
        </Transition>
    </Teleport>
</template>
