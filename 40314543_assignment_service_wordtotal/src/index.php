<?php
    // microservice to check total number of times a word exists inside a paragraph
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/json");

    $output = array(
        "error" => false,
        "display" => "",
        "result" => "0 words found"
    );

    // make sure paragraph and word are valid before processing
    if (!isset($_REQUEST['paragraph']) || strlen($_REQUEST['paragraph']) == 0) {
        $output['error']= true;
        $output['display']= "Error - Please enter a paragraph to check";
    } else if (!isset($_REQUEST['word']) || strlen($_REQUEST['word']) == 0) {
        $output['error']= true;
        $output['display']= "Error - Please enter one word to check";
    } else if (str_word_count($_REQUEST['word'], 0) > 1) {
        $output['error']= true;
        $output['display']= "Error - Please enter only one word to check";
    } else {
        $paragraph = urldecode($_REQUEST['paragraph']);
        $word = urldecode($_REQUEST['word']);

        $count = 0;
        $paragraphArray = explode(" ", $paragraph);
        foreach($paragraphArray as $paragraphWord) {
            if (strtolower($paragraphWord) == strtolower($word)) {
                $count++;
            }
        }

        if ($count == 1) {
            $suffix = " word found";
        } else {
            $suffix = " words found";
        }

        $output['display'] = "Searched Paragraph was : '" . $paragraph . "', and searched word was : '" . $word . "'";
        $output['result'] = $count . $suffix;
    }

    echo json_encode($output);
    exit();
?>