<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    materials: Array,
    canPost: Boolean,
});

const files = ref([]);

const form = useForm({
    type: '',
    title: '',
    content: '',
    media: null,
});

const onFileChange = (e) => {
    if (!e.target || !e.target.files) return;
    files.value = Array.from(e.target.files);
    form.media = files.value[0];
};

const submitMaterial = () => {
    form.post(route('materials.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            files.value = [];
            alert('Material posted successfully!');
        },
        onError: (errors) => {
            console.error(errors);
            alert(JSON.stringify(errors, null, 2));
        },
    });
};

// --- YOUTUBE HELPERS ---

const getYouTubeId = (url) => {
    const regExp =
        /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return match && match[2].length === 11 ? match[2] : null;
};

const isYouTube = (url) => !!getYouTubeId(url);

const getYouTubeEmbed = (url) => {
    const id = getYouTubeId(url);
    return id ? `https://www.youtube.com/embed/${id}` : null;
};

// --- FILE HELPERS ---

const isImageFile = (path) => {
    if (!path) return false;
    const ext = path.split('.').pop().toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
};

const isVideoFile = (path) => {
    if (!path) return false;
    const ext = path.split('.').pop().toLowerCase();
    return ['mp4', 'webm'].includes(ext);
};
</script>

<template>
    <Head title="Psychoeducational Materials" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Psychoeducational Materials
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="props.canPost"
                    class="bg-white p-6 shadow-sm sm:rounded-lg"
                >
                    <h3 class="mb-4 text-lg font-bold">Share new material</h3>
                    <form @submit.prevent="submitMaterial" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Type</label
                            >
                            <select
                                v-model="form.type"
                                class="block w-full rounded-md border-gray-300"
                            >
                                <option value="" disabled>
                                    -- Select type --
                                </option>
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="link">
                                    Link (URL / YouTube)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Title</label
                            >
                            <input
                                v-model="form.title"
                                type="text"
                                class="block w-full rounded-md border-gray-300"
                            />
                        </div>

                        <div
                            v-if="form.type === 'text' || form.type === 'link'"
                        >
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                {{
                                    form.type === 'link'
                                        ? 'Paste Link (URL or YouTube)'
                                        : 'Content'
                                }}
                            </label>
                            <textarea
                                v-model="form.content"
                                rows="3"
                                class="block w-full rounded-md border-gray-300"
                            ></textarea>
                        </div>

                        <div
                            v-if="
                                ['image', 'video', 'audio'].includes(form.type)
                            "
                        >
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Upload file</label
                            >
                            <input
                                type="file"
                                @change="onFileChange"
                                class="block w-full text-sm"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-white"
                        >
                            Post
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="material in props.materials"
                        :key="material.id"
                        class="bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div class="mb-2 text-sm text-gray-500">
                            {{ material.author?.name }} ·
                            {{ new Date(material.created_at).toLocaleString() }}
                        </div>

                        <h4 v-if="material.title" class="mb-2 font-bold">
                            {{ material.title }}
                        </h4>

                        <div v-if="material.type === 'link'" class="space-y-3">
                            <template v-if="isYouTube(material.content)">
                                <div
                                    class="aspect-video w-full overflow-hidden rounded-lg shadow-sm"
                                >
                                    <iframe
                                        class="h-full w-full"
                                        :src="getYouTubeEmbed(material.content)"
                                        frameborder="0"
                                        allow="
                                            accelerometer;
                                            autoplay;
                                            clipboard-write;
                                            encrypted-media;
                                            gyroscope;
                                            picture-in-picture;
                                        "
                                        allowfullscreen
                                    ></iframe>
                                </div>
                                <a
                                    :href="material.content"
                                    target="_blank"
                                    class="text-xs text-indigo-500 hover:underline"
                                >
                                    Open on YouTube →
                                </a>
                            </template>

                            <template v-else>
                                <a
                                    :href="material.content"
                                    target="_blank"
                                    class="block rounded-lg border p-4 transition hover:bg-gray-50"
                                >
                                    <div
                                        class="flex items-center space-x-2 font-medium text-indigo-600"
                                    >
                                        <span>🔗 Visit Link:</span>
                                        <span class="truncate">{{
                                            material.content
                                        }}</span>
                                    </div>
                                </a>
                            </template>
                        </div>

                        <p
                            v-else-if="material.content"
                            class="mb-4 whitespace-pre-line text-gray-800"
                        >
                            {{ material.content }}
                        </p>

                        <div v-if="material.media_path" class="mt-4">
                            <img
                                v-if="
                                    material.type === 'image' ||
                                    isImageFile(material.media_path)
                                "
                                :src="`/storage/${material.media_path}`"
                                class="max-h-96 w-full rounded-lg bg-gray-50 object-contain"
                            />

                            <video
                                v-else-if="
                                    material.type === 'video' ||
                                    isVideoFile(material.media_path)
                                "
                                controls
                                class="max-h-96 w-full rounded-lg"
                            >
                                <source
                                    :src="`/storage/${material.media_path}`"
                                />
                            </video>

                            <audio
                                v-else-if="material.type === 'audio'"
                                controls
                                class="mt-2 w-full"
                            >
                                <source
                                    :src="`/storage/${material.media_path}`"
                                />
                            </audio>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
