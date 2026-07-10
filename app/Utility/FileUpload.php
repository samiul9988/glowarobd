<?php
namespace App\Utility;

use App\Models\Upload;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Image;
use Storage;

/**
 * Class FileUpload
 * @package App\Utility
 */
class FileUpload
{
    /**
     * If the uploaded file name matches with the name of the file in the uploaded directory,
     * the methods overrides the current file with the new file.
     *
     * @param UploadedFile $uploadedFile
     * @param string       $path
     * @param string       $fileSystem
     * @param bool         $assignNewName
     *
     * @return false|string
     * @throws \Exception
     */
    public function handle(UploadedFile $uploadedFile, $path = 'uploads/all', $assignNewName = true, $fileSystem = 'custom')
    {
        if ( $assignNewName ) {
            $extension = $uploadedFile->getClientOriginalExtension();
            $fileName  = sprintf('%s.%s', strtotime(now()), $extension);
        } else {
            $fileName = $uploadedFile->getClientOriginalName();
        }
        try {
            $uploadedFile->storeAs(
                $path,
                $fileName,
                $fileSystem
            );

            return $fileName;

        } catch ( \Exception $e ) {
            throw new \Exception($e);
        }
    }

    public function upload(UploadedFile $uploadedFile){
        $type = array(
            "jpg"=>"image",
            "jpeg"=>"image",
            "png"=>"image",
            "svg"=>"image",
            "webp"=>"image",
            "gif"=>"image",
            "mp4"=>"video",
            "mpg"=>"video",
            "mpeg"=>"video",
            "webm"=>"video",
            "ogg"=>"video",
            "avi"=>"video",
            "mov"=>"video",
            "flv"=>"video",
            "swf"=>"video",
            "mkv"=>"video",
            "wmv"=>"video",
            "wma"=>"audio",
            "aac"=>"audio",
            "wav"=>"audio",
            "mp3"=>"audio",
            "zip"=>"archive",
            "rar"=>"archive",
            "7z"=>"archive",
            "doc"=>"document",
            "txt"=>"document",
            "docx"=>"document",
            "pdf"=>"document",
            "csv"=>"document",
            "xml"=>"document",
            "ods"=>"document",
            "xlr"=>"document",
            "xls"=>"document",
            "xlsx"=>"document"
        );

        try {
            if($uploadedFile){
                $upload = new Upload;
                $extension = strtolower($uploadedFile->getClientOriginalExtension());

                if(isset($type[$extension])){
                    $upload->file_original_name = null;
                    $arr = explode('.', $uploadedFile->getClientOriginalName());
                    for($i=0; $i < count($arr)-1; $i++){
                        if($i == 0){
                            $upload->file_original_name .= $arr[$i];
                        }
                        else{
                            $upload->file_original_name .= ".".$arr[$i];
                        }
                    }

                    $path = $uploadedFile->store('uploads/all', 'local');
                    $size = $uploadedFile->getSize();

                    // Return MIME type ala mimetype extension
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);

                    // Get the MIME type of the file
                    $file_mime = finfo_file($finfo, base_path('public/').$path);

                    if($type[$extension] == 'image' && get_setting('disable_image_optimization') != 1){
                        try {
                            $img = Image::make($uploadedFile->getRealPath())->encode();
                            $height = $img->height();
                            $width = $img->width();
                            if($width > $height && $width > 1500){
                                $img->resize(1500, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                            }elseif ($height > 1500) {
                                $img->resize(null, 800, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                            }
                            $img->save(base_path('public/').$path);
                            clearstatcache();
                            $size = $img->filesize();

                        } catch (\Exception $e) {
                            //dd($e);
                        }
                    }

                    if (env('FILESYSTEM_DRIVER') == 's3') {
                        Storage::disk('s3')->put($path, file_get_contents(base_path('public/').$path));
                        if($arr[0] != 'updates') {
                            unlink(base_path('public/').$path);
                        }
                    }

                    $upload->extension = $extension;
                    $upload->file_name = $path;
                    $upload->user_id = Auth::user()->id ?? null;
                    $upload->type = $type[$upload->extension];
                    $upload->file_size = $size;
                    $upload->save();
                }
                return $upload;
            }
        } catch ( \Exception $e ) {
            throw new \Exception($e);
        }
    }
}
