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
        $dirpath = $this->getDirPath($request);

        $files = scandir( base_path().$this->root.$dirpath, SCANDIR_SORT_ASCENDING );
        if (!$files) {
            return response()->json([]);
        }
        $files = array_values(array_filter($files, function ($m) {return !in_array($m, [".", ".."]); }));
        return response()->json(["status" => true, "files" => $files]);
    }

    /**
     * Get file conent
     */
    public function content(Request $request) {
        $file = "/".$request->input("fl");
        try {
            $content = file_get_contents( base_path().$this->root.$file );
            return response()->json(["status" => true, "content" => $content]);
        } catch (\Throwable $th) {
            return response()->json(["status" => false, "message" => $th->getMessage()]);
        }

    }

    /**
     * Create directory
     */
    public function createdir(Request $request) {
        $dirpath = $this->getDirPath($request);
        if (base_path().$this->root.mkdir($dirpath)) {
            return response()->json(["status" => true]);
        } else {
            return response()->json(["status" => false]);
        }
    }


    private function getDirPath(Request $request) {
        $dirpath = $request->input("dir");
        if (!$dirpath) {
            $dirpath = "/";
        } else {
            $dirpath = "/" . $dirpath;
        }
        return $dirpath;
    }

}
