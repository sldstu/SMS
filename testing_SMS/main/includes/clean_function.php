<?php
$_title_ = "";
function clean_input($input)
{
    $input = trim($input);

    $input = stripslashes($input);

    $input = htmlspecialchars($input);

    return $input;
}
