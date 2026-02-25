<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

const quotes = ref([]);
const loading = ref(false);

const columns = [
    { title: '#', key: 'index', width: 60 },
    { title: 'Quote', dataIndex: 'q', key: 'quote' },
    { title: 'Author', dataIndex: 'a', key: 'author', width: 200 },
];

const fetchQuotes = async () => {
    loading.value = true;
    try {
        const token = usePage().props.token;
        const response = await axios.get('/api/quotes', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });
        quotes.value = response.data;
    } catch (error) {
        if (error.response?.status === 401) {
            message.error('Unauthorized. Please reload the page.');
        } else {
            message.error('Failed to fetch quotes');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchQuotes();
});
</script>

<template>
    <Head title="Daily Quotes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Daily Quotes</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 flex justify-end">
                            <a-button type="primary" :loading="loading" @click="fetchQuotes">
                                Refresh Quotes
                            </a-button>
                        </div>

                        <a-table
                            :columns="columns"
                            :data-source="quotes"
                            :loading="loading"
                            :pagination="{ pageSize: 50, showSizeChanger: false }"
                            row-key="q"
                        >
                            <template #bodyCell="{ column, index }">
                                <template v-if="column.key === 'index'">
                                    {{ index + 1 }}
                                </template>
                            </template>
                        </a-table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
