<?php
    $emailBody = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error</title>
        </head>
        <body>
        <h4>Dear Admin</h4>
        <p>Please Note the '{$serviceKeyword}' microservice for the Web Word Count Application was recorded as being down just now.</p>
        <p>Refer to the monitoring and metrics documentation to debug this error</p>
        <p>Regards, Administration.</p>
        </body>
        </html>
    ";
?>