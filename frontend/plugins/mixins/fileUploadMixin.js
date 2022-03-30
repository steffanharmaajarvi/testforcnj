import axios from 'axios';

const BASE_URL = 'http://localhost:3000/api/';

export default {
  data() {
    return {
      currentStatus: null,
      statuses: {
        STATUS_INITIAL: 0,
        STATUS_SAVING: 1,
        STATUS_SUCCESS: 2,
        STATUS_FAILED: 3
      },
      errors: []
    }
  },
  methods: {
    async upload(file) {
      const url = `${BASE_URL}/upload?saveToDatabase=${this.saveToDatabase}`

      const formData = new FormData()
      formData.append('file', file, 'someFileName.csv')

      try {
        const data = await axios.post(url, formData, {
          headers: {
            'content-type': 'multipart/form-data'
          }
        })

        console.log(data)
        this.currentStatus = this.statuses.STATUS_SUCCESS
      } catch (e) {
        this.errors = e.response?.data?.errors
        this.currentStatus = this.statuses.STATUS_FAILED
      }


    }
  }
}

