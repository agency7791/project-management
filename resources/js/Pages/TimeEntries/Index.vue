<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Time Tracking</h2>
                <Link :href="route('time-entries.create')" 
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Time Entry
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Timer Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Quick Timer</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Project</label>
                                <select v-model="timerForm.project_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Project</option>
                                    <option v-for="project in projects" :key="project.id" :value="project.id">
                                        {{ project.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Task</label>
                                <input v-model="timerForm.task_name" 
                                       type="text" 
                                       placeholder="What are you working on?"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-mono font-bold text-gray-800 mb-2">
                                    {{ formatTime(elapsedTime) }}
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button v-if="!isTimerRunning" 
                                        @click="startTimer"
                                        :disabled="!timerForm.project_id || !timerForm.task_name"
                                        class="bg-green-500 hover:bg-green-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded flex-1">
                                    Start
                                </button>
                                <button v-else 
                                        @click="stopTimer"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex-1">
                                    Stop
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date Range</label>
                                <select v-model="filters.date_range" 
                                        @change="applyDateRange"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div v-if="filters.date_range === 'custom'">
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input v-model="filters.start_date" 
                                       type="date" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div v-if="filters.date_range === 'custom'">
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input v-model="filters.end_date" 
                                       type="date" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Project</label>
                                <select v-model="filters.project_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Projects</option>
                                    <option v-for="project in projects" :key="project.id" :value="project.id">
                                        {{ project.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Billable</label>
                                <select v-model="filters.billable" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All</option>
                                    <option value="1">Billable Only</option>
                                    <option value="0">Non-Billable Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button @click="applyFilters" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Apply Filters
                            </button>
                            <button @click="clearFilters" 
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Hours</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ summary.total_hours }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Billable Hours</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ summary.billable_hours }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Entries</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ summary.total_entries }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Revenue</div>
                                    <div class="text-2xl font-bold text-gray-900">${{ summary.total_revenue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Entries Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billable</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="entry in timeEntries.data" :key="entry.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(entry.date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ entry.project.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ entry.task_name }}</div>
                                        <div class="text-gray-500">{{ entry.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ entry.start_time }} - {{ entry.end_time }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDuration(entry.duration_minutes) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="entry.is_billable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ entry.is_billable ? 'Billable' : 'Non-billable' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('time-entries.edit', entry.id)" 
                                              class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Edit
                                        </Link>
                                        <button @click="deleteEntry(entry)" 
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6" v-if="timeEntries.links.length > 3">
                    <nav class="flex justify-center">
                        <div class="flex space-x-1">
                            <Link v-for="link in timeEntries.links" 
                                  :key="link.label"
                                  :href="link.url"
                                  v-html="link.label"
                                  :class="[
                                      'px-3 py-2 text-sm font-medium rounded-md',
                                      link.active 
                                          ? 'bg-blue-500 text-white' 
                                          : 'bg-white text-gray-500 hover:text-gray-700 border border-gray-300'
                                  ]"
                                  :disabled="!link.url">
                            </Link>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    timeEntries: Object,
    projects: Array,
    summary: Object,
    filters: Object,
})

const filters = reactive({
    date_range: props.filters.date_range || 'this_week',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    project_id: props.filters.project_id || '',
    billable: props.filters.billable || '',
})

const timerForm = reactive({
    project_id: '',
    task_name: '',
})

const isTimerRunning = ref(false)
const elapsedTime = ref(0)
const timerInterval = ref(null)
const timerStartTime = ref(null)

const startTimer = () => {
    if (!timerForm.project_id || !timerForm.task_name) return
    
    isTimerRunning.value = true
    timerStartTime.value = Date.now()
    elapsedTime.value = 0
    
    timerInterval.value = setInterval(() => {
        elapsedTime.value = Math.floor((Date.now() - timerStartTime.value) / 1000)
    }, 1000)
    
    // Call API to start timer
    router.post(route('time-entries.start-timer'), {
        project_id: timerForm.project_id,
        task_name: timerForm.task_name,
    }, {
        preserveState: true,
        onSuccess: () => {
            // Timer started successfully
        }
    })
}

const stopTimer = () => {
    if (!isTimerRunning.value) return
    
    isTimerRunning.value = false
    clearInterval(timerInterval.value)
    
    // Call API to stop timer
    router.post(route('time-entries.stop-timer'), {}, {
        preserveState: false,
        onSuccess: () => {
            // Reset timer form
            timerForm.project_id = ''
            timerForm.task_name = ''
            elapsedTime.value = 0
        }
    })
}

const applyDateRange = () => {
    const today = new Date()
    const ranges = {
        today: {
            start_date: today.toISOString().split('T')[0],
            end_date: today.toISOString().split('T')[0],
        },
        yesterday: {
            start_date: new Date(today.getTime() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            end_date: new Date(today.getTime() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        },
        this_week: {
            start_date: new Date(today.getTime() - (today.getDay() * 24 * 60 * 60 * 1000)).toISOString().split('T')[0],
            end_date: today.toISOString().split('T')[0],
        },
        this_month: {
            start_date: new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0],
            end_date: today.toISOString().split('T')[0],
        },
    }
    
    if (ranges[filters.date_range]) {
        filters.start_date = ranges[filters.date_range].start_date
        filters.end_date = ranges[filters.date_range].end_date
    }
}

const applyFilters = () => {
    router.get(route('time-entries.index'), filters, {
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    filters.date_range = 'this_week'
    filters.start_date = ''
    filters.end_date = ''
    filters.project_id = ''
    filters.billable = ''
    applyDateRange()
    applyFilters()
}

const deleteEntry = (entry) => {
    if (confirm('Are you sure you want to delete this time entry?')) {
        router.delete(route('time-entries.destroy', entry.id))
    }
}

const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatDuration = (minutes) => {
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return `${hours}h ${mins}m`
}

onMounted(() => {
    applyDateRange()
})

onUnmounted(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
    }
})
</script>