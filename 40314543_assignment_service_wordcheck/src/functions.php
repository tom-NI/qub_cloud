<?php
    function checkWordExists($paragraph, $word) {
        // if keyword is empty, return false
        if (strlen($word) > 0) {
            $paragraph = strtolower($paragraph);
            $word = strtolower($word);
            if (strpos($paragraph, $word) !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
?>
