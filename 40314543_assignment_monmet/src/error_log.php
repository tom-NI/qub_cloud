<?php
    include_once(__DIR__ . '/functions.php');
    ini_set('max_execution_time', '300');
    date_default_timezone_set('Europe/London');
    $error = false;

    if (isset($_REQUEST['service'])) {
        // print logs for an admin
        if (strlen($_REQUEST['service']) == 0) {
            echo "<p>Unknown Request, please refer documentation and try again</p>";
            exit();
        } else {
            $serviceToCheck = $_REQUEST['service'];
        }
        
        $csvFile = getTheCorrectCSVFile($serviceToCheck);
        $file = new SplFileObject($csvFile, 'r');
        $file->seek(PHP_INT_MAX);
        $maxLinesInFile = $file->key();
        
        (isset($_REQUEST['num']) && strlen($_REQUEST['num']) > 0 && $_REQUEST['num'] > 0) 
            ? $numRecordsToPrint = (int) $_REQUEST['num'] 
            : $numRecordsToPrint = $maxLinesInFile;

        // ensure requested records not > total available!
        if ($numRecordsToPrint > $maxLinesInFile) {
            $numRecordsToPrint = $maxLinesInFile;
        }

        $logDate = date('d/m/Y H:i:s');
        $openedfile = fopen($csvFile, "a+");
        $loopCounter = 0;
    } else {
        $error = true;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="my_stylesheet.css">
    <title>40314543 WWC Raw Log Print</title>
</head>
<body class="master_site_width">
    <?php
        if ($error) {
            echo "<p>Unknown Request, please refer documentation and try again</p>";
            exit();
        } else {
            echo "
            <div class='has-background-info-light p-4'>
                <h1 class='has-text-weight-bold has-text-left is-size-3 px-3 mt-4'>Web Word Count Application : '{$serviceToCheck}' microservice</h1>
                <h2 class='has-text-weight-normal has-text-left is-size-4 px-3 pb-3'>Raw (Source) Logs</h2>
                <h2 class='is-size-6 px-3 has-text-left has-text-weight-normal'>Report Ran at : {$logDate}</h2>
                <h3 class='is-size-6 px-3 has-text-left has-text-weight-normal'><a href='http://monmet.40314543.qpc.hal.davecutting.uk/dashboard.php'>Click Here</a> for Application Dashboard</h3>
            </div>
            <div class='has-background-info-light mt-4 p-4'>
                <h2 class='is-size-5 px-3 has-text-left has-text-weight-bold'>{$maxLinesInFile} logs available. Showing last {$numRecordsToPrint} records.</h2>
            </div>
            <table class='table is-striped mt-4'>
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Date and time</th>
                        <th>Paragraph</th>
                        <th>Word</th>
                        <th>Ping speed (ms)</th>
                        <th>Service Working</th>
                        <th>Service Accurate</th>
                        <th>Response (ms)</th>
                        <th>Time Rating</th>
                    </tr>
                </thead>
                <tbody>";

            // loop thru each line of CSV (front the end backwards) and print record to html
            while ($loopCounter < $numRecordsToPrint) {
                $string = fgets($openedfile, 4096);
                $row = str_getcsv($string);
                $currentLogCount = $loopCounter + 1;

                // CSV structure
                $time = trim($row[0]);
                $paragraph = trim($row[1]);
                $word = trim($row[2]);
                $ping_speed = trim($row[3]);
                $service_working = trim($row[4]);
                $accurate = trim($row[5]);
                $response_in_ms = trim($row[6]);
                $time_acceptable = trim($row[7]);

                echo "
                    <tr>
                        <td>{$currentLogCount}</td>
                        <td>{$time}</td>
                        <td>{$paragraph}</td>
                        <td>{$word}</td>
                        <td>{$ping_speed}</td>
                        <td>{$service_working}</td>
                        <td>{$accurate}</td>
                        <td>{$response_in_ms}</td>
                        <td>{$time_acceptable}</td>
                    </tr>
                ";
                $loopCounter++;
            }
            // close the CSV file
            fclose($openedfile);
                    
            echo "
                </tbody>
            </table>
            ";
        }
    ?>
    
</body>
</html>