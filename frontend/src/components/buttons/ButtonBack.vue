<template>
  <v-btn aria-label="Zurück" icon v-bind="$attrs" @click="$router.go(-1)">
    <v-icon>mdi-arrow-left</v-icon>
  </v-btn>
</template>

<script>
export default {
  name: 'ButtonBack',
}
</script>

<style scoped></style>
