<script setup lang="ts">
import { defineProps, defineEmits, watch, ref, nextTick, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import PrioritySelect from '@/components/PrioritySelect.vue'
import VueDatePicker from '@vuepic/vue-datepicker'
import { CircleAlert } from 'lucide-vue-next'
import { useAppearance } from '@/composables/useAppearance'
import { useMediaQuery } from '@vueuse/core'

interface TaskPayload {
    [key: string]: any
    id?: number
    title: string
    description?: string
    priority?: 'high' | 'medium' | 'low' | 'none'
    due_date?: Date
}

const { appearance } = useAppearance()
const prefersDark = useMediaQuery('(prefers-color-scheme: dark)')
const isDark = computed(() => {
    if (appearance.value === 'dark') return true
    if (appearance.value === 'light') return false
    return prefersDark.value
})

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

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      form.clearErrors()
      form.reset('title','description','priority','due_date')
      form.id = undefined
    }
  }
)

function close() {
    emit('update:open', false)
    form.reset('title','description','priority','due_date')
}

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
        <DialogContent class="p-4">
            <DialogHeader>
                <DialogTitle>{{ form.id ? 'Edit Task' : 'New Task' }}</DialogTitle>
                <DialogDescription class="sr-only">
                    {{ form.id ? 'Update your task details.' : 'Create a new task to manage your work.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 pt-2">
                <div class="grid w-full items-center gap-1.5">
                    <Label for="task-title" class="flex items-center gap-2">
                        <span>Task Title</span>
                        <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">Required</span>
                    </Label>
                    <Input
                        id="task-title"
                        label="Title"
                        v-model="form.title"
                        :error="form.errors.title"
                        placeholder="What do you want to do?"
                        :class="form.errors.title ? 'border-red-500 bg-red-50' : ''"
                    />
                    <p v-if="form.errors.title" class="text-red-600 text-sm flex items-center">
                        <CircleAlert class="inline mr-1 size-5" />
                        {{ form.errors.title }}
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 space-y-6 md:space-y-0 md:gap-3">
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
                            placeholder="Select a date"
                            v-model="form.due_date"
                            :enable-time-picker="false"
                            :auto-apply="true"
                            format="dd/MM/yyyy"
                            :dark="isDark"
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

<style>
.dp__theme_dark {
    --dp-background-color: oklch(26.9% 0 0 / 0.3);
}

.dp__menu.dp__theme_dark {
    --dp-background-color: var(--color-neutral-950) !important;
}
</style>
