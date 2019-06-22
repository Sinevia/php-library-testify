<?php

namespace Testify;

/**
 * Util class
 *
 * @license GPL
 */

class Util {

    /**
     * Calculate the percentage of success for a test
     *
     * @param array $suiteResults
     * @return float Percent
     */
    public static function percent($suiteResults) {
        $sum = $suiteResults['pass'] + $suiteResults['fail'];
        return round($suiteResults['pass'] * 100 / max($sum, 1), 2);
    }
}
