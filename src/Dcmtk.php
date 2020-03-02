<?php
/**
 * Created by ymc
 * Date: 2020/3/2
 * Time: 14:59
 */

namespace vemce\dcmtk;


use Intervention\Image\ImageManager;

class Dcmtk
{
    /**
     * @var string
     */
    private $dcmdump;
    private $tags = [];
    private $data = [];
    private $itemPattern = "/^(\s{0,3})\((\S{4},\S{4})\) \S{2} (.*) #\s{1,3}\d{1,6}, \d (.+)$/isU";
    private $valuePattern = [
        '/(^=)/',
        '/(\w{4}\\\\)/',
        '/(\w{4}\.\.\.)/',
        '/0000/',
        '/00\\\\01/',
        '/\[/',
        '/]/',
        '/\(no value available\)/',
        '/=/',
    ];
    private $file;
    private $Intervention;

    public function __construct($file = '')
    {
        if (!file_exists($file) || mime_content_type($file) != 'application/dicom') {
            throw new DcmtkException($file . ' is no a dicom file');
        }
        $this->file = $file;
        $this->dcmdump = exec('which dcmdump');
        if (!$this->dcmdump) {
            throw new DcmtkException($file . 'command dcmdump is no exit');
        }
        unset($output);
        exec('dcmdump ' . $file, $output);
        foreach ($output as $item) {
            $item = iconv('GB2312', 'UTF-8//IGNORE', $item);
            preg_match($this->itemPattern, $item, $matches);
            if ($matches) {
                $matches[3] = $this->formatValue($matches[3]);
                $this->data[] = [
                    'element' => $matches[1] . $matches[2],
                    'tag' => $matches[4],
                    'value' => $matches[3],
                ];
                if (!strlen($matches[1]) && !preg_match("/\s|Unknown/", $matches[4])) {
                    $this->tags[$matches[4]] = $matches[3];
                }
            }
        }
    }

    public function hasImage(){
        if (isset($this->tags['PixelData'])) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 去除一些字符
     * @param string $value
     * @return string|string[]|null
     */
    private function formatValue($value = '')
    {
        $value = preg_replace(
            $this->valuePattern,
            '',
            trim($value)
        );
        return $value;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * 导出jpg
     * @param $jpg
     * @param int $quality
     * @return bool
     */
    public function saveJPG($jpg, $quality = 85)
    {
        if (!$this->hasImage()) {
            return false;
        }
        exec("dcmj2pnm +Wm +oj +Jq {$quality} {$this->file} {$jpg}");
        if (file_exists($jpg)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 用imagick导出jpg
     * @param $jpg
     * @param int $quality
     * @return bool
     */
    public function saveJpgByImagick($jpg, $quality = 85)
    {
        if (!$this->hasImage()) {
            return false;
        }
        if (!$this->Intervention) {
            $this->Intervention = new ImageManager(array('driver' => 'imagick'));
        }
        $this->Intervention->make($this->file)->save($jpg, 85);
        if (file_exists($jpg)) {
            return true;
        } else {
            return false;
        }
    }
}