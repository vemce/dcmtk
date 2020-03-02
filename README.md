# 使用php调用dcmtk来解析dicom文件

## 安装

```shell
$ composer require vemce/dcmtk
```

## 用法

```php
use vemce\dcmtk\Dcmtk;

require_once 'vendor/autoload.php';

$dicom = new Dcmtk('test.dcm');
var_dump($dicom->getTags());
var_dump($dicom->getData());
$dicom->saveJpgByImagick('1.jpg');
$dicom->saveJPG('2.jpg');



```
