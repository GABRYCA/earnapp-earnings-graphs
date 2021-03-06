<?php

$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "earnapp_" . $_POST["username"];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dataPointsEarnings = array();
$dataPointsTraffic = array();
$dataPointsEarningsDaily = array();
$dataPointsTrafficDaily = array();
$result = mysqli_query($conn, "SELECT * FROM earnings");

$counter = 0;
$sumEarnings = 0;
$sumTraffic = 0;
while($row = mysqli_fetch_array($result))
{
    $phpDate = date($row['time']);
    $phpTimestamp = strtotime($phpDate);
    $javaScriptTimestamp = $phpTimestamp * 1000;
    $earningsData = $row['earnings'];
    $trafficData = $row['traffic'];
    $earned = array("x" =>  $javaScriptTimestamp, "y" => (float) $earningsData);
    $traffic = array("x" =>  $javaScriptTimestamp, "y" => (float) $trafficData);
    $sumEarnings += (float) $earningsData;
    $sumTraffic += (float) $trafficData;
    $counter++;

    if ($counter % 24 === 0){
        $dataPointsEarningsDaily[] = array("x" => $javaScriptTimestamp, "y" => (float) $sumEarnings);
        $dataPointsTrafficDaily[] = array("x" => $javaScriptTimestamp, "y" => (float) $sumTraffic);
        $sumEarnings = 0;
        $sumTraffic = 0;
    }

    $dataPointsEarnings[] = $earned;
    $dataPointsTraffic[] = $traffic;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AnonymousGCA Earnapp Earnings</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
    <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <script src="js/bootstrap.bundle.js"></script>
    <link href="css/style.css" type="text/css" rel="stylesheet">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        window.onload = function () {

            var chart = new CanvasJS.Chart("chartEarnings", {
                animationEnabled: true,
                zoomEnabled: true,
                theme: "dark2",
                title:{
                    text: "Earnings",
                },
                axisX: {
                    valueFormatString: "DD-MM-YY HH:mm",
                },
                axisY: {
                    title: "Cents Earned",
                    includeZero: true,
                    valueFormatString: "$##0.00",
                },
                data: [{
                    type: "area",
                    color: "#008f20",
                    xValueType: "dateTime",
                    xValueFormatString: "DDD HH:mm",
                    yValueFormatString: "$##0.00",
                    dataPoints: <?php echo json_encode($dataPointsEarnings); ?>
                }]
            });

            var chart2 = new CanvasJS.Chart("chartTraffic", {
                animationEnabled: true,
                zoomEnabled: true,
                theme: "dark2",
                title:{
                    text: "Traffic",
                },
                axisX: {
                    valueFormatString: "DD-MM-YY HH:mm",
                },
                axisY: {
                    title: "Traffic MegaBytes",
                    includeZero: true,
                    valueFormatString: "##0.00MB",
                },
                data: [{
                    type: "splineArea",
                    color: "#006fff",
                    xValueType: "dateTime",
                    xValueFormatString: "DDD HH:mm",
                    yValueFormatString: "##0.00MB",
                    dataPoints: <?php echo json_encode($dataPointsTraffic); ?>
                }]
            });

            var chart3 = new CanvasJS.Chart("chartEarningsDaily", {
                animationEnabled: true,
                zoomEnabled: true,
                theme: "dark2",
                title:{
                    text: "Daily Earnings",
                },
                axisX: {
                    valueFormatString: "DD-MM-YY",
                },
                axisY: {
                    title: "Cents Earned",
                    includeZero: true,
                    valueFormatString: "$##0.00",
                },
                data: [{
                    type: "splineArea",
                    color: "#21ab40",
                    xValueType: "dateTime",
                    xValueFormatString: "DDD",
                    yValueFormatString: "$##0.00",
                    dataPoints: <?php echo json_encode($dataPointsEarningsDaily); ?>
                }]
            });

            var chart4 = new CanvasJS.Chart("chartTrafficDaily", {
                animationEnabled: true,
                zoomEnabled: true,
                theme: "dark2",
                title:{
                    text: "Daily Traffic",
                },
                axisX: {
                    valueFormatString: "DD-MM-YY",
                },
                axisY: {
                    title: "Traffic MegaBytes",
                    includeZero: true,
                    valueFormatString: "##0.00MB",
                },
                data: [{
                    type: "splineArea",
                    color: "#3989ef",
                    xValueType: "dateTime",
                    xValueFormatString: "DDD",
                    yValueFormatString: "##0.00MB",
                    dataPoints: <?php echo json_encode($dataPointsTrafficDaily); ?>
                }]
            });

            currentUser();

            chart.render();
            chart2.render();
            chart3.render();
            chart4.render();
        }
        function currentUser(){
            document.getElementById("user").innerText = "Current user: " + "<?php echo $_POST["showname"]?>"
        }
    </script>
</head>
<body>
<div class="container-fluid">

    <div id="header"></div>
    <script src="js/header.js"></script>

    <hr style="color: #dedede">

    <p class="h1">Statistics:</p>
    <p class="h6" id="user">Current user: </p>
    <div class="container container-selection-graph rounded-3 pt-3 pb-3">
        <div id="chartEarnings" style="height: 370px; width: 100%;"></div>
        <br>
        <div id="chartTraffic" style="height: 370px; width: 100%;"></div>
        <br>
        <div id="chartEarningsDaily" style="height: 370px; width: 100%;"></div>
        <br>
        <div id="chartTrafficDaily" style="height: 370px; width: 100%;"></div>
    </div>

    <div class="pt-5" id="footer"></div>
    <script src="js/footer.js"></script>

</div>
</body>
</html>