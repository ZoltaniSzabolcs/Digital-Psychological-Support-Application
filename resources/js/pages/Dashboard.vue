<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// 1. Get the Current User from Inertia Props
const user = usePage().props.auth.user;

// 2. Set up the Form
const form = useForm({
    score: 5,               // Default middle value
    emoji: '😐',            // Default emoji
    suicidal_thought_flag: false,
    notes: ''
});

// 3. Dynamic Emoji Logic
// Updates the emoji automatically when the slider moves
const currentEmoji = computed(() => {
    const s = form.score;
    if (s >= 9) return '🤩'; // 9-10
    if (s >= 7) return '🙂'; // 7-8
    if (s >= 5) return '😐'; // 5-6
    if (s >= 3) return '😢'; // 3-4
    return '😭';             // 1-2
});

const submitMood = () => {
    // Sync computed emoji to form before submit
    form.emoji = currentEmoji.value;

    // Post to your Laravel Route
    form.post(route('mood.store'), {
        onSuccess: () => {
            // Optional: Show a toast or reset form
            form.reset();
            alert('Mood logged successfully!');
        },
        onError: () => {
            alert('Something went wrong.');
        }
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Welcome back, {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        How are you feeling today?
                    </h3>

                    <form @submit.prevent="submitMood" class="space-y-6 max-w-xl">

                        <div class="text-center">
                            <span class="text-6xl block mb-2">{{ currentEmoji }}</span>
                            <span class="text-2xl font-bold text-blue-600">{{ form.score }} / 10</span>
                        </div>

                        <div>
                            <input
                                type="range"
                                min="1"
                                max="10"
                                step="1"
                                v-model.number="form.score"
                                class="w-full h-4 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                            >
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>Very Bad (1)</span>
                                <span>Neutral (5)</span>
                                <span>Excellent (10)</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea
                                v-model="form.notes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="2"
                                placeholder="Anything specific on your mind?"
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="safety_flag"
                                type="checkbox"
                                v-model="form.suicidal_thought_flag"
                                class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                            >
                            <label for="safety_flag" class="ml-2 block text-sm text-red-600 font-bold">
                                I am having thoughts of self-harm
                            </label>
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Log Mood' }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
