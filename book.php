<?php
$mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
if(isset($_GET['date'])){
    $date = $_GET['date'];
    $stmt = $mysqli->prepare("SELECT * FROM booking WHERE date = ?");
    $stmt->bind_param('s', $date);
    $bookings = array();
    if($stmt->execute()) {
        $result = $stmt->get_result();

        if($result->num_rows>0) {
            while($row = $result->fetch_assoc()) {
                $bookings[] = $row['timeslot'];
            }
            $stmt->close();
        }
    }
}

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $tel_num = $_POST['tel_num'];
    $timeslot = $_POST['timeslot'];
    $message = $_POST['message'];
    $stmt = $mysqli->prepare("SELECT * FROM booking WHERE date = ? AND timeslot = ?");
    $stmt->bind_param('ss', $date, $timeslot);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $msg = "<div class='alert alert-danger'>Már foglalt</div>";
        }else{
            $stmt = $mysqli->prepare("INSERT INTO booking (name, email, date, tel_num, timeslot, message) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('ssssss', $name, $email, $date, $tel_num, $timeslot, $message);
            $stmt->execute();
            $msg = "<div class='alert alert-success'>Sikeres foglalás</div>";
            $bookings[] = $timeslot;
            $stmt->close();
            $mysqli->close();
        }
    }
}

$duration = 30;
$cleanup = 0;
$start = "09:00";
$end = "17:00";

function timeslots($duration, $cleanup, $start, $end) {
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = new DateInterval("PT".$duration."M");
    $cleanupInterval = new DateInterval("PT".$cleanup."M");
    $slots = array();

    for($intStart = $start; $intStart<$end; $intStart->add($interval)->add($cleanupInterval)) {
        $endPeriod = clone $intStart;
        $endPeriod->add($interval);
        if($endPeriod>$end) {
            break;
        }
        $slots[] = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
    }
    return $slots;
}


?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="css/time.css">
  </head>

  <body>
    <div class="container">
        <h1 class="text-center">Foglalás Dátuma: <?php echo date('Y/m/d', strtotime($date)); ?></h1><hr>
        <div class="row">
           <div class="col-md12">
                <?php echo(isset($msg)) ?$msg:"" ?>
           </div>
                <?php $timeslots = timeslots($duration, $cleanup, $start, $end);
                foreach($timeslots as $ts) {
                    ?>
                    <div Class="col-md-2">
                        <div class="form-group">
                            <?php if(in_array($ts, $bookings)) { ?>
                                <button class="btn btn-danger"><?php echo $ts; ?></button>
                                <?php } else { ?>
                                    <button class="btn btn-success book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>
                                <?php } ?>
                        </div>
                    </div>
                <?php } ?>           
        </div>
        <div>
            <a class="btn btn-success" href="calendar.php" role="button">Vissza</a>
        </div>
    </div>
    

    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Időpontfoglalás : <span id="slot"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form action="" method="post">
                               <div class="form-group">
                                    <label for="">Időpont</label>
                                    <input readonly type="text" class="form-control" id="timeslot" name="timeslot">
                                </div>
                                <div class="form-group">
                                    <label for="">Név</label>
                                    <input required type="text" class="form-control" name="name" placeholder="Írd be a teljes neved...">
                                </div>
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input required type="email" class="form-control" name="email" placeholder="pl: tamas@gmail.com...">
                                </div>
                                <div class="form-group">
                                    <label for="">Telefonszám</label>
                                    <input required type="tel" class="form-control" name="tel_num" placeholder="+36102345678...">
                                </div>
                                <div class="form-group">
                                    <label for="">Üzenet</label>
                                    <input required type="textarea" class="form-control" name="message" placeholder="Ide írd azt, amit szeretnél...">
                                </div>
                                <div class="form-group pull-right">
                                    <button name="submit" type="submit" class="btn btn-primary">Jelentkez</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    
    <script>
        $(".book").click(function(){
            var timeslot = $(this).attr('data-timeslot');
            $("#slot").html(timeslot);
            $("#timeslot").val(timeslot);
            $("#myModal").modal("show");
        });
    </script>

  </body>

</html>