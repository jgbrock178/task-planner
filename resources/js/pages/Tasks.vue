<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Task } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner'
import { Checkbox } from '@/components/ui/checkbox';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: '/tasks',
    },
];

const props = defineProps<{ tasks: Task[] }>()
const page  = usePage<{ flash: Record<string,string> }>()
const tasks = ref<Task[]>(props.tasks);

watch(
  () => props.tasks,
  (newTasks) => {
    tasks.value = newTasks
  }
)

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
    task.is_completed = completed;

    router.patch(route('task.toggleCompleted', task.id),
        { completed },
        {
            onSuccess: () => {
                toast.success(`Task "${task.title}" marked as ${completed ? 'completed' : 'incomplete'}`);
            },
            onError: (errors) => {
                toast.error('Error updating task', {
                    description: errors.message?.[0] ?? 'Please try again.',
                });
            },
        }
    );
}

</script>

<template>
    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mx-auto xl:w-1/2">
                <form @submit.prevent="addTask" class="w-full items-center gap-1.5">
                    <Label for="task-title" class="mb-2">
                        What do you need to do?
                    </Label>
                    <div class="flex w-full items-center gap-1.5">
                        <Input id="task-title" v-model="form.title" type="text" placeholder="Enter a task..."
                            :class="[
                                form.errors.title ? 'border-red-500' : ''
                            ]"
                        />
                        <Button type="submit" :disabled="form.processing">Add Task</Button>
                    </div>
                </form>
                <!-- Error message -->
                <p
                    v-if="form.errors.title"
                    class="text-red-600 text-sm mt-1"
                >
                    {{ form.errors.title }}
                </p>
            </div>
            <ul class="flex flex-col mx-auto xl:w-1/2">
                <li v-for="task in tasks" :key="task.id" class="flex items-center p-2 border-b">
                    <Checkbox
                        :id="`task-${task.id}`"
                        v-model="task.is_completed"
                        @click="toggleCompleted(task, !task.is_completed)"
                        class="mr-3"
                    />
                    <span>{{ task.title }}</span>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
