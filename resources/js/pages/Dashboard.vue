<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// 1. Receive Backend Props
const props = defineProps({
    hasLoggedToday: Boolean,
    availablePsychologists: Array, // List from DB
    currentPsychologistId: Number  // Current selection (can be null)
});

const user = usePage().props.auth.user;

// --- MOOD FORM LOGIC ---
const moodForm = useForm({
    score: 5,
    emoji: '😐',
    suicidal_thought_flag: false,
    notes: ''
});

const currentEmoji = computed(() => {
    const s = moodForm.score;
    if (s >= 9) return '🤩';
    if (s >= 7) return '🙂';
    if (s >= 5) return '😐';
    if (s >= 3) return '😢';
    return '😭';
});

const submitMood = () => {
    moodForm.emoji = currentEmoji.value;
    moodForm.post(route('mood.store'), {
        onSuccess: () => moodForm.reset(),
        onError: () => alert('Something went wrong with mood logging.')
    });
};

// --- PSYCHOLOGIST ASSIGNMENT FORM LOGIC ---
const psychologistForm = useForm({
    psychologist_id: props.currentPsychologistId || ""
});

const assignPsychologist = () => {
    psychologistForm.put(route('user.assign_psychologist'), {
        onSuccess: () => alert('Specialist updated successfully!'),
        preserveScroll: true
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

        <div class="py-12 space-y-6"> <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    My Specialist
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Select the psychologist who will monitor your progress. You can only be assigned to one specialist at a time.
                </p>

                <form @submit.prevent="assignPsychologist" class="flex gap-4 items-end">
                    <div class="w-full max-w-md">
                        <label for="psychologist" class="block text-sm font-medium text-gray-700">Choose Specialist</label>
                        <select
                            id="psychologist"
                            v-model="psychologistForm.psychologist_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="" disabled>-- Select a Psychologist --</option>
                            <option
                                v-for="psych in props.availablePsychologists"
                                :key="psych.id"
                                :value="psych.id"
                            >
                                {{ psych.name }} ({{ psych.email }})
                            </option>
                        </select>
                        <div v-if="psychologistForm.errors.psychologist_id" class="text-red-600 text-sm mt-1">
                            {{ psychologistForm.errors.psychologist_id }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="psychologistForm.processing"
                        class="mb-0.5 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        {{ psychologistForm.processing ? 'Saving...' : 'Assign Specialist' }}
                    </button>
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">

                <div v-if="props.hasLoggedToday" class="text-center py-10">
                    <div class="text-6xl mb-4">✅</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                        Check-in Complete!
                    </h3>
                    <p class="text-gray-600">
                        You have already recorded your mood for today. <br>
                        Come back tomorrow to track your progress.
                    </p>
                </div>

                <div v-else>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        How are you feeling today?
                    </h3>

                    <form @submit.prevent="submitMood" class="space-y-6 max-w-xl">

                        <div class="text-center">
                            <span class="text-6xl block mb-2">{{ currentEmoji }}</span>
                            <span class="text-2xl font-bold text-blue-600">{{ moodForm.score }} / 10</span>
                        </div>

                        <div>
                            <input
                                type="range"
                                min="1"
                                max="10"
                                step="1"
                                v-model.number="moodForm.score"
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
                                v-model="moodForm.notes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="2"
                                placeholder="Anything specific on your mind?"
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="safety_flag"
                                type="checkbox"
                                v-model="moodForm.suicidal_thought_flag"
                                class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                            >
                            <label for="safety_flag" class="ml-2 block text-sm text-red-600 font-bold">
                                I am having thoughts of self-harm
                            </label>
                        </div>

                        <div>
                            <button
                                type="submit"
                                :disabled="moodForm.processing"
                                class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                {{ moodForm.processing ? 'Saving...' : 'Log Mood' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        </div>
    </AuthenticatedLayout>
</template>
