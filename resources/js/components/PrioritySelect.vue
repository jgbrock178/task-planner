<script setup lang="ts">
import { computed } from 'vue'
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select'

/** Allow empty string for “None” */
export type Priority = 'high' | 'medium' | 'low' | 'none'

const props = defineProps<{
    modelValue: Priority
    id?: string
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', v: Priority): void
}>()

// two-way binding for v-model
const localValue = computed<Priority>({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
})

// our 4 options, with a hollow dot for “None”
const options: { value: Priority; label: string; colorClass: string }[] = [
    { value: 'high',   label: 'High',   colorClass: 'bg-red-500'    },
    { value: 'medium', label: 'Medium', colorClass: 'bg-yellow-500' },
    { value: 'low',    label: 'Low',    colorClass: 'bg-green-500'  },
    { value: 'none',   label: 'None',   colorClass: 'bg-gray-300'   },
]

const selected = computed(() =>
    options.find(o => o.value === props.modelValue)!
)
</script>


<template>
    <Select v-model="localValue">
        <SelectTrigger :id="id" class="w-[180px]">
            <div class="flex items-center space-x-2">
                <span
                    :class="['h-2 w-2 rounded-full', selected.colorClass]"
                    v-if="selected"
                />
                <SelectValue placeholder="Priority…" />
            </div>
        </SelectTrigger>
        <SelectContent>
            <SelectItem
                v-for="opt in options"
                :key="opt.value"
                :value="opt.value"
            >
                <div class="flex items-center space-x-2">
                    <span :class="['h-2 w-2 rounded-full', opt.colorClass]" />
                    <span>{{ opt.label }}</span>
                </div>
            </SelectItem>
        </SelectContent>
    </Select>
</template>
