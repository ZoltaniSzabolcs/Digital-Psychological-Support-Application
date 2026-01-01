<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Make sure lodash is installed
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object, // Paginator object
    filters: Object,
});

// --- Search Logic ---
const search = ref(props.filters.search || '');

// Watch for changes in search input and trigger a "get" request
watch(search, debounce((value) => {
    router.get(
        route('admin.users.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300));

// --- Role Update Logic ---
const updateRole = (user, newRole) => {
    if (confirm(`Are you sure you want to change ${user.name}'s role to ${newRole}?`)) {
        router.put(route('admin.users.update', user.id), {
            role: newRole
        }, {
            preserveScroll: true,
            onSuccess: () => alert('Role updated!')
        });
    } else {
        // Revert the dropdown if they cancel (optional UI fix)
        // In a real app, you might just force a page reload or manage local state better
        window.location.reload();
    }
};

// --- Helper for Role Colors ---
const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'admin': return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'psychologist': return 'bg-blue-100 text-blue-800 border-blue-200';
        default: return 'bg-green-100 text-green-800 border-green-200';
    }
};
</script>

<template>
    <Head title="Manage Users" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                User Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                    <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">All Users ({{ users.total }})</h3>
                        <div class="w-1/3">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name or email..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select
                                        :value="user.role"
                                        @change="updateRole(user, $event.target.value)"
                                        :class="['text-xs font-semibold rounded-full px-2 py-1 border-0 ring-1 ring-inset cursor-pointer focus:ring-2 focus:ring-indigo-600', getRoleBadgeClass(user.role)]"
                                    >
                                        <option value="patient">Patient</option>
                                        <option value="psychologist">Psychologist</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('admin.users.edit', user.id)"
                                        class="text-indigo-600 hover:text-indigo-900 font-bold"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    No users found matching "{{ search }}".
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center" v-if="users.links.length > 3">
                        <div class="text-sm text-gray-500">
                            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, k) in users.links"
                                :key="k"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 border rounded text-sm',
                                    link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AdminLayout>
</template>
