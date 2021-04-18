<?php declare(strict_types=1);

namespace TomasKulhanek\Serializer\Utils;

use JMS\Serializer\Annotation as Serializer;

class SplFileInfo extends \SplFileInfo
{

    /**
     * @var bool
     * @Serializer\Exclude()
     */
    private $temp;

    public function __construct($file_name, $temp = false)
    {
        $this->temp = $temp;
        parent::__construct($file_name);
    }

    public function isTemp(): bool
    {
        return $this->temp;
    }


    protected static function getTempNam(string $type): ?string
    {
        $filePath = tempnam(sys_get_temp_dir(), $type);

        return !$filePath ? null : $filePath;
    }

    public function __destruct()
    {
        $filePath = $this->getRealPath();
        if ($filePath !== false && $this->temp && file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    public static function createInTemp($content): SplFileInfo
    {
        $obj = new static(self::getTempNam((string)strtotime('now')), true);
        if ($content !== null) {
            $filePath = $obj->getRealPath();
            if ($filePath !== false) {
                @file_put_contents($filePath, $content);
            }
        }
        return $obj;
    }

    /**
     * Returns the contents of the file.
     *
     * @return string the contents of the file
     *
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $content = file_get_contents($this->getPathname());
        restore_error_handler();
        if (false === $content) {
            throw new \RuntimeException($error);
        }

        return $content;
    }

    public static function createFromSplFileInfo(\SplFileInfo $fileInfo): SplFileInfo
    {
        return new static($fileInfo->getRealPath());
    }

    public function __toString(): string
    {
        return $this->getContents();
    }
}
