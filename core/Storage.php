<?php

class Storage
{
    protected string $baseDir;

    public function __construct(string $folder = 'uploads', ?string $root = null)
    {
        $storageRoot = $root ?: (defined('MNT_PATH') ? MNT_PATH : STORAGE_PATH);
        $this->baseDir = rtrim($storageRoot, '/\\') . DIRECTORY_SEPARATOR . trim($folder, '/\\');

        if (!is_dir($this->baseDir)) {
            if (!mkdir($this->baseDir, 0775, true) && !is_dir($this->baseDir)) {
                throw new RuntimeException("Failed to create storage directory.");
            }
        }
    }

    public function pathFor(string $filename): string
    {
        $safeName = basename(str_replace('\\', '/', $filename));
        return $this->baseDir . DIRECTORY_SEPARATOR . $safeName;
    }

    public function saveAs(array $file, string $filename): string|false
    {
        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            return false;
        }

        $safeName = basename(str_replace('\\', '/', $filename));
        if ($safeName === '') {
            return false;
        }

        return move_uploaded_file($file['tmp_name'], $this->pathFor($safeName)) ? $safeName : false;
    }

    public function save(array $file, string $ext, string $indicateFilename = ''): string|false
    {
        // Ensure valid uploaded file
        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $ext = strtolower($ext);

        // Generate filename
        ///// longer filename
        // if ($indicateFilename) {
        //     $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $indicateFilename);

        //     if (!$safeName) {
        //         $safeName = bin2hex(random_bytes(8));
        //     }

        //     $filename = rtrim($safeName, '.') . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
        // } else {
        //     $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        // }
        ///// longer filename

        ///// shorter filename
        $safeName = $indicateFilename
            ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $indicateFilename) ?: 'FIL', 0, 3))
            : 'FIL';
        $filename = $safeName . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        ///// shorter filename

        $path = $this->baseDir . '/' . $filename;

        // Prevent extremely rare collision
        while (file_exists($path)) {
            $filename = bin2hex(random_bytes(16)) . '.' . $ext;
            $path = $this->baseDir . '/' . $filename;
        }

        return $this->saveAs($file, $filename);
    }
}