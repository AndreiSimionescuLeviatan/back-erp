<?php

namespace api\components;

class GeometrySphericalUtil
{
    /**
     * @param $lat
     * @param $lon
     * @param $distance
     * @param $radius
     * @return array
     * Calc distance from single lat and long + 300 meters
     * @added 2022-06-27
     * @added_by Alex G.
     */
    public static function calcSingleCoordDistances($lat, $lon, $distance, $radius)
    {

        $maxLat = $lat + rad2deg($distance / $radius);
        $minLat = $lat - rad2deg($distance / $radius);
        $maxLon = $lon + rad2deg(asin($distance / $radius) / cos(deg2rad($lat)));
        $minLon = $lon - rad2deg(asin($distance / $radius) / cos(deg2rad($lat)));

        return [$maxLat, $maxLon, $minLat, $minLon];
    }
}