<?php

namespace LaravelEloquentSpatial\Types;

/**
 * @link https://www.ibm.com/support/knowledgecenter/en/SSEPGG_11.1.0/com.ibm.db2.luw.spatial.topics.doc/doc/rsbp4121.html
 */
class EwkbFormat
{
    public function convertBinaryToObject($mysqlBinary)
    {
        $stream = fopen('php://memory', 'r+');

        fwrite($stream, $mysqlBinary);
        rewind($stream);

        // header information, 9 bytes
        $values = unpack('isrid/cendianness/itype', fread($stream, 9));

        switch ($values['type']) {
            case 1:
                $object = $this->toPointObject($stream, $values['srid']);
                break;
            case 3:
                $object = $this->toPolygonObject($stream, $values['srid']);
                break;
            default:
                throw new \RuntimeException($values['type'] . ' is currently not a supported type in this library');
        }

        fclose($stream);

        return $object;
    }

    public function convertObjectToBinary($object)
    {
        $stream = fopen('php://memory', 'r+');

        switch (get_class($object)) {
            case Point::class:
                $type = 1;
                $binary = $this->toPointBinary($object);
                break;
            case Polygon::class:
                $type = 3;
                $binary = $this->toPolygonBinary($object);
                break;
            default:
                throw new \RuntimeException(get_class($object) . ' is currently not a supported type in this library');
        }

        fwrite($stream, pack('ici', $object->srid, 1, $type)); // 1 is little endian
        fwrite($stream, $binary);

        rewind($stream);
        $contents = stream_get_contents($stream);

        fclose($stream);

        return $contents;
    }

    /**
     * WKBPolygon {
     *   byte       byteOrder;
     *   uint32     wkbType;     // 3=wkbPolygon
     *   uint32     numRings;
     *   LinearRing rings[numRings];
     * }
     */
    protected function toPolygonObject($stream, $srid)
    {
        $ringCountValues = unpack('Lcount', fread($stream, 4));

        $rings = [];

        while (count($rings) < $ringCountValues['count']) {
            $pointCountValues = unpack('Lcount', fread($stream, 4));

            $points = [];

            while (count($points) < $pointCountValues['count']) {
                $points[] = $this->toPointObject($stream, $srid);
            }

            $rings[] = $points;
        }

        $polygon = new Polygon;
        $polygon->rings = $rings;
        $polygon->srid = $srid;

        return $polygon;
    }

    protected function toPolygonBinary(Polygon $polygon)
    {
        $binary = pack('i', count($polygon->rings));

        foreach ($polygon->rings as $ring) {

            $binary .= pack('i', count($ring));

            foreach ($ring as $point) {
                $binary .= $this->toPointBinary($point);
            }
        }

        return $binary;
    }

    /**
     * WKBPoint {
     *  byte     byteOrder;
     *  uint32   wkbType;     // 1=wkbPoint
     *  Point    point;
     * };
     */
    protected function toPointObject($stream, $srid)
    {
        $pointParts = unpack('dx/dy', fread($stream, 16));

        $latLong = new Point;
        $latLong->x = $pointParts['x'];
        $latLong->y = $pointParts['y'];
        $latLong->srid = $srid;

        return $latLong;
    }

    protected function toPointBinary(Point $point)
    {
        return pack('dd', $point->x, $point->y);
    }
}

