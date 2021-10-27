<?php    
    /* gets the data from a URL */
    function getProxyDataFromURL($url) {
        $ch = curl_init();
        $timeout = 5;

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    // function to remove any undesirable characters from inputs
    function removeNonAlphabetCharacters($text) {
        $alphabetRegex = '/[^A-Za-z]/';
        return preg_replace($alphabetRegex, "", $text);
    }

    // check a URL is suitable 
    function checkURLSuitable($urlToCheck) {
        // sanitise the URL for dodgy characters etc
        $filteredURL = filter_var($urlToCheck, FILTER_SANITIZE_URL);

        // check URL is valid and the URL uses HTTP protocol
        if (filter_var($filteredURL, FILTER_VALIDATE_URL) == true && preg_match('/^http:\/\//', $filteredURL) == 1) {
            return true;
        } else {
            return false;
        }
    }

    // return the correct JSON config file based on request
    // testing the HTTP services uses a seperate config 
    function getAndReturnCurrentConfigJSON($isTesting) {
        $isTesting ? $currentConfig = file_get_contents("test_config.json") : $currentConfig = file_get_contents("proxy_config.json");
        return json_decode($currentConfig, true);
    }
?>