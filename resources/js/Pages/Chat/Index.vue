<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Team Chat</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex h-96">
                        <!-- Chat Rooms Sidebar -->
                        <div class="w-1/3 border-r border-gray-200">
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold">Chat Rooms</h3>
                                    <button v-if="$page.props.auth.user.role !== 'staff'"
                                            @click="showCreateRoom = true"
                                            class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-1 px-2 rounded">
                                        New Room
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <input v-model="searchQuery" 
                                           type="text" 
                                           placeholder="Search rooms..."
                                           class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <div class="overflow-y-auto h-80">
                                <div v-for="room in filteredRooms" 
                                     :key="room.id"
                                     @click="selectRoom(room)"
                                     :class="[
                                         'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50',
                                         selectedRoom?.id === room.id ? 'bg-blue-50 border-blue-200' : ''
                                     ]">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ room.name }}</h4>
                                            <p class="text-sm text-gray-500">{{ room.project.name }}</p>
                                            <p v-if="room.latest_message" 
                                               class="text-xs text-gray-400 mt-1 truncate">
                                                {{ room.latest_message.user.name }}: {{ room.latest_message.message }}
                                            </p>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ formatTime(room.updated_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages Area -->
                        <div class="flex-1 flex flex-col">
                            <div v-if="selectedRoom" class="flex-1 flex flex-col">
                                <!-- Chat Header -->
                                <div class="p-4 border-b border-gray-200 bg-gray-50">
                                    <h3 class="font-semibold text-gray-900">{{ selectedRoom.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ selectedRoom.project.name }}</p>
                                </div>

                                <!-- Messages -->
                                <div ref="messagesContainer" 
                                     class="flex-1 overflow-y-auto p-4 space-y-4">
                                    <div v-for="message in messages" 
                                         :key="message.id"
                                         :class="[
                                             'flex',
                                             message.user.id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'
                                         ]">
                                        <div :class="[
                                                 'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                                                 message.user.id === $page.props.auth.user.id 
                                                     ? 'bg-blue-500 text-white' 
                                                     : 'bg-gray-200 text-gray-900'
                                             ]">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="text-xs font-medium">{{ message.user.name }}</span>
                                                <span class="text-xs opacity-75">{{ formatMessageTime(message.created_at) }}</span>
                                            </div>
                                            
                                            <div v-if="message.file_path" class="mb-2">
                                                <div class="flex items-center space-x-2 p-2 bg-white bg-opacity-20 rounded">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <a :href="`/storage/${message.file_path}`" 
                                                       target="_blank"
                                                       class="text-sm underline">
                                                        {{ message.file_name }}
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <p class="text-sm">{{ message.message }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message Input -->
                                <div class="p-4 border-t border-gray-200">
                                    <form @submit.prevent="sendMessage" class="flex space-x-2">
                                        <input v-model="newMessage" 
                                               type="text" 
                                               placeholder="Type your message..."
                                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               :disabled="sending">
                                        <input ref="fileInput" 
                                               type="file" 
                                               @change="handleFileSelect"
                                               class="hidden">
                                        <button type="button" 
                                                @click="$refs.fileInput.click()"
                                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded">
                                            📎
                                        </button>
                                        <button type="submit" 
                                                :disabled="(!newMessage.trim() && !selectedFile) || sending"
                                                class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                                            Send
                                        </button>
                                    </form>
                                    <div v-if="selectedFile" class="mt-2 text-sm text-gray-600">
                                        Selected file: {{ selectedFile.name }}
                                        <button @click="selectedFile = null" class="ml-2 text-red-600">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <!-- No Room Selected -->
                            <div v-else class="flex-1 flex items-center justify-center">
                                <div class="text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No chat selected</h3>
                                    <p class="mt-1 text-sm text-gray-500">Select a chat room to start messaging</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Room Modal -->
        <div v-if="showCreateRoom" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Chat Room</h3>
                    <form @submit.prevent="createRoom">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Room Name</label>
                            <input v-model="newRoomForm.name" 
                                   type="text" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Project</label>
                            <select v-model="newRoomForm.project_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Project</option>
                                <option v-for="project in availableProjects" :key="project.id" :value="project.id">
                                    {{ project.name }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="newRoomForm.description" 
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" 
                                    @click="showCreateRoom = false"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, nextTick, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    chatRooms: Object,
    filters: Object,
})

const searchQuery = ref('')
const selectedRoom = ref(null)
const messages = ref([])
const newMessage = ref('')
const selectedFile = ref(null)
const sending = ref(false)
const showCreateRoom = ref(false)
const messagesContainer = ref(null)
const availableProjects = ref([])

const newRoomForm = reactive({
    name: '',
    project_id: '',
    description: '',
})

const filteredRooms = computed(() => {
    if (!searchQuery.value) return props.chatRooms.data
    
    return props.chatRooms.data.filter(room => 
        room.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        room.project.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const selectRoom = async (room) => {
    selectedRoom.value = room
    await loadMessages(room.id)
    scrollToBottom()
}

const loadMessages = async (roomId) => {
    try {
        const response = await fetch(`/chat/rooms/${roomId}/messages`)
        const data = await response.json()
        messages.value = data.messages
    } catch (error) {
        console.error('Failed to load messages:', error)
    }
}

const sendMessage = async () => {
    if ((!newMessage.value.trim() && !selectedFile.value) || sending.value) return
    
    sending.value = true
    
    try {
        const formData = new FormData()
        formData.append('message', newMessage.value)
        if (selectedFile.value) {
            formData.append('file', selectedFile.value)
        }
        
        const response = await fetch(`/chat/rooms/${selectedRoom.value.id}/messages`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        
        const data = await response.json()
        
        if (data.success) {
            messages.value.push(data.message)
            newMessage.value = ''
            selectedFile.value = null
            scrollToBottom()
        }
    } catch (error) {
        console.error('Failed to send message:', error)
    } finally {
        sending.value = false
    }
}

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file && file.size <= 10 * 1024 * 1024) { // 10MB limit
        selectedFile.value = file
    } else {
        alert('File size must be less than 10MB')
    }
}

const createRoom = async () => {
    try {
        const response = await fetch('/chat/rooms', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(newRoomForm),
        })
        
        const data = await response.json()
        
        if (data.success) {
            showCreateRoom.value = false
            newRoomForm.name = ''
            newRoomForm.project_id = ''
            newRoomForm.description = ''
            
            // Refresh the page to show new room
            router.reload()
        }
    } catch (error) {
        console.error('Failed to create room:', error)
    }
}

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

const formatTime = (timestamp) => {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInHours = (now - date) / (1000 * 60 * 60)
    
    if (diffInHours < 24) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    } else {
        return date.toLocaleDateString()
    }
}

const formatMessageTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const loadProjects = async () => {
    try {
        const response = await fetch('/api/projects')
        const data = await response.json()
        availableProjects.value = data
    } catch (error) {
        console.error('Failed to load projects:', error)
    }
}

onMounted(() => {
    loadProjects()
    
    // Auto-select first room if available
    if (props.chatRooms.data.length > 0) {
        selectRoom(props.chatRooms.data[0])
    }
})

// TODO: Implement real-time messaging with Laravel Echo
// onMounted(() => {
//     window.Echo.channel('chat')
//         .listen('MessageSent', (e) => {
//             if (selectedRoom.value && e.message.chat_room_id === selectedRoom.value.id) {
//                 messages.value.push(e.message)
//                 scrollToBottom()
//             }
//         })
// })
</script>