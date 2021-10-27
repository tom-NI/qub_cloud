<?php
    // this file has the automated functions coded AND developers can request error logs from this file
    include_once(__DIR__ . '/functions.php');
    ini_set('max_execution_time', '300');
    date_default_timezone_set('Europe/London');

    $developerError = ", the test has not been ran, please refer to the documentation and try again";
    $paragraph = "";
    $word = "";

    // therefore run an automated check at regular intervals
    // builds randomly generated paragraph, words, randomly adds words multiple times 
    // and randomly decides if the word is inside the paragraph or not

    // get a random paragraph length
    $paragraphLength = rand(4,15);

    // randomly get the extra words to add to a paragraph
    $paragraphNumberDuplicates = rand(0,4);
    
    // randomly decide if the word will be inside the paragraph or not
    $wordIsInsideParagraph = rand(0,1);

    // load dictionary file for words
    $dictionary = file("dictionary_small.txt");
    
    if ($wordIsInsideParagraph == 1 && $paragraphNumberDuplicates == 0) {
        // select a word position at random from inside the paragraph
        $wordPosition = rand(0, $paragraphLength - 1);
    } else {
        // select a word at random from the whole array
        $wordPosition = array_rand($dictionary, 1);
        $word = trim($dictionary[$wordPosition]);
    }
    
    // build the paragraph, selecting one word at random from dictionary
    for ($i = 0; $i < $paragraphLength; $i++) {
        $randomKey = array_rand($dictionary, 1);
        $paragraph .= trim($dictionary[$randomKey]) . " ";

        // if the word position somehow ends up the same as the randomkey, reset it        
        if (strlen($word) == 0) {
            if ($wordIsInsideParagraph == 1 && $wordPosition == $i) {
                $word = trim($dictionary[$randomKey]);
            }
        }
    }

    // add the same random word to the paragraph multiple times randomly
    if ($paragraphNumberDuplicates > 0) {
        $randomKey = array_rand($dictionary, 1);
        if ($wordIsInsideParagraph == 1) {
            $word = trim($dictionary[$randomKey]);
        }
        for ($i = 0; $i <= $paragraphNumberDuplicates; $i++) {
            $paragraph .= trim($dictionary[$randomKey]) . " ";
        }
    }

    // run tests on all three microservices and log results;
    runTestAndLogData("wordcount", $paragraph, $word, $wordIsInsideParagraph, $paragraphNumberDuplicates);
    runTestAndLogData("totalcount", $paragraph, $word, $wordIsInsideParagraph, $paragraphNumberDuplicates);
    runTestAndLogData("keyword_exists", $paragraph, $word, $wordIsInsideParagraph, $paragraphNumberDuplicates);
?>
