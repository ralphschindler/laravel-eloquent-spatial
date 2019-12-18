<?php

namespace LaravelEloquentSpatial\Tests\GeoTypes;

use LaravelEloquentSpatial\GeoTypes\EwkbFormat;
use LaravelEloquentSpatial\GeoTypes\Point;
use LaravelEloquentSpatial\GeoTypes\Polygon;
use Orchestra\Testbench\TestCase;

class EwkbFormatTest extends TestCase
{
    public function testConvertBinaryToObjectForPoint()
    {
        $ewkbPointBinary = hex2bin('E61000000101000000640E3801BC8656C05AACF24E4F013E40');

        $point = (new EwkbFormat)->convertBinaryToObject($ewkbPointBinary);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(4326, $point->srid);
        $this->assertEquals(-90.1052249, $point->x);
        $this->assertEquals(30.0051164, $point->y);

        $this->assertEquals(30.0051164, $point->latitude);
        $this->assertEquals(-90.1052249, $point->longitude);
    }

    public function testConvertBinaryToObjectForPointPolygon()
    {
        $ewkbPolygonBinary = hex2bin('E61000000103000000010000000F00000000529B38B98756C0622D3E05C0083E40959A3DD00A8856C0707CED9925F93D403B014D840D8956C08716D9CEF7F33D40C6C4E6E3DA8856C0F9DA334B02EC3D404C6C3EAE0D8756C055DE8E705AE83D4059DDEA39E98356C0774A07EBFFEC3D40E4BD6A65C28356C0BBF2599E07EF3D40C6E1CCAFE67E56C000AE64C746E83D40F6622827DA7D56C031CEDF8442EC3D40B4E55C8AAB8056C0184339D1AEF23D400708E6E8F17F56C0707CED9925F93D403546EBA86A7C56C09BC937DBDC003E404CC3F011316D56C0A7B393C151023E403CDA38622D7856C00A68226C782A3E4000529B38B98756C0622D3E05C0083E40');

        $polygon = (new EwkbFormat)->convertBinaryToObject($ewkbPolygonBinary);

        $this->assertInstanceOf(Polygon::class, $polygon);
        $this->assertCount(1, $polygon->rings);
    }

    public function testConvertObjectToBinaryForPoint()
    {
        $point = new Point;
        $point->x = -90.1052249;
        $point->y = 30.0051164;
        $point->srid = 4326;

        $binary = (new EwkbFormat())->convertObjectToBinary($point);

        $this->assertEquals('E61000000101000000640E3801BC8656C05AACF24E4F013E40', strtoupper(bin2hex($binary)));
    }
}

