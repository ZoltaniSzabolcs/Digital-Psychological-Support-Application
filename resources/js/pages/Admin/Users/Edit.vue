<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    psychologists: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    assigned_psychologist_id: props.user.assigned_psychologist_id || '',
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        onSuccess: () => alert('User updated successfully!'),
    });
};
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit User: {{ user.name }}
                </h2>
                <Link :href="route('admin.users.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select
                                v-model="form.role"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="patient">Patient</option>
                                <option value="psychologist">Psychologist</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</div>
                        </div>

                        <div v-if="form.role === 'patient'" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign Specialist</label>
                            <p class="text-xs text-gray-500 mb-2">Select the psychologist responsible for this patient.</p>

                            <select
                                v-model="form.assigned_psychologist_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>-- Select Specialist --</option>
                                <option v-for="psych in psychologists" :key="psych.id" :value="psych.id">
                                    {{ psych.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.assigned_psychologist_id" class="text-red-500 text-xs mt-1">
                                {{ form.errors.assigned_psychologist_id }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                            <Link :href="route('admin.users.index')" class="text-gray-600 hover:text-gray-900 text-sm">
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving Changes...' : 'Update User' }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
