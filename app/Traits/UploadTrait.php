<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait UploadTrait
{
    public function uploadOne(UploadedFile $uploadedFile, $folder, $filename = null)
    {
        // $folder = target folder
        $ext = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION); // original file extension
        $file_name = (is_null($filename)) ? Str::random(40) . '.' . $ext :  $filename . '.' . $ext; // Random generated file name with original extension
        return $uploadedFile->storeAs($folder, $file_name); // saving method with custom generated filename with original file extension
        // return $uploadedFile->move(public_path($folder), $file_name);
    }

    public function deleteOne($path)
    {
        // $path = path with image name
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    public function is_file_exists($path)
    {
        return Storage::exists('/public/' . $path) ? true : false;
    }

    /**
     * @function ResizeImages
     * create required directory if not exist and set permissions
     * @param $uploadedFile
     * @param $realPath
     * @param $file_name
     * @return mixed
     */
    public function ResizeImages($uploadedFile, $realPath)
    {
        $path = $this->uploadOne($uploadedFile, 'public/' . $realPath);
        $thumbPath = public_path('storage/' . $realPath . 'thumbs/');
        if (!File::isDirectory($thumbPath)) {
            File::makeDirectory($thumbPath, 0755, true, true);
        }
        $image_name = pathinfo($path, PATHINFO_BASENAME);
        Image::make($uploadedFile)->resize(config('constants.image.width'), config('constants.image.height'))->save($thumbPath . $image_name);
        $image_array['original'] = $uploadedFile->getClientOriginalName();
        $image_array['image'] = $realPath . pathinfo($path, PATHINFO_BASENAME);
        $image_array['thumbnail'] = $realPath . 'thumbs/' . pathinfo($path, PATHINFO_BASENAME);
        return $image_array;
    }
}
