<template>
  <auth-container>
    <h1 class="display-1 text-center">{{ $tc('views.auth.register.title') }}</h1>
    <v-form @submit.prevent="register">
      <e-text-field
        v-model="username"
        :name="$tc('entity.user.fields.username')"
        append-icon="mdi-account-outline"
        dense
        required
        type="text"
        autofocus
      />

      <e-text-field
        v-model="firstname"
        :name="$tc('entity.user.fields.firstname')"
        append-icon="mdi-account-outline"
        dense
        required
        type="text"
      />

      <e-text-field
        v-model="surname"
        :name="$tc('entity.user.fields.surname')"
        append-icon="mdi-account-outline"
        dense
        required
        type="text"
      />

      <e-text-field
        v-model="email"
        :name="$tc('entity.user.fields.email')"
        vee-rules="email"
        append-icon="mdi-at"
        dense
        required
        type="text"
      />

      <e-text-field
        v-model="pw1"
        :name="$tc('entity.user.fields.password')"
        :rules="pw1Rules"
        validate-on-blur
        append-icon="mdi-lock-outline"
        dense
        required
        type="password"
      />

      <e-text-field
        v-model="pw2"
        :name="$tc('views.auth.register.passwordConfirmation')"
        :rules="pw2Rules"
        validate-on-blur
        dense
        required
        append-icon="mdi-lock-outline"
        type="password"
      />

      <e-select
        v-model="language"
        :name="$tc('entity.user.fields.language')"
        dense
        :items="availableLocales"
      />

      <e-checkbox v-model="tos" required class="align-center">
        <template #label>
          <span style="hyphens: auto" :class="{ 'body-2': $vuetify.breakpoint.xsOnly }">
            {{ $tc('views.auth.register.acceptTermsOfUse') }}
          </span>
        </template>
        <template #append>
          <v-btn
            text
            dense
            min-width="0"
            :title="$tc('global.button.open')"
            target="_blank"
            class="px-1"
            to="#"
            tabindex="-1"
          >
            <v-icon small>mdi-open-in-new</v-icon>
          </v-btn>
        </template>
      </e-checkbox>

      <p class="mt-0 mb-4 text--secondary text-left">
        <small>
          <span style="color: #d32f2f">*</span>
          {{ $tc('views.auth.register.requiredField') }}
        </small>
      </p>

      <v-btn type="submit" color="primary" :disabled="!formComplete" block x-large>
        <v-progress-circular v-if="registering" indeterminate size="24" />
        <v-spacer />
        <span>{{ $tc('views.auth.register.register') }}</span>
        <v-spacer />
        <icon-spacer />
      </v-btn>
    </v-form>

    <p class="mt-8 mb-0 text--secondary text-center">
      {{ $tc('views.auth.register.alreadyHaveAnAccount') }}<br />
      <router-link :to="{ name: 'login' }">
        {{ $tc('views.auth.register.login') }}
      </router-link>
    </p>
  </auth-container>
</template>

<script>
import { load } from 'recaptcha-v3'
import AuthContainer from '@/components/layout/AuthContainer.vue'
import VueI18n from '@/plugins/i18n'

export default {
  name: 'Register',
  components: {
    AuthContainer,
  },
  data() {
    return {
      registering: false,
      username: '',
      firstname: '',
      surname: '',
      email: '',
      pw1: '',
      pw2: '',
      language: '',
      tos: false,
      recaptcha: null,
    }
  },
  computed: {
    formComplete() {
      return (
        this.tos &&
        this.username !== '' &&
        this.firstname !== '' &&
        this.surname !== '' &&
        this.email !== '' &&
        this.pw1 !== '' &&
        this.pw2 !== '' &&
        this.pw1 === this.pw2
      )
    },
    formData() {
      return {
        username: this.username,
        firstname: this.firstname,
        surname: this.surname,
        email: this.email,
        password: this.pw1,
        language: this.language,
      }
    },
    pw2Rules() {
      return [(v) => (!!v && v) === this.pw1 || 'Nicht übereinstimmend']
    },
    pw1Rules() {
      return [(v) => v.length >= 8 || 'Mindestens 8 Zeichen lang sein']
    },
    availableLocales() {
      return VueI18n.availableLocales.map((l) => ({
        value: l,
        text: this.$tc('global.language', 1, l),
      }))
    },
  },
  watch: {
    language() {
      if (VueI18n.availableLocales.includes(this.language)) {
        this.$store.commit('setLanguage', this.language)
      }
    },
  },
  mounted() {
    this.language = this.$i18n.browserPreferredLocale

    if (window.environment.RECAPTCHA_SITE_KEY) {
      this.recaptcha = load(window.environment.RECAPTCHA_SITE_KEY, {
        explicitRenderParameters: {
          badge: 'bottomleft',
        },
      })
    }
  },
  methods: {
    async register() {
      this.registering = true
      let recaptchaToken = null
      if (this.recaptcha) {
        const recaptcha = await this.recaptcha
        recaptchaToken = await recaptcha.execute('login')
      }

      this.$auth
        .register({
          password: this.formData.password,
          profile: {
            username: this.formData.username,
            firstname: this.formData.firstname,
            surname: this.formData.surname,
            email: this.formData.email,
            language: this.formData.language,
          },
          recaptchaToken: recaptchaToken,
        })
        .then(() => this.$router.push({ name: 'register-done' }))
        .catch(() => (this.registering = false))
    },
  },
}
</script>
