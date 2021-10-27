<?php 
    declare(strict_types=1);
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;

    require(__DIR__ . '/../src/functions.php');
    class WordCheckTests extends TestCase {
        protected function setUp(): void {
            $this->paragraphLower = 'This is a test paragraph';
            $this->paragraphUpper = 'THIS IS A TEST PARAGRAPH';
            $this->wordPassLower = 'test';
            $this->wordPassUpper = 'TEST';
            $this->wordEmpty = '';
            $this->wordWrong = 'none';
            $this->client = new Client(['base_uri' => (string) "http://wordcheck.40314543.qpc.hal.davecutting.uk/", 'timeout' => 2.0]);
        }

        /**
         * @test
         */
        public function checkHTTPgetParagraphAndWordMissingError() {
            $response = $this->client->get('/?paragraph=&word=', ['verify' => false]);

            $this->assertEquals($response->getStatusCode(), 200);
            $response = json_decode((string) $response->getBody(), true);

            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['string'], "Error : Please provide a search paragraph with the 'paragraph' key and try again");
            $this->assertEquals($response['result'], "Word Not Found");
        }

        /**
         * @test
         */
        public function checkHTTPgetWordMissingError() {
            $response = $this->client->get('/?paragraph=test+paragraph&word=', ['verify' => false]);

            $this->assertEquals($response->getStatusCode(), 200);
            $response = json_decode((string) $response->getBody(), true);

            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['string'], "Error : Please provide a search word with the 'word' key and try again");
            $this->assertEquals($response['result'], "Word Not Found");
        }

        /**
         * @test
         */
        public function checkHTTPgetKeysMissingError() {
            $response = $this->client->get('/?', ['verify' => false]);

            $this->assertEquals($response->getStatusCode(), 200);
            $response = json_decode((string) $response->getBody(), true);

            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['string'], "Error : Please provide a search word and a search paragraph for this service");
            $this->assertEquals($response['result'], "Word Not Found");
        }

        /**
         * @test
         */
        public function checkHTTPgetTwoKeywordsError() {
            $response = $this->client->get('/?paragraph=test+paragraph&word=test+paragraph', ['verify' => false]);

            $this->assertEquals($response->getStatusCode(), 200);
            $response = json_decode((string) $response->getBody(), true);

            $this->assertEquals($response['error'], true);
            $this->assertEquals($response['string'], "Error : Please provide only one keyword for searching");
            $this->assertEquals($response['result'], "Word Not Found");
        }

        /**
         * @test
         */
        public function checkHTTPgetParagraphAndWordCorrectAllLowerCase() {
            $response = $this->client->get('/?paragraph=one+two+buckle+my+shoe&word=shoe', ['verify' => false]);

            $this->assertEquals($response->getStatusCode(), 200);
            $response = json_decode((string) $response->getBody(), true);

            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['string'], "Searched Paragraph was : 'one two buckle my shoe', and searched word was : 'shoe'");
            $this->assertEquals($response['result'], "Word Found");
        }

        /**
         * @test
         */
        public function checkHTTPgetParagraphAndWordCorrectPartUpperCase() {
            $response = $this->client->get('/?paragraph=one+two+buckle+my+shoe&word=SHOE', ['verify' => false]);

            $this->assertEquals($response->getStatusCode(), 200);
            $response = json_decode((string) $response->getBody(), true);

            $this->assertEquals($response['error'], false);
            $this->assertEquals($response['string'], "Searched Paragraph was : 'one two buckle my shoe', and searched word was : 'SHOE'");
            $this->assertEquals($response['result'], "Word Found");
        }

        /**
         * @test
         */
        public function checkKeywordExistsAllLowerCase() {
            $result = checkWordExists($this->paragraphLower, $this->wordPassLower);
            $this->assertEquals($result, true);
        }

        /**
         * @test
         */
        public function checkKeywordExistsWithUppercaseParagraph() {
            $result = checkWordExists($this->paragraphUpper, $this->wordPassLower);
            $this->assertEquals($result, true);
        }

        /**
         * @test
         */
        public function checkKeywordExistsUpperCaseKeyword() {
            $result = checkWordExists($this->paragraphLower, $this->wordPassUpper);
            $this->assertEquals($result, true);
        }

        /**
         * @test
         */
        public function checkKeywordExistsAllUpperCase() {
            $result = checkWordExists($this->paragraphUpper, $this->wordPassUpper);
            $this->assertEquals($result, true);
        }

        /**
         * @test
         */
        public function checkKeywordDoesntExistWhenKeywordMissing() {
            $result = checkWordExists($this->paragraphLower, $this->wordEmpty);
            $this->assertEquals($result, false);
        }

        /**
         * @test
         */
        public function checkKeywordWrong() {
            $result = checkWordExists($this->paragraphLower, $this->wordWrong);
            $this->assertEquals($result, false);
        }
    }
?>