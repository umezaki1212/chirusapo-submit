<?php
/**
 * @param int $count
 * @return string
 */
function random(int $count) : string {
    $random = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $string = null;
    for ($i = 0; $i < $count; $i++) {
        $string .= $random[rand(0, count($random) - 1)];
    }

    return $string;
}

/**
 * @param $array
 * @return mixed
 */
function array_escape($array) {
    if (is_null($array) || empty($array)) {
        return $array;
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = array_escape($value);
        } else {
            $array[$key] = escape($value);
        }
    }
    return $array;
}

/**
 * @param $value
 * @return string
 */
function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * @param mixed ...$value
 * @return bool
 */
function is_nulls(...$value): bool {
    $judge = [];

    for ($i = 0; $i < count($value); $i++) {
        if (is_null($value[$i])) {
            $judge[$i] = true;
        } else if ($value[$i] == '') {
            $judge[$i] = true;
        } else {
            $judge[$i] = false;
        }

        /*
        if (is_null($value[$i])) {
            $judge[$i] = true;
        } else if ($value[$i] === 0 || $value[$i] === "0") {
            $judge[$i] = false;
        } else if (is_bool($value[$i])) {
            $judge[$i] = false;
        } else if (empty($value[$i])) {
            $judge[$i] = true;
        } else {
            $judge[$i] = false;
        }
        */

        /*
        if (is_null($value[$i])) {
            return true;
        } else {
            if (is_int($value[$i])) {
                return false;
            } else if (empty($value[$i])) {
                return true;
            }
        }
        */
    }

    return in_array(true, $judge, true);
}