<template>
  <div
    class="q-pa-md q-ml-sm"
    style="border: 2px solid grey; border-radius: 4px; margin-right: 12px"
  >
    <div class="row justify-between">
      <div class="cursor-pointer row q-gutter-x-md">
        <q-avatar size="32px"><img src="profile.jpg" /></q-avatar>

        <div>
          <p>Robert J.</p>

          <p style="font-size: 12px">2 hours ago</p>
        </div>
      </div>

      <q-icon
        name="bookmark"
        size="24px"
        class="cursor-pointer"
        @click="savePost(post.id)"
        v-if="false"
      />

      <q-icon
        name="sym_o_bookmark"
        size="24px"
        class="cursor-pointer"
        @click="savePost(post.id)"
        v-else
      />
    </div>

    Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur dolor saepe
    quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus, debitis repellendus
    harum quas nam.

    <br />
    <br />

    Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur dolor saepe
    quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus, debitis repellendus
    harum quas nam. Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur
    dolor saepe quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus, debitis
    repellendus harum quas nam.

    <!-- Code -->

    <div class="q-py-md">
      <q-card class="bg-dark text-white">
        <q-card-section class="row justify-between">
          <div class="text-bold">Code Snippet</div>

          <q-icon name="content_copy" @click="copyCode" class="cursor-pointer" size="16px" />
        </q-card-section>

        <q-separator dark />

        <q-card-section>
          <pre><code ref="codeBlock" class="language-javascript">{{ codeSnippet }}</code></pre>
        </q-card-section>

        <q-expansion-item expand-separator label="Show Full Code">
          <q-card-section>
            <pre><code ref="fullCodeBlock" class="language-javascript">{{ fullCode }}</code></pre>
          </q-card-section>
        </q-expansion-item>
      </q-card>
    </div>

    <!-- Assets like video ro image -->

    <div class="q-py-md">
      <q-img
        style="max-height: 500px"
        src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg"
      />
    </div>

    <!-- Tags and action buttons -->

    <div class="q-pt-md q-gutter-y-sm">
      <div class="cursor-pointer" style="padding-left: 15px" @click="search(post.tag)">#tag</div>

      <div class="row">
        <div class="post-actionButtons"><q-icon name="thumb_up" size="16px" /> 204</div>

        <div class="post-actionButtons"><q-icon name="chat" size="16px" /> 24</div>

        <div class="post-actionButtons"><q-icon name="share" size="16px" /> 40</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import 'highlight.js/styles/github-dark.min.css'
import hljs from 'highlight.js'

const $q = useQuasar()
const fullCode = ref(
  `
function greet() {
  console.log("Hello, World!");
  console.log("This is a test line.");
  console.log("Another line of code.");
  console.log("This part should be hidden.");
}
`.trim(),
)

const codeSnippet = ref(fullCode.value.split('\n').slice(0, 4).join('\n'))

const codeBlock = ref(null)
const fullCodeBlock = ref(null)

function copyCode() {
  navigator.clipboard.writeText(fullCode.value)
  $q.notify({ message: 'Copied to clipboard!', color: 'green' })
}

onMounted(() => {
  hljs.highlightAll()
})
</script>
