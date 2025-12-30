<script setup>
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { computed } from 'vue'

// Register Chart.js modules
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

const props = defineProps({
    labels: Array,
    data: Array
})

// Configuration for the Chart
const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: 'Emotional State (1-10)',
            backgroundColor: 'rgba(59, 130, 246, 0.2)', // Light Blue Fill
            borderColor: '#2563eb', // Blue Line
            pointBackgroundColor: '#1d4ed8',
            data: props.data,
            fill: true,
            tension: 0.4 // Smooth curves
        }
    ]
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            min: 1,
            max: 10,
            grid: { color: '#f3f4f6' }
        },
        x: {
            grid: { display: false }
        }
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (context) => `Mood Score: ${context.raw}`
            }
        }
    }
}
</script>

<template>
    <div class="w-full h-64">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
