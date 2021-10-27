<?php
    include_once(__DIR__ . '/functions.php');
    date_default_timezone_set('Europe/London');

    // run the logging function - one for each service!
    $wordCountMetrics = calculateDashboardMetrics("wordcount");
    $totalCountMetrics = calculateDashboardMetrics("totalcount");
    $wordExistsMetrics = calculateDashboardMetrics("keyword_exists");
    
    // build all metrics into one master array to loop thru
    $allservices = array($wordCountMetrics, $totalCountMetrics, $wordExistsMetrics);
?>

<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
        <link rel="stylesheet" href="my_stylesheet.css">
        <script src="https://www.gstatic.com/charts/loader.js"></script>
        <script type='text/javascript' src='/charts/line_chart.js'></script>
        <script type='text/javascript' src='/charts/pie_chart.js'></script>
        <script type='text/javascript' src='/charts/bar_chart.js'></script>
        <title>40314543 Web Word Count Dashboard</title>
    </head>

    <body class="master_site_width">
        <div class="has-background-info-light p-4">
            <h1 class="has-text-weight-bold has-text-left is-size-3 px-3 mt-4">Web Word Count Application</h1>
            <h2 class="has-text-weight-normal has-text-left is-size-4 px-3 pb-3">Status Dashboard</h2>

            <?php
                $recordsCheckedDateTime = date('d/m/Y H:i:s');
                echo "<h2 id='report_time' class='is-size-6 px-3 has-text-left has-text-weight-normal'>Page last loaded at : {$recordsCheckedDateTime}.</h2>";
                echo "<h3 id='report_time' class='is-size-6 px-3 has-text-left has-text-weight-normal'>Refresh page to generate fresh reports. Hover over charts for data.</h3>";
                echo "<h3 id='report_time' class='is-size-6 px-3 has-text-left has-text-weight-normal'>Statistics automatically logged every 5 minutes. <a target='_blank' href='http://monmet.40314543.qpc.hal.davecutting.uk'>Click here</a> to manually run logging statistics</h3>";
                echo "</div>";

                foreach ($allservices as $service) {
                    $serviceName = $service['service_name'];
                    $logDate = $service['report_datetime'];
                    $totalRecordCount = $service['total_records'];
                    $oldestRecordDateTime = $service['oldest_record'];
                    $serviceDownFlag = $service['service_active'];
                    $serviceUpSince = $service['service_active_since'];

                    $allResponseTimes = $service['all_response_times'];
                    $allErrorsArray = $service['all_errors'];

                    $responseTimeAverage = $service['average_response_time'];
                    $pingTimeAverage = $service['average_ping_time'];
                    $mostRecentLogTime = $service['most_recent_log_time'];

                    $serviceDownCount = $service['service_down_count'];
                    $inaccurateCount = $service['service_inaccurate_count'];

                    // build service down data for chart
                    $serviceDownChartArray = array();
                    $serviceDownChartArray[] = array("State", "Percent");
                    $serviceUpCount = $totalRecordCount - $serviceDownCount;
                    $serviceDownChartArray[] = array("Service Up Percent", $serviceUpCount);
                    $serviceDownChartArray[] = array("Service Down Percent", $serviceDownCount);

                    // service inaccurate date for chart
                    $serviceInaccurateChartArray = array();
                    $serviceInaccurateChartArray[] = array("State", "Percent");
                    $serviceAccurateCount = $totalRecordCount - $inaccurateCount;
                    $serviceInaccurateChartArray[] = array("Service Accurate Percent", $serviceAccurateCount);
                    $serviceInaccurateChartArray[] = array("Service Inaccurate Percent", $inaccurateCount);

                    // build times array for pie charts
                    $timeFastCount = array("Fast Time < 150ms", $service['time_fast_count']);
                    $timeAverageCount = array("Average Time 150-500ms" , $service['time_average_count']);
                    $timeSlowCount = array("Slow Time > 500ms" , $service['time_slow_count']);
                    $timeMissingCount = array("Missing Time" , $service['time_missing_count']);
                    $allTimesArrayForChart = array();
                    $allTimesArrayForChart[] = array("Speed Rating", "Percent");
                    $allTimesArrayForChart[] = $timeFastCount;
                    $allTimesArrayForChart[] = $timeAverageCount;
                    $allTimesArrayForChart[] = $timeSlowCount;
                    $allTimesArrayForChart[] = $timeMissingCount;

                    if ($serviceDownFlag == true) {
                        $active = "No";
                        $serviceUpSince = "Service Down";
                    } else {
                        $active = "Yes";
                    }

                    echo "
                        <div id='full_service_div' class='box px-4 mt-6 mb-4'>
                            <div class='columns has-background-info-light px-4 m-2'>
                                <h2 id='service_name' class='column is-narrow has-text-weight-bold has-text-left is-size-4 px-4'>'{$serviceName}' microservice.</h2>
                                <h2 id='service_name' class='column is-narrow has-text-weight-normal has-text-left is-size-5 px-4'>Total Records Checked : {$totalRecordCount}</h2>
                            </div>
                            <div class='has-background-info-light px-4 p-2 m-2'>
                                <h3 class='is-size-6 px-3 p-1 has-text-left has-text-weight-normal'><a href='http://monmet.40314543.qpc.hal.davecutting.uk/error_log.php?service={$serviceName}'>Click Here</a> for Raw Logs</h3>
                            </div>
                            <div id='metrics_div'>
                                <div id='datacells' class='columns has-text-centered px-4 mt-3'>
                                    <div class='column box m-1'>
                                        <h3 class='has-text-weight-semibold p-2 is-size-5' >Service Active</h3>
                                        <p id='service_active_value is-size-4 m-3'>{$active}</p>
                                    </div>
                                    <div class='column box m-1'>
                                        <h3 class='has-text-weight-semibold p-2 is-size-5' >Active Since:</h3>
                                        <p id='active_since_value m-2'>{$serviceUpSince}</p>
                                    </div>
                                    <div class='column box m-1'>
                                        <h3 class='has-text-weight-semibold p-2 is-size-6' >Average response Time:</h3>
                                        <p id='total_records_value m-2'>{$responseTimeAverage} milliseconds</p>
                                    </div>
                                    <div class='column box m-1'>
                                        <h3 class='has-text-weight-semibold p-2 is-size-6' >Average Ping Time:</h3>
                                        <p id='total_records_value m-2'>{$pingTimeAverage} milliseconds</p>
                                    </div>
                                    <div class='column box m-1'>
                                        <h3 class='has-text-weight-semibold p-2 is-size-5' >Last Record:</h3>
                                        <p id='total_records_value m-2'>{$mostRecentLogTime}</p>
                                    </div>
                                    <div class='column box m-1'>
                                        <h3 class='has-text-weight-semibold p-2 is-size-5' >Oldest Record:</h3>
                                        <p id='total_records_value m-2'>{$oldestRecordDateTime}</p>
                                    </div>
                                </div>
                                <div class='columns'>
                                    <div class='column mt-5 mx-2'>
                                        <div id='response_times_graph_{$serviceName}' class='all_charts'></div>
                                    </div>
                                    <div class='column mt-5 mx-2'>
                                        <div id='errors_graph_{$serviceName}' class='all_charts'></div>
                                    </div>
                                </div>
                                <div>
                                    <div class='columns'>
                                        <div class='column m-5 all_charts' id='status_pie_chart_{$serviceName}'></div>
                                        <div class='column m-5 all_charts' id='accuracy_pie_chart_{$serviceName}'></div>
                                        <div class='column m-5 all_charts' id='speed_pie_chart_{$serviceName}'></div>
                                    </div>
                                </div>
                            </div>
                        </div>";

                        $responseTimesDiv = "response_times_graph_{$serviceName}";
                        $errorsGraphDiv = "errors_graph_{$serviceName}";
                        $statusPieChart = "status_pie_chart_{$serviceName}";
                        $accuracyPieChart = "accuracy_pie_chart_{$serviceName}";
                        $speedPieChart = "speed_pie_chart_{$serviceName}";

                        // populate all graph divs with graphs for each service
                        echo "
                            <script>drawLineChart(";
                                print_r(json_encode($allResponseTimes)); 
                                echo ",'{$responseTimesDiv}',";
                            echo "'Past Service Response Times');</script>

                            <script>drawBarChart(";
                                print_r(json_encode($allErrorsArray));
                                echo ",'{$errorsGraphDiv}',";
                            echo "'Past Errors', '1 = Error');</script>

                            <script>drawMetricPieChart(";
                                print_r(json_encode($serviceDownChartArray));  
                                echo ",'{$statusPieChart}',";
                            echo "'Historic Status');</script>

                            <script>drawMetricPieChart("; 
                                print_r(json_encode($serviceInaccurateChartArray));
                                echo ",'{$accuracyPieChart}',";
                            echo "'Historic Accuracy');</script>

                            <script>drawMetricPieChart("; 
                                print_r(json_encode($allTimesArrayForChart));
                                echo ",'{$speedPieChart}',";
                            echo "'Historic Load Speed Rating');</script>";
                    }
                ?>
    </body>
</html>