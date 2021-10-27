<?php
    // microservice to count the total words inside a paragraph
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/json");

    // build a starting array
    $output = array(
        "error" => false,
        "display" => "",
        "result" => "0 words in paragraph"
    );

    if (!isset($_REQUEST['paragraph']) || strlen($_REQUEST['paragraph']) == 0) {
        $output['error'] = true;
        $output['display'] = "Please enter a paragraph to check";
    } else {
        $paragraphToCheck = urldecode($_REQUEST['paragraph']);
        // check state of paragraph, and determine data to return
        $numwords = str_word_count($paragraphToCheck, 0);
        if ($numwords == 1) {
            $suffix = " word in paragraph";
        } else {
            $suffix = " words in paragraph";
        }
        $output['result'] = $numwords . $suffix;
    }

    echo json_encode($output);
    exit();
?>