<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;

class FileSystemController extends Controller
{

    private $root;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->root = env("WORKPLACE_HOSTING_ROOTPATH");
    }

    /**
     * Get directory list
     */
    public function list(Request $request) {
        try {
            $dirpath = $this->getDirPath($request);
            $files = scandir( $this->reelPath($dirpath), SCANDIR_SORT_ASCENDING );
            if (!$files) {
                return response()->json([]);
            }
            $files = array_map(function ($m) {
                    return ["path" => $m, "name" => basename($m), "type" => is_dir($m) ? 0 : 1 ];
                }, array_values(array_filter($files, function ($m) {
                        return !in_array($m, [".", ".."]);
                    })
                )
            );
            return response()->json($files);
        } catch (\Throwable $th) {
            return response()->setStatusCode(500);
        }
    }

    /**
     * Get file conent
     */
    public function content(Request $request) {
        try {
            $jdata = $request->json()->all();
            $file = $jdata["path"];
            $content = file_get_contents( $this->reelPath($file) );
            return response()->json(["content" => $content]);
        } catch (\Throwable $th) {
            return response()->setStatusCode(500);
        }

    }

    /**
     * Set file content
     */
    public function setContent(Request $request) {
        try {
            $jdata = $request->json()->all();
            $path = $jdata["path"];
            $content = $jdata["content"];
            $create = $jdata["create"];
            $overwrite = $jdata["overwrite"];
            if ((file_exists($this->reelPath($path)) && $overwrite) || (!file_exists($this->reelPath($path)) && $create)) {
                file_put_contents($this->reelPath($path), $content);
                return response()->json(["status" => true]);
            }
            return response()->json(["status" => false]);
        } catch (\Throwable $th) {
            return response()->setStatusCode(500);
        }
    }

    /**
     * Create directory
     */
    public function createdir(Request $request) {
        try {
            $dirpath = $this->getDirPath($request);
            if (mkdir($this->reelPath($dirpath))) {
                return response()->json(["status" => true]);
            } else {
                return response()->json(["status" => false]);
            }
        } catch (\Throwable $th) {
            return response()->setStatusCode(500);
        }
    }

    /**
     * Rename directory
     */
    public function renamedir(Request $request) {
        try {
            $jdata = $request->json()->all();
            $dirpath = $this->getDirPath($request);
            $newname = dirname($dirpath).$jdata["newpath"];
            if (rename($this->reelPath($dirpath), $this->reelPath($newname))) {
                return response()->json(["status" => true]);
            } else {
                return response()->json(["status" => false]);
            }
        } catch (\Throwable $th) {
            return response()->setStatusCode(500);
        }
    }

    /**
     * Copy directory
     */
    public function copydir(Request $request) {
        try {
            $jdata = $request->json()->all();
            $dirpath = $this->getDirPath($request);
            $targetpath = $jdata["targetpath"];
            $filesystem = new Filesystem();
            if ($filesystem->copyDirectory($this->reelPath($dirpath), $this->reelPath("/".$targetpath))) {
                return response()->json(["status" => true]);
            } else {
                return response()->json(["status" => false]);
            }
        } catch (\Throwable $th) {
            return response()->setStatusCode(500);
        }
    }

    private function getDirPath(Request $request) {
        $jdata = $request->json()->all();
        $dirpath = "/".$jdata["path"];
        return $dirpath;
    }
    private function reelPath($path) {
        return base_path().$this->root.$path;
    }
}
