<?php

namespace Minigyima\Aurora\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Imagick;

/**
 * ImageConverter - Uses imagick to convert a wide array of image formats to PNG, WebP or ICO.
 * - The files are saved in place.
 * @package Minigyima\Aurora\Support
 */
class ImageConverter
{
    /**
     * Mime types used to detect if the file is an ICO
     */
    private const ICO_MIMES = ['image/vnd.microsoft.icon', 'image/x-icon'];

    /**
     * Image filename
     *
     * @var string
     */
    private readonly string $input_filename;

    /**
     * Output filename
     *
     * @var string
     */
    private readonly string $output_filename;

    /**
     * The filesystem that was used to load/save the image
     *
     * @var FilesystemAdapter
     */
    private readonly FilesystemAdapter $disk;

    /**
     * Internal ImageMagick instance
     *
     * @var Imagick
     */
    private readonly Imagick $image;

    /**
     * ImageConverter constructor
     *
     * @param string $filename - Image filename
     * @param string $disk - The used disk's name
     */
    public function __construct(string $filename, string $disk = 'public')
    {
        $this->input_filename = $filename;
        $this->output_filename = $filename;
        $this->disk = Storage::disk($disk);
        $this->init_imagick();
    }

    /**
     * Used for setting the Image's output filename
     *
     * @param string $filename
     * @return void
     */
    public function output(string $filename): static
    {
        $this->output_filename = $filename;
        return $this;
    }

    /**
     * Used for initializing the internal ImageMagick instance
     * @return void
     * @internal
     */
    private function init_imagick(): void
    {
        $mime = $this->disk->mimeType($this->input_filename);
        $this->image = new Imagick();
        if (in_array($mime, self::ICO_MIMES)) {
            $this->image->setFormat('ICO');
        }

        $file = $this->disk->get($this->input_filename);
        $this->image->readImageBlob($file);
    }

    /**
     * Converts the image to PNG format
     * - The converted image is written to $output_path
     * @return string
     */
    public function convert_to_png(
        int $height = 300,
        int $width = 300,
        bool $lossless = false
    ): string {
        $new_filename = pathinfo($this->output_filename, PATHINFO_FILENAME) . '.png';
        $new_path = $this->disk->path($new_filename);

        $this->image->setImageFormat('png');
        $this->image->scaleImage($height, $width, true, false);
        if (!$lossless) {
            $this->image->setImageCompression(Imagick::COMPRESSION_ZIP);
            $this->image->setImageCompressionQuality(50);
        }

        $this->image->stripImage();
        $this->image->writeImage($new_path);

        if (
            $this->input_filename !== $new_filename &&
            $this->output_filename === $this->input_filename
        ) {
            $this->disk->delete($this->input_filename);
        }

        return $new_filename;
    }

    /**
     * Converts the image to ICO format
     * - The converted image is written to $output_path
     * @return string
     */
    public function convert_to_ico(): string
    {
        $new_filename = pathinfo($this->output_filename, PATHINFO_FILENAME) . '.ico';
        $new_path = $this->disk->path($new_filename);

        $this->image->setImageFormat('ico');
        $this->image->scaleImage(256, 256, true, false);
        $this->image->stripImage();
        $this->image->setOption('icon-auto-resize', '256,128,96,64,48,32,16');
        $this->image->writeImage($new_path);

        if (
            $this->input_filename !== $new_filename &&
            $this->output_filename === $this->input_filename
        ) {
            $this->disk->delete($this->input_filename);
        }

        return $new_filename;
    }

    /**
     * Clears and destroys the Imagick object
     */
    public function __destruct()
    {
        $this->image->clear();
        $this->image->destroy();
    }
}
