<?php
    declare(strict_types=1);
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    include_once(__DIR__ . '/../src/proxyfunctions.php');

    class ProxyFunctionTests extends TestCase {
        // test functions 
        /**
         * @test
         */
        public function checkNumbersAtEnd() {
            $cleanedString = removeNonAlphabetCharacters("tom123");
            $this->assertEquals($cleanedString, "tom");
        }

        /**
         * @test
         */
        public function checkNumbersAtStartAllCaps() {
            $cleanedString = removeNonAlphabetCharacters("123TOM");
            $this->assertEquals($cleanedString, "TOM");
        }

        /**
         * @test
         */
        public function checkGibberishRemoved() {
            $cleanedString = removeNonAlphabetCharacters("<>!.+-)(8TOM");
            $this->assertEquals($cleanedString, "TOM");
        }

        /**
         * @test
         */
        public function checkMessyText() {
            $cleanedString = removeNonAlphabetCharacters("12T345O567M");
            $this->assertEquals($cleanedString, "TOM");
        }
        
        /**
         * @test
         */
        public function checkGoodURL() {
            $urlCheck = checkURLSuitable("http://webwordcount.40314543.qpc.hal.davecutting.uk/");
            $this->assertEquals($urlCheck, true);
        }

        /**
         * @test
         */
        public function checkEmptyURLFails() {
            $urlCheck = checkURLSuitable("");
            $this->assertEquals($urlCheck, false);
        }

        /**
         * @test
         */
        public function checkUnsuitableProtocolURL() {
            $urlCheck = checkURLSuitable("https://webwordcount.40314543.qpc.hal.davecutting.uk/");
            $this->assertEquals($urlCheck, false);
        }

        /**
         * @test
         */
        public function checkMissingProtocolURL() {
            $urlCheck = checkURLSuitable("//webwordcount.40314543.qpc.hal.davecutting.uk/");
            $this->assertEquals($urlCheck, false);
        }
        
        /**
         * @test
         */
        public function checkWrongProtocolURL() {
            $urlCheck = checkURLSuitable("afp://webwordcount.40314543.qpc.hal.davecutting.uk/");
            $this->assertEquals($urlCheck, false);
        }

        /**
         * @test
         */
        public function checkMissingDomainURL() {
            $urlCheck = checkURLSuitable("http://");
            $this->assertEquals($urlCheck, false);
        }

        // now test HTTP requests using GET commands
        /**
         * @test
         */
        public function checkGettingDataViaCurlWorks() {
            $returnedData = getProxyDataFromURL("http://totalwords.40314543.qpc.hal.davecutting.uk/");

            $this->output = array(
                "error" => true,
                "display" => "Please enter a paragraph to check",
                "result" => "0 words in paragraph"
            );

            $this->assertJsonStringEqualsJsonString((string)$returnedData, json_encode($this->output));
        }
    }
?>