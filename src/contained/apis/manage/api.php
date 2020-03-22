<?php

/**
 * Copyright (c) 2020 Nadav Tasher
 * https://github.com/NadavTasher/SelfContained/
 **/

// Include Base API
include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "base" . DIRECTORY_SEPARATOR . "api.php";

// Include Authentication API
include_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "authenticate" . DIRECTORY_SEPARATOR . "api.php";

class Manage
{
    // API string
    private const API = "manage";

    // Contents location
    private const CONTENTS_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "contents";

    /**
     * Main API hook.
     */
    public static function init()
    {
        // Return the result so that other APIs could use it.
        return API::handle(self::API, function ($action, $parameters) {
            // Make sure the user is authenticated.
            $userID = Authenticate::init();
            if ($userID !== null) {
                // Make sure signup is locked
                self::lock();
                // Handle request
                if ($action === "import") {
                    if (isset($parameters->file) && is_string($parameters->file)) {
                        $contents = $parameters->file;
                        // Decode contents
                        $decoded = base64_decode($contents);
                        // Try to archive
                        try {
                            // Create a temporary file name
                            $file = self::temporary(".zip");
                            // Write file
                            file_put_contents($file, $decoded);
                            // Initialize the archive
                            $archive = new PharData($file, null, null, Phar::ZIP);
                            // Extract to contents
                            $archive->extractTo(self::CONTENTS_DIRECTORY);
                            // Return a base64 representation
                            return [true, null];
                        } catch (Exception $exception) {
                            return [false, "Archive extraction failure: " . $exception];
                        }
                    }
                    return [false, "Invalid 'files' parameter"];
                } else if ($action === "export") {
                    // Try to archive
                    try {
                        // Create a temporary file name
                        $file = self::temporary(".zip");
                        // Initialize the archive
                        $archive = new PharData($file, null, null, Phar::ZIP);
                        // Add the whole directory
                        $archive->buildFromDirectory(self::CONTENTS_DIRECTORY);
                        // Return a base64 representation
                        return [true, base64_encode(file_get_contents($file))];
                    } catch (Exception $exception) {
                        return [false, "Archive creation failure: " . $exception];
                    }
                } else if ($action === "list") {
                    return [true, self::list()];
                } else if ($action === "remove") {
                    if (isset($parameters->file) && is_string($parameters->file)) {
                        // Create the full path
                        $contents = realpath(self::CONTENTS_DIRECTORY);
                        // Compile path
                        $path = realpath($contents . $parameters->file);
                        // Make sure the path is a sub-path to CONTENTS_DIRECTORY
                        if (strpos($path, $contents) === 0) {
                            // Remove the path
                            self::remove($path);
                        }
                        return [true, null];
                    }
                    return [false, "Invalid 'file' parameter"];
                }
            }
            return [false, "Authentication failure"];
        }, true);
    }

    /**
     * Removes a path.
     * @param string $path Path
     */
    private static function remove($path)
    {
        // List array
        $array = self::list($path);
        // Remove files
        foreach ($array as $path) {
            if ($path[strlen($path) - 1] === DIRECTORY_SEPARATOR) {
                rmdir(self::CONTENTS_DIRECTORY . $path);
            } else {
                unlink(self::CONTENTS_DIRECTORY . $path);
            }
        }
    }

    /**
     * Creates a path tree.
     * @param string $path Path
     * @param array $array Current tree
     * @return array | null List
     */
    private static function list($path = DIRECTORY_SEPARATOR, $array = [])
    {
        $realPath = self::CONTENTS_DIRECTORY . $path;
        if (is_dir($realPath)) {
            foreach (scandir($realPath) as $entry) {
                if ($entry !== "." && $entry !== "..") {
                    $array = self::list($path . $entry, $array);
                }
            }
            array_push($array, $path . DIRECTORY_SEPARATOR);
        } else {
            array_push($array, $path);
        }
        return $array;
    }

    /**
     * Locks the signup endpoint.
     */
    private static function lock()
    {
        $hooks_file = API::directory("authenticate") . DIRECTORY_SEPARATOR . "configuration" . DIRECTORY_SEPARATOR . "hooks.json";
        // Load the file
        $hooks = json_decode(file_get_contents($hooks_file));
        // Modify the settings
        $hooks->signup = false;
        // Write the file
        file_put_contents($hooks_file, json_encode($hooks));
    }

    /**
     * Creates a path for a temporary file.
     * @param string $postfix Path postfix
     * @return string Path
     */
    private static function temporary($postfix = "")
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . "temporary_" . self::random(10) . $postfix;
    }

    /**
     * Creates a random string.
     * @param int $length String length
     * @return string String
     */
    private static function random($length = 0)
    {
        if ($length > 0) {
            return str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz")[0] . self::random($length - 1);
        }
        return "";
    }
}