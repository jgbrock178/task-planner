<script setup lang="ts">
import { computed } from 'vue'
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select'
import { ArrowUp, ArrowRight, ArrowDown } from 'lucide-vue-next'

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
const options: { value: Priority; label: string; colorClass: string; Icon?: any }[] = [
    { value: 'high',   label: 'High',   colorClass: 'text-red-500',    Icon: ArrowUp },
    { value: 'medium', label: 'Medium', colorClass: 'text-yellow-500', Icon: ArrowRight },
    { value: 'low',    label: 'Low',    colorClass: 'text-green-500',  Icon: ArrowDown },
    { value: 'none',   label: 'None',   colorClass: 'bg-gray-300'   },
]

const selected = computed(() =>
    options.find(o => o.value === props.modelValue)!
)
</script>


<template>
    <Select v-model="localValue">
        <SelectTrigger :id="id" class="w-full">
            <div class="flex items-center space-x-2">
                <component
                    v-if="selected.Icon"
                    :is="selected.Icon"
                    class="h-4 w-4 flex-shrink-0"
                    :class="selected.colorClass"
                />
                <span
                    :class="['h-2 w-2 rounded-full', selected.colorClass]"
                    v-if="!selected.Icon || selected.value !== 'none'"
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
                    <component
                        v-if="opt.Icon"
                        :is="opt.Icon"
                        class="h-4 w-4 flex-shrink-0"
                        :class="opt.colorClass"
                    />
                    <span
                        :class="['h-2 w-2 rounded-full', opt.colorClass]"
                        v-if="!opt.Icon || opt.value === 'none'"
                    />
                    <span>{{ opt.label }}</span>
                </div>
            </SelectItem>
        </SelectContent>
    </Select>
</template>
