<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MoodChart from '@/Components/MoodChart.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    patient: Object,
    moodGraph: Object,
    weeklyReports: Object // Passed as a keyed object from Laravel Collection
});

// Helper for mood coloring
const getScoreColor = (score) => {
    if (score >= 8) return 'text-green-600 font-bold';
    if (score >= 5) return 'text-blue-600 font-bold';
    return 'text-red-600 font-bold';
};
</script>

<template>
    <Head :title="`Patient: ${patient.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Patient File: {{ patient.name }}
                </h2>
                <Link :href="route('dashboard')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    &larr; Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">60-Day Mood Evolution</h3>
                    <div v-if="moodGraph.data.length" class="h-80 w-full">
                        <MoodChart :labels="moodGraph.labels" :data="moodGraph.data" />
                    </div>
                    <div v-else class="text-center py-10 text-gray-500">
                        Insufficient data to generate graph.
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Weekly Progress Reports</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week Range</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Mood</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dominant Emotion</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Alerts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(report, key) in weeklyReports" :key="key">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ report.week_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="getScoreColor(report.average_mood)">
                                            {{ report.average_mood }}
                                        </span>
                                    <span class="text-xs text-gray-400 ml-1">
                                            (Range: {{ report.lowest_score }}-{{ report.highest_score }})
                                        </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ report.total_logs }} entries
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-2xl">
                                    {{ report.dominant_emoji }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="report.alerts_count > 0" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ report.alerts_count }} Critical
                                        </span>
                                    <span v-else class="text-gray-400 text-sm">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="report.average_mood >= 5" class="text-green-600">Stable</span>
                                    <span v-else class="text-orange-600">Declining</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
