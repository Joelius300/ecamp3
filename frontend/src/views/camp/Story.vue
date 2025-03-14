<!--
Admin screen of a camp: Displays details & periods of a single camp and allows to edit them.
-->

<template>
  <content-card :title="$tc('views.camp.story.title')" toolbar>
    <template #title-actions>
      <template v-if="$vuetify.breakpoint.smAndUp">
        <e-switch
          v-model="editing"
          :disabled="!isContributor"
          :label="$tc('global.button.editable')"
          class="ec-story-editable ml-auto"
          @click="$event.preventDefault()"
        />
      </template>
      <v-menu v-else offset-y>
        <template #activator="{ on, attrs }">
          <v-btn class="ml-auto" text icon v-bind="attrs" v-on="on">
            <v-icon>mdi-dots-vertical</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item :href="previewUrl">
            <v-list-item-icon>
              <v-icon>mdi-printer</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              {{ $tc('views.camp.print.title') }}
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <e-switch
              v-model="editing"
              :label="$tc('global.button.editable')"
              class="ec-story-editable"
              @click.stop="$event.preventDefault()"
            />
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <v-expansion-panels
      v-if="camp().periods().items.length > 1"
      v-model="openPeriods"
      accordion
      flat
      multiple
    >
      <story-period
        v-for="period in camp().periods().items"
        :key="period._meta.self"
        :editing="editing"
        :period="period"
      />
    </v-expansion-panels>
    <div v-else-if="camp().periods().items.length === 1" class="px-4">
      <story-day
        v-for="day in camp().periods().items[0].days().items"
        :key="day._meta.self"
        :day="day"
        :editing="editing"
        class="my-4"
      />
    </div>
    <v-card-actions v-if="$vuetify.breakpoint.smAndUp">
      <v-btn :href="previewUrl" class="ml-auto" color="primary" target="_blank">
        <v-icon left>mdi-printer</v-icon>
        {{ $tc('views.camp.print.title') }}
      </v-btn>
    </v-card-actions>
  </content-card>
</template>

<script>
import ContentCard from '@/components/layout/ContentCard.vue'
import StoryPeriod from '@/components/story/StoryPeriod.vue'
import { campRoleMixin } from '@/mixins/campRoleMixin'
import StoryDay from '@/components/story/StoryDay.vue'

const PRINT_SERVER = window.environment.PRINT_SERVER

export default {
  name: 'Story',
  components: {
    StoryDay,
    StoryPeriod,
    ContentCard,
  },
  mixins: [campRoleMixin],
  props: {
    camp: { type: Function, required: true },
  },
  data() {
    return {
      editing: false,
      openPeriods: [],
    }
  },
  computed: {
    previewUrl() {
      const config = {
        showStoryline: true,
      }
      const configGetParams = Object.entries(config)
        .map(([key, val]) => `${key}=${val}`)
        .join('&')
      return `${PRINT_SERVER}/?camp=${this.camp().id}&pagedjs=true&${configGetParams}`
    },
  },
  mounted() {
    this.camp()
      .periods()
      ._meta.load.then((periods) => {
        this.openPeriods = periods.items
          .map((period, idx) => (Date.parse(period.end) >= new Date() ? idx : null))
          .filter((idx) => idx !== null)
      })
  },
}
</script>

<style lang="scss" scoped>
.ec-story-editable ::v-deep .v-input--selection-controls {
  margin-top: 0;
}
</style>
