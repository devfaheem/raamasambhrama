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
          totalAmount: "",
          dependants: []
        },
        zones: [],
        clubs: [],
        registrationTypes: [],
        csrfToken: "",
        selectedRegistrationType: null
      }
    },
  mounted() {
    this.loadCsrfToken()
    this.loadZones()
    this.loadRegistrationTypes()
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
    loadRegistrationTypes: async function (event) {
      var response = await vueAxios.get(`/api/registration-types?_format=json`)
      this.registrationTypes = response.data
    },
    selectRegistrationType: async function (event) {

      this.selectedRegistrationType = this.registrationTypes.find(item => item.tid == event.target.value)
      this.formData["registrationType"] = this.selectedRegistrationType["tid"];
    },
    submitRegistration: async function () {
      try {
        if (this.formData["password"] != this.formData["confirmpassword"]) {
          alert("Password & Confirm Password do not match")
          return;
        }
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
      this.formData.dependants = [];
      if (this.selectedRegistrationType == null) {
        return 0
      }
      for (var i = 0; i < parseInt(this.selectedRegistrationType["totalMembers"]); i++) {
        this.formData.dependants.push({});
      }
      return parseInt(this.selectedRegistrationType["totalMembers"])
    }
  }
})
