<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed, nextTick } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Task } from '@/types';
import { toast } from 'vue-sonner'
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 lg:w-2/3 lg:mx-auto 2xl:w-1/2">

            <!-- shadcn Tabs wrapper -->
            <Tabs defaultValue="todo">
                <form @submit.prevent="addTask" class="mb-4">
                    <Label for="task-title" class="mb-1">What do you need to do?</Label>
                    <div class="flex gap-2">
                        <Input id="task-title" v-model="form.title" type="text" placeholder="Enter a task..."
                            :class="{ 'border-red-500': form.errors.title }" />
                        <Button type="submit" :disabled="form.processing">Add</Button>
                    </div>
                    <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                        {{ form . errors . title }}
                    </p>
                </form>

                <TabsList class="border-b w-full">
                    <TabsTrigger value="todo">To do</TabsTrigger>
                    <TabsTrigger value="completed">Completed</TabsTrigger>
                </TabsList>

                <!-- To-do Pane -->
                <TabsContent value="todo" class="pt-4">

                    <transition-group name="task" @leave="animateStrikeThenRemove" tag="ul" class="space-y-2">
                        <li v-for="task in todoTasks" :key="task.id"
                            class="flex items-center p-2 border rounded-lg bg-gray-50 transition-colors">
                            <input
                                type="checkbox"
                                :checked="task.is_completed"
                                @change="e => onChecked(task, (e.target as HTMLInputElement).checked)"
                                class="mr-3 size-5"
                            />
                            <span>{{ task.title }}</span>
                        </li>
                    </transition-group>
                </TabsContent>

                <!-- Completed Pane -->
                <TabsContent value="completed" class="pt-4">
                    <h2 class="text-lg font-semibold mb-2">Completed Tasks</h2>

                    <transition-group name="task" tag="ul" class="space-y-2">
                        <li v-for="task in completedTasks" :key="task.id"
                            class="flex items-center p-2 border rounded-lg bg-green-100 text-green-900 transition-colors">
                            <input
                                type="checkbox"
                                :checked="task.is_completed"
                                @change="e => onChecked(task, (e.target as HTMLInputElement).checked)"
                                class="mr-3 size-5"
                            />
                            <span>{{ task.title }}</span>
                            <span class="ml-auto text-xs text-gray-500">
                                Completed {{ task.completed_at ? new Date(task.completed_at).toLocaleString() : '' }}
                            </span>
                        </li>
                    </transition-group>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>


<style scoped>

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
