<?php

namespace Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/** Storage esclusivo del singolo caso, nel checkout attestato. */
final class IsolatedTestStorage
{
    private static array $owned = [];

    public static function create($app): string
    {
        $info = TestingEnvironment::requireIsolatedRuntime();
        $root = $info['checkout'].'/storage/framework/testing/cases/'.bin2hex(random_bytes(16));
        (new Filesystem)->makeDirectory($root, 0700, true);
        self::$owned[$root] = true;
        $app['config']->set('cache.stores.file.path', $root.'/cache');
        $app['config']->set('cache.stores.file.lock_path', $root.'/cache');
        \Illuminate\Support\Facades\Cache::forgetDriver('file');
        foreach (['local', 'public'] as $disk) {
            $app['config']->set('filesystems.disks.'.$disk.'.root', $root.'/'.$disk);
            Storage::forgetDisk($disk);
        }

        return $root;
    }

    public static function remove(string $root): void
    {
        $info = TestingEnvironment::requireIsolatedRuntime();
        $base = $info['checkout'].'/storage/framework/testing/cases';
        if (! isset(self::$owned[$root]) || dirname($root) !== $base || ! preg_match('/^[a-f0-9]{32}$/D', basename($root))
            || realpath($root) !== $root || is_link($root)) {
            TestingEnvironment::fail('pulizia storage fuori dal caso attestato');
        }
        (new Filesystem)->deleteDirectory($root);
        unset(self::$owned[$root]);
    }
}
