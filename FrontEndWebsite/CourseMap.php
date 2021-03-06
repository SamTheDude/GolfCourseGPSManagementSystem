<?php
if(!empty($_COOKIE["BedAndCountySessionToken"])){
	//Stats a new database connection.
	$PDO = new PDO('sqlite:C:\Users\kent_\OneDrive\Documents\Project work\GolfCourseGPSManagementSystem\Database\GolfData.db');
	//Command that finds the user Id asociated with the token in the cookie.
	$Command = "SELECT * FROM UserSessions WHERE SessionToken = '" . $_COOKIE["BedAndCountySessionToken"] . "';";
	//Prepared to prevent injection.
	$statement = $PDO->prepare($Command);
	//Executes command.
	$statement->execute();
	//Gets all of the results into $SessionResults.
	$SessionResults = $statement->fetchAll();

	//Command that selects all the user details about the user.
	$Command0 = "SELECT * FROM UserAccounts WHERE UserID = " . $SessionResults[0][3] . ";";
	$statement = $PDO->prepare($Command0);
	//Executes command.
	$GoodCookie = $statement->execute();
	if($GoodCookie){
		//If the details exist then they are retrived into $UserResults and then split down into individual variables.
		$UserResults = $statement->fetchAll();
		$UserID = $UserResults[0][0];
		$UserName = $UserResults[0][1];
		$Email = $UserResults[0][2];
		$FirstName = $UserResults[0][3];
		$SecondName = $UserResults[0][4];
		$DateOfBirth = $UserResults[0][5];
		$Password = $UserResults[0][5];

		//SQL query to verify the user's permission.
		$TokenQuery = "SELECT PermissionName FROM UserSessions 
		INNER JOIN UserAccounts ON UserSessions.UserID = UserAccounts.UserID
		INNER JOIN PermissionGroupAllocation ON UserAccounts.UserID = PermissionGroupAllocation.UserID
		INNER JOIN PermissionGroups ON PermissionGroupAllocation.PermissionGroupID = PermissionGroups.PermissionGroupID
		INNER JOIN PermissionAllocation ON PermissionGroups.PermissionGroupID = PermissionAllocation.PermissionGroupID
		INNER JOIN Permissions ON Permissions.PermissionID = PermissionAllocation.PermissionID
		WHERE SessionToken = '" . $_COOKIE["BedAndCountySessionToken"] . "';";
		
		//Prepares the query.
		$TokenStatement = $PDO->prepare($TokenQuery);
		//Executes the above query.
		$TokenStatement->execute();
		//Gets all the permission results into $TokenQueryResults.
		$TokenQueryResults = $TokenStatement->fetchAll();
	}else{
		//If the cookie is invalid then it is removed.
		setcookie("BedAndCountySessionToken", null, time() + (86400 * 30), "/");
	}
}
?>
<Head>
<div id="CodeRefs">
<!--External code links-->
<link rel="stylesheet" href="Styles.css">
<Script src="CourseMapLocationUpdater.js"></Script>
</div>

<!--Background frames animation-->
<div class="Frame1"></div>
<div class="Frame2"></div>
<div class="Frame3"></div>
<div class="Frame4"></div>

<!--Navigation bar at the top is housed here.-->
<Nav class="Navigation">
	<li class="Block" onclick="window.location.href = 'Index.php'">Home</li>
	<li class="TopBlock" onclick="window.location.href = 'CourseMap.php'">CourseMap</li>
	
	<?php
	//Decides wether or not the user needs to be given the user account in the top right or the loggin sign up section.
	if(empty($_COOKIE["BedAndCountySessionToken"])){
		echo"
		<li class='Login Block' onclick='window.location.href = \"Login.php\"'>Login</li>
		<li class='Login Block' onclick='window.location.href = \"SignIn.php\"'>Sign Up</li>
		";
	}else{
		echo "
		<li class='Login Block' onclick='window.location.href = \"UserHome.php\"'>" .  $FirstName . " " . $SecondName . "</li>
		<li class='Login Block' onclick='document.cookie = \"BedAndCountySessionToken=0\"; window.location.href = \"Index.php\"'>Log Out</li>
		";
	}
	
	?>
</Nav>
</Style>
</Head>
<body>

<!--Map of the course is housed here.-->
<div id="Map">
<div class="Course-Image" style='float:left;'><img src="ImageGallery/CourseMap.png" alt="Course Map" width="800px" height="1300px"></div>

<!--All of the course points are put into here so that they are rendered ontop of the map image.-->
<div id="InsertDiv"></div>
</div>
<!--Course Logo-->
<img src="ImageGallery/bedfordcountylogo.jpg" class="CourseLogo"/>
</body>


