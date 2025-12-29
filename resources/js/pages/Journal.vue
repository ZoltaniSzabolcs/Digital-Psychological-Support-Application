<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    entries: {
        type: Array,
        default: () => [],
    },
});

// --- Audio Recorder Logic ---
const isRecording = ref(false);
const audioBlob = ref(null);
const audioUrl = ref(null); // For previewing before submit
let mediaRecorder = null;
let audioChunks = [];

const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            audioChunks.push(event.data);
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            audioBlob.value = blob;
            audioUrl.value = URL.createObjectURL(blob);

            // Attach to form
            form.audio = blob;
        };

        mediaRecorder.start();
        isRecording.value = true;
    } catch (err) {
        alert('Microphone access denied or not supported.');
        console.error(err);
    }
};

const stopRecording = () => {
    if (mediaRecorder) {
        mediaRecorder.stop();
        isRecording.value = false;
        // Stop all tracks to release microphone
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
    }
};

const deleteRecording = () => {
    audioBlob.value = null;
    audioUrl.value = null;
    form.audio = null;
};

// --- Form Logic ---
const form = useForm({
    content: '',
    visibility: 'private',
    audio: null, // New field for the file
});

const submitJournal = () => {
    form.post(route('journal.store'), {
        forceFormData: true, // REQUIRED for file uploads in Inertia
        onSuccess: () => {
            form.reset();
            deleteRecording(); // Clear the recorder
            alert('Journal entry created successfully!');
        },
        onError: (e) => {
            console.error(e);
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

                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="mb-4 text-lg font-bold text-gray-900">
                        Write or Record
                    </h3>

                    <form @submit.prevent="submitJournal" class="space-y-4">

                        <textarea
                            v-model="form.content"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Write your thoughts here..."
                        ></textarea>

                        <div class="flex items-center gap-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <button
                                type="button"
                                @click="isRecording ? stopRecording() : startRecording()"
                                :class="[
                                    'flex items-center gap-2 px-4 py-2 rounded-full font-bold transition-all',
                                    isRecording
                                        ? 'bg-red-100 text-red-600 animate-pulse border border-red-200'
                                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100'
                                ]"
                            >
                                <span v-if="isRecording">🛑 Stop Recording</span>
                                <span v-else>🎙️ Record Audio</span>
                            </button>

                            <div v-if="audioUrl" class="flex items-center gap-3 flex-1">
                                <audio controls :src="audioUrl" class="h-8 w-full max-w-[200px]"></audio>
                                <button @click="deleteRecording" type="button" class="text-red-500 hover:text-red-700 text-sm">
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" value="private" v-model="form.visibility" class="text-blue-600 focus:ring-blue-500" />
                                    <span>Private</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" value="shared" v-model="form.visibility" class="text-blue-600 focus:ring-blue-500" />
                                    <span>Share with psychologist</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex justify-center rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 shadow-sm"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Entry' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Your entries</h3>

                    <div v-if="entries.length" class="space-y-4">
                        <div
                            v-for="entry in entries"
                            :key="entry.id"
                            class="bg-white p-5 shadow-sm sm:rounded-lg border border-gray-100"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div class="text-sm text-gray-500">
                                    {{ new Date(entry.created_at).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) }}
                                </div>
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full font-medium',
                                        entry.visibility === 'shared' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'
                                    ]"
                                >
                                    {{ entry.visibility === 'shared' ? 'Shared with Specialist' : 'Private' }}
                                </span>
                            </div>

                            <p v-if="entry.content" class="whitespace-pre-line text-gray-800 mb-3">
                                {{ entry.content }}
                            </p>

                            <div v-if="entry.audio_url" class="mt-3 bg-gray-50 p-2 rounded-lg inline-block w-full max-w-md">
                                <div class="text-xs text-gray-500 mb-1 ml-1 font-semibold uppercase tracking-wider">Voice Note</div>
                                <audio controls :src="entry.audio_url" class="w-full h-8"></audio>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                        <p class="text-gray-500">You haven’t written any journal entries yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
