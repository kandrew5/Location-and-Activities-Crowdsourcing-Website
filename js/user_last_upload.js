$(document).ready(function() {
  $.ajax({
    type: 'POST',
    url: "charts_data/user_file_data.php",
    dataType: 'json',
    error: function(xhr, status, error) {
      alert(xhr.responseText);
    },
    success: function(data) {
      var file_table = data[0];

      $.each(file_table, function (key, val) {
        if(key == "Last file upload"){
          if(val== 'You have not uploaded any file yet'){
            $('#last_upload').append(val);
          }
          else{
            $('#last_upload').append("Your last upload date: " + val);
          }

        }
      });
    }
  });
});
