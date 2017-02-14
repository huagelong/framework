<?php
/**
 *  image captcha
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Support;


class ImgCaptcha
{
    //声明图像大小
    private $width = 78;
    private $height = 46;

    //验证码字符有限集
    private $v_char = '12346789abcdefgjkmpqrtwxyz';
    private $v_code_str = '';

    //验证码数量
    private $v_num = 4;

    // 第i个文字x轴起始位置计算公式： x轴起始坐标 = margin + padding * i
    //文字内外边距
    private $padding = 16;
    private $margin = 3;

    //字体大小
    private $font_size = 30;

    //字体逆时针旋转的角度
    private $font_angles = array(-5, 5);

    //字体名称
    //private $font = 'Wattauchimma.ttf';
    private $font = 'msyh.ttf';    //加上路径非常重要

    //图像容器
    private $img;

    //颜色容器
    private $colors = array();


    /**
     * 生成图片验证码主逻辑
     */
    public function __construct($width, $heigth, $vNum)
    {
        $this->width = $width;
        $this->height = $heigth;
        $this->v_num = $vNum;
        //生成一幅图像
        $this->img = imagecreate($this->width, $this->height);

        //生成颜色
        $this->colors['white'] = imagecolorallocate($this->img, 255, 255, 255);
        $this->colors['blue'] = imagecolorallocate($this->img, 0, 47, 167);

        // 生成纯白色背景
        imagecolorallocate($this->img, 255, 255, 255);

        //生成验证码字符
        $this->randomContent();
    }

    public function setFont($font)
    {
        $this->font = $font;
    }

    public function setFontSize($font_size)
    {
        $this->font_size = $font_size;
    }

    public function setMargin($margin)
    {
        $this->margin = $margin;
    }

    public function setVChar($v_char)
    {
        $this->v_char = $v_char;
    }

    public function setPadding($padding)
    {
        $this->padding = $padding;
    }

    /**
     * 输出验证码,返回值是验证码的字符串表示
     * @return string
     */
    public function create()
    {
        $this->generate();

        $header = [];
        $header[] = 'Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate';
        $header[] = 'Cache-Control: post-check=0, pre-check=0';
        $header[] = 'Pragma: no-cache';
        $header[] = "content-type: image/png";
        ob_start();
        imagepng($this->img);
        $imgSource = ob_get_clean();
        imagedestroy($this->img);

        return [$this->v_code_str, $imgSource, $header];
    }

    /**
     * 生成随机的验证码的内容
     * @return string
     */
    private function randomContent()
    {
        for ($i = 0; $i < $this->v_num; $i++) {
            $this->v_code_str .= $this->v_char[rand(0, strlen($this->v_char) - 1)];
        }
    }

    /**
     * 生成验证码的图像
     */
    private function generate()
    {
        //生成验证码的算法
        for ($i = 0; $i < $this->v_num; $i++) {
            // 下一个字符的起始x轴坐标
            $x = $this->margin + $this->padding * $i;
            // 下一个字符的起始y轴坐标
            $y = 38;

            imagettftext(
                $this->img,
                $this->font_size,
                $this->font_angles[rand(0, count($this->font_angles) - 1)],
                $x, $y,
                $this->colors['blue'],
                $this->font,    //加上了字体的相对路径
                $this->v_code_str[$i]
            );
        }

        $dst = imagecreatetruecolor($this->width, $this->height);
        $dWhite = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $dWhite);

        //扭曲，变形
        for ($i = 0; $i < $this->width; $i++) {
            // 根据正弦曲线计算上下波动的posY

            $offset = 4; // 最大波动几个像素
            $round = 2; // 扭2个周期,即4PI
            $posY = round(sin($i * $round * 2 * M_PI / $this->width) * $offset); // 根据正弦曲线,计算偏移量

            imagecopy($dst, $this->img, $i, $posY, $i, 0, 1, $this->height);
        }

        $this->img = $dst;
    }

    public function __destruct()
    {
        unset($this->colors);
    }
}