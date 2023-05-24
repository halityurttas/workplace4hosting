<?php

namespace App\Http\Controllers;

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
            $files = array_values(array_filter($files, function ($m) {return !in_array($m, [".", ".."]); }));
            return response()->json(["status" => true, "files" => $files]);
        } catch (\Throwable $th) {
            return response()->json(["status" => false, "files" => []]);
        }
    }

    /**
     * Get file conent
     */
    public function content(Request $request) {
        try {
            $jdata = $request->json()->all();
            $file = $jdata["fl"];
            $content = file_get_contents( $this->reelPath($file) );
            return response()->json(["status" => true, "content" => $content]);
        } catch (\Throwable $th) {
            return response()->json(["status" => false, "message" => $th->getMessage()]);
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
            return response()->json(["status" => false, "message" => $th->getMessage()]);
        }
    }

    /**
     * Rename directory
     */
    public function renamedir(Request $request) {
        $dirpath = $this->getDirPath($request);
        $newname = dirname($dirpath);
    }

    private function getDirPath(Request $request) {
        $jdata = $request->json()->all();
        $dirpath = "/".$jdata["dir"];
        return $dirpath;
    }
    private function reelPath($path) {
        return base_path().$this->root.$path;
    }
}
