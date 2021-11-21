const vueAxios = axios.create({
  baseURL: "/",
  timeout: 30000,
});
Vue.use(VeeValidate);
var app = new Vue({
  el: "#app",
  data: function () {
    return {
      formData: [],
      paymentMode:null,
      club:null,
      zone:null,
      zones: [],
      clubs: [],
      registrationTypes: [],
      csrfToken: "",
      selectedRegistrationType: null,
    };
  },
  mounted() {
    this.addUser()
    this.addUser()
    this.addUser()
    this.addUser()
    this.loadCsrfToken();
    this.loadZones();
    this.loadRegistrationTypes();
    // this.initReCaptcha();
  },
  
  methods: {
    initReCaptcha: function() {
      var self = this;
      setTimeout(function() {
          if(typeof grecaptcha === 'undefined') {
              self.initReCaptcha();
          }
          else {
              grecaptcha.render('recaptcha', {
                  sitekey: '6LeSgUUdAAAAAClz1GqCJ6Ms-G4y3Jgvl35K3fAO',
                  size: 'invisible',
                  badge: 'inline',
                  callback: self.submitRegistration
              });
          }
      }, 100);
  },
  validate: function() {
      // your validations...
      // ...
      grecaptcha.execute();
  },
    loadZones: async function () {
      var response = await vueAxios.get("/api/list/zones?_format=json");
      this.zones = response.data;
    },
    
    addUser:function(){
      var user = {}
      user.uuid = this.uuidv4()
      this.formData.push(user)
    },
    removeUser: function(index){
      this.$delete(this.formData, index)
    },
    uuidv4: function() {
      return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
      );
    },
    loadClubs: async function (event) {
      var response = await vueAxios.get(
        `/api/list/clubs/${this.zone}?_format=json`
      );
      this.clubs = response.data;
    },
    loadRegistrationTypes: async function (event) {
      var response = await vueAxios.get(`/api/registration-types?_format=json`);
      this.registrationTypes = response.data;
    },
    selectRegistrationType: async function (registrant, event) {
      this.selectedRegistrationType = this.registrationTypes.find(
        (item) => item.tid == event.target.value
      );
      registrant["registrationType"] = this.selectedRegistrationType["tid"];
    },
    submitRegistration: function (token) {
      this.$validator.validate().then(async (valid) => {
        
        try {
          if (valid) {
            var data = {}
            data.zone = this.zone
            data.club = this.club
            data.paymentMode = this.paymentMode
            data.registrations = this.formData
            data.recaptchaToken = token;
            var response = await vueAxios.post(
              `/event/registration?registrationtype=multiple`,
              data
            );
            window.location = "/members-confirmation";
          }
        } catch (err) {
          if(err.response.data["error"])
          {
            alert(err.response.data["error"]);
          }
        }
      });
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
        return 0;
      }
      for (
        var i = 0;
        i < parseInt(this.selectedRegistrationType["totalMembers"]);
        i++
      ) {
        this.formData.dependants.push({});
      }
      return parseInt(this.selectedRegistrationType["totalMembers"]);
    },
  },
});
