<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports & Analytics</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Report Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Timesheet Report -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Timesheet Report</h3>
                                    <p class="text-sm text-gray-600">Detailed time tracking reports with filtering and export options</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link :href="route('reports.timesheet')" 
                                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full inline-block text-center">
                                    View Report
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Project Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Project Summary</h3>
                                    <p class="text-sm text-gray-600">Overview of all projects with budget and time analysis</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link :href="route('reports.project-summary')" 
                                      class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full inline-block text-center">
                                    View Report
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Team Performance -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Team Performance</h3>
                                    <p class="text-sm text-gray-600">Individual and team productivity metrics</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded w-full opacity-50 cursor-not-allowed">
                                    Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Analysis -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Revenue Analysis</h3>
                                    <p class="text-sm text-gray-600">Financial insights and billing analysis</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded w-full opacity-50 cursor-not-allowed">
                                    Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Client Analysis -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a2 2 0 104 0 2 2 0 00-4 0zm6 0a2 2 0 104 0 2 2 0 00-4 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Client Analysis</h3>
                                    <p class="text-sm text-gray-600">Client profitability and project distribution</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded w-full opacity-50 cursor-not-allowed">
                                    Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Reports -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Custom Reports</h3>
                                    <p class="text-sm text-gray-600">Build your own reports with custom filters</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full opacity-50 cursor-not-allowed">
                                    Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ quickStats.total_projects }}</div>
                                <div class="text-sm text-gray-500">Total Projects</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ quickStats.active_projects }}</div>
                                <div class="text-sm text-gray-500">Active Projects</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ quickStats.total_hours }}</div>
                                <div class="text-sm text-gray-500">Total Hours This Month</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-yellow-600">${{ quickStats.revenue }}</div>
                                <div class="text-sm text-gray-500">Revenue This Month</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div v-for="activity in recentActivity" :key="activity.id" 
                                 class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div :class="getActivityIcon(activity.type)" 
                                         class="w-8 h-8 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path v-if="activity.type === 'time_entry'" 
                                                  fill-rule="evenodd" 
                                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" 
                                                  clip-rule="evenodd"></path>
                                            <path v-else-if="activity.type === 'project'" 
                                                  d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"></path>
                                            <path v-else 
                                                  d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                                    <p class="text-xs text-gray-500">{{ formatTime(activity.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const quickStats = ref({
    total_projects: 0,
    active_projects: 0,
    total_hours: 0,
    revenue: 0,
})

const recentActivity = ref([])

const loadQuickStats = async () => {
    try {
        const response = await fetch('/api/reports/quick-stats')
        const data = await response.json()
        quickStats.value = data
    } catch (error) {
        console.error('Failed to load quick stats:', error)
        // Fallback data
        quickStats.value = {
            total_projects: 12,
            active_projects: 8,
            total_hours: 156,
            revenue: 12450,
        }
    }
}

const loadRecentActivity = async () => {
    try {
        const response = await fetch('/api/reports/recent-activity')
        const data = await response.json()
        recentActivity.value = data
    } catch (error) {
        console.error('Failed to load recent activity:', error)
        // Fallback data
        recentActivity.value = [
            {
                id: 1,
                type: 'time_entry',
                description: 'John Doe logged 3 hours on Website Redesign project',
                created_at: new Date().toISOString(),
            },
            {
                id: 2,
                type: 'project',
                description: 'New project "Mobile App Development" was created',
                created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
            },
            {
                id: 3,
                type: 'team',
                description: 'Sarah Smith was added to the Frontend Team',
                created_at: new Date(Date.now() - 4 * 60 * 60 * 1000).toISOString(),
            },
        ]
    }
}

const getActivityIcon = (type) => {
    const icons = {
        time_entry: 'bg-blue-500',
        project: 'bg-green-500',
        team: 'bg-purple-500',
        client: 'bg-yellow-500',
    }
    return icons[type] || 'bg-gray-500'
}

const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)
    
    if (diffInHours < 1) {
        return 'Just now'
    } else if (diffInHours < 24) {
        return `${Math.floor(diffInHours)} hours ago`
    } else {
        return date.toLocaleDateString()
    }
}

onMounted(() => {
    loadQuickStats()
    loadRecentActivity()
})
</script>