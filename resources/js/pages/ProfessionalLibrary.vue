<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    resources: Array,
});

const fileInput = ref(null);

const form = useForm({
    type: 'pdf',
    title: '',
    description: '',
    file: null,
    external_url: '',
});

const onFileChange = (e) => {
    if (!e.target || !e.target.files) return;
    form.file = e.target.files[0];
};

const submit = () => {
    form.post(route('professional.library.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const isFile = (r) => r.file_path;
</script>

<template>
    <Head title="Professional Library" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Professional Library
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <!-- CREATE RESOURCE -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 text-lg font-bold">
                        Add Professional Resource
                    </h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <select
                            v-model="form.type"
                            class="w-full rounded-md border-gray-300"
                        >
                            <option value="pdf">PDF / Worksheet</option>
                            <option value="document">Document</option>
                            <option value="video">Video</option>
                            <option value="link">External Link</option>
                        </select>

                        <input
                            v-model="form.title"
                            placeholder="Title"
                            class="w-full rounded-md border-gray-300"
                        />

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Description"
                            class="w-full rounded-md border-gray-300"
                        ></textarea>

                        <input
                            v-if="form.type !== 'link'"
                            ref="fileInput"
                            type="file"
                            @change="onFileChange"
                            class="block w-full text-sm"
                        />

                        <input
                            v-if="form.type === 'link'"
                            v-model="form.external_url"
                            placeholder="https://..."
                            class="w-full rounded-md border-gray-300"
                        />

                        <button
                            class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing ? 'Saving...' : 'Save Resource'
                            }}
                        </button>
                    </form>
                </div>

                <!-- RESOURCE LIST -->
                <div class="space-y-4">
                    <div
                        v-for="r in props.resources"
                        :key="r.id"
                        class="bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div class="mb-1 text-sm text-gray-500">
                            {{ r.author.name }} ·
                            {{ new Date(r.created_at).toLocaleString() }}
                        </div>

                        <h4 class="font-bold">{{ r.title }}</h4>
                        <p v-if="r.description" class="mb-3 text-gray-700">
                            {{ r.description }}
                        </p>

                        <a
                            v-if="isFile(r)"
                            :href="`/storage/${r.file_path}`"
                            target="_blank"
                            class="text-indigo-600 hover:underline"
                        >
                            Open file
                        </a>

                        <a
                            v-if="r.external_url"
                            :href="r.external_url"
                            target="_blank"
                            class="text-indigo-600 hover:underline"
                        >
                            Open link
                        </a>
                    </div>

                    <p
                        v-if="!props.resources.length"
                        class="text-center text-gray-500"
                    >
                        No resources yet.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
