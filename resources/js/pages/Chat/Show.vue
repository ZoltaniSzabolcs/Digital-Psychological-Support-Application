<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';

const props = defineProps({
    messages: Array,
    partner: Object
});

// --- Chat Container Ref for Scrolling ---
const chatContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

// Scroll on load and when messages change
onMounted(scrollToBottom);
watch(() => props.messages, scrollToBottom, { deep: true });

// --- Polling for New Messages (Fit Criterion: < 1 min) ---
let pollingInterval = null;
onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['messages'] }); // Only fetch new messages
    }, 5000); // 5 Seconds
});
onUnmounted(() => clearInterval(pollingInterval));


// --- Audio Recorder Logic (Simplified) ---
const isRecording = ref(false);
const audioUrl = ref(null);
let mediaRecorder = null;
let audioChunks = [];

const startRecording = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);
        mediaRecorder.onstop = () => {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            form.audio = blob;
            audioUrl.value = URL.createObjectURL(blob);
        };
        mediaRecorder.start();
        isRecording.value = true;
    } catch (err) { alert('Microphone error'); }
};

const stopRecording = () => {
    mediaRecorder.stop();
    isRecording.value = false;
};

const cancelAudio = () => {
    form.audio = null;
    audioUrl.value = null;
};

// --- Form Logic ---
const form = useForm({
    content: '',
    audio: null
});

const submit = () => {
    form.post(route('chat.store', props.partner.id), {
        onSuccess: () => {
            form.reset();
            cancelAudio();
            scrollToBottom();
        },
        forceFormData: true // Crucial for file upload
    });
};
</script>

<template>
    <Head :title="`Chat with ${partner.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Chat with {{ partner.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden flex flex-col h-[600px]">

                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-4">
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="['flex', msg.is_me ? 'justify-end' : 'justify-start']"
                        >
                            <div
                                :class="[
                                    'max-w-[70%] rounded-lg p-3 shadow-sm',
                                    msg.is_me ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none'
                                ]"
                            >
                                <p v-if="msg.type === 'text'" class="text-sm">{{ msg.content }}</p>

                                <div v-if="msg.type === 'audio'" class="flex items-center gap-2">
                                    <span class="text-xs uppercase font-bold opacity-75">Voice Note</span>
                                    <audio :src="msg.audio_url" controls class="h-8 w-48 rounded"></audio>
                                </div>

                                <div :class="['text-[10px] mt-1 text-right', msg.is_me ? 'text-blue-100' : 'text-gray-400']">
                                    {{ msg.created_at }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-white border-t border-gray-200">
                        <form @submit.prevent="submit" class="flex items-end gap-2">

                            <div v-if="audioUrl" class="flex-1 flex items-center gap-3 bg-gray-100 p-2 rounded-lg">
                                <audio :src="audioUrl" controls class="h-8 w-full"></audio>
                                <button type="button" @click="cancelAudio" class="text-red-500 hover:text-red-700">✖</button>
                            </div>

                            <textarea
                                v-else
                                v-model="form.content"
                                rows="1"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-none py-3"
                                placeholder="Type a message..."
                                @keydown.enter.exact.prevent="submit"
                            ></textarea>

                            <div class="flex items-center gap-2 h-[46px]">
                                <button
                                    type="button"
                                    @click="isRecording ? stopRecording() : startRecording()"
                                    :class="[
                                        'p-2 rounded-full transition-all',
                                        isRecording ? 'bg-red-500 text-white animate-pulse' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                    :title="isRecording ? 'Stop Recording' : 'Record Voice'"
                                >
                                    <span v-if="isRecording">⏹</span>
                                    <span v-else>🎙</span>
                                </button>

                                <button
                                    type="submit"
                                    :disabled="form.processing || (!form.content && !form.audio)"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 font-bold"
                                >
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
