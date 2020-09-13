$(document).ready(function() {
  $.ajax({
    type: 'POST',
    url: "account.php",
    dataType: 'json',
    error: function(xhr, status, error) {
      alert(xhr.responseText);
    },
    success: function(data) {
      var firstname = document.getElementById("name_label");
      var lastname = document.getElementById('last_label');
      var username = document.getElementById('username_label');

      var  user_data = data;
      var counter=0;

      $.each(user_data, function (key, val) {
        if(counter==0){
          document.getElementById("name_label").value = user_data[counter];
        }
        if(counter==1){
          document.getElementById("last_label").value = user_data[counter];
        }
        if(counter==2){
          document.getElementById("username_label").value = user_data[counter];
        }
        if(counter==3){
          document.getElementById("email_label").value = user_data[counter];
        }
        counter+=1;
      });
      document.getElementById("name_label").setAttribute("disabled", "true");
      document.getElementById('last_label').setAttribute("disabled", "true");
      document.getElementById('username_label').setAttribute("disabled", "true");
    }
  });
});

function change_data() {
  document.getElementById("name_label").removeAttribute("disabled");
  document.getElementById('last_label').removeAttribute("disabled");
  document.getElementById('username_label').removeAttribute("disabled");
  document.getElementById('name_label').focus();
}

function save_data() {
  var firstname = document.getElementById("name_label");
  var lastname = document.getElementById('last_label');
  var username = document.getElementById('username_label');
  var save= "true";
  var new_name = $("#name_label").val();
  var new_surname = $("#last_label").val();
  var new_username = $("#username_label").val();

  firstname.setAttribute("disabled", "true");
  lastname.setAttribute("disabled", "true");
  username.setAttribute("disabled", "true");

  $.ajax({
    type: 'POST',
    url: "account.php",
    data: {
      save: save,
      new_name: new_name,
      new_surname: new_surname,
      new_username: new_username,
    },
    dataType: 'json',
    success: function(data) {
        alert("Successful data update!");
    }
  });
}