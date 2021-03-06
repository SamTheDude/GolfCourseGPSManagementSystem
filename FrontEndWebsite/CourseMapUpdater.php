<?php
//Location Cordinates

//Location cordinates
$TenPXMarkLong = 52.151330;
$TenPXMarkLat = -0.485627;
$HunderedPXMarkLong = 52.150642;
$HunderedPXMarkLat = -0.484531;

//Used for testing all the Requests
//Getting Data
//$StartTime = $_REQUEST["StartTime"];
//$EndTime = $_REQUEST["EndTime"];
//$PhoneID = $_REQUEST["ID"];

//Database Querying for all of the coordinates ever.
//Query Generation
//$PhoneIDCondition = " AND PhoneID = " . PhoneID;
//if(PhoneID == ""){
//	$PhoneIDCondition = "";
//}
//$Query = "SELECT * FROM GPSData WHERE DateTimeStamp >= '" . $StartTime . "' AND WHERE DateTimeStamp <= '" . $EndTime . "'" . PhoneIDCondition . ";" ;

//Gets the time
$time = time();
//Takes away 200 seconds
$timeMin = $time - 200;
//puts the time into an SQL friendly format
$date = date('m-d-Y H:i:s', $timeMin);
//Queries to find all of the gps positions that have been recorded within the last 200 seconds.
$Query = "SELECT * FROM GPSData
INNER JOIN Phone ON GPSData.PhoneID = Phone.PhoneID INNER JOIN PhoneBookings ON Phone.PhoneID = PhoneBookings.PhoneID INNER JOIN UserAccounts on PhoneBookings.UserID = UserAccounts.UserID WHERE DateTimeStamp >= '" . $date . "';";

//Database connection and execution
$PDO = new PDO('sqlite:C:\Users\kent_\OneDrive\Documents\Project work\GolfCourseGPSManagementSystem\Database\GolfData.db');
$statement = $PDO->prepare($Query);
$statement->execute();
$results = $statement->fetchAll();

//Verifying Permission is valid.
$TokenQuery = "SELECT PermissionName FROM UserSessions 
INNER JOIN UserAccounts ON UserSessions.UserID = UserAccounts.UserID
INNER JOIN PermissionGroupAllocation ON UserAccounts.UserID = PermissionGroupAllocation.UserID
INNER JOIN PermissionGroups ON PermissionGroupAllocation.PermissionGroupID = PermissionGroups.PermissionGroupID
INNER JOIN PermissionAllocation ON PermissionGroups.PermissionGroupID = PermissionAllocation.PermissionGroupID
INNER JOIN Permissions ON Permissions.PermissionID = PermissionAllocation.PermissionID
WHERE SessionToken = '" . $_GET['Token'] . "';";

//Runs the query and gets all of the data back into $TokenQueryResults.
$TokenStatement = $PDO->prepare($TokenQuery);
$TokenStatement->execute();
$TokenQueryResults = $TokenStatement->fetchAll();

//Checks the permission list to see if the user has the correct permissions.
$AllowedToView = false;
$AllowedToViewDetailed = false;
foreach($TokenQueryResults as $Row){
	if($Row[0] == "CourseMapView"){
		$AllowedToView = true;
	}
	if($Row[0] == "DetailedMapView"){
		$AllowedToViewDetailed = true;
	}
}

//if the token isn't empty this runs and updates the course map
if(!empty($_GET['Token'])){
	//Will only run if the user has the correct permissions.
	if($AllowedToView){
		$Count = 0;
		//Runs for all of the users on the course and displays a shape in the correct place.
		foreach($results as $Row){
			//Works out the pixel position
			$TopPX = intval((($Row['Longitude'] - $TenPXMarkLong)/($HunderedPXMarkLong-$TenPXMarkLong))*90);
			$LeftPX = intval((($Row['Latitude'] - $TenPXMarkLat)/($HunderedPXMarkLat-$TenPXMarkLat))*90);
			
			//Works out the date format for each of the points.
			$dtime = DateTime::createFromFormat("m-d-Y H:i:s", $Row[1]);
			$TimeMade = $dtime->getTimestamp();
			
			//Works out the difference in time from when the gps came in and the time at the current time and then uses this to decide the colour of the shape.
			//White shape means old and blue is for the newer shapes.
			$HexAppend = dechex(256-(intval(intval($TimeMade-$timeMin))*(256/200)));
			
			//Adds a 0 to the front of the hex append
			if(strlen($HexAppend) == 1){
				$HexAppend = "0" . $HexAppend;
			}
			
			//Compiles the hex code.
			$HexCode = "#" . $HexAppend . $HexAppend . "ff";
			
			//If the user is allowed to view the detailed course breakdown then they will het to see all of the usernames otherwise they just see the long, lat of the person.
			if($AllowedToViewDetailed){
				$Title = $Row['UserName'];
			}else{
				$Title = string($Row['Longitude'] . ", " . $Row['Latitude']);
			}
			//Compiles all of the HTML into one place and returns it
			echo "<div class='Point-Overlay' title='" . $Title . "' style='background: " . $HexCode . ";top: " . $TopPX . "px;left: " . $LeftPX . "px;'></div>";
			$Count = $Count + 1;
			$dtime = DateTime::createFromFormat("m-d-Y H:i:s", $Row[1]);
			$TimeMade = $dtime->getTimestamp();
		}
		
		if($AllowedToViewDetailed){
			//Adds all the HTML from the course map info updater here if the user is allowed to view the detailed view so that the table appears to the left of the course.
			include 'CourseMapInfoUpdater.php';
		}
	}else{
		//Tells the user that they don't have any permission to view all of this.
		echo "
		<div class='Pannel Spacer'>
		<div class='Course-Image'>
		Your Account Isn't Permitted to View The Position of Players On The Course.
		</div>
		</div>
		";
	}
}




?>

