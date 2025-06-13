<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Task } from '@/types';
import { toast } from 'vue-sonner'
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { ChevronUp, ChevronDown, GripVertical } from 'lucide-vue-next'

// PrimeVue filter state
const globalFilter = ref<string | null>(null)
const filters = ref({
  'global': { value: null, matchMode: 'contains' },
})

const showReorderButtons = ref(false)

// handle row-reorder event
function onRowReorder(event: any) {
    tasks.value = event.value
}

// accessible move up/down buttons
function moveUp(index: number) {
    if (index === 0) return
    const row = tasks.value.splice(index, 1)[0]
    tasks.value.splice(index - 1, 0, row)
}
function moveDown(index: number) {
    if (index === tasks.value.length - 1) return
    const row = tasks.value.splice(index, 1)[0]
    tasks.value.splice(index + 1, 0, row)
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

const form = useForm({
    title: '',
});


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

function toggleCompleted(task: Task, completed: boolean) {
    task.is_completed = completed

    router.patch(route('task.toggleCompleted', task.id), {
        completed
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.success(`Task marked as ${completed ? 'completed' : 'incomplete'}`);
        },
        onError: (errors) => {
            task.is_completed = !completed
            toast.error('Error updating task', {
                description: errors.message?.[0] ?? 'Please try again.',
            });
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

async function onChecked(task: Task, completed: boolean) {
    task.is_completed = completed

    // wait 150ms so the native checkbox paints
    await sleep(150)

    toggleCompleted(task, completed)
}
</script>

<template>

    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl py-4 px-8">
            <div class="mb-4 flex items-center space-x-2">
            <Button
                size="sm"
                variant="outline"
                @click="showReorderButtons = !showReorderButtons"
            >
                {{ showReorderButtons ? 'Show drag handle' : 'Show buttons' }}
            </Button>
            <span class="text-sm text-gray-600">
                {{ showReorderButtons
                    ? 'Use the arrows in the Reorder column to move rows'
                    : 'Drag the handles to reorder rows'
                }}
            </span>
            </div>

            <DataTable
                :value="tasks"
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
                            class="py-2 px-1 flex items-center hover:bg-gray-100 rounded cursor-move"
                            data-pc-section="reorderablerowhandle"
                            draggable="true"
                        >
                            <GripVertical
                                class="size-5 hover:bg-gray-100"
                            />
                        </div>
                    </template>
                </Column>
                <!-- up/down buttons -->
                <Column
                    header="Reorder"
                    class="w-10 px-0!"
                    v-if="showReorderButtons"
                >
                    <template #body="slotProps">
                        <Button
                            variant="outline"
                            class="p-1 size-6 rounded-r-none"
                            @click="moveUp(slotProps.rowIndex)"
                            :disabled="slotProps.rowIndex === 0"
                            aria-label="Move up"
                        >
                            <ChevronUp class="size-4" />
                        </Button>

                        <Button
                            variant="outline"
                            class="p-1 size-6 rounded-l-none"
                            @click="moveDown(slotProps.rowIndex)"
                            :disabled="slotProps.rowIndex === tasks.length - 1"
                            aria-label="Move down"
                        >
                            <ChevronDown class="size-4" />
                        </Button>
                    </template>
                </Column>
                <Column class="w-5">
                    <template #body="slotProps">
                        <div class="flex h-full items-center">
                            <input
                                type="checkbox"
                                :checked="slotProps.data.is_completed"
                                @change="e => onChecked(
                                    slotProps.data,
                                        (e.target as HTMLInputElement).checked
                                    )"
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
    </AppLayout>
</template>


<style scoped>

:deep(.p-datatable .p-datatable-tbody > tr > td),
:deep(.p-datatable .p-datatable-thead > tr > th) {
    padding-top: 10px;
    padding-bottom: 10px;
    font-size: 14px;
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
    text-decoration-color: var(--color-green-600);
    text-decoration-thickness: 2px;
    transition: text-decoration 300ms ease-in-out;
    background-color: var(--color-green-100);
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
