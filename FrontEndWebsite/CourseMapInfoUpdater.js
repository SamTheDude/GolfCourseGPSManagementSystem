function getCookie(cname) {
	//Gets the cookie name that is passed into the function and appends an eaquels to it.
    var name = cname + "=";
	//Decodes the cookie into the variable.
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function UpdateMap(){
	//Creates a new xhttp object.
	var xhttp = new XMLHttpRequest();
	//Gets the cookie with this name.
	var Cookie = getCookie("BedAndCountySessionToken");
	
	//Runs this when the xhttp connection is ready.
	xhttp.onreadystatechange = function() {
		//Runs when the conditions for connecting are correct.
		if (this.readyState == 4 && this.status == 200) {
			//Puts all of the HTML that will be recived from the site into a single div at the bottom of the site.
			document.getElementById("InsertDiv").innerHTML = this.responseText;
		}
	}
	//creates a URL that contains all the get data that the php site needs to opperate.
	var SitePHP = "CourseMapInfoUpdater.php?Token=" + Cookie;
	//Opens the connection for the data to be sent.
	xhttp.open("GET", SitePHP, true);
	xhttp.send();
}

window.onload = function(){
	//Runs the update map function
	UpdateMap();
	//Runs the update map function every 10000 milliseconds or 10 seconds.
	setInterval(UpdateMap, 10000);
}