<?php
    // microservice to check if a word exists
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/json");
    include_once(__DIR__ . '/functions.php');

    $output = array(
        "error" => false,
        "string" => "",
        "result" => "Word Not Found"
    );

    if (isset($_REQUEST['paragraph']) && isset($_REQUEST['word'])) {
        if (strlen($_REQUEST['paragraph']) == 0) {
            $output['error'] = true;
            $output['string'] = "Error : Please provide a search paragraph with the 'paragraph' key and try again";

        } else if (strlen($_REQUEST['word']) == 0) {
            $output['error'] = true;
            $output['string'] = "Error : Please provide a search word with the 'word' key and try again";

        } else if (str_word_count($_REQUEST['word'], 0) > 1) {
            // user has entered more than one keyword, error!
            $output['error'] = true;
            $output['string'] = "Error : Please provide only one keyword for searching";

        } else {
            $paragraph = urldecode($_REQUEST['paragraph']);
            $word = urldecode($_REQUEST['word']);
    
            $answer = checkWordExists($paragraph,$word);
    
            $output['string'] = "Searched Paragraph was : '" . $paragraph . "', and searched word was : '" . $word . "'";
            ($answer === true) ? $output['result'] = "Word Found" : $output['result'] = "Word Not Found";
        }
    } else {
        $output['error'] = true;
        $output['string'] = "Error : Please provide a search word and a search paragraph for this service";
    }

    echo json_encode($output);
    exit();
?>
