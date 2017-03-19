function check_sesion()
{
	if (location.hash.indexOf("login") == -1)
		$.ajax({
		    url: "php/check_sesion.php",
		    type: "POST",
		    data: {},
		    beforeSend: function(){},
		    success: function(data){
		        if (data == "1")
		        {
		        	location.reload();
		        }
		    }
		});

	setTimeout("check_sesion()", 5000);
}

function refresh_selectpicker()
{
	$('.selectpicker').selectpicker('refresh');
	setTimeout("refresh_selectpicker()", 1000);
	console.log("Refresheando")
}

//refresh_selectpicker();

Array.prototype.contains = function(obj){
	var i = this.length;
    while (i--) {
        if (this[i] == obj) {
            return true;
        }
    }
    return false;
}

function scrollTo(destination)
{
    $('html, body').animate({
        scrollTop: $(destination).offset().top
    }, 500);
}