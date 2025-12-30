<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MoodChart from '@/Components/MoodChart.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    moodGraph: Object,
    stats: Object,
    recent_entries: Array
});

// Helper for color coding the average mood
const getMoodColor = (score) => {
    if (score >= 8) return 'text-green-600';
    if (score >= 5) return 'text-blue-600';
    return 'text-orange-600';
};
</script>

<template>
    <Head title="Insights" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Emotional Insights
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                        <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Entries</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_logs }}</div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                        <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Average Mood</div>
                        <div :class="['mt-2 text-3xl font-bold', getMoodColor(stats.average_mood)]">
                            {{ stats.average_mood }} / 10
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                        <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Best Day</div>
                        <div class="mt-2 text-xl font-bold text-gray-900">{{ stats.best_day }}</div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                        <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Hardest Day</div>
                        <div class="mt-2 text-xl font-bold text-gray-900">{{ stats.worst_day }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Mood History</h3>
                    <div v-if="moodGraph.data.length > 0" class="h-80 w-full">
                        <MoodChart
                            :labels="moodGraph.labels"
                            :data="moodGraph.data"
                        />
                    </div>
                    <div v-else class="text-center py-10 text-gray-400">
                        Not enough data to display graph.
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Notes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="entry in recent_entries" :key="entry.created_at">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(entry.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xl mr-2">{{ entry.emoji }}</span>
                                    <span class="font-bold text-gray-700">{{ entry.score }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 italic">
                                    {{ entry.notes || 'No notes added.' }}
                                </td>
                            </tr>
                            <tr v-if="recent_entries.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No entries found.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
