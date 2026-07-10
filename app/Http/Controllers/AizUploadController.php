<?php

namespace App\Http\Controllers;

use Image;
use Response;
use App\Models\Upload;
use Illuminate\Http\Request;
use App\Jobs\CompressVideoJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AizUploadController extends Controller
{

    public function index(Request $request)
    {

        $all_uploads = (auth()->user()->user_type == 'seller') ? Upload::where('user_id', auth()->user()->id) : Upload::query();
        $search = null;
        $sort_by = null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());

        return (auth()->user()->user_type == 'seller')
        ? view('frontend.user.seller.uploads.index', compact('all_uploads', 'search', 'sort_by'))
        : view('backend.uploaded_files.index', compact('all_uploads', 'search', 'sort_by'));
    }

    public function show($id = null)
    {
        //
    }

    public function create()
    {
        return (auth()->user()->user_type == 'seller')
        ? view('frontend.user.seller.uploads.create')
        : view('backend.uploaded_files.create');
    }

    public function show_uploader(Request $request)
    {
        return view('uploader.aiz-uploader');
    }

    public function upload(Request $request)
    {
        $types = $this->getTypes();

        // $max_file_size = (int) max(get_setting('max_file_size', 10), 10) * 1024 * 1024; // Convert to bytes
        // $max_video_size = (int) max(get_setting('max_video_size', 50), 50) * 1024 * 1024; // Convert to bytes

        if ($request->hasFile('aiz_file')) {
            $upload = new Upload;
            $extension = strtolower($request->file('aiz_file')->extension());

            // dd($extension);
            if (isset($types[$extension])) {
                $upload->file_original_name = null;
                $arr = explode('.', $request->file('aiz_file')->hashName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0) {
                        $upload->file_original_name .= $arr[$i];
                    } else {
                        $upload->file_original_name .= "." . $arr[$i];
                    }
                }

                if($types[$extension] == 'video'){
                    $path = $request->file('aiz_file')->store('uploads/all/videos', 'local');
                }
                else{
                    $path = $request->file('aiz_file')->store('uploads/all', 'local');
                }
                // $path = $request->file('aiz_file')->store('uploads/all', 'local');
                $size = $request->file('aiz_file')->getSize();

                // if(($types[$extension] == 'video' && $size > $max_video_size) || ($types[$extension] != 'video' && $size > $max_file_size)){
                //     return response()->json([
                //         'error' => "File size exceeds the maximum limit of ". (int) $max_video_size / (1024*1024) ." MB."
                //     ], 413);
                // }

                // Return MIME type ala mimetype extension
                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                // Get the MIME type of the file
                $file_mime = finfo_file($finfo, base_path('public/') . $path);

                if ($types[$extension] == 'image' && in_array($extension, ['jpg', 'jpeg', 'png']) && !get_setting('disable_image_optimization', 0)) {
                    try {
                        $img = Image::make($request->file('aiz_file')->getRealPath());

                        // Convert to WebP if setting is enabled and it's a convertible format
                        if (get_setting('convert_images_to_webp', 0)) {
                            unlink(base_path('public/') . $path); // Remove old file if exists

                            $img->encode('webp', 90); // Convert to WebP with 90% quality
                            $path = 'uploads/all/' . pathinfo($request->file('aiz_file')->hashName(), PATHINFO_FILENAME) . '.webp';
                            $extension = 'webp';

                        }else{
                            $img->encode();
                        }

                        // Image optimization (resizing)
                        $height = $img->height();
                        $width = $img->width();
                        if ($width > $height && $width > 1500) {
                            $img->resize(1500, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        } elseif ($height > 1500) {
                            $img->resize(null, 800, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        }

                        $img->save(base_path('public/') . $path);
                        clearstatcache();
                        $size = $img->filesize();

                    } catch (\Exception $e) {
                        Log::error('Image optimization failed: ' . $e->getMessage());
                    }
                }

                if (($types[$extension] == 'video' && get_setting('video_file_driver', 'local') == 's3') || (env('FILESYSTEM_DRIVER', 'local') == 's3' && $types[$extension] != 'video')) {
                    // Log::channel('custom')->info('Uploading to S3: ' . $path);
                    // Upload to S3
                    Storage::disk('s3')->put($path, file_get_contents(base_path('public/') . $path));
                    if ($arr[0] != 'updates') {
                        unlink(base_path('public/') . $path);
                    }
                }

                $upload->extension = $extension;
                $upload->file_name = $path;
                $upload->user_id = Auth::id();
                $upload->type = $types[$upload->extension];
                $upload->file_size = $size;
                $upload->save();

                if ($types[$extension] == 'video' && get_setting('compress_videos', 0) == 1) {
                    CompressVideoJob::dispatch($upload);
                }

                return response()->json([
                    'id' => $upload->id,
                    'file_original_name' => $upload->file_original_name,
                    'file_name' => $upload->file_name,
                    'file_size' => $upload->file_size,
                    'file_url' => uploaded_asset($upload->id),
                ]);
            }
            // return $upload?->toJson() ?? '{}';
            return response()->json([
                'error' => 'File type not allowed'
            ], 500);
        } else {
            return response()->json([
                'message' => 'No file provided or file too large!',
            ], 500);
        }
    }

    public function get_uploaded_files(Request $request)
    {
        $uploads = Upload::query()
            ->when(Auth::check() && Auth::user()->user_type === 'customer', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->when(filled($request->type) && $request->type !== 'all', function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when(filled($request->search), function ($q) use ($request) {
                $q->where('file_original_name', 'like', "%{$request->search}%");
            });

        $sortMap = [
            'newest'   => ['created_at', 'desc'],
            'oldest'   => ['created_at', 'asc'],
            'smallest' => ['file_size', 'asc'],
            'largest'  => ['file_size', 'desc'],
        ];

        [$column, $direction] = $sortMap[$request->sort ?? 'newest'] ?? ['created_at', 'desc'];

        return $uploads
            ->orderBy($column, $direction)
            ->paginate(60)
            ->appends(request()->query());

    }

    public function destroy(Request $request, $id)
    {
        $upload = Upload::findOrFail($id);

        if (auth()->user()->user_type == 'seller' && $upload->user_id != auth()->user()->id) {
            flash(("You don't have permission for deleting this!"))->error();
            return back();
        }
        try {
            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->delete($upload->file_name);
                if (file_exists(public_path() . '/' . $upload->file_name)) {
                    unlink(public_path() . '/' . $upload->file_name);
                }
            } else {
                unlink(public_path() . '/' . $upload->file_name);
            }
            $upload->forceDelete();
            flash('File deleted successfully')->success();
        } catch (\Exception $e) {
            // dd($e->getMessage());
            $upload->delete();
            flash('File deleted successfully')->success();
        } finally {
            Cache::forget('uni_uploaded_file_' . $id);
        }
        return back();
    }

    public function get_preview_file(Request $request)
    {
        $file = Upload::find($request->id);

        return $file;
    }

    public function get_preview_files(Request $request)
    {
        $ids = explode(',', $request->ids);
        $files = Upload::whereIn('id', $ids)->get();
        return $files;
    }

    //Download project attachment
    public function attachment_download($id)
    {
        $project_attachment = Upload::find($id);
        try {
            $file_path = public_path($project_attachment->file_name);
            return Response::download($file_path);
        } catch (\Exception $e) {
            flash(('File does not exist!'))->error();
            return back();
        }

    }

    //Download project attachment
    public function file_info(Request $request)
    {
        $file = Upload::findOrFail($request['id']);

        return (auth()->user()->user_type == 'seller')
        ? view('frontend.user.seller.uploads.info', compact('file'))
        : view('backend.uploaded_files.info', compact('file'));
    }

    protected function getTypes()
    {
        return [
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
            "mp4" => "video",
            "mpg" => "video",
            "mpeg" => "video",
            "webm" => "video",
            "ogg" => "video",
            "avi" => "video",
            "mov" => "video",
            "flv" => "video",
            "swf" => "video",
            "mkv" => "video",
            "wmv" => "video",
            "wma" => "audio",
            "aac" => "audio",
            "wav" => "audio",
            "mp3" => "audio",
            "zip" => "archive",
            "rar" => "archive",
            "7z" => "archive",
            "doc" => "document",
            "txt" => "document",
            "docx" => "document",
            "pdf" => "document",
            "csv" => "document",
            "xml" => "document",
            "ods" => "document",
            "xlr" => "document",
            "xls" => "document",
            "xlsx" => "document",
        ];
    }
}
