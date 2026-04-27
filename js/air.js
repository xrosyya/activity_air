$(document).ready(function(){
    uri=window.Location.href;
    e=uri.split("=");
    console.log("URI: "+uri+" e[1]:"+e[1]);

    if (e[1]=="user") {
        $("#summary, #chart, #user_add" ).hide();
        $("#user_list").show();
        $(".datatable-dropdown").append("<button type=button class='btn btn-outline-success float-start me-2'><i class='fa-solid fa-user-plus'></i> User</button>");
        $(".datatable-dropdown button").click(function(){
            console.log("tombol diklik");
        })
    } else {
        $("#summary, #chart").show();
        $("#user_add, #user_list").hide();
    }
    
});
