<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    entries: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    content: '',
    visibility: 'private',
});

const submitJournal = () => {
    form.post(route('journal.store'), {
        onSuccess: () => {
            form.reset();
            alert('Journal entry created successfully!');
        },
        onError: () => {
            alert('Something went wrong.');
        },
    });
};
</script>

<template>
    <Head title="Journal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Journal
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-8 sm:px-6 lg:px-8">
                <!-- Create Journal Entry -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">
                        Write a journal entry
                    </h3>

                    <form @submit.prevent="submitJournal" class="space-y-4">
                        <textarea
                            v-model="form.content"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Write your thoughts here..."
                        ></textarea>

                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    value="private"
                                    v-model="form.visibility"
                                />
                                Private
                            </label>

                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    value="shared"
                                    v-model="form.visibility"
                                />
                                Share with psychologist
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Entry' }}
                        </button>
                    </form>
                </div>

                <!-- Journal Entries List -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Your entries
                    </h3>

                    <div v-if="entries.length" class="space-y-4">
                        <div
                            v-for="entry in entries"
                            :key="entry.id"
                            class="bg-white p-4 shadow-sm sm:rounded-lg"
                        >
                            <div class="mb-1 text-sm text-gray-500">
                                {{
                                    new Date(entry.created_at).toLocaleString()
                                }}
                                ·
                                <span
                                    :class="
                                        entry.visibility === 'shared'
                                            ? 'text-blue-600'
                                            : 'text-gray-400'
                                    "
                                >
                                    {{ entry.visibility }}
                                </span>
                            </div>

                            <p class="whitespace-pre-line text-gray-800">
                                {{ entry.content }}
                            </p>
                        </div>
                    </div>

                    <p v-else class="text-gray-500">
                        You haven’t written any journal entries yet.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
