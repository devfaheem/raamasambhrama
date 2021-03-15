const vueAxios = axios.create({
  baseURL: '/',
  timeout: 30000
});

var app = new Vue({
  el: '#app',
  data:
    function () {
      return {
        formData: {
          registrantName: "",
          email: "",
          password: "",
          confirmpassword: "",
          registrationType: 0,
          clubId: "",
          zoneId: "",
          paymentMode: "",
          mobile: "",
          contactAddress: "",
          currentDesignation: "",
          dependants: []
        },
        zones: [],
        clubs: [],
        csrfToken: ""
      }
    },
  mounted() {
    this.loadCsrfToken()
    this.loadZones()
  },
  methods: {
    loadZones: async function () {
      var response = await vueAxios.get("/api/list/zones?_format=json")
      this.zones = response.data
    },
    loadClubs: async function (event) {
      var response = await vueAxios.get(`/api/list/clubs/${this.formData.zoneId}?_format=json`)
      this.clubs = response.data

    },
    submitRegistration: async function () {
      try {
        var response = await vueAxios.post(`/event/registration`, this.formData)
        window.location = "/complete-message"
      }
      catch (err) {

      }
    },
    loadCsrfToken() {
      vueAxios.get("/session/token").then((response) => {
        this.csrfToken = response.data;
        vueAxios.defaults.headers.common["X-Csrf-Token"] = this.csrfToken;
      });
    },
  },
  computed: {
    dependantsCount: function () {
      switch (parseInt(this.formData.registrationType)) {
        case 0:

          return 0;

        case 9:
          this.formData.dependants = [{}]
          return 1;
        case 10:
          this.formData.dependants = [{}]
          return 1;
        case 11:
          this.formData.dependants = [{}]
          return 1;
        case 12:
          this.formData.dependants = [{}, {}]
          return 2;
        case 13:
          this.formData.dependants = [{}, {}, {}]
          return 3;
        case 14:
          this.formData.dependants = [{}, {}, {}, {}]
          return 4;
      }
    }
  }
})
