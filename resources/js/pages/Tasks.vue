<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed, nextTick } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Task } from '@/types';
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button';
import { ChevronUp, ChevronDown, GripVertical, Check, CircleCheck } from 'lucide-vue-next'
import TaskFormModal from '@/components/TaskFormModal.vue'

// PrimeVue filter state
const globalFilter = ref<string | null>(null)
const filters = ref({
  'global': { value: null, matchMode: 'contains' },
})

// modal state
const isModalOpen = ref(false)
const taskToEdit = ref<Task|undefined>(undefined)

function openCreate(task: undefined) {
    taskToEdit.value = undefined
    isModalOpen.value = true
}
function openEdit(task: Task) {
    taskToEdit.value = task
    isModalOpen.value = true
}
// refresh after save
function onSaved() {
  // simple Inertia reload
    window.location.reload()
}

const showReorderButtons = ref(false)
const showCompleted = ref(false)
const form = useForm({ order: [] as number[] })

const filteredTasks = computed(() => {
    return tasks.value.filter(task => {
        if (showCompleted.value) return true
        return !task.is_completed
    })
})

// handle row-reorder event
function onRowReorder(event: any) {
    tasks.value = event.value
    form.order = tasks.value.map(t => t.id)

    // send to server
    form.post(route('tasks.reorder'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Tasks reordered successfully');
        },
        onError: (errors) => {
           console.error('Reorder failed', errors)
        }
    })
}

// accessible move up/down buttons
function moveUp(i: number) {
    if (i === 0) return
    // grab the task id before we reorder
    const id = tasks.value[i].id

    // produce a brand-new array so DataTable updates
    const reordered = [...tasks.value]
    ;[reordered[i-1], reordered[i]] = [reordered[i], reordered[i-1]]
    tasks.value = reordered

    // after DOM updates, focus the same button
    nextTick(() => {
        document.getElementById(`move-up-${id}`)?.focus()
    })
}

function moveDown(i: number) {
    if (i === tasks.value.length - 1) return
    const id = tasks.value[i].id

    const reordered = [...tasks.value]
    ;[reordered[i], reordered[i+1]] = [reordered[i+1], reordered[i]]
    tasks.value = reordered

    nextTick(() => {
        document.getElementById(`move-down-${id}`)?.focus()
    })
}

// format dates (optional)
function formatDate(task: Task) {
    return task.completed_at ? new Date(task.completed_at).toLocaleString() : '—'
}

const breadcrumbs: BreadcrumbItem[] = [{
    title: 'Tasks',
    href: route('tasks.index'),
}, ];

const props = defineProps<{ tasks: Task[] }>()
const page = usePage<{ flash: Record<string, string> }>()
const tasks = ref<Task[]>(props.tasks);

watch(() => props.tasks, (newTasks) => { tasks.value = newTasks })

/* Computed lists */
const todoTasks = computed(() =>
    tasks.value.filter((t) => !t.is_completed)
);

const completedTasks = computed(() =>
    tasks.value
        .filter((t) => t.is_completed)
        .sort((a, b) =>
            new Date(b.completed_at).getTime() - new Date(a.completed_at).getTime()
        )
);

function addTask() {
    form.post(route('task.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Task added successfully');
        },
        onError: (errors) => {
            toast.error('Error adding task', {
                description: errors.title ?? 'Please check your input.',
            })
        },
    });
}

function sleep(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms))
}

function animateStrikeThenRemove(el: Element, done: () => void) {
    el.classList.add('strike');
    requestAnimationFrame(() => {
        el.classList.add('strike--active')
    });
    setTimeout(done, 500);
}

function onChecked(task: Task, completed: boolean, e: Event) {
    const tr = (e.target as HTMLElement).closest('tr')

    // Include a fallback just in case.
    if (!tr) {
        return toggleNow();
    }

    // Find the title and add the strike
    const titleSpan = tr.querySelector('[data-task-title]') as HTMLElement
    if (titleSpan && completed) {
        titleSpan.classList.add('strike')
        tr.classList.add('bg-green-100!')
    } else {
        titleSpan.classList.remove('strike')
    }

    // Wait for the transition to finish before toggling
    setTimeout(toggleNow, 500)

    function toggleNow() {
        task.is_completed = completed

        router.patch(
            route('tasks.toggleCompleted', task.id),
            { completed },
            {
                preserveScroll: true,
                onSuccess: () =>
                toast.success(
                    `Task marked as ${completed ? 'complete' : 'incomplete'}`
                ),
                onError: () => {
                task.is_completed = !completed
                toast.error('Error updating task')
                },
            }
        )
    }
}
</script>

<template>

    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl py-4 px-8">
            <div class="mb-4 flex items-center space-x-2">
                <Button @click="openCreate">New Task</Button>
                <Button
                    v-if="completedTasks.length > 0"
                    size="sm"
                    variant="outline"
                    @click="showCompleted = !showCompleted"
                >
                    <Check class="size-4" />
                    {{ showCompleted ? 'Hide completed tasks' : 'Show completed tasks' }}
                </Button>

                <!-- <Button
                    size="sm"
                    variant="outline"
                    @click="showReorderButtons = !showReorderButtons"
                >
                    <ArrowUpDown class="size-4" />
                    {{ showReorderButtons ? 'Hide Arrow Buttons' : 'Show Arrow Buttons' }}
                </Button> -->
            </div>

            <div v-if="filteredTasks.length === 0" class=" text-gray-500">
                <p>No tasks found. Why not add some?</p>
            </div>

            <DataTable
                v-if="filteredTasks.length > 0"
                v-model:value="filteredTasks"
                dataKey="id"
                rowReorder
                removableSort
                :reorderableRows="true"
                :filters="filters"
                filterDisplay="menu"
                :globalFilterFields="['title']"
                @row-reorder="onRowReorder"
            >
                <!-- drag handle -->
                <Column
                    rowReorder
                    class="w-5 p-0!"
                    v-if="!showReorderButtons"
                >
                    <template #rowreordericon>
                        <div
                            class="py-2 px-1 flex items-center border border-transparent rounded cursor-move hover:border-gray-300"
                            data-pc-section="reorderablerowhandle"
                            draggable="true"
                        >
                            <GripVertical
                                class="size-5"
                            />
                        </div>
                    </template>
                </Column>

                <!-- up/down buttons -->
                <Column
                    header="Reorder"
                    class="w-10 px-0! py-0!"
                    v-if="showReorderButtons"
                >
                    <template #body="{ data: task, index }">
                        <Button
                            :id="`move-up-${task.id}`"
                            variant="outline"
                            class="p-1 size-6 rounded-r-none"
                            @click="moveUp(index)"
                            :disabled="index === 0"
                            aria-label="Move up"
                        >
                            <ChevronUp class="size-4" />
                        </Button>

                        <Button
                            :id="`move-down-${task.id}`"
                            variant="outline"
                            class="p-1 size-6 rounded-l-none"
                            @click="moveDown(index)"
                            :disabled="index === tasks.length - 1"
                            aria-label="Move down"
                        >
                            <ChevronDown class="size-4" />
                        </Button>
                    </template>
                </Column>

                <!-- Checkbox for completion -->
                <Column
                    class="w-5"
                    field="is_completed"
                    sortable
                >
                    <template #header>
                        <CircleCheck class="size-5" />
                    </template>

                    <template #body="{ data: task }">
                        <div class="flex h-full items-center justify-center">
                            <input
                                type="checkbox"
                                :checked="task.is_completed"
                                @change="e => onChecked(task, (e.target as HTMLInputElement).checked, e)"
                                class="size-5 accent-green-600 focus:ring-green-500"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Task title -->
                <Column
                    field="title"
                    header="Task"
                    sortable
                    filter
                    filterPlaceholder="Filter by title"
                >
                    <template #body="{ data: task }">
                        <span>
                            <button
                                data-task-title
                                @click="openEdit(task)"
                                class="hover:underline cursor-pointer"
                                :class="{ strike: task.is_completed }"
                            >
                                {{ task.title }}
                            </button>
                        </span>
                    </template>
                </Column>

                <!-- Priority -->
                <Column
                    field="priority"
                    header="Priority"
                    sortable
                    filter
                    filterPlaceholder="Filter by priority"
                >
                    <template #body="{ data }">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                            :class="{
                                'bg-red-100 text-red-800': data.priority === 'high',
                                'bg-yellow-100 text-yellow-800': data.priority === 'medium',
                                'bg-green-100 text-green-800': data.priority === 'low',
                                'bg-gray-100 text-gray-800': data.priority === 'none',
                            }"
                        >
                            {{ data.priority.charAt(0).toUpperCase() + data.priority.slice(1) }}
                        </span>
                    </template>
                </Column>

                <!-- Completed at -->
                <Column
                    field="completed_at"
                    header="Completed"
                    sortable
                    filter
                    filterPlaceholder="Filter by date"
                    >
                    <template #body="{ data }">
                        {{ data.completed_at
                            ? new Date(data.completed_at).toLocaleString()
                            : '—'
                        }}
                    </template>
                </Column>
            </DataTable>
        </div>

        <TaskFormModal
            v-model:open="isModalOpen"
            :task-to-edit="taskToEdit"
            @saved="onSaved"
        />
    </AppLayout>
</template>


<style scoped>

:deep(.p-datatable .p-datatable-tbody > tr > td),
:deep(.p-datatable .p-datatable-thead > tr > th) {
    padding-top: 10px;
    padding-bottom: 10px;
    font-size: 14px;
}

:deep(.p-datatable tbody tr:focus-within) {
    background-color: var(--color-gray-100);
}

:deep(.p-datatable tbody tr:hover) {
    background-color: var(--color-gray-50);
}

input[type="checkbox"] {
    cursor: pointer;
    transition-duration: 100ms;
    transition-timing-function: ease-in-out;
}

input[type="checkbox"]:hover {
    color: var(--color-green-800) !important;
    accent-color: var(--color-green-600) !important;
    scale: 1.2;
}

input[type="checkbox"]:checked {
    color: var(--color-green-800) !important;
    accent-color: var(--color-green-600) !important;
}

.strike {
    position: relative;
    text-decoration: line-through;
}

.task-leave-active {
    transition: transform 300ms ease 200ms,
                opacity   300ms ease 200ms;
    position: relative;
}

.task-leave-to {
    opacity: 0;
}
</style>
