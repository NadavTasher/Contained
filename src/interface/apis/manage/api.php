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
                    if (isset($parameters->files) && is_array($parameters->files)) {
                        foreach ($parameters->files as $file) {
                            // Make sure we got an object with the properties
                            if (is_object($file)) {
                                if (isset($file->name) && isset($file->contents)) {
                                    if (is_string($file->name) && is_string($file->contents)) {
                                        // Create the full path
                                        $path = self::CONTENTS_DIRECTORY . DIRECTORY_SEPARATOR . $file->name;
                                        // Simplify the path
                                        $path = realpath($path);
                                        // Make sure the path is a sub-path to CONTENTS_DIRECTORY
                                        if (strpos($path, self::CONTENTS_DIRECTORY . DIRECTORY_SEPARATOR) === 0) {
                                            // Copy the contents
                                            file_put_contents($path, $file->contents);
                                        }
                                    }
                                }
                            }
                        }
                        return [true, null];
                    }
                    return [false, "Invalid 'files' parameter"];
                } else if ($action === "export") {
                    // Try to archive
                    try {
                        // Create a temporary file name
                        $file = self::temporary("export_", ".tar");
                        $file = "file.tar";
                        // Initialize the archive
                        $archive = new PharData($file);
                        // Add the whole directory
                        $archive->buildFromDirectory(self::CONTENTS_DIRECTORY);
                        // Build archive
                        $archive->compress(Phar::NONE);
                        // Return a base64 representation
                        return [true, base64_encode(file_get_contents($file))];
                    } catch (Exception $exception) {
                        return [false, "Archive creation failure: " . $exception];
                    }
                } else if ($action === "list") {
                    return [true, self::tree(self::CONTENTS_DIRECTORY)];
                } else if ($action === "remove") {
                    if (isset($parameters->files) && is_array($parameters->files)) {
                        foreach ($parameters->files as $file) {
                            // Make sure we got a string
                            if (is_string($file)) {
                                // Create the full path
                                $path = self::CONTENTS_DIRECTORY . DIRECTORY_SEPARATOR . $file;
                                // Simplify the path
                                $path = realpath($path);
                                // Make sure the path is a sub-path to CONTENTS_DIRECTORY
                                if (strpos($path, self::CONTENTS_DIRECTORY . DIRECTORY_SEPARATOR) === 0) {
                                    // Remove the path
                                    self::remove($path);
                                }
                            }
                        }
                        return [true, null];
                    }
                    return [false, "Invalid 'files' parameter"];
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
        if (is_file($path)) {
            unlink($path);
        } else {
            if (is_dir($path)) {
                foreach (scandir($path) as $entry) {
                    if ($entry !== "." && $entry !== "..") {
                        self::remove($path . DIRECTORY_SEPARATOR . $entry);
                    }
                }
                rmdir($path);
            }
        }
    }

    /**
     * Creates a path tree.
     * @param string $path Path
     * @return stdClass | null Tree
     */
    private static function tree($path)
    {
        if (is_dir($path)) {
            $tree = new stdClass();
            foreach (scandir($path) as $entry) {
                if ($entry !== "." && $entry !== "..") {
                    $tree->$entry = self::tree($path . DIRECTORY_SEPARATOR . $entry);
                }
            }
            return $tree;
        }
        return null;
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
     * @param string $prefix Path prefix
     * @param string $postfix Path postfix
     * @return string Path
     */
    private static function temporary($prefix = "temporary_", $postfix = "")
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . self::random(10) . $postfix;
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