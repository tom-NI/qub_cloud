<?php
    date_default_timezone_set('Europe/London');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require(__DIR__ . '/phpmailer_source/PHPMailer.php');
    require(__DIR__ . '/phpmailer_source/Exception.php');
    require(__DIR__ . '/phpmailer_source/SMTP.php');

    // function to decide speed of the service based on milliseconds response time
    function determineSpeedRating($timeInMilliseconds) {
        $timeInMilliseconds = (int)$timeInMilliseconds;
        if ($timeInMilliseconds == 0) {
            return "Error";
        } else if ($timeInMilliseconds < 150) {
            return "Fast";
        } else if ($timeInMilliseconds >= 150 && $timeInMilliseconds <= 500) {
            return "Average";
        } else {
            return "Slow";
        }
    }

    // function to determine the csv file to load based on keyword
    function getTheCorrectCSVFile($serviceKeyword){
        $file = "";
        
        // read in the appropriate CSV file
        switch ($serviceKeyword) {
            case "wordcount":
                $file = "wordcount_records.csv";   
                break;
            case "totalcount": 
                $file = "totalwords_records.csv";
                break;
            case "keyword_exists": 
                $file = "wordexists_records.csv";
                break;
            default :
                $file = null;
                break;
        }
        return $file;
    }

    // function to run tests and record log data to external file.
    // written as a function to enable different services to be called 
    function runTestAndLogData($serviceKeyword, $nonURLparagraph, $word, $wordIsInsideParagraph, $paragraphNumberDuplicates) {
        // log test date and time
        $logDate = date('d/m/Y H:i:s');

        // encode the raw paragraph to use in a URL
        $urlEncodedParagraph = urlencode($nonURLparagraph);

        // build checking url from provided info
        $proxy_base_uri = "http://proxy.40314543.qpc.hal.davecutting.uk/?";
        $final_url = $proxy_base_uri . "check=" . $serviceKeyword . "&sentence=" . $urlEncodedParagraph . "&word=" . $word;
        
        // log time and run test live
        $functionStartTime = hrtime(true);

        // get data and ping speed
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $final_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $returnedData = curl_exec($ch);
        $connectionSpeedSeconds = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
        curl_close($ch);
        
        // log end time and calculate time difference
        $functionEndTime = hrtime(true);
        
        $pingSpeedMilliseconds = (int) ($connectionSpeedSeconds * 1000);
        $timeDifferenceInMilliSeconds = ($functionEndTime - $functionStartTime) / 1000000;
        
        // increment duplicates by one to reflect the one original word added
        $paragraphNumberDuplicates++;

        // strip new line characters from the paragraph for the csv file        
        $cleanParagraphForCSV = trim(preg_replace('/\s+/', ' ', $nonURLparagraph));
        
        // desired order for logging data to CSV file;
        // time, paragraph, word, ping_speed, service_working, accurate, response_in_ms, time_acceptable
        $newCSVLine = array($logDate, $cleanParagraphForCSV, $word, $pingSpeedMilliseconds);

        // load the file to log data into
        $fileName = getTheCorrectCSVFile($serviceKeyword);
        if ($fileName == null) {
            echo "There was an issue with the provided service key, please refer to documentation and try again";
            exit();
        }
        
        $decodedData = json_decode($returnedData, true);
        if ($decodedData != null) {
            // service is live
            // now add data to array in a strict order for placing into CSV log file - cannot use assoc array for fputcsv()
            if ($serviceKeyword == 'totalcount') {
                // total word count was done
                $count = str_word_count($nonURLparagraph, 0);
                if ($count == 1) {
                    $suffix = " word in paragraph";
                } else {
                    $suffix = " words in paragraph";
                }

                $reply = $count . $suffix;

                if ($decodedData['result'] != $reply) {
                    // working
                    // not accurate
                    $newCSVLine[] = "yes";
                    $newCSVLine[] = "no";
                } else {
                    // working
                    // is accurate
                    $newCSVLine[] = "yes";
                    $newCSVLine[] = "yes";
                }
            } else {
                if ($wordIsInsideParagraph == 1) { // word is inside paragraph
                    if ($serviceKeyword == 'wordcount') {

                        if ($paragraphNumberDuplicates == 1) {
                            $suffix = " word found";
                        } else {
                            $suffix = " words found";
                        }

                        $reply = $paragraphNumberDuplicates . $suffix;

                        if ($decodedData['result'] == "0 words found") {
                            // working
                            // not accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "no";
                        } else if ($decodedData['result'] == $reply) {
                            // working
                            // is accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "yes";
                        }
                    } else if ($serviceKeyword == 'keyword_exists') {
                        if ($decodedData['result'] == "Word Not Found") {
                            // working
                            // not accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "no";
                        } else {
                            // working
                            // is accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "yes";
                        }
                    }
                } else { // word isnt inside the paragraph
                    if ($serviceKeyword == 'wordcount') {
                        if ($decodedData['result'] == "0 words found") {
                            // working
                            // is accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "yes";
                        } else {
                            // working
                            // not accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "no";
                        }
                        // word exists
                    } else if ($serviceKeyword == 'keyword_exists') {
                        if ($decodedData['result'] == "Word Not Found") {
                            // working
                            // is accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "yes";
                        } else {
                            // working
                            // not accurate
                            $newCSVLine[] = "yes";
                            $newCSVLine[] = "no";
                        }
                    }
                }
            }
            // add response_in_ms
            $newCSVLine[] = $timeDifferenceInMilliSeconds;
        } else {
            // service is not live
            // three keys are service_working, accurate, response_in_ms,
            $newCSVLine[] = "no";
            $newCSVLine[] = "no";
            $newCSVLine[] = 0;
        }

        // time acceptable?
        $newCSVLine[] = determineSpeedRating($timeDifferenceInMilliSeconds);
        
        // save to CSV file
        $newCSVLine = array_values($newCSVLine);
        $fileToWrite = fopen($fileName, "a+");
        fputcsv($fileToWrite, $newCSVLine, ",");
        fclose($fileToWrite);

        // send error email if the service is down or inaccurate
        if ($newCSVLine[4] == "no") {
            // service is down
            include_once(__DIR__ . "/email_templates/down_email.php");
            sendErrorEmail($emailBody);
        } else if ($newCSVLine[5] == "no") {
            // service is inaccurate
            include_once(__DIR__ . "/email_templates/inaccurate_email.php");
            sendErrorEmail($emailBody);
        }
        checkCSVFileIsntOversize($fileName);
    }

    // trim a CSV file to ensure it doesnt get bloated
    // for the purposes of QUB assignment dont keep mountains of junk logs!
    function checkCSVFileIsntOversize($csvFileName) {
        $maxRecordsToKeep = 100;

        // only proceed to check line deletions if the file has data!
        if (filesize(__DIR__ . "/{$csvFileName}") > 0) {
            $numberOfRecords = 1;
            $fileHandler = fopen($csvFileName, "r");
        
            while(!feof($fileHandler)) {
                $singleLine = fgets($fileHandler, 4096);
                $numberOfRecords += substr_count($singleLine, PHP_EOL);
            }
            fclose($fileHandler);
            
            // now delete older records if there are more records than the max
            if ($numberOfRecords > $maxRecordsToKeep) {
                $rowsToDelete = $numberOfRecords - $maxRecordsToKeep;
    
                // remove the oldest entries?
                $input = explode("\n", file_get_contents($csvFileName));
                $newlyTrimmedArray = array_slice($input, ($rowsToDelete -1));
                file_put_contents($csvFileName, implode("\n", $newlyTrimmedArray));
            }
        }
    }

    // function to calculate and export service metrics as JSON
    function calculateDashboardMetrics($serviceToCheck) {
        $csvFile = getTheCorrectCSVFile($serviceToCheck);
        if ($csvFile == null) {
            echo "<h2>Error with the provided service key, please check documentation and try again</h2>";
            exit();
        }
        $logDate = date('d/m/Y H:i:s');

        $totalRecordCount = 0;
        $serviceDownCount = 0;
        $inaccurateCount = 0;
        $timeFastCount = 0;
        $timeAverageCount = 0;
        $timeSlowCount = 0;
        $timeMissingCount = 0;
        $totalResponseTime = 0;
        $totalPingTime = 0;

        // obtain and store the filepath
        $filepath = fopen($csvFile, "r");
        
        $allResponseTimes = array();
        $firstResponseTimeRow = array("Date and Time", "Time in milliseconds");
        $allResponseTimes[] = $firstResponseTimeRow;
        
        $allErrorsArray = array();
        $firstErrorArrayHeader = array("Date and Time", "Errors");
        $allErrorsArray[] = $firstErrorArrayHeader;

        $serviceUpSince = "";
        $firstRecord = true;
        $oldestRecordDateTime = "";

        // loop thru full file, print out first item in each row!
        while (($currentLine = fgetcsv($filepath)) !== FALSE) {
            $totalRecordCount++;

            $time = trim($currentLine[0]);
            $pingTime = trim($currentLine[3]);
            $service_working = trim($currentLine[4]);
            $accurate = trim($currentLine[5]);
            $response_in_ms = trim($currentLine[6]);
            $time_acceptable = trim($currentLine[7]);
            
            $thisRecordResponseTime = array($time, (int) $response_in_ms);
            $allResponseTimes[] = $thisRecordResponseTime;

            if ($firstRecord == true) {
                $oldestRecordDateTime = $time;
                $firstRecord = false;
            }
            $lastLoggedDate = $time;

            $serviceDownFlag = false;
            if ($service_working == "no") {
                $serviceDownCount++;
                $serviceDownFlag = true;
            } else {
                $serviceDownFlag = false;
            }

            if ($accurate == "no") {
                $inaccurateCount++;
                $errorCount = 1;
            } else {
                $errorCount = 0;
            }
            $thisRecordError = array($time, $errorCount);
            $allErrorsArray[] = $thisRecordError;

            // record the last time the service became active
            if ($serviceDownFlag == true && $service_working == "yes") {
                $serviceUpSince = $time;
            }

            $totalResponseTime += (int) $response_in_ms;
            $totalPingTime += $pingTime;
            
            if ($time_acceptable == "Fast") {
                $timeFastCount++;
            } else if ($time_acceptable == "Average") {
                $timeAverageCount++;
            } else if ($time_acceptable == "Slow") {
                $timeSlowCount++;
            } else {
                $timeMissingCount++;
            }
        }

        if (strlen($serviceUpSince) == 0) {
            $serviceUpSince = $oldestRecordDateTime;
        }

        if ($totalRecordCount > 0) {
            $responseTimeAverage = (int) ((double) $totalResponseTime / (double) $totalRecordCount);
            $pingTimeAverage = (int) ((double) $totalPingTime / (double) $totalRecordCount);
        } else {
            $responseTimeAverage = 0;
            $pingTimeAverage = 0;
        }
        
        // export data JSON for dashboard
        $serviceAnalysis = array(
            "service_name" => $serviceToCheck,
            "report_datetime" => $logDate,
            "most_recent_log_time" => $lastLoggedDate,
            "total_records" => $totalRecordCount,
            "oldest_record" => $oldestRecordDateTime,
            "service_active" => $serviceDownFlag,
            "service_active_since" => $serviceUpSince,
            "all_response_times" => $allResponseTimes,
            "all_errors" => $allErrorsArray,
            "average_response_time" => $responseTimeAverage,
            "average_ping_time" => $pingTimeAverage,
            "service_down_count" => $serviceDownCount,
            "service_inaccurate_count" => $inaccurateCount,
            "time_fast_count" => $timeFastCount,
            "time_average_count" => $timeAverageCount,
            "time_slow_count" => $timeSlowCount,
            "time_missing_count" => $timeMissingCount,
        );
        return $serviceAnalysis;
    }

    // send an email using PHPMailer
    function sendErrorEmail($emailBody) {
        $mail = new PHPMailer(TRUE);

        try {
            $mail->setFrom('tkilpatrick01@qub.ac.uk', "Web Word Count Monitoring");
            $mail->addAddress("tkilpatrick01@qub.ac.uk", "Web Word Count Admin");
            $mail->Subject = "ALERT - Web Word Count : Monitoring Error";
            $mail->Body = $emailBody;
            $mail->isHTML(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com';
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = 'STARTTLS';
            $mail->Username = '40314543@ads.qub.ac.uk';
            $mail->Password = 'LearnMore*-2020*';
            $mail->Port = 587;

            $mail->send();
        } catch (Exception $e) {
        } catch (\Exception $e) {
        }
        if ($mail) {
            return true;
        } else {
            return false;
        }
    }
?>