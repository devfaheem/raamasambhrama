const vueAxios = axios.create({
  baseURL: "/",
  timeout: 30000,
});
Vue.use(VeeValidate);
var app = new Vue({
  el: "#app",
  data: function () {
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
        foodprefs: "",
        dependants: [],
      },
      utrnumber:null,
      bankRecipetImage:null,
      zones: [],
      clubs: [],
      registrationTypes: [],
      csrfToken: "",
      selectedRegistrationType: null,
    };
  },
  mounted() {
    // this.initReCaptcha();
    this.loadCsrfToken();
    this.loadZones();
    this.loadRegistrationTypes();
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
    loadClubs: async function (event) {
      var response = await vueAxios.get(
        `/api/list/clubs/${this.formData.zoneId}?_format=json`
      );
      this.clubs = response.data;
    },
    loadRegistrationTypes: async function (event) {
      var response = await vueAxios.get(`/api/registration-types?_format=json`);
      this.registrationTypes = response.data;
    },
    selectRegistrationType: async function (event) {
      this.selectedRegistrationType = this.registrationTypes.find(
        (item) => item.tid == event.target.value
      );
      this.formData["registrationType"] = this.selectedRegistrationType["tid"];
    },
    
    async handleFileUpload(event) {
      var file = event.target.files[0];
      const  fileType = file['type'];
      const validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
      if (!validImageTypes.includes(fileType)) {
        alert("Only image file type is allowed.")
        this.$refs.ackUpload.value=null;
        return;
      }
      if(file.size > 2097152 ){
        alert("Max file upload size is 2MB.")
        this.$refs.ackUpload.value=null;
        return;
      }
      this.createBase64Image(file)
      console.log(file)
    },
    createBase64Image(fileObject) {
      const reader = new FileReader();
      reader.onload = (e) => {
        this.bankRecipetImage = e.target.result;
        console.log(this.bankRecipetImage)
      }
      reader.readAsDataURL(fileObject);
    },
    submitRegistration: function (token) {
      this.$validator.validate().then(async (valid) => {
      this.formData.recaptchaToken = token;
      this.formData.utrnumber = this.utrnumber;
      this.formData.amount = this.selectedRegistrationType["price"]
      if(this.bankRecipetImage!="")
      this.formData.payment_acknowledgement = this.bankRecipetImage;
        try {
          if (valid) {
            var response = await vueAxios.post(
              `/event/registration?registrationtype=single`,
              this.formData
            );
            window.location = "/complete-message";
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
