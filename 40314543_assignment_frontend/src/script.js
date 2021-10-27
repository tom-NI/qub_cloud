let result = 0;
let paragraph = '';
let word = '';

// the names of respective services
const WORD_COUNT_SERVICE_NAME = "wordcount";
const TOTAL_COUNT_SERVICE_NAME = "totalcount";
const KEYWORD_EXISTS_SERVICE_NAME = "keyword_exists";

// define list of multi proxy endpoints for redundancy
const PROXY_URL_LIST = [
    "http://proxy.40314543.qpc.hal.davecutting.uk/",
    "http://proxy2.40314543.qpc.hal.davecutting.uk/",
    "http://proxy3.40314543.qpc.hal.davecutting.uk/"
];

// attempt to find a live url on page load as this code is asynchronous
let liveUrlArrayCounter = 0;
let liveURL = "";

// async function to determine the first LIVE proxy URL for all services to use
// loop thru all available inside PROXY_URL_LIST to find a live proxy
async function findLiveUrl() {
    let isLiveRequest = new XMLHttpRequest();
    isLiveRequest.open('GET', PROXY_URL_LIST[liveUrlArrayCounter]);
    isLiveRequest.send();

    // wait for 1 second for a reply from the proxy
    // then check the http response code
    await new Promise(r => setTimeout(r, 1000)).then(() => {
        if (isLiveRequest.status !== 200) {
            liveUrlArrayCounter++;
            if (liveUrlArrayCounter <= PROXY_URL_LIST.length - 1) {
                findLiveUrl();
            } else {
                liveUrlArrayCounter = 0;
            }
        } else {
            // print for video live demo
            console.log(PROXY_URL_LIST[liveUrlArrayCounter]);
            liveURL = PROXY_URL_LIST[liveUrlArrayCounter];
        }
    });
}

// find and set a live proxy
findLiveUrl();

// grab the data returned from the http request and show on UI
function displayResult() {
    if (result['error'] == true) {
        document.getElementById('error_display').value = result['string'];
    } else {
        document.getElementById('display_result').value = result['result'];
    }
}

// now reset the error box if its showing an error
function clearErrors() {
    if (document.getElementById("error_display").style.display === 'inline-block') {
        document.getElementById("error_display").style.display = 'none';
    }
}

// empty all input fields and clear errors
function clear() {
    document.getElementById('paragraph').value = '';
    document.getElementById('word').value = '';
    document.getElementById('display_result').value = '';
    document.getElementById('error_display').innerHTML = '';

    clearErrors();
}

// show an error to the user inside the assigned error box
function displayError(errorString) {
    let errorText = document.getElementById("error_display");
    if (errorText.style.display === 'none') {
        errorText.style.display = 'inline-block';
    }
    errorText.innerHTML = errorString;
}

// function to make http request to the proxy
function makeHTTPRequest(requestedMicroservice, paragraph, word) {
    // remove any previously displayed errors
    clearErrors();
    
    // proceed with request if there is a live URL
    if (liveURL !== null || liveURL.length === 0) {
        let finalRequest = new XMLHttpRequest();
        finalRequest.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                result = JSON.parse(this.response);
                displayResult();
            }
        };

        let finalUrl = "";
        if (word === null) {
            finalUrl = liveURL + "?check=" + requestedMicroservice + "&sentence=" + paragraph;
        } else {
            finalUrl = liveURL + "?check=" + requestedMicroservice + "&sentence=" + paragraph + "&word=" + word;
        }
        finalRequest.open("GET", finalUrl);
        finalRequest.send();
        return;
    } else {
        alert("Service unavailable at present, please try again later");
    }
}

// check a paragraph isnt blank before sending to the backend
function checkParagraphInputs(){
    paragraph = document.getElementById('paragraph').value;
    if (paragraph == '') {
        displayError("Error - Please enter a paragraph to check")
    } else {
        makeHTTPRequest(TOTAL_COUNT_SERVICE_NAME, encodeURI(paragraph), null);
    }
}

// check both paragraph and word have values
function bothInputsHaveValues() {
    paragraph = document.getElementById('paragraph').value;
    word = document.getElementById('word').value;
    let regex = /\s/g;

    if (paragraph == '' && word == '') {
        displayError("Error - Please enter a paragraph and one keyword")
    } else if (word == '' || word.trim().match(regex)) {
        displayError("Error - Please enter one keyword");
    } else if (paragraph == '') {
        displayError("Error - Please enter a paragraph");
    } else {
        clearErrors();
        return true;
    }
}

// check word count service if fields have values
function checkWordCount() {
    paragraph = document.getElementById('paragraph').value;
    word = document.getElementById('word').value;

    if (bothInputsHaveValues()) {
        makeHTTPRequest(WORD_COUNT_SERVICE_NAME, encodeURI(paragraph), word);
    }
}

// check keyword exists
function checkKeywordExists() {
    paragraph = document.getElementById('paragraph').value;
    word = document.getElementById('word').value;

    if (bothInputsHaveValues()) {
        makeHTTPRequest(KEYWORD_EXISTS_SERVICE_NAME, encodeURI(paragraph), word);
    }
}
 
// assign click listeners to buttons if the objects exist on the DOM
document.getElementById("total_words_btn").addEventListener('click', checkParagraphInputs);
document.getElementById("check_keyword_btn").addEventListener('click', checkKeywordExists);
document.getElementById("total_keywords_btn").addEventListener('click', checkWordCount);
document.getElementById("clear_btn").addEventListener("click", clear);