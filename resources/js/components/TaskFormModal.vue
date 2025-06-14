<script setup lang="ts">
import { defineProps, defineEmits, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import PrioritySelect from '@/components/PrioritySelect.vue'
import VueDatePicker from '@vuepic/vue-datepicker'

interface TaskPayload {
    [key: string]: any
    id?: number
    title: string
    description?: string
    priority?: 'high' | 'medium' | 'low' | 'none'
    due_date?: Date
}

const props = defineProps<{
    open: boolean
    taskToEdit?: {
        id: number;
        title: string;
        description?: string;
        priority?: 'high' | 'medium' | 'low' | 'none'
        due_date?: Date;
    }
}>()

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void
  (e: 'saved'): void
}>()

// form state (handles both create & update)
const form = useForm<TaskPayload>({
    id: props.taskToEdit?.id,
    title: props.taskToEdit?.title ?? '',
    description: props.taskToEdit?.description ?? '',
    priority: props.taskToEdit?.priority ?? 'none',
    due_date: props.taskToEdit?.due_date ?? undefined,
})

watch(
    () => props.taskToEdit,
    (t) => {
        form.clearErrors()

        if (t) {
            form.id = t.id
            form.title = t.title
            form.description = t.description ?? ''
            form.priority = t.priority ?? 'none'
            form.due_date = t.due_date ?? undefined
        } else {
            form.id          = undefined
            form.title       = ''
            form.description = ''
            form.priority    = 'none'
            form.due_date    = undefined
        }
    },
    { immediate: true }
)

// close handler
function close() {
    emit('update:open', false)
    form.reset('title','description','priority','due_date')
}

// submit handler
function submit() {
    if (form.id) {
        form.put(route('tasks.update', form.id), {
            onSuccess: () => {
                emit('saved')
                close()
            },
        })
    } else {
        form.post(route('tasks.store'), {
            onSuccess: () => {
                emit('saved')
                close()
            },
        })
    }
}
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="val => emit('update:open', val)"
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ form.id ? 'Edit Task' : 'New Task' }}</DialogTitle>
                <DialogDescription class="sr-only">
                    {{ form.id ? 'Update your task details.' : 'Create a new task to manage your work.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 pt-2">
                <div class="grid w-full items-center gap-1.5">
                    <Label for="task-title">
                        Task Title
                    </Label>
                    <Input
                        id="task-title"
                        label="Title"
                        v-model="form.title"
                        :error="form.errors.title"
                        required
                        placeholder="What do you want to do?"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid w-full items-center gap-1.5">
                        <Label for="task-priority">
                            Task Priority
                        </Label>
                        <PrioritySelect
                            id="task-priority"
                            v-model="form.priority"
                            :error="form.errors.priority"
                            class="w-full"
                        />
                    </div>
                    <div class="datepicker grid w-full items-center gap-1.5">
                        <Label for="task-due-date">
                            Due Date
                        </Label>
                        <VueDatePicker
                            v-model="form.due_date"
                            :enable-time-picker="false"
                            :auto-apply="true"
                            format="dd/MM/yyyy"
                        />
                    </div>
                </div>
                <div class="grid w-full items-center gap-1.5">
                    <Label for="task-description">
                        Task Description
                    </Label>
                    <Textarea
                        id="task-description"
                        label="Description"
                        v-model="form.description"
                        :error="form.errors.description"
                        class="resize-y h-24"
                        placeholder="Add any further details about the task here...."
                    />
                </div>

                <DialogFooter class="flex justify-end space-x-2">
                    <!-- <DialogClose asChild>
                        <Button variant="ghost">Cancel</Button>
                    </DialogClose> -->
                    <Button type="submit" :disabled="form.processing">
                        {{ form.id ? 'Save Changes' : 'Create Task' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped>

.datepicker {
    --dp-font-size: 14px;
    --dp-preview-font-size: 14px;
    --dp-border-radius: 6px;
}

</style>
