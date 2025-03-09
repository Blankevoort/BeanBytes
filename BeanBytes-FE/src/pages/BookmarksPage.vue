<template>
  <q-page class="bg-dark text-secondary row justify-center">
    <div class="bg-dark text-white text-grey-6 col-sm-10 col-md-10 col-lg-8 col-xl-8">
      <div class="q-mt-lg q-gutter-y-md">
        <!-- Posts -->

        <PostCard />
      </div>
    </div>
  </q-page>
</template>

<script>
import PostCard from 'src/components/PostCard.vue'

export default {
  components: {
    PostCard,
  },
}
</script>
