<?php
    declare(strict_types=1);
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    include_once(__DIR__ . '/../src/proxyfunctions.php');

    // *** READ THIS ***
    // ensure that the word 'testing' is added as a key to each test
    // adding this key ensures test config uses a seperate test_config file
    class HTTPTests extends TestCase {
        // setup the test environment before each test
        protected function setUp(): void {
            
            $this->client = new Client(['base_uri' => (string) "http://proxy.40314543.qpc.hal.davecutting.uk/", 'timeout' => 2.0]);
        }

        // reset the test output after each test
        protected function tearDown(): void {
            $this->client->get('?testing&administrate=addservice&service=wordcount&url=http://wordtotal.40314543.qpc.hal.davecutting.uk/&keys=sentence+word', ['verify' => false]);
            $this->client->get('?testing&administrate=addservice&service=totalcount&url=http://totalwords.40314543.qpc.hal.davecutting.uk/&keys=sentence', ['verify' => false]);
        }

        /**
         * @test
         */
        public function checkAdminPrintFullConfig() {
            $comparisonConfig = array(
                "services" => array(
                    "wordcount" => array(
                        "url" => "http://wordtotal.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence", "word"]
                    ),
                    "totalcount" => array(
                        "url" => "http://totalwords.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence"]
                    )
                )
            );

            $response = $this->client->get('?testing&administrate=printconfig', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonFile(__DIR__ . '/../src/test_config.json', json_encode($comparisonConfig));
        }

        /**
         * @test
         */
        public function checkAdminAddingANewService() {
            // run the test AND grab the result in two seperate stages
            $return = $this->client->get('?testing&administrate=addservice&service=whatever&url=http://totalwords.40314543.qpc.hal.davecutting.uk/&keys=sentence+words+text', ['verify' => false]);

            $thisTestConfig = array(
                "services" => array(
                    "wordcount" => array(
                        "url" => "http://wordtotal.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence", "word"]
                    ),
                    "totalcount" => array(
                        "url" => "http://totalwords.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence"]
                    ),
                    "whatever" => array(
                        "url" => "http://totalwords.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence", "words", "text"]
                    )
                )
            );

            $errorOutput = array(
                "error" => false,
                "string" => "Addition successful"
            );

            $configFile = $this->client->get('?testing&administrate=printconfig', ['verify' => false]);
            $this->assertJsonStringEqualsJsonString((string) $configFile->getBody()->getContents(), json_encode($thisTestConfig));
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkNoMasterKeyAddedError() {
            $return = $this->client->get('?testing', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Request not recognised"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewServiceFailsTwoMissingKeys() {
            $return = $this->client->get('?testing&administrate=addservice&service=whatever', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please ensure all required keys are added, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewServiceFailsOneMissingKey() {
            $return = $this->client->get('?testing&administrate=addservice&=whatever&url=http://totalwords.40314543.qpc.hal.davecutting.uk/&keys=paragraph', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please ensure all required keys are added, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewServiceOneKeyMissingItsValueError() {
            $return = $this->client->get('?testing&administrate=addservice&service=&url=http://totalwords.40314543.qpc.hal.davecutting.uk/&keys=paragraph&keys=paragraph', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please ensure all keys have values, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewServiceTwoKeysMissingValuesError() {
            $return = $this->client->get('?testing&administrate=addservice&service=&url=http://totalwords.40314543.qpc.hal.davecutting.uk/&keys=paragraph&keys=', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please ensure all keys have values, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewServiceServiceKeyMisspellError() {
            $return = $this->client->get('?testing&administrate=addse&service=whatever&url=http://totalwords.40314543.qpc.hal.davecutting.uk/&keys=paragraph&keys=', ['verify' => false]);

            $errorOutput = array(
                "error" => true,
                "string" => "Request not recognised, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddKeyFailsMissingServiceKey() {
            $return = $this->client->get('?testing&administrate=addkey', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Keys are missing from the command provided, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddKeySuccessfullyToExistingService() {
            $return = $this->client->get('?testing&administrate=addkey&service=totalcount&newkey=whatever', ['verify' => false]);

            $comparisonConfig = array(
                "services" => array(
                    "wordcount" => array(
                        "url" => "http://wordtotal.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence", "word"]
                    ),
                    "totalcount" => array(
                        "url" => "http://totalwords.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence", "whatever"]
                    ),
                    "whatever" => array(
                        "url" => "http://totalwords.40314543.qpc.hal.davecutting.uk/",
                        "required_keys" => ["sentence", "words", "text"]
                    )
                )
            );

            $errorOutput = array(
                "error" => false,
                "string" => "Requested key added"
            );

            $configFile = $this->client->get('?testing&administrate=printconfig', ['verify' => false]);
            $this->assertJsonStringEqualsJsonString((string) $configFile->getBody()->getContents(), json_encode($comparisonConfig));
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddKeyValueMisspellError() {
            $return = $this->client->get('?testing&administrate=addkey&service=wordct&newkey=whatever', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Requested service doesnt exist, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddKeyKeyMisspellError() {
            $return = $this->client->get('?testing&administrate=addkey&serve=wordcount&newkey=whatever', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Keys are missing from the command provided, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewKeyKeyNameMissingError() {
            $return = $this->client->get('?testing&administrate=addkey&service=wordcount&newkey', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "New key not added, insert a key name, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewKeyKeyMissingError() {
            $return = $this->client->get('?testing&administrate=addkey&service=wordcount', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Keys are missing from the command provided, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminAddingNewKeyBothKeysMissingError() {
            $return = $this->client->get('?testing&administrate=addkey', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Keys are missing from the command provided, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminUpdatingMissingServiceError() {
            $return = $this->client->get('?testing&administrate=update', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please ensure all relevant keys and values have been provided, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminUpdatingMissingKeysError() {
            $return = $this->client->get('?testing&administrate=update&service=wordcount', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please ensure all relevant keys and values have been provided, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminUpdatingBadURLError() {
            $return = $this->client->get('?testing&administrate=update&service=totalcount&url=ht://webwordcount.40314543.qpc.hal.davecutting.uk/', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "The new URL was unsuitable, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminUpdatingNewnameEmptyError() {
            $return = $this->client->get('?testing&administrate=update&service=wordcount&newname=', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "The new name was unsuitable, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminUpdatingServiceNonExistentError() {
            $return = $this->client->get('?testing&administrate=update&service=gibberish&url=http://webwordcount.40314543.qpc.hal.davecutting.uk/', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Requested service doesnt exist, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminDeletingServiceMissingDeleteKeywordError() {
            $return = $this->client->get('?testing&administrate=delete', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Please provide the service to be deleted, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function checkAdminDeletingServiceServiceMisspellError() {
            $return = $this->client->get('?testing&administrate=delete&service=wordco', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "That key doesnt exist, please refer to documentation and try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function serviceEnquiryMissingCheckValueError() {
            $return = $this->client->get('?check=&', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Request not recognised"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }



        // FRONT END (NON ADMIN) REQUESTS
        /**
         * @test
         */
        public function serviceEnquiryMissingAllKeysError() {
            $return = $this->client->get('?', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Request not recognised"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function serviceEnquiryNonExistentServiceError() {
            $return = $this->client->get('?check=giberish', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Request not recognised"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function serviceEnquiryMissingKeyError() {
            $return = $this->client->get('?check=totalcount', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Error, Unknown Request, please try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function serviceEnquiryKeyMissingValueError() {
            $return = $this->client->get('?check=totalcount&sentence=', ['verify' => false]);
            $errorOutput = array(
                "error" => true,
                "string" => "Error, Missing Value, please try again"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($errorOutput));
        }

        /**
         * @test
         */
        public function serviceEnquirySuccessfulRequestOne() {
            $return = $this->client->get('?check=totalcount&sentence=one+flew+over+the+cuckoos+nest', ['verify' => false]);
            $successfulOutput = array(
                "error" => false,
                "display" => "",
                "result" => "6 words in paragraph"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($successfulOutput));
        }

        /**
         * @test
         */
        public function serviceEnquirySuccessfulRequestTwo() {
            $return = $this->client->get('?check=totalcount&sentence=one', ['verify' => false]);
            $successfulOutput = array(
                "error" => false,
                "display" => "",
                "result" => "1 word in paragraph"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($successfulOutput));
        }

        /**
         * @test
         */
        public function serviceEnquirySuccessfulRequestThree() {
            $return = $this->client->get('?check=wordcount&sentence=one+flew+over+the+cuckoos+nest&word=one', ['verify' => false]);
            $successfulOutput = array(
                "error" => false,
                "display" => "Searched Paragraph was : 'one flew over the cuckoos nest', and searched word was : 'one'",
                "result" => "1 word found"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($successfulOutput));
        }

        /**
         * @test
         */
        public function serviceEnquirySuccessfulRequestFour() {
            $return = $this->client->get('?check=wordcount&sentence=one+one+one+flew+over+the+cuckoos+nest&word=one', ['verify' => false]);
            $successfulOutput = array(
                "error" => false,
                "display" => "Searched Paragraph was : 'one one one flew over the cuckoos nest', and searched word was : 'one'",
                "result" => "3 words found"
            );

            $this->assertEquals($return->getStatusCode(), 200);
            $this->assertJsonStringEqualsJsonString((string) $return->getBody()->getContents(), json_encode($successfulOutput));
        }
    }
?>