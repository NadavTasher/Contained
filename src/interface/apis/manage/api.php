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
                    // Initialize the object
                    $base64 = null;
                    // Try to archive
                    try {
                        // Create a temporary file name
                        $file = tempnam(null, "tar");
                        // Initialize the archive
                        $archive = new PharData($file);
                        // Add the whole directory
                        $archive->buildFromDirectory(self::CONTENTS_DIRECTORY);
                        // Return a base64 representation
                        $base64 = base64_encode(file_get_contents($file));
                    } catch (Exception $exception) {
                    }
                    // Make sure we got the archive
                    if ($base64 !== null) {
                        return [true, $base64];
                    }
                    return [false, "Archive creation failure"];
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
                } else if ($action === "lock") {
                    $hooks_file = API::directory("authenticate") . DIRECTORY_SEPARATOR . "configuration" . DIRECTORY_SEPARATOR . "hooks.json";
                    $hooks = json_decode(Authenticate::)
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
}