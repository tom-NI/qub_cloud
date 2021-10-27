<?php 
    declare(strict_types=1);
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    class WordTotalTest extends TestCase {
        // setup the test environment before each test
        protected function setUp(): void {
            $this->testOutput = array(
                "error" => false,
                "display" => "",
                "result" => "0 words found"
            );
            $this->client = new Client(['base_uri' => (string) "http://wordtotal.40314543.qpc.hal.davecutting.uk/", 'timeout' => 2.0]);
        }

        // reset the test output after each test
        protected function tearDown(): void {
            $this->testOutput = array(
                "error" => false,
                "display" => "",
                "result" => "0 words found"
            );
        }

        /**
         * @test
         */
        public function checkWordCountParagraphKeyMissingError() {
            $response = $this->client->get('/?', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Error - Please enter a paragraph to check");
            $this->assertEquals($response['result'], "0 words found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphEmptyError() {
            $response = $this->client->get('/?paragraph=', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Error - Please enter a paragraph to check");
            $this->assertEquals($response['result'], "0 words found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphOkWordKeyMissingError() {
            $response = $this->client->get('/?paragraph=one+flew+over+cuckoos+nest', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Error - Please enter one word to check");
            $this->assertEquals($response['result'], "0 words found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphOkWordEmptyError() {
            $response = $this->client->get('/?paragraph=one+flew+over+cuckoos+nest&word=', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Error - Please enter one word to check");
            $this->assertEquals($response['result'], "0 words found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphOkTwoWordsError() {
            $response = $this->client->get('/?paragraph=one+flew+over+cuckoos+nest&word=one+flew', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['display'], "Error - Please enter only one word to check");
            $this->assertEquals($response['result'], "0 words found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphPartialWordMatchError() {
            $response = $this->client->get('/?paragraph=whatever&word=what', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "Searched Paragraph was : 'whatever', and searched word was : 'what'");
            $this->assertEquals($response['result'], "0 words found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphOkOneWord() {
            $response = $this->client->get('/?paragraph=one+flew+over+cuckoos+nest&word=one', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "Searched Paragraph was : 'one flew over cuckoos nest', and searched word was : 'one'");
            $this->assertEquals($response['result'], "1 word found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphUpperCaseOKOneWord() {
            $response = $this->client->get('/?paragraph=ONE+FLEW+OVER+CUCKOOS+NEST&word=one', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "Searched Paragraph was : 'ONE FLEW OVER CUCKOOS NEST', and searched word was : 'one'");
            $this->assertEquals($response['result'], "1 word found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphOKOneWordUpperCase() {
            $response = $this->client->get('/?paragraph=one+flew+over+cuckoos+nest&word=ONE', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "Searched Paragraph was : 'one flew over cuckoos nest', and searched word was : 'ONE'");
            $this->assertEquals($response['result'], "1 word found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphAndWordUpperCase() {
            $response = $this->client->get('/?paragraph=ONE+FLEW+OVER+CUCKOOS+NEST&word=ONE', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "Searched Paragraph was : 'ONE FLEW OVER CUCKOOS NEST', and searched word was : 'ONE'");
            $this->assertEquals($response['result'], "1 word found");
        }

        /**
         * @test
         */
        public function checkWordCountParagraphOkOneWordThreeAppearances() {
            $response = $this->client->get('/?paragraph=one+one+one+flew+over+cuckoos+nest&word=one', ['verify' => false]);
            $this->assertEquals($response->getStatusCode(), 200);

            $response = json_decode((string) $response->getBody(), true);
            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['display'], "Searched Paragraph was : 'one one one flew over cuckoos nest', and searched word was : 'one'");
            $this->assertEquals($response['result'], "3 words found");
        }
    }
?>