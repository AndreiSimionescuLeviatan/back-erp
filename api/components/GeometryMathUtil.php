<?php

namespace api\components;

class GeometryMathUtil
{
    /**
     * Restrict x to the range [low, high].
     */
    public static function clamp( $x, $low, $high) {
        return $x < $low ? $low : ($x > $high ? $high : $x);
    }

    /**
     * Wraps the given value into the inclusive-exclusive interval between min and max.
     *
//     * @param n   The value to wrap.
//     * @param min The minimum.
//     * @param max The maximum.
     */
    public static function wrap($n, $min, $max) {
        return ($n >= $min && $n < $max) ? $n : (self::mod($n - $min, $max - $min) + $min);
    }
    /**
     * Returns the non-negative remainder of x / m.
//     * @param x The operand.
//     * @param m The modulus.
     */
    public static function mod($x, $m) {
        return (($x % $m) + $m) % $m;
    }
    /**
     * Returns mercator Y corresponding to latitude.
     * See http://en.wikipedia.org/wiki/Mercator_projection .
     */
    public static function mercator($lat) {
        return log(tan($lat * 0.5 + M_PI/4));
    }
}