<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-type: application/json");
    include_once(__DIR__ . '/proxyfunctions.php');

    // load JSON configuration file to memory (all queries are checked vs. config)
    // Unit tests add 'testing' to the HTTP GET query to use a seperate config file for testing
    isset($_GET['testing']) ? $testingCode = true : $testingCode = false;
    isset($_GET['testing']) ? $configFileName = 'test_config.json' : $configFileName = 'proxy_config.json';
                        
    $currentConfigArray = getAndReturnCurrentConfigJSON($testingCode);

    // build assoc array for publishing any applicable errors
    // to be converted to JSON output when used
    $errorOutput = array(
        "error" => false,
        "string" => ""
    );

    // common error messages used throughout
    $documentationError = ", please refer to documentation and try again";
    $userErrorSuffix = ", please try again";
    $errorToUser = "Request not recognised";

    // check keys to determine action taken
    if (isset($_GET['administrate'])) {
        // all administration functions follow
        $adminKey = $_GET['administrate'];
        if ($adminKey == 'printconfig') {
            header('Content-Type: application/json');
            echo file_get_contents($configFileName);
            exit();
        } else if ($adminKey == 'addservice') {
            if (!isset($_GET['service']) || !isset($_GET['url']) || !isset($_GET['keys'])) {
                // one or all keys are missing
                $errorOutput['error'] = true;
                $errorOutput['string'] = "Please ensure all required keys are added" . $documentationError;
                echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                exit();
            } else if (strlen($_GET['service']) == 0 || strlen($_GET['url']) == 0 || strlen($_GET['keys']) == 0) {
                $errorOutput['error'] = true;
                $errorOutput['string'] = "Please ensure all keys have values" . $documentationError;
                echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                exit();
            } else {
                $serviceName = $_GET['service'];
                $baseURL = $_GET['url'];
                $keys = $_GET['keys'];

                // check data suitability
                if (!checkURLSuitable($baseURL)) {
                    $errorOutput['error'] = true;
                    $errorOutput['string'] = "Invalid URL" . $documentationError;
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    exit();
                } else {
                    // sanitise the URL for dodgy characters etc
                    $filteredURL = filter_var($baseURL, FILTER_SANITIZE_URL);

                    // break down each key by the + sign
                    $keyarray = preg_split("/[\s]/", $keys, 0, PREG_SPLIT_NO_EMPTY);

                    // clean each key of dodgy characters
                    foreach($keyarray as $key) {
                        $cleanedKeyArray[] = removeNonAlphabetCharacters($key);
                    }
                    $cleanedKeyArray = array_values($cleanedKeyArray);

                    // cleanup the service name
                    $cleanedServiceName = removeNonAlphabetCharacters($serviceName);
                                        
                    $newFunctionEntry = array(
                        $cleanedServiceName => array(
                            "url" => $filteredURL,
                            "required_keys" => $cleanedKeyArray
                        )
                    );

                    $currentConfigArray['services'][$cleanedServiceName] = $newFunctionEntry[$cleanedServiceName];

                    file_put_contents($configFileName, json_encode($currentConfigArray, JSON_PRETTY_PRINT));
                    
                    // check if the key was added and echo a response to the admin either way
                    $newConfigArray = getAndReturnCurrentConfigJSON($testingCode);
                    if (array_key_exists($cleanedServiceName, $newConfigArray['services'])) {
                        $errorOutput['error'] = false;
                        $errorOutput['string'] = "Addition successful";
                        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    } else {
                        $errorOutput['error'] = true;
                        $errorOutput['string'] = "That didnt work" . $documentationError;
                        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    }
                }
            }
        } else if ($adminKey == 'addkey') {
            if (!isset($_GET['service']) || !isset($_GET['newkey'])) {
                $errorOutput['error'] = true;
                $errorOutput['string'] = "Keys are missing from the command provided" . $documentationError;
                echo json_encode($errorOutput, JSON_PRETTY_PRINT);
            } else if (strlen($_GET['newkey']) == 0) {
                $errorOutput['error'] = true;
                $errorOutput['string'] = "New key not added, insert a key name" . $documentationError;
                echo json_encode($errorOutput, JSON_PRETTY_PRINT);
            } else {
                $key = $_GET['newkey'];
                $cleanedKey = removeNonAlphabetCharacters($key);
                $serviceName = trim($_GET['service']);

                // clean each key of dodgy characters
                if (array_key_exists($serviceName, $currentConfigArray['services'])) {
                    $existingkeys = $currentConfigArray['services'][$serviceName]['required_keys'];
                    $existingkeys[] = $cleanedKey;

                    $currentConfigArray['services'][$serviceName]['required_keys'] = $existingkeys;
                    
                    file_put_contents($configFileName, json_encode($currentConfigArray, JSON_PRETTY_PRINT));
                    
                    $errorOutput['error'] = false;
                    $errorOutput['string'] = "Requested key added";
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                } else {
                    $errorOutput['error'] = true;
                    $errorOutput['string'] = "Requested service doesnt exist" . $documentationError;
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                }
            }
        } else if ($adminKey == 'update') {
            if (!isset($_GET['service']) || (!isset($_GET['url']) && !isset($_GET['newname']))) {
                $errorOutput['error'] = true;
                $errorOutput['string'] = "Please ensure all relevant keys and values have been provided" . $documentationError;
                echo json_encode($errorOutput, JSON_PRETTY_PRINT);
            } else {
                $serviceName = $_GET['service'];

                if (array_key_exists($serviceName, $currentConfigArray['services'])) {
                    // check for changing the service URL
                    if (isset($_GET['url']) && strlen($_GET['url']) > 0) {
                        $newURL = $_GET['url'];

                        // check the url is suitable
                        if (checkURLSuitable($newURL)) {
                            // change the existing URL to the new one
                            $filteredURL = filter_var($newURL, FILTER_SANITIZE_URL);
                            $currentConfigArray['services'][$serviceName]['url'] = $filteredURL;
                            file_put_contents($configFileName, json_encode($currentConfigArray, JSON_PRETTY_PRINT));
                            
                            $errorOutput['error'] = false;
                            $errorOutput['string'] = "Requested URL Updated";
                            echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                            exit();
                        } else {
                            // URL unsuitable, send error message to user 
                            $errorOutput['error'] = true;
                            $errorOutput['string'] = "The new URL was unsuitable" . $documentationError;
                            echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                            exit();
                        }                   
                    }

                    // check for changing the name of the service
                    if (isset($_GET['newname']) && strlen($_GET['newname']) > 0) {
                        $newName = removeNonAlphabetCharacters($_GET['newname']);
                        $currentConfigArray['services'][$newName] = $currentConfigArray['services'][$serviceName];
                        unset($currentConfigArray['services'][$serviceName]);
                        file_put_contents($configFileName, json_encode($currentConfigArray, JSON_PRETTY_PRINT));
                        
                        // send message to user
                        $errorOutput['error'] = false;
                        $errorOutput['string'] = $serviceName . " has had its name updated to " . $newName;
                        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    } else {
                        $errorOutput['error'] = true;
                        $errorOutput['string'] = "The new name was unsuitable" . $documentationError;
                        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    }
                } else {
                    // send error message to user
                    $errorOutput['error'] = true;
                    $errorOutput['string'] = "Requested service doesnt exist" . $documentationError;
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                }
            }
        } else if ($adminKey == 'delete') {
            if (!isset($_GET['service'])) {
                $errorOutput['error'] = true;
                $errorOutput['string'] = "Please provide the service to be deleted" . $documentationError;
                echo json_encode($errorOutput, JSON_PRETTY_PRINT);
            } else {
                $serviceName = $_GET['service'];

                if (array_key_exists($serviceName, $currentConfigArray['services'])) {
                    unset($currentConfigArray['services'][$serviceName]);
                    file_put_contents($configFileName, json_encode($currentConfigArray, JSON_PRETTY_PRINT));

                    // double check if the key was deleted and echo a response
                    $newConfigArray = getAndReturnCurrentConfigJSON($testingCode);
                    if (!array_key_exists($serviceName, $newConfigArray['services'])) {
                        $errorOutput['error'] = false;
                        $errorOutput['string'] = "Service deleted.";
                        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    } else {
                        $errorOutput['error'] = true;
                        $errorOutput['string'] = "Service not deleted" . $documentationError;
                        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    }
                } else {
                    $errorOutput['error'] = true;
                    $errorOutput['string'] = "That key doesnt exist" .$documentationError;
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    exit();
                }
            }
        } else {
            $errorOutput['error'] = true;
            $errorOutput['string'] = "Request not recognised" . $documentationError;
            echo json_encode($errorOutput, JSON_PRETTY_PRINT);
        }
    } else if (isset($_GET['check'])) {
        // a service has been requested from the outside world (non admin)    
        $requestedService = $_GET['check'];

        // check if the service exists in config, then send on request if so
        if (array_key_exists($requestedService, $currentConfigArray['services'])) {
            // request exists inside the config file
            $base_url = $currentConfigArray['services'][$requestedService]['url'];
            $final_url = $base_url . "?";
            $builtUpURL = "";
            
            // check all required keys from config file have been provided
            foreach ($currentConfigArray['services'][$requestedService]['required_keys'] as $key) {
                if (!isset($_GET[$key])) {
                    $errorOutput['error'] = true;
                    $errorOutput['string'] = "Error, Unknown Request" . $userErrorSuffix;
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    exit();
                } else if (strlen($_GET[$key]) == 0) {
                    $errorOutput['error'] = true;
                    $errorOutput['string'] = "Error, Missing Value" . $userErrorSuffix;
                    echo json_encode($errorOutput, JSON_PRETTY_PRINT);
                    exit();
                } else {
                    // switch over from 'sentence' to paragraph to obscure the request somewhat from the front end
                    if ($key == "sentence") {
                        $key = "paragraph";
                        $builtUpURL .= urlencode($key) . "=" . urlencode($_GET['sentence']) . "&";
                    } else {
                        $builtUpURL .= urlencode($key) . "=" . urlencode($_GET[$key]) . "&";
                    }
                }
            }
            $final_url .= $builtUpURL;

            // make the request to the server and store the result;
            $returnedData = getProxyDataFromURL($final_url);
            echo $returnedData;
        } else {
            $errorOutput['error'] = true;
            $errorOutput['string'] = $errorToUser;
            echo json_encode($errorOutput, JSON_PRETTY_PRINT);
        }
    } else {
        // error as neither the 'admininistrate' key nor the 'check' key have been used
        $errorOutput['error'] = true;
        $errorOutput['string'] = $errorToUser;
        echo json_encode($errorOutput, JSON_PRETTY_PRINT);
    }
?>