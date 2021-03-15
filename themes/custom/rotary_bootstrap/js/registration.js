const vueAxios = axios.create({
  baseURL: '/',
  timeout: 30000
});

var app = new Vue({
  el: '#app',
  data:
    function () {
      return {
        registrantName: "",
        email: "",
        password: "",
        confirmpassword: "",
        registrationType: "",
        clubId: "",
        zoneId: "",
        paymentMode: "",
        mobile: "",
        contactAddress: "",
        currentDesignation: "",
        zones: []
      }
    },
  mounted() {
    this.loadZones()
  },
  methods: {
    loadZones: async function () {
      var response = await vueAxios.get("/api/list/zones?_format=json")
      this.zones = response.data
    }
  }
})
