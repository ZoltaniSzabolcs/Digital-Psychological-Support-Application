<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    initialUsers: Object // The CursorPaginator object from Laravel
});

// Reactive state
const allUsers = ref([...props.initialUsers.data]);
const nextCursor = ref(props.initialUsers.next_page_url);
const isLoading = ref(false);
const observerTarget = ref(null); // The HTML element we watch

// --- Infinite Scroll Logic ---
const loadMoreUsers = async () => {
    if (isLoading.value || !nextCursor.value) return;

    isLoading.value = true;

    try {
        // Fetch next page via standard JSON AJAX
        const response = await axios.get(nextCursor.value);

        // Append new users to the list
        allUsers.value = [...allUsers.value, ...response.data.data];

        // Update cursor
        nextCursor.value = response.data.next_page_url;
    } catch (error) {
        console.error("Failed to load users", error);
    } finally {
        isLoading.value = false;
    }
};

// Set up the IntersectionObserver
let observer = null;

onMounted(() => {
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            loadMoreUsers();
        }
    }, {
        rootMargin: '100px', // Start loading before user hits exact bottom
    });

    if (observerTarget.value) {
        observer.observe(observerTarget.value);
    }
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});
</script>

<template>
    <Head title="Chat Directory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Messages Directory
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                    <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700">Start a Conversation</h3>
                        <span class="text-xs text-gray-500">{{ allUsers.length }} users loaded</span>
                    </div>

                    <ul class="divide-y divide-gray-100">
                        <li v-for="user in allUsers" :key="user.id" class="hover:bg-gray-50 transition">
                            <Link
                                :href="route('chat.show', user.id)"
                                class="flex items-center px-6 py-4"
                            >
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold shrink-0">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>

                                <div class="ml-4 flex-1">
                                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </div>

                                <div class="text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                            </Link>
                        </li>
                    </ul>

                    <div ref="observerTarget" class="p-6 text-center">
                        <div v-if="isLoading" class="text-gray-500 flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading more...
                        </div>
                        <div v-else-if="!nextCursor && allUsers.length > 0" class="text-sm text-gray-400">
                            End of list
                        </div>
                        <div v-else-if="allUsers.length === 0" class="text-gray-500">
                            No users found.
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
