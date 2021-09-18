<?php

function build_calendar($month, $year) {
    $mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
    $stmt = $mysqli->prepare("SELECT * FROM booking WHERE MONTH(date) = ? AND YEAR(date)=?");
    $stmt -> bind_param('ss', $month, $year);
    $bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $bookings[] = $row['date'];
            }
            $stmt->close();
        }
    }
 
    $daysOfWeek = array('vasárnap', 'Hétfő','Kedd','Szerda','Csütörtök','Péntek','Szombat');
    $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
    $numberDays = date('t',$firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $monthName = $dateComponents['month'];
    $dayOfWeek = $dateComponents['wday']; 
    $datetoday = date('Y-m-d');
    
    $prev_month = date('m', mktime(0,0,0, $month-1, 1, $year));
    $prev_year = date('Y', mktime(0,0,0, $month-1, 1, $year));
    $next_month = date('m', mktime(0,0,0, $month+1, 1, $year));
    $next_year = date('Y', mktime(0,0,0, $month+1, 1, $year));

    $calendar = "<center><h2>$monthName $year</h2>";
    $calendar .= "<a class='btn btn-primary btn-xs m-1' href='?month=".$prev_month."$year=".$prev_year."'>Előző Hónap</a>";
    $calendar .= "<a class='btn btn-primary btn-xs m-1' href='?month=".date('m')."$year=".date('Y')."'>Jelenlegi Hónap</a>";
    $calendar .= "<a class='btn btn-primary btn-xs m-1' href='?month=".$next_month."$year=$next_year'>Következő Hónap</a>";
    $calendar .= "<table class='table table-bordered'>";
    $calendar .="<tr>";
    
    

    
    foreach($daysOfWeek as $day) {
        $calendar .= "<th  class='header'>$day</th>";
    } 
    
    $currentDay = 1;
    $calendar .= "</tr><tr>";

    if($dayOfWeek > 0) { 
        for($k=0;$k<$dayOfWeek;$k++){
            $calendar .= "<td  class='empty'></td>"; 
        }
    }
    
     
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    
    while ($currentDay <= $numberDays) {
         if ($dayOfWeek == 7) {
             $dayOfWeek = 0;
             $calendar .= "</tr><tr>";
         }
          
         $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
         $date = "$year-$month-$currentDayRel";
         $dayname = strtolower(date('l', strtotime($date)));
         $eventNum = 0;
         $today = $date==date('Y-m-d')? "today" : "";

         if($date<date('Y-m-d')){
             $calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>N/A</button>";
         }else{
             $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Foglalás</a>";
         }
         
         
         $calendar .="</td>";

         $currentDay++;
         $dayOfWeek++;
     }

     if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek;
        for($l=0;$l<$remainingDays;$l++){
            $calendar .= "<td class='empty'></td>"; 
        }
     }
     
    $calendar .= "</tr>";
    $calendar .= "</table>";
    return $calendar;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/timestyle.css">
    <title>Időpontfoglalás</title>
</head>
<body>
    <div class="container"> 
        <div class="row"> 
            <div class="col-md-12"> 
                <div id="calendar"> 
                    <?php 
                    $dateComponents = getdate(); 
                    $month = $dateComponents['mon']; 
                    $year = $dateComponents['year']; 
                    echo build_calendar($month,$year); 
                    ?> 
                </div>
                <div>
                <a class="btn btn-primary" href="main.php" role="button">Vissza</a>
                </div> 
        </div> 
    </div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script> 
</body>
</html>