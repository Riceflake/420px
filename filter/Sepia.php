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

class Sepia implements \Imagine\Filter\FilterInterface {
    protected $_imagine;
    public function __construct(\Imagine\Image\ImagineInterface $imagine) {
        $this->_imagine = $imagine;
    }
    public function apply(\Imagine\Image\ImageInterface $image) {
        $new = $this->_imagine->create($image->getSize(), new Color('fff'));
        for ($x = 0; $x < $image->getSize()->getWidth(); $x++) {
            for ($y = 0; $y < $image->getSize()->getHeight(); $y++) {
                $position = new Point($x, $y);
                $pixel = $image->getColorAt($position);
                $r = $pixel->getRed();
                $g = $pixel->getGreen();
                $b = $pixel->getBlue();

                $r = min(array((0.393 * $r) + (0.769 * $g) + (0.189 * ($b)), 255));
                $g = min(array((0.349 * $r) + (0.686 * $g) + (0.168 * ($b)), 255));
                $b = min(array((0.272 * $r) + (0.534 * $g) + (0.131 * ($b)), 255));

                $pixel = new Color(array($r, $g, $b));
                $new->draw()->dot($position, $pixel);
            }
        }
        return $new;
    }
}
?>