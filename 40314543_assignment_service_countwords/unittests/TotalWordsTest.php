<?php 
    declare(strict_types=1);
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    class TotalWordsTest extends TestCase {
        // setup the test environment before each test
        protected function setUp(): void {
            $this->testOutput = array(
                "error" => false,
                "display" => "",
                "result" => "0 words in paragraph"
            );
            $this->client = new Client(['base_uri' => (string) "http://totalwords.40314543.qpc.hal.davecutting.uk/", 'timeout' => 2.0]);
        }

        // reset the test output after each test
        protected function tearDown(): void {
            $this->testOutput = array(
                "error" => false,
                "display" => "",
                "result" => "0 words in paragraph"
            );
        }

        /**
         * @test
         */
        public function checkWordCountAccurateFiveWords() {   
            $response = $this->client->get('/?paragraph=one+flew+over+cuckoos+nest', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "");
            $this->assertEquals($response['result'], "5 words in paragraph");
        }

        /**
         * @test
         */
        public function checkWordCountAccurateOneWord() {   
            $response = $this->client->get('/?paragraph=one', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "");
            $this->assertEquals($response['result'], "1 word in paragraph");
        }

        /**
         * @test
         */
        public function checkParagraphKeyMissingError() {
            $response = $this->client->get('/?', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Please enter a paragraph to check");
            $this->assertEquals($response['result'], "0 words in paragraph");
        }

        /**
         * @test
         */
        public function checkParagraphEmptyError() {
            $response = $this->client->get('/?paragraph=', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Please enter a paragraph to check");
            $this->assertEquals($response['result'], "0 words in paragraph");
        }
    }
?>