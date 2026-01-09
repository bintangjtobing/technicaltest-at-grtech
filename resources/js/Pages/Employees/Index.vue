<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { message, Modal } from 'ant-design-vue';

const props = defineProps({
    employees: Object,
    companies: Object,
    filters: Object,
});

const page = usePage();
const searchText = ref(props.filters?.search || '');
const viewMode = ref(props.filters?.view || 'flat');
const showModal = ref(false);
const editModal = ref(false);
const companyModal = ref(false);
const currentEmployee = ref(null);
const selectedCompany = ref(null);
const expandedRowKeys = ref([]);
const form = ref({
    first_name: '',
    last_name: '',
    company_id: null,
    email: '',
    phone: '',
});

const columns = [
    { title: '#', key: 'index', width: 60 },
    { title: 'Full Name', key: 'full_name', sorter: true },
    { title: 'Company', key: 'company' },
    { title: 'Email', dataIndex: 'email', key: 'email' },
    { title: 'Phone', dataIndex: 'phone', key: 'phone' },
    { title: 'Action', key: 'action', width: 150 },
];

const groupedColumns = [
    { title: 'Company', key: 'company_name' },
    { title: 'Total Employees', key: 'employee_count', width: 150 },
    { title: 'Action', key: 'action', width: 100 },
];

const employeeColumns = [
    { title: '#', key: 'index', width: 60 },
    { title: 'Full Name', key: 'full_name' },
    { title: 'Email', dataIndex: 'email', key: 'email' },
    { title: 'Phone', dataIndex: 'phone', key: 'phone' },
    { title: 'Action', key: 'action', width: 150 },
];

const groupedData = computed(() => {
    const groups = {};
    props.employees.data.forEach(emp => {
        const companyId = emp.company?.id || 0;
        if (!groups[companyId]) {
            groups[companyId] = {
                id: companyId,
                company: emp.company,
                company_name: emp.company?.name || 'No Company',
                employees: [],
            };
        }
        groups[companyId].employees.push(emp);
    });
    return Object.values(groups).map(g => ({
        ...g,
        employee_count: g.employees.length,
    }));
});

const pagination = computed(() => ({
    current: props.employees.meta.current_page,
    pageSize: props.employees.meta.per_page,
    total: props.employees.meta.total,
    showSizeChanger: true,
    pageSizeOptions: ['10', '25', '50'],
}));

const handleTableChange = (pag, filters, sorter) => {
    router.get(route('employees.index'), {
        page: pag.current,
        pageSize: pag.pageSize,
        search: searchText.value,
        sortField: sorter.field || 'id',
        sortOrder: sorter.order || 'ascend',
        view: viewMode.value,
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get(route('employees.index'), {
        search: searchText.value,
        view: viewMode.value,
    }, { preserveState: true });
};

const handleViewChange = () => {
    router.get(route('employees.index'), {
        search: searchText.value,
        view: viewMode.value,
    }, { preserveState: true });
};

const openCreateModal = (companyId = null) => {
    form.value = { first_name: '', last_name: '', company_id: companyId, email: '', phone: '' };
    showModal.value = true;
};

const openEditModal = (employee) => {
    currentEmployee.value = employee;
    form.value = {
        first_name: employee.first_name,
        last_name: employee.last_name,
        company_id: employee.company_id,
        email: employee.email || '',
        phone: employee.phone || '',
    };
    editModal.value = true;
};

const openCompanyModal = (company) => {
    selectedCompany.value = company;
    companyModal.value = true;
};

const handleCreate = () => {
    router.post(route('employees.store'), form.value, {
        onSuccess: () => {
            showModal.value = false;
            message.success('Employee created successfully');
        },
        onError: (errors) => {
            message.error(Object.values(errors)[0]);
        },
    });
};

const handleUpdate = () => {
    router.put(route('employees.update', currentEmployee.value.id), form.value, {
        onSuccess: () => {
            editModal.value = false;
            message.success('Employee updated successfully');
        },
        onError: (errors) => {
            message.error(Object.values(errors)[0]);
        },
    });
};

const handleDelete = (id) => {
    Modal.confirm({
        title: 'Delete Employee',
        content: 'Are you sure you want to delete this employee?',
        okText: 'Yes',
        cancelText: 'No',
        onOk() {
            router.delete(route('employees.destroy', id), {
                onSuccess: () => {
                    message.success('Employee deleted successfully');
                },
            });
        },
    });
};

const toggleExpand = (record) => {
    const key = record.id;
    const idx = expandedRowKeys.value.indexOf(key);
    if (idx > -1) {
        expandedRowKeys.value.splice(idx, 1);
    } else {
        expandedRowKeys.value.push(key);
    }
};

watch(() => page.props.flash, (flash) => {
    if (flash.success) message.success(flash.success);
    if (flash.error) message.error(flash.error);
}, { immediate: true });
</script>

<template>
    <Head title="Employees" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Employees</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <a-input-search v-model:value="searchText" placeholder="Search employees..." style="width: 300px" @search="handleSearch" />
                                <a-radio-group v-model:value="viewMode" button-style="solid" size="small" @change="handleViewChange">
                                    <a-radio-button value="flat">Flat</a-radio-button>
                                    <a-radio-button value="grouped">Grouped</a-radio-button>
                                </a-radio-group>
                            </div>
                            <a-button type="primary" @click="openCreateModal()">Add Employee</a-button>
                        </div>

                        <!-- Grouped View -->
                        <a-table
                            v-if="viewMode === 'grouped'"
                            :columns="groupedColumns"
                            :data-source="groupedData"
                            :pagination="false"
                            :expanded-row-keys="expandedRowKeys"
                            row-key="id"
                        >
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.key === 'company_name'">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400 cursor-pointer" @click="toggleExpand(record)">{{ expandedRowKeys.includes(record.id) ? '▼' : '▶' }}</span>
                                        <a href="#" @click.prevent="openCompanyModal(record.company)" class="text-blue-600 hover:underline font-medium">
                                            {{ record.company_name }}
                                        </a>
                                    </div>
                                </template>
                                <template v-else-if="column.key === 'employee_count'">
                                    <a-tag color="blue">{{ record.employee_count }} employees</a-tag>
                                </template>
                                <template v-else-if="column.key === 'action'">
                                    <a-button size="small" type="primary" ghost @click="openCreateModal(record.id)">+ Add</a-button>
                                </template>
                            </template>
                            <template #expandedRowRender="{ record }">
                                <a-table
                                    :columns="employeeColumns"
                                    :data-source="record.employees"
                                    :pagination="false"
                                    row-key="id"
                                    size="small"
                                    class="ml-6"
                                >
                                    <template #bodyCell="{ column, record: emp, index }">
                                        <template v-if="column.key === 'index'">
                                            {{ index + 1 }}
                                        </template>
                                        <template v-else-if="column.key === 'full_name'">
                                            {{ emp.full_name }}
                                        </template>
                                        <template v-else-if="column.key === 'action'">
                                            <a-space>
                                                <a-button size="small" @click="openEditModal(emp)">Edit</a-button>
                                                <a-button size="small" danger @click="handleDelete(emp.id)">Delete</a-button>
                                            </a-space>
                                        </template>
                                    </template>
                                </a-table>
                            </template>
                        </a-table>

                        <!-- Flat View -->
                        <a-table
                            v-else
                            :columns="columns"
                            :data-source="employees.data"
                            :pagination="pagination"
                            @change="handleTableChange"
                            row-key="id"
                        >
                            <template #bodyCell="{ column, record, index }">
                                <template v-if="column.key === 'index'">
                                    {{ (employees.meta.current_page - 1) * employees.meta.per_page + index + 1 }}
                                </template>
                                <template v-else-if="column.key === 'full_name'">
                                    {{ record.full_name }}
                                </template>
                                <template v-else-if="column.key === 'company'">
                                    <a v-if="record.company" href="#" @click.prevent="openCompanyModal(record.company)" class="text-blue-600 hover:underline">
                                        {{ record.company.name }}
                                    </a>
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

        <a-modal v-model:open="showModal" title="Add Employee" @ok="handleCreate" ok-text="Create">
            <a-form layout="vertical">
                <a-form-item label="First Name" required>
                    <a-input v-model:value="form.first_name" />
                </a-form-item>
                <a-form-item label="Last Name" required>
                    <a-input v-model:value="form.last_name" />
                </a-form-item>
                <a-form-item label="Company" required>
                    <a-select v-model:value="form.company_id" placeholder="Select company" style="width: 100%">
                        <a-select-option v-for="company in companies.data" :key="company.id" :value="company.id">
                            {{ company.name }}
                        </a-select-option>
                    </a-select>
                </a-form-item>
                <a-form-item label="Email">
                    <a-input v-model:value="form.email" type="email" />
                </a-form-item>
                <a-form-item label="Phone">
                    <a-input v-model:value="form.phone" />
                </a-form-item>
            </a-form>
        </a-modal>

        <a-modal v-model:open="editModal" title="Edit Employee" @ok="handleUpdate" ok-text="Update">
            <a-form layout="vertical">
                <a-form-item label="First Name" required>
                    <a-input v-model:value="form.first_name" />
                </a-form-item>
                <a-form-item label="Last Name" required>
                    <a-input v-model:value="form.last_name" />
                </a-form-item>
                <a-form-item label="Company" required>
                    <a-select v-model:value="form.company_id" placeholder="Select company" style="width: 100%">
                        <a-select-option v-for="company in companies.data" :key="company.id" :value="company.id">
                            {{ company.name }}
                        </a-select-option>
                    </a-select>
                </a-form-item>
                <a-form-item label="Email">
                    <a-input v-model:value="form.email" type="email" />
                </a-form-item>
                <a-form-item label="Phone">
                    <a-input v-model:value="form.phone" />
                </a-form-item>
            </a-form>
        </a-modal>

        <a-modal v-model:open="companyModal" title="Company Details" :footer="null">
            <div v-if="selectedCompany" class="space-y-4">
                <div v-if="selectedCompany.logo" class="flex justify-center">
                    <img :src="selectedCompany.logo" class="h-24 w-24 object-cover rounded" />
                </div>
                <a-descriptions bordered :column="1">
                    <a-descriptions-item label="Name">{{ selectedCompany.name }}</a-descriptions-item>
                    <a-descriptions-item label="Email">{{ selectedCompany.email || '-' }}</a-descriptions-item>
                    <a-descriptions-item label="Website">
                        <a v-if="selectedCompany.website" :href="selectedCompany.website" target="_blank" class="text-blue-600 hover:underline">
                            {{ selectedCompany.website }}
                        </a>
                        <span v-else>-</span>
                    </a-descriptions-item>
                </a-descriptions>
            </div>
        </a-modal>
    </AuthenticatedLayout>
</template>
