<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    patients: Array
});

const getStatusBadge = (score, isCritical) => {
    if (isCritical) return { text: 'CRITICAL', class: 'bg-red-100 text-red-800 border-red-200' };
    if (!score) return { text: 'No Data', class: 'bg-gray-100 text-gray-500' };
    if (score >= 8) return { text: 'Excellent', class: 'bg-green-100 text-green-800' };
    if (score >= 5) return { text: 'Stable', class: 'bg-blue-100 text-blue-800' };
    return { text: 'Low Mood', class: 'bg-orange-100 text-orange-800' };
};
</script>

<template>
    <Head title="Specialist Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Patient Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="bg-white p-6 shadow-sm sm:rounded-lg mb-6 border-l-4 border-indigo-500">
                    <h3 class="text-lg font-bold text-gray-900">Active Case Load</h3>
                    <p class="text-gray-600">
                        You have {{ patients.length }} active patients assigned to you.
                        Patients marked in <span class="text-red-600 font-bold">red</span> require immediate attention.
                    </p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="patient in patients"
                                :key="patient.id"
                                :class="patient.is_critical ? 'bg-red-50' : ''"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-0">
                                            <div class="text-sm font-medium text-gray-900">{{ patient.name }}</div>
                                            <div class="text-sm text-gray-500">{{ patient.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ patient.joined_at }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ patient.last_activity }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                getStatusBadge(patient.current_status, patient.is_critical).class
                                            ]"
                                        >
                                            {{ getStatusBadge(patient.current_status, patient.is_critical).text }}
                                            <span v-if="patient.current_status">({{ patient.current_status }}/10)</span>
                                        </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('psychologist.patient.show', patient.id)"
                                        class="text-indigo-600 hover:text-indigo-900 font-bold"
                                    >
                                        Open File &rarr;
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="patients.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    No patients have been assigned to you yet.
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
