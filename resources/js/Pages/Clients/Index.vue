<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clients</h2>
                <Link v-if="$page.props.auth.user.role !== 'staff'" 
                      :href="route('clients.create')" 
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    New Client
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <input v-model="form.search" 
                                       type="text" 
                                       placeholder="Search clients..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select v-model="form.status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Statuses</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button @click="applyFilters" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                    Search
                                </button>
                                <button @click="clearFilters" 
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clients Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="client in clients.data" :key="client.id" 
                         class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <Link :href="route('clients.show', client.id)" 
                                          class="hover:text-blue-600">
                                        {{ client.name }}
                                    </Link>
                                </h3>
                                <span :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                      class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ client.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div v-if="client.contact_person">
                                    <span class="text-gray-500">Contact:</span>
                                    <span class="font-medium ml-2">{{ client.contact_person }}</span>
                                </div>
                                <div v-if="client.email">
                                    <span class="text-gray-500">Email:</span>
                                    <a :href="`mailto:${client.email}`" 
                                       class="font-medium ml-2 text-blue-600 hover:text-blue-800">
                                        {{ client.email }}
                                    </a>
                                </div>
                                <div v-if="client.phone">
                                    <span class="text-gray-500">Phone:</span>
                                    <a :href="`tel:${client.phone}`" 
                                       class="font-medium ml-2 text-blue-600 hover:text-blue-800">
                                        {{ client.phone }}
                                    </a>
                                </div>
                                <div v-if="client.company">
                                    <span class="text-gray-500">Company:</span>
                                    <span class="font-medium ml-2">{{ client.company }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Projects:</span>
                                    <span class="font-medium ml-2">{{ client.projects_count || 0 }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center">
                                <Link :href="route('clients.show', client.id)" 
                                      class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </Link>
                                <div v-if="$page.props.auth.user.role !== 'staff'" class="flex space-x-2">
                                    <Link :href="route('clients.edit', client.id)" 
                                          class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        Edit
                                    </Link>
                                    <button @click="deleteClient(client)" 
                                            class="text-red-600 hover:text-red-800 text-sm">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="clients.data.length === 0" 
                     class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No clients found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new client.</p>
                        <div class="mt-6">
                            <Link v-if="$page.props.auth.user.role !== 'staff'" 
                                  :href="route('clients.create')" 
                                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                New Client
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6" v-if="clients.links.length > 3">
                    <nav class="flex justify-center">
                        <div class="flex space-x-1">
                            <Link v-for="link in clients.links" 
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
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    clients: Object,
    filters: Object,
})

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
})

const applyFilters = () => {
    router.get(route('clients.index'), form, {
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.status = ''
    applyFilters()
}

const deleteClient = (client) => {
    if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
        router.delete(route('clients.destroy', client.id))
    }
}
</script>