<template>
  <card-content-node v-bind="$props">
    <div class="mb-3">
      <api-form :entity="contentNode">
        <api-richtext
          fieldname="data.text"
          :label="$tc('contentNode.notes.name')"
          rows="4"
          auto-grow
          :disabled="layoutMode || disabled"
          :filled="layoutMode"
        />
      </api-form>
    </div>
  </card-content-node>
</template>

<script>
import ApiForm from '@/components/form/api/ApiForm.vue'
import CardContentNode from '@/components/activity/CardContentNode.vue'
import { contentNodeMixin } from '@/mixins/contentNodeMixin.js'

export default {
  name: 'Notes',
  components: {
    CardContentNode,
    ApiForm,
  },
  mixins: [contentNodeMixin],
}
</script>

<style scoped></style>
