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

// Filter states
const filterFirstName = ref(props.filters?.filter_first_name || '');
const filterLastName = ref(props.filters?.filter_last_name || '');
const filterEmail = ref(props.filters?.filter_email || '');
const filterCompany = ref(props.filters?.filter_company ? Number(props.filters.filter_company) : undefined);
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

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

const getFilterParams = () => {
    const params = { view: viewMode.value };
    if (filterFirstName.value) params.filter_first_name = filterFirstName.value;
    if (filterLastName.value) params.filter_last_name = filterLastName.value;
    if (filterEmail.value) params.filter_email = filterEmail.value;
    if (filterCompany.value) params.filter_company = filterCompany.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    return params;
};

const handleTableChange = (pag, filters, sorter) => {
    router.get(route('employees.index'), {
        page: pag.current,
        pageSize: pag.pageSize,
        sortField: sorter.field || 'id',
        sortOrder: sorter.order || 'ascend',
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleFilter = () => {
    router.get(route('employees.index'), getFilterParams(), { preserveState: true });
};

const handleReset = () => {
    filterFirstName.value = '';
    filterLastName.value = '';
    filterEmail.value = '';
    filterCompany.value = undefined;
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('employees.index'), { view: viewMode.value }, { preserveState: true });
};

const handleViewChange = () => {
    router.get(route('employees.index'), getFilterParams(), { preserveState: true });
};

const onDateFromChange = (date, dateString) => {
    dateFrom.value = dateString || '';
};

const onDateToChange = (date, dateString) => {
    dateTo.value = dateString || '';
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
                        <!-- Filters -->
                        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4" @keyup.enter="handleFilter">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Filters</span>
                                <a-radio-group v-model:value="viewMode" button-style="solid" size="small" @change="handleViewChange">
                                    <a-radio-button value="flat">Flat</a-radio-button>
                                    <a-radio-button value="grouped">Grouped</a-radio-button>
                                </a-radio-group>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">First Name</label>
                                    <a-input v-model:value="filterFirstName" placeholder="First name..." allow-clear />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">Last Name</label>
                                    <a-input v-model:value="filterLastName" placeholder="Last name..." allow-clear />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">Email</label>
                                    <a-input v-model:value="filterEmail" placeholder="Email..." allow-clear />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">Company</label>
                                    <a-select v-model:value="filterCompany" placeholder="All companies" style="width: 100%" allow-clear>
                                        <a-select-option v-for="company in companies.data" :key="company.id" :value="company.id">
                                            {{ company.name }}
                                        </a-select-option>
                                    </a-select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">Date From</label>
                                    <a-date-picker style="width: 100%" :value="dateFrom || undefined" value-format="YYYY-MM-DD" @change="onDateFromChange" placeholder="From date" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">Date To</label>
                                    <a-date-picker style="width: 100%" :value="dateTo || undefined" value-format="YYYY-MM-DD" @change="onDateToChange" placeholder="To date" />
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <a-button type="primary" @click="handleFilter">Filter</a-button>
                                <a-button @click="handleReset">Reset</a-button>
                            </div>
                        </div>

                        <div class="mb-4 flex justify-end">
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
