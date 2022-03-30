<template lang="pug">
  .p-6.bg-white
    form(enctype="multipart/form-data" novalidate)
      .p-4.bg-red-300.after.my-2(
        v-if="isFailed"
      )
        p(
          v-for="error in errors"
        ) {{ error }}
      input.border-2.px-4.py-2.my-2(
        @change="fileChanged($event.target.files)"
        type="file"
        multiple
        :name="uploadFieldName"
        :disabled="isSaving"
        accept="text/csv"
       )
      p(v-if="isInitial") No file selected
      p(v-if="isSuccess") Done!
      p(v-if="isSaving") Uploading ...
      label(
        for="saveToDatabase"
      ) Save to database
      input.ml-2(
        id="saveToDatabase"
        type="checkbox"
        v-model="saveToDatabase"
      )
      .py-3
        input.border-2.px-4.py-2(
          type="submit"
          @click.prevent="save"
        )
    .py-4
      .py-2
        .font-bold Avg price:
          .font-normal {{ data.averageAll }}
      .py-2
        .font-bold Total houses sold:
          .font-normal {{ data.totalHousesSold }}
      .py-2
        .font-bold No of crimes in 2011:
          .font-normal {{ data.numberOfCrimesIn2011 }}
      .py-2
        .font-bold Average price per year in London Area
        .font-normal(
          v-for="(val,year) in data.allYearsAveragePrice"
        ) {{ year }}: {{ val }}

</template>

<script>
import FileUploadMixin from '@/plugins/mixins/fileUploadMixin'
import axios from "axios"

const BASE_URL = 'http://localhost:3000/api/'

export default {
  name: "UploadForm",
  mixins: [FileUploadMixin],
  data() {
    return {
      data: [],
      files: [],
      saveToDatabase: false,
      uploadedFiles: [],
      uploadError: null,
      uploadFieldName: 'file'
    }
  },
  computed: {
    isInitial() {
      return this.currentStatus === this.statuses.STATUS_INITIAL
    },
    isSaving() {
      return this.currentStatus === this.statuses.STATUS_SAVING
    },
    isSuccess() {
      return this.currentStatus === this.statuses.STATUS_SUCCESS
    },
    isFailed() {
      return this.currentStatus === this.statuses.STATUS_FAILED
    }
  },
  methods: {
    async load() {
      const url = `${BASE_URL}/all`

      const response = await axios.get(url)

      this.data = response.data
    },
    reset() {
      this.currentStatus = this.statuses.STATUS_INITIAL;
      this.uploadedFiles = [];
      this.uploadError = null;
    },
    fileChanged(files) {
      this.files = files[0]
    },
    save() {
      this.currentStatus = this.statuses.STATUS_SAVING;
      this.upload(this.files)
        .then(() => {
          this.load()
        })
    },
  },
  mounted() {
    this.reset()
    this.load()
  },
}
</script>

<style scoped lang="sass">
</style>
