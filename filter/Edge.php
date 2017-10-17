<?php
/**
 * Created by PhpStorm.
 * User: franckzhang
 * Date: 0503//2017
 * Time: 21:17
 */

use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Color;

class Edge implements \Imagine\Filter\FilterInterface {
    protected $_imagine;
    public function __construct(\Imagine\Image\ImagineInterface $imagine) {
        $this->_imagine = $imagine;
    }
    public function apply(\Imagine\Image\ImageInterface $image)
    {
        $k = array(array(0, 1, 0), array(1, -4, 1), array(0, 1, 0));


        $new = $this->_imagine->create($image->getSize(), new Color('fff'));
        $w = $image->getSize()->getWidth();
        $h = $image->getSize()->getHeight();
        $size = count($k) / 2;


        for ($x = $size; $x < $w - $size; $x++)
        {
            for ($y = $size; $y < $h - $size; $y++)
            {
                $r = 0;
                $g = 0;
                $b = 0;

                for ($i = - $size; $i <= $size; $i++)
                {
                    for ($j = -$size; $j <= $size; $j++)
                    {
                        $position = new Point((int)$x + (int) $i, (int)$y + (int)$j);
                        $pixel = $image->getColorAt($position);
                        $r = $r + $pixel->getRed() * $k[(int)$i + (int)$size][(int)$j + (int)$size];
                        $g = $g + $pixel->getGreen() * $k[(int)$i + (int)$size][(int)$j + (int)$size];
                        $b = $b + $pixel->getBlue() * $k[(int)$i + (int)$size][(int)$j + (int)$size];

                    }
                }


                $r = $r > 255 ? 255 : $r < 0 ? 0 : $r;
                $g = $g > 255 ? 255 : $g < 0 ? 0 : $g;
                $b = $b > 255 ? 255 : $b < 0 ? 0 : $b;

                $new_pixel = new Color(array($r, $g, $b));
                $new->draw()->dot($position, $new_pixel);
            }
        }
        return $new;
    }
}
?>