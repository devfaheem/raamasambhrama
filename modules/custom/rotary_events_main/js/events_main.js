(function ($, Drupal) {
    $("#edit-field-zone-target-id").on("change",function(e){
        var target = e.target
        var zone = target.value
        $.ajax({
            url:"/api/list/clubs/"+zone+"?_format=json",
            type:"GET",
            success: function(data){
                var clubsDropdown = $("#edit-field-club-target-id")
                clubsDropdown.find('option').remove();
                clubsDropdown.append("<option value='All'>Any</option>")
                for(index in data){
                    var club = data[index]
                    clubsDropdown.append("<option value="+club["tid"]+">"+club["name"]+"</option>")
                }
            }
        })
    })
})(jQuery, Drupal);
