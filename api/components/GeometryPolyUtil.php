<?php

namespace api\components;

class GeometryPolyUtil
{
    /**
     * Returns tan(latitude-at-lng3) on the great circle (lat1, lng1) to (lat2, lng2). lng1==0.
     * See http://williams.best.vwh.net/avform.htm .
     */
    private static function tanLatGC($lat1, $lat2, $lng2, $lng3)
    {
        return (tan($lat1) * sin($lng2 - $lng3) + tan($lat2) * sin($lng3)) / sin($lng2);
    }

    /**
     * Returns mercator(latitude-at-lng3) on the Rhumb line (lat1, lng1) to (lat2, lng2). lng1==0.
     */
    private static function mercatorLatRhumb($lat1, $lat2, $lng2, $lng3)
    {
        return (GeometryMathUtil::mercator($lat1) * ($lng2 - $lng3) + GeometryMathUtil::mercator($lat2) * $lng3) / $lng2;
    }

    /**
     * Computes whether the vertical segment (lat3, lng3) to South Pole intersects the segment
     * (lat1, lng1) to (lat2, lng2).
     * Longitudes are offset by -lng1; the implicit lng1 becomes 0.
     */
    private static function intersects($lat1, $lat2, $lng2, $lat3, $lng3, $geodesic)
    {
        // Both ends on the same side of lng3.
        if (($lng3 >= 0 && $lng3 >= $lng2) || ($lng3 < 0 && $lng3 < $lng2)) {
            return false;
        }
        // Point is South Pole.
        if ($lat3 <= -M_PI / 2) {
            return false;
        }
        // Any segment end is a pole.
        if ($lat1 <= -M_PI / 2 || $lat2 <= -M_PI / 2 || $lat1 >= M_PI / 2 || $lat2 >= M_PI / 2) {
            return false;
        }
        if ($lng2 <= -M_PI) {
            return false;
        }
        $linearLat = ($lat1 * ($lng2 - $lng3) + $lat2 * $lng3) / $lng2;
        // Northern hemisphere and point under lat-lng line.
        if ($lat1 >= 0 && $lat2 >= 0 && $lat3 < $linearLat) {
            return false;
        }
        // Southern hemisphere and point above lat-lng line.
        if ($lat1 <= 0 && $lat2 <= 0 && $lat3 >= $linearLat) {
            return true;
        }
        // North Pole.
        if ($lat3 >= M_PI / 2) {
            return true;
        }
        // Compare lat3 with latitude on the GC/Rhumb segment corresponding to lng3.
        // Compare through a strictly-increasing function (tan() or mercator()) as convenient.
        return $geodesic ?
            tan($lat3) >= self::tanLatGC($lat1, $lat2, $lng2, $lng3) :
            GeometryMathUtil::mercator($lat3) >= self::mercatorLatRhumb($lat1, $lat2, $lng2, $lng3);
    }


    /**
     * Computes whether the given point lies inside the specified polygon.
     * The polygon is always cosidered closed, regardless of whether the last point equals
     * the first or not.
     * Inside is defined as not containing the South Pole -- the South Pole is always outside.
     * The polygon is formed of great circle segments if geodesic is true, and of rhumb
     * (loxodromic) segments otherwise.
     */
    public static function containsLocation($point, $polygon, $geodesic = false)
    {
        $size = count($polygon);

        if ($size == 0) {
            return false;
        }
        $lat3 = deg2rad($point['lat']);
        $lng3 = deg2rad($point['lng']);
        $prev = $polygon[$size - 1];
        $lat1 = deg2rad($prev['lat']);
        $lng1 = deg2rad($prev['lng']);

        $nIntersect = 0;

        foreach ($polygon as $key => $val) {

            $dLng3 = GeometryMathUtil::wrap($lng3 - $lng1, -M_PI, M_PI);
            // Special case: point equal to vertex is inside.
            if ($lat3 == $lat1 && $dLng3 == 0) {
                return true;
            }

            $lat2 = deg2rad($val['lat']);
            $lng2 = deg2rad($val['lng']);

            // Offset longitudes by -lng1.
            if (self::intersects($lat1, $lat2, GeometryMathUtil::wrap($lng2 - $lng1, -M_PI, M_PI), $lat3, $dLng3, $geodesic)) {
                ++$nIntersect;
            }
            $lat1 = $lat2;
            $lng1 = $lng2;
        }
        return ($nIntersect & 1) != 0;
    }
}