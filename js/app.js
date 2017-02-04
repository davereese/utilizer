new Vue({

  el: '#utilizer',

  data: {
    form: false,
    username: null,
    password: null,
    loading: null,
    name: null,
    avatar: null,
    id: null,
    title: null,
    items: null,
    billable: null,
    nonBillable: null,
    errorMessage: null,
    thisWeek: true,
    active: 'active'
  },

  created: function () {
    this.checkCookies();
  },

  methods: {
    checkCookies: function() {
      var username = Cookies.get('name'),
          password = Cookies.get('password');
      if ( undefined !== username && undefined !== password ) {
        this.username = username;
        this.password = password;
        this.fetchUser();
      } else {
        this.loadForm();
      }
    },
    loadForm: function() {
      this.form = true;
    },
    fetchUser: function () {
      var self = this;
      var sendData = {
        'action': 'user',
        'username': self.username,
        'password': self.password
      };
      self.form = false;
      self.loading = true;
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: 'php/user-request.php',
        data: sendData,
        success: function(data) {
          if ( 'Error' !== data['user'] ) {
            var userData = JSON.parse(data['user']);
            self.name = userData.firstName + ' ' + userData.lastName;
            self.avatar = userData.avatar;
            self.id = userData.id;
            // Set cookies
            Cookies.set('name', self.username, { expires: 1 });
            Cookies.set('password', self.password, { expires: 1 });
            self.fetchData();
          } else {
            self.loading = false;
            self.errorMessage = 'Incorrect Harvest credentials'
            self.loadForm();
          }
        }
      });
    },
    fetchData: function (lastWeek = null) {
      var self = this;
      var sendData = {
        'action': 'userEntries',
        'username': self.username,
        'password': self.password,
        'id': self.id,
        'last': lastWeek
      };
      self.items = null;
      self.loading = true;
      if ( null !== lastWeek ) {
        self.thisWeek = false;
      } else {
        self.thisWeek = true;
      }
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: 'php/request.php',
        data: sendData,
        success: function(data) {
          if ( 'Error' !== data['json'] ) {
            var newData = JSON.parse(data['json']),
                billableData = 0.00,
                nonBillableData = 0.00;
            if ( null !== lastWeek ) {
              self.title = 'Hours last week';
            } else {
              self.title = 'Hours this week';
            }
            // Assign all items to items
            if ( 0 < newData.length ) {
              self.items = newData;
            }  else {
              self.items = [{name:'No entries yet this week.'}];
            }
            // Assign billable and non billable hours
            for(i = 0; i < newData.length; i++ ) {
              if ( 'true' === newData[i].billable ) {
                billableData = billableData+parseFloat(newData[i].hours);
              } else if ( 'false' === newData[i].billable ) {
                nonBillableData = nonBillableData+parseFloat(newData[i].hours);
              }
            }
            self.billable = Math.round(billableData * 100) / 100;
            self.nonBillable = Math.round(nonBillableData * 100) / 100;
          } else {
            self.loading = false;
            self.errorMessage = 'Incorrect Harvest credentials'
            self.loadForm();
          }
        }
      });
    },
    beforeEnter: function (el) {
      el.style.opacity = 0;
      el.style.left = '-100px';
    },
    enter: function (el, done) {
      var delay = el.dataset.index * 100;
      var opacityVal = $(el).hasClass('billable') ? 1 : 0.6;
      setTimeout(function () {
        Velocity(
          el,
          { opacity: opacityVal, left: 0 },
          { complete: done, easing: 'easeInOutCirc' }
        );
      }, delay);
    },
    leave: function (el, done) {
      var delay = el.dataset.index * 100;
      setTimeout(function () {
        Velocity(
          el,
          { opacity: 0, left: -100 },
          { complete: done, easing: 'easeInOutCirc' }
        );
      }, delay);
    },
    logOut: function(e) {
      e.preventDefault();
      Cookies.remove('name');
      Cookies.remove('password');
      this.username = null;
      this.password = null;
      this.name = null;
      this.avatar = null;
      this.items = null;
      this.billable = null;
      this.nonBillable = null;
      this.loading = false;
      this.loadForm();
    }
  },

  filters: {
    parseDate: function (value) {
      if (!value) return '';
      date = value.split('-');
      return date[1]+'/'+date[2];
    },
    parseYear: function (value) {
      if (!value) return '';
      date = value.split('-');
      return date[0];
    }
  },

  computed: {
    utilization: function() {
      return (Math.round((this.billable/45) * 100) / 100)*100+'%';
    },
    totalHours: function() {
      return Math.round((this.billable + this.nonBillable) * 100) / 100;
    }
  }
});
