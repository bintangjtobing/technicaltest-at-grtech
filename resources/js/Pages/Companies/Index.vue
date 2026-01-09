<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { message, Modal } from 'ant-design-vue';

const props = defineProps({
    companies: Object,
    filters: Object,
});

const page = usePage();
const searchText = ref(props.filters?.search || '');
const showModal = ref(false);
const editModal = ref(false);
const currentCompany = ref(null);
const form = ref({
    name: '',
    email: '',
    website: '',
    logo: null,
});

const columns = [
    { title: '#', key: 'index', width: 60 },
    { title: 'Name', dataIndex: 'name', key: 'name', sorter: true },
    { title: 'Email', dataIndex: 'email', key: 'email' },
    { title: 'Logo', dataIndex: 'logo', key: 'logo', width: 100 },
    { title: 'Website', dataIndex: 'website', key: 'website' },
    { title: 'Action', key: 'action', width: 150 },
];

const pagination = computed(() => ({
    current: props.companies.meta.current_page,
    pageSize: props.companies.meta.per_page,
    total: props.companies.meta.total,
    showSizeChanger: true,
    pageSizeOptions: ['10', '25', '50'],
}));

const handleTableChange = (pag, filters, sorter) => {
    router.get(route('companies.index'), {
        page: pag.current,
        pageSize: pag.pageSize,
        search: searchText.value,
        sortField: sorter.field || 'id',
        sortOrder: sorter.order || 'ascend',
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get(route('companies.index'), {
        search: searchText.value,
    }, { preserveState: true });
};

const openCreateModal = () => {
    form.value = { name: '', email: '', website: '', logo: null };
    showModal.value = true;
};

const openEditModal = (company) => {
    currentCompany.value = company;
    form.value = {
        name: company.name,
        email: company.email || '',
        website: company.website || '',
        logo: null,
    };
    editModal.value = true;
};

const handleCreate = () => {
    const formData = new FormData();
    formData.append('name', form.value.name);
    if (form.value.email) formData.append('email', form.value.email);
    if (form.value.website) formData.append('website', form.value.website);
    if (form.value.logo) formData.append('logo', form.value.logo);

    router.post(route('companies.store'), formData, {
        onSuccess: () => {
            showModal.value = false;
            message.success('Company created successfully');
        },
        onError: (errors) => {
            message.error(Object.values(errors)[0]);
        },
    });
};

const handleUpdate = () => {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', form.value.name);
    if (form.value.email) formData.append('email', form.value.email);
    if (form.value.website) formData.append('website', form.value.website);
    if (form.value.logo) formData.append('logo', form.value.logo);

    router.post(route('companies.update', currentCompany.value.id), formData, {
        onSuccess: () => {
            editModal.value = false;
            message.success('Company updated successfully');
        },
        onError: (errors) => {
            message.error(Object.values(errors)[0]);
        },
    });
};

const handleDelete = (id) => {
    Modal.confirm({
        title: 'Delete Company',
        content: 'Are you sure you want to delete this company?',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
            router.delete(route('companies.destroy', id), {
                onSuccess: () => {
                    message.success('Company deleted successfully');
                },
            });
        },
    });
};

const handleFileChange = (e) => {
    form.value.logo = e.target.files[0];
};

watch(() => page.props.flash, (flash) => {
    if (flash.success) message.success(flash.success);
    if (flash.error) message.error(flash.error);
}, { immediate: true });
</script>

<template>
    <Head title="Companies" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Companies</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 flex justify-between">
                            <a-input-search v-model:value="searchText" placeholder="Search companies..." style="width: 300px" @search="handleSearch" />
                            <a-button type="primary" @click="openCreateModal">Add Company</a-button>
                        </div>

                        <a-table :columns="columns" :data-source="companies.data" :pagination="pagination" @change="handleTableChange" row-key="id">
                            <template #bodyCell="{ column, record, index }">
                                <template v-if="column.key === 'index'">
                                    {{ (companies.meta.current_page - 1) * companies.meta.per_page + index + 1 }}
                                </template>
                                <template v-else-if="column.key === 'logo'">
                                    <img v-if="record.logo" :src="record.logo" class="h-10 w-10 object-cover rounded" />
                                    <span v-else>-</span>
                                </template>
                                <template v-else-if="column.key === 'website'">
                                    <a v-if="record.website" :href="record.website" target="_blank" class="text-blue-600 hover:underline">{{ record.website }}</a>
                                    <span v-else>-</span>
                                </template>
                                <template v-else-if="column.key === 'action'">
                                    <a-space>
                                        <a-button size="small" @click="openEditModal(record)">Edit</a-button>
                                        <a-button size="small" danger @click="handleDelete(record.id)">Delete</a-button>
                                    </a-space>
                                </template>
                            </template>
                        </a-table>
                    </div>
                </div>
            </div>
        </div>

        <a-modal v-model:open="showModal" title="Add Company" @ok="handleCreate" ok-text="Create">
            <a-form layout="vertical">
                <a-form-item label="Name" required>
                    <a-input v-model:value="form.name" />
                </a-form-item>
                <a-form-item label="Email">
                    <a-input v-model:value="form.email" type="email" />
                </a-form-item>
                <a-form-item label="Website">
                    <a-input v-model:value="form.website" />
                </a-form-item>
                <a-form-item label="Logo">
                    <input type="file" @change="handleFileChange" accept=".jpg,.jpeg,.png,.svg" />
                    <p class="text-xs text-gray-500 mt-1">Max 2.5MB (JPG, PNG, SVG)</p>
                </a-form-item>
            </a-form>
        </a-modal>

        <a-modal v-model:open="editModal" title="Edit Company" @ok="handleUpdate" ok-text="Update">
            <a-form layout="vertical">
                <a-form-item label="Name" required>
                    <a-input v-model:value="form.name" />
                </a-form-item>
                <a-form-item label="Email">
                    <a-input v-model:value="form.email" type="email" />
                </a-form-item>
                <a-form-item label="Website">
                    <a-input v-model:value="form.website" />
                </a-form-item>
                <a-form-item label="Logo">
                    <div v-if="currentCompany?.logo" class="mb-2">
                        <img :src="currentCompany.logo" class="h-16 w-16 object-cover rounded" />
                    </div>
                    <input type="file" @change="handleFileChange" accept=".jpg,.jpeg,.png,.svg" />
                    <p class="text-xs text-gray-500 mt-1">Max 2.5MB (JPG, PNG, SVG)</p>
                </a-form-item>
            </a-form>
        </a-modal>
    </AuthenticatedLayout>
</template>
