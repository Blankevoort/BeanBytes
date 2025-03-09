<template>
  <q-page class="bg-dark text-secondary row justify-center">
    <div class="bg-dark text-white text-grey-6 col-8">
      <!-- Add new post -->

      <div class="q-pt-lg">
        <div class="">
          <div class="post-input">
            <textarea placeholder="New Post"></textarea>
          </div>

          <div class="post-actions">
            <div class="action-left">
              <button class="icon-btn">
                <q-icon name="code" size="24px" color="grey-6" />
              </button>

              <button class="icon-btn">
                <q-icon name="photo_camera" size="24px" color="grey-6" />
              </button>
            </div>

            <button class="post-btn">
              <q-icon name="send" size="24px" color="secondary" />
            </button>
          </div>
        </div>
      </div>

      <div class="q-mt-lg q-gutter-y-md">
        <!-- Tabs -->

        <div class="row q-ml-sm">
          <div class="flex items-center" @click="postsTabs = 'following'">
            <q-icon size="24px" name="favorite" :color="postsTabs == 'following' ? 'red' : ''" />

            <span class="q-pl-sm">Following</span>
          </div>

          <div class="flex items-center q-ml-lg" @click="postsTabs = 'featured'">
            <q-icon
              size="24px"
              name="local_fire_department"
              :color="postsTabs == 'featured' ? 'orange' : ''"
            />

            <span class="q-pl-sm">Featured</span>
          </div>

          <div class="flex items-center q-ml-lg" @click="postsTabs = 'trends'">
            <q-icon
              size="24px"
              name="rocket_launch"
              :color="postsTabs == 'trends' ? 'yellow' : ''"
            />

            <span class="q-pl-sm">Trends</span>
          </div>
        </div>

        <!-- Posts -->

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

            <q-icon name="sym_o_bookmark" size="24px" class="cursor-pointer" @click="savePost(post.id)" />
          </div>

          Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur dolor
          saepe quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus, debitis
          repellendus harum quas nam.

          <br />
          <br />

          Lorem ipsum dolor sit amet consectetur adipisicing elit. Id quo debitis pariatur dolor
          saepe quas sed provident ab quasi ducimus. Sit quae reiciendis amet voluptatibus, debitis
          repellendus harum quas nam. Lorem ipsum dolor sit amet consectetur adipisicing elit. Id
          quo debitis pariatur dolor saepe quas sed provident ab quasi ducimus. Sit quae reiciendis
          amet voluptatibus, debitis repellendus harum quas nam.

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
            <div class="cursor-pointer" style="padding-left: 15px" @click="search(post.tag)">
              #tag
            </div>

            <div class="row">
              <div class="post-actionButtons"><q-icon name="thumb_up" size="16px" /> 204</div>

              <div class="post-actionButtons"><q-icon name="chat" size="16px" /> 24</div>

              <div class="post-actionButtons"><q-icon name="share" size="16px" /> 40</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import 'highlight.js/styles/github-dark.min.css'
import hljs from 'highlight.js'

const $q = useQuasar()
const search = ref()
const postsTabs = ref('following')
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

<style scoped>
.left-sidebar {
  position: fixed;
  width: 318px;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.dark-mode-toggle {
  margin-top: auto;
  width: 100%;
}

.sidebar-content {
  flex-grow: 1;
  overflow-y: auto;
  padding-bottom: 60px;
}

.sidebar-footer {
  padding: 15px;
}

.new-post-box {
  width: 1100px;
  border-radius: 8px;
  margin: 1rem auto;
  color: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.post-input textarea {
  width: 100%;
  min-height: 120px;
  max-height: 240px;
  background-color: #2a2a2a;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
  padding: 0.75rem;
  color: #fff;
  font-size: 0.9rem;
  resize: vertical;
}

.post-input textarea::placeholder {
  color: #888;
}

.post-input textarea:focus {
  outline: 2px solid #555;
}

.post-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #1e1e1e;
  border-bottom-left-radius: 4px;
  border-bottom-right-radius: 4px;
  padding-left: 4px;
}

.action-left {
  display: flex;
  gap: 0.5rem;
}

.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: #fff;
  font-size: 0.9rem;
  cursor: pointer;
  border-radius: 4px;
  padding: 0.4rem 0.3rem;
  transition: background 0.2s;
}

.icon-btn:hover {
  background-color: #333;
}

.icon-btn img {
  height: 16px;
  margin-right: 0.3rem;
}

.post-btn {
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.post-actionButtons {
  padding: 15px;
  border-radius: 4px;
  transition: ease-in 0.3s;
}

.post-actionButtons:hover {
  color: #d9ac30;
  background: #7e600f;
}

.code-card {
  background: #1e1e1e;
  color: white;
  font-family: 'Fira Code', monospace;
}

.code-header {
  padding: 10px 15px;
  font-weight: bold;
}

.code-content {
  max-height: 150px;
  overflow: hidden;
  white-space: pre-wrap;
}
</style>
