<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Cart;
use App\Models\City;
use App\Models\User;
use App\Models\Order;
use App\Models\Upload;
use App\Models\Address;
use App\Models\Country;
use App\Models\Wishlist;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\V2\CitiesCollection;
use App\Http\Resources\V2\AddressCollection;
use App\Http\Resources\V2\CountriesCollection;

class ProfileController extends Controller
{
    public function counters($user_id)
    {
        return response()->json([
            'cart_item_count' => Cart::where('user_id', $user_id)->count(),
            'wishlist_item_count' => Wishlist::where('user_id', $user_id)->count(),
            'order_count' => Order::where('user_id', $user_id)->count(),
        ]);
    }

    public function update(Request $request)
    {
        try {
            $dob = $request->dob;
            if (is_numeric($dob) && strlen((string) $dob) === 13) {
                // It's a timestamp in milliseconds
                $date = \Carbon\Carbon::createFromTimestampMs($dob)->format('Y-m-d');
            } elseif (is_numeric($dob)) {
                // It's a timestamp in seconds
                $date = \Carbon\Carbon::createFromTimestamp($dob)->format('Y-m-d');
            } else {
                // It's a date string
                $date = \Carbon\Carbon::parse($dob)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => "Invalid date of birth. Please use a valid date."
            ]);
        }
        $user = User::find($request->id);

        $user->name = $request->name;

        if ($request->password != "") {
            $user->password = bcrypt($request->password);
        }

        if ($request->gender != "") {
            $user->gender = $request->gender;
        }

        if ($request->dob != "") {
            $user->date_of_birth = $date;
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => ("Profile information updated")
        ]);
    }

    public function update_device_token(Request $request)
    {

        $user = User::find($request->id);

        $user->device_token = $request->device_token;


        $user->save();

        return response()->json([
            'result' => true,
            'message' => ("device token updated")
        ]);
    }

    public function updateImageOld(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [];
            foreach ($errors->all() as $error) {
                $response[] = $error;
            }
            return response()->json([
                'result' => false,
                'message' => implode(' ', $response),
                'path' => ""
            ]);
        }

        // $image = base64_encode(file_get_contents($request->file('image')->path()));
        // $f = finfo_open();

        // $realImage = base64_decode($image);
        // $mime_type = finfo_buffer($f, $realImage, FILEINFO_MIME_TYPE);
        // return $mime_type;

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $imageSize = strlen($realImage) ?? 300000;
            if($imageSize > 300000){
                return response()->json([
                    'result' => false,
                    'message' => "Image size can't be more than 300 kilobytes. You uploaded ".($imageSize/1000)." kilobytes",
                    'path' => ""
                ]);
            }

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => ""
                ]);
            }

            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => ""
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = (Auth::guard('api')->check() ?
            Auth::guard('api')->id() . '_' . str_replace(' ','',strtolower(Auth::guard('api')->user()->name)) :
            'unknown_user_' . strtolower(Str::random(25))) . '.' . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => ""
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $request->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            $user  = User::find($request->id);
            $user->avatar_original = $upload->id;
            $user->save();

            return response()->json([
                'result' => true,
                'message' => ("Image updated"),
                'path' => api_asset($upload->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => ""
            ]);
        }
    }

    public function updateImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'nullable|string',
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [];
            foreach ($errors->all() as $error) {
                $response[] = $error;
            }
            return response()->json([
                'result' => false,
                'message' => implode(' ', $response),
                'path' => ""
            ]);
        }

        if(!isBase64Image($request->input('image'))){
            $file = $request->file('image');
            $mime = $file->getMimeType();

            $imageData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->path()));
        } else {
            $imageData = $request->input('image');
        }

        // Validate & extract base64
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $extension = strtolower($type[1]);
            $imageData = str_replace(' ', '+', $imageData);
            $image = base64_decode($imageData);
        } else {
            return response()->json([
                'result' => false,
                'message' => 'Invalid image data provided.',
                'path' => ""
            ], 422);
        }

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        // Validate file extension
        if (!isset($type[$extension])) {
            return response()->json([
                'result' => false,
                'message' => 'Only image files are allowed (jpg, jpeg, png, svg, webp, gif).',
                'path' => ""
            ], 422);
        }

        // Check decoded data size (bytes → MB)
        $fileSize = strlen($image); // bytes
        $maxSize = get_setting('max_file_size', 10) * 1024 * 1024; // 10 MB
        if ($fileSize > $maxSize) {
            return response()->json([
                'result' => false,
                'message' => 'File exceeds maximum size of ' . get_setting('max_file_size', 10) . 'MB.'
            ], 422);
        }

        // Generate file details
        $fileName = Auth::guard('api')->check() ?
            Auth::guard('api')->id() . '_' . str_replace(' ','',strtolower(Auth::guard('api')->user()->name)) :
            'unknown_user_' . strtolower(Str::random(25));
        $filePath = 'uploads/all/' . $fileName . '.' . $extension;

        // Default file storage driver
        $defaultDisk = config('filesystems.default', 'local');
        Storage::disk($defaultDisk)->put($filePath, $image);
        $fileSize = Storage::disk($defaultDisk)->size($filePath);

        // Save info to uploads table
        $upload = new Upload();
        $upload->file_original_name = $fileName;
        $upload->file_name = $filePath;
        $upload->user_id = $request->id ?? null;
        $upload->file_size = $fileSize;
        $upload->extension = $extension;
        $upload->type = 'image';
        $upload->save();

        $user = User::find($request->id ?? 0);
        if ($user) {
            $user->avatar_original = $upload->id;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully.',
            'path' => api_asset($upload->id)
        ]);
    }

    // not user profile image but any other base 64 image through uploader
    public function imageUpload(Request $request)
    {

        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
        );

        try {
            $image = $request->image;
            $request->filename;
            $realImage = base64_decode($image);

            $dir = public_path('uploads/all');
            $full_path = "$dir/$request->filename";

            $file_put = file_put_contents($full_path, $realImage); // int or false

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "File uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload = new Upload;
            $extension = strtolower(File::extension($full_path));
            $size = File::size($full_path);

            if (!isset($type[$extension])) {
                unlink($full_path);
                return response()->json([
                    'result' => false,
                    'message' => "Only image can be uploaded",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }


            $upload->file_original_name = null;
            $arr = explode('.', File::name($full_path));
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0) {
                    $upload->file_original_name .= $arr[$i];
                } else {
                    $upload->file_original_name .= "." . $arr[$i];
                }
            }

            //unlink and upload again with new name
            unlink($full_path);
            $newFileName = (Auth::guard('api')->check() ?
            Auth::guard('api')->id() . '_' . str_replace(' ','',strtolower(Auth::guard('api')->user()->name)) :
            'unknown_user_' . strtolower(Str::random(25))) . '.' . $extension;
            $newFullPath = "$dir/$newFileName";

            $file_put = file_put_contents($newFullPath, $realImage);

            if ($file_put == false) {
                return response()->json([
                    'result' => false,
                    'message' => "Uploading error",
                    'path' => "",
                    'upload_id' => 0
                ]);
            }

            $newPath = "uploads/all/$newFileName";

            if (env('FILESYSTEM_DRIVER') == 's3') {
                Storage::disk('s3')->put($newPath, file_get_contents(base_path('public/') . $newPath));
                unlink(base_path('public/') . $newPath);
            }

            $upload->extension = $extension;
            $upload->file_name = $newPath;
            $upload->user_id = $request->id;
            $upload->type = $type[$upload->extension];
            $upload->file_size = $size;
            $upload->save();

            return response()->json([
                'result' => true,
                'message' => ("Image updated"),
                'path' => api_asset($upload->id),
                'upload_id' => $upload->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
                'path' => "",
                'upload_id' => 0
            ]);
        }
    }

    public function checkIfPhoneAndEmailAvailable(Request $request)
    {


        $phone_available = false;
        $email_available = false;
        $phone_available_message = ("User phone number not found");
        $email_available_message = ("User email  not found");

        $user = User::find($request->user_id);

        if ($user->phone != null || $user->phone != "") {
            $phone_available = true;
            $phone_available_message = ("User phone number found");
        }

        if ($user->email != null || $user->email != "") {
            $email_available = true;
            $email_available_message = ("User email found");
        }
        return response()->json(
            [
                'phone_available' => $phone_available,
                'email_available' => $email_available,
                'phone_available_message' => $phone_available_message,
                'email_available_message' => $email_available_message,
            ]
        );
    }
}
