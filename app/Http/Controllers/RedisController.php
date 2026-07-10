<?php

namespace App\Http\Controllers;

use App\Services\SafeCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class RedisController extends Controller
{
    public function testRedis()
    {
        try {
            Redis::set('test_key', 'Hello Redis');
            return Redis::get('test_key');
        } catch (\Exception $e) {
            return "Redis connection failed: " . $e->getMessage();
        }
    }

    public function testTag()
    {
        try {
            Cache::tags(['health-check'])->put('redis:test', [
                'tag'      => 'health-check',
                'timestamp' => now()->toDateTimeString(),
                'random'    => rand(1000, 9999),
            ], 3600);

            $value = Cache::tags(['health-check'])->get('redis:test');

            return response()->json([
                'status' => 'ok',
                'cache' => 'Default',
                'driver' => config('cache.default'),
                'value'  => $value,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function testSafeCache()
    {
        try {
            SafeCache::tags(['health-check'])->put('test-safe-cache', [
                'tag'      => 'health-check',
                'timestamp' => now()->toDateTimeString(),
                'random'    => rand(1000, 9999),
            ], 3600);

            $value = SafeCache::tags(['health-check'])->get('test-safe-cache');

            return response()->json([
                'status' => 'ok',
                'cache' => 'SafeCache',
                'driver' => config('cache.default'),
                'value'  => $value,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function flushTag()
    {
        try {
            Cache::tags(['health-check'])->flush();

            return response()->json([
                'status' => 'ok',
                'message'  => 'Tag flushed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function flushSafeCacheTag()
    {
        try {
            SafeCache::tags(['health-check'])->flush();

            return response()->json([
                'status' => 'ok',
                'message'  => 'Tag flushed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function testSession()
    {
        try {
            // Session::put('redis_session_test', [
            //     'timestamp' => now()->toDateTimeString(),
            //     'random'    => rand(1000, 9999),
            // ]);

            // Session::save(); // Use this for non http requests

            $value = Session::get('redis_session_test');

            return response()->json([
                'status' => 'ok',
                'driver' => config('session.driver'),
                'value'  => $value,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
