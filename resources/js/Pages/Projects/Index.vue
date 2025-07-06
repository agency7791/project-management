<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Projects</h2>
                <Link v-if="$page.props.auth.user.role !== 'staff'" 
                      :href="route('projects.create')" 
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    New Project
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <input v-model="form.search" 
                                       type="text" 
                                       placeholder="Search projects..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select v-model="form.status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Statuses</option>
                                    <option value="planning">Planning</option>
                                    <option value="active">Active</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Client</label>
                                <select v-model="form.client" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Clients</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Priority</label>
                                <select v-model="form.priority" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
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

                <!-- Projects Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="project in projects.data" :key="project.id" 
                         class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <Link :href="route('projects.show', project.id)" 
                                          class="hover:text-blue-600">
                                        {{ project.name }}
                                    </Link>
                                </h3>
                                <span :class="getStatusClass(project.status)" 
                                      class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ project.status }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4">{{ project.description }}</p>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Client:</span>
                                    <span class="font-medium">{{ project.client.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Priority:</span>
                                    <span :class="getPriorityClass(project.priority)" 
                                          class="px-2 py-1 text-xs font-semibold rounded">
                                        {{ project.priority }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Team Size:</span>
                                    <span class="font-medium">
                                        {{ project.team ? project.team.members.length : 0 }} members
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">End Date:</span>
                                    <span class="font-medium">{{ formatDate(project.end_date) }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center">
                                <Link :href="route('projects.show', project.id)" 
                                      class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </Link>
                                <div v-if="$page.props.auth.user.role !== 'staff'" class="flex space-x-2">
                                    <Link :href="route('projects.edit', project.id)" 
                                          class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        Edit
                                    </Link>
                                    <button @click="deleteProject(project)" 
                                            class="text-red-600 hover:text-red-800 text-sm">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6" v-if="projects.links.length > 3">
                    <nav class="flex justify-center">
                        <div class="flex space-x-1">
                            <Link v-for="link in projects.links" 
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
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    projects: Object,
    clients: Array,
    filters: Object,
})

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    client: props.filters.client || '',
    priority: props.filters.priority || '',
})

const applyFilters = () => {
    router.get(route('projects.index'), form, {
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.status = ''
    form.client = ''
    form.priority = ''
    applyFilters()
}

const deleteProject = (project) => {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(route('projects.destroy', project.id))
    }
}

const getStatusClass = (status) => {
    const classes = {
        planning: 'bg-yellow-100 text-yellow-800',
        active: 'bg-green-100 text-green-800',
        on_hold: 'bg-orange-100 text-orange-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800',
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>