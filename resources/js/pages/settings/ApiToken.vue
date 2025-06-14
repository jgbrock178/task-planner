<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { toast } from 'vue-sonner'
import type { BreadcrumbItem, PersonalToken } from '@/types'

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'API Tokens', href: route('api-tokens.index') },
]

const props = defineProps<{
  tokens: PersonalToken[]
  newToken?: string
}>()

// local copy of tokens
const tokens = ref<PersonalToken[]>(props.tokens)
watch(() => props.tokens, (v) => (tokens.value = v))

// control whether to show full token
const showFull = ref(false)

// let user know a token was created
if (props.newToken) {
  toast.success('Token created — see it below.', { timeout: 3000 })
}

const form = useForm({ name: '' })
function createToken() {
  form.post(route('api-tokens.store'), {
    onSuccess: () => {
      form.reset()
      showFull.value = false
    },
    onError: () => toast.error('Could not create token'),
  })
}

function revokeToken(id: number) {
  if (!confirm('Revoke this token?')) return
  router.delete(route('api-tokens.destroy', id), {
    preserveState: true,
    onSuccess: () => toast.success('Token revoked'),
    onError: () => toast.error('Error revoking token'),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="API Tokens" />

    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="API Tokens"
          description="Create and manage your personal API tokens"
        />

        <!-- NEW TOKEN BANNER -->
        <div
          v-if="props.newToken"
          class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded"
        >
          <div class="flex items-center justify-between">
            <span class="font-medium">New API Token:</span>
            <Button size="sm" variant="outline" @click="showFull = !showFull">
              {{ showFull ? 'Hide full token' : 'Show full token' }}
            </Button>
          </div>
          <code class="block font-mono bg-gray-100 mt-2 p-2 rounded break-all">
            <!-- mask all but last 8 chars -->
            {{ showFull
                ? props.newToken
                : '••••••••' + props.newToken.slice(-8) }}
          </code>
          <p class="text-sm text-gray-500 mt-2">
            Make sure to copy this now. You won’t be able to see it again.
          </p>
        </div>

        <!-- CREATE FORM -->
        <form @submit.prevent="createToken" class="flex gap-2">
          <Input
            v-model="form.name"
            placeholder="Token name (e.g. “CLI script”)"
            :error="form.errors.name"
          />
          <Button type="submit" :disabled="form.processing">
            Create
          </Button>
        </form>

        <!-- TOKENS TABLE -->
        <table class="w-full border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-2">Name</th>
              <th class="text-left p-2">Last Used</th>
              <th class="text-left p-2">Created</th>
              <th class="p-2">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="token in tokens"
              :key="token.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="p-2">{{ token.name }}</td>
              <td class="p-2">{{ token.last_used_at ?? '—' }}</td>
              <td class="p-2">
                {{ new Date(token.created_at).toLocaleString() }}
              </td>
              <td class="p-2 text-right">
                <Button
                  variant="outline"
                  size="icon"
                  @click="revokeToken(token.id)"
                  aria-label="Revoke token"
                >
                  &times;
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
