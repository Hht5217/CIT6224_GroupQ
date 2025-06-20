<?php
// Helper function to format file size
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to convert size with unit to bytes
function convertToBytes($sizeStr)
{
    $lastChar = strtoupper(substr($sizeStr, -1));
    $numericPart = (int) substr($sizeStr, 0, -1);
    switch ($lastChar) {
        case 'G':
            return $numericPart * 1024 * 1024 * 1024;
        case 'M':
            return $numericPart * 1024 * 1024;
        case 'K':
            return $numericPart * 1024;
        default:
            return $numericPart; // Assume bytes if no unit
    }
}

// Validate and upload file
function uploadFile($file, $upload_dir, $allowed_types, $max_size_mb)
{
    $error = null;
    $filepath = null;
    $max_size = $max_size_mb * 1024 * 1024; // Convert MB to bytes

    // Check PHP upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['error' => "File size too large. Maximum size is {$max_size_mb}MB", 'filepath' => null, 'filename' => null, 'file_type' => null, 'file_size' => null];
            case UPLOAD_ERR_NO_FILE:
                return ['error' => 'No file was uploaded', 'filepath' => null, 'filename' => null, 'file_type' => null, 'file_size' => null];
            default:
                return ['error' => 'Upload error occurred', 'filepath' => null, 'filename' => null, 'file_type' => null, 'file_size' => null];
        }
    }

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        $error = 'Invalid file type. Supported types: ' . implode(', ', array_map(function ($type) {
            return strtoupper(pathinfo('file.' . str_replace('application/', '', str_replace('image/', '', str_replace('video/', '', str_replace('audio/', '', str_replace('text/', '', $type))))), PATHINFO_EXTENSION));
        }, $allowed_types));
    } elseif ($file['size'] > $max_size) {
        $error = "File size too large. Maximum size is {$max_size_mb}MB";
    } else {
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $error = 'Error uploading file';
            $filepath = null;
        }
    }

    return [
        'error' => $error,
        'filepath' => $filepath,
        'filename' => $file['name'],
        'file_type' => $file['type'],
        'file_size' => $file['size']
    ];
}

// Delete resource and update associated talent if necessary
function deleteResource($conn, $resource_id, $user_id)
{
    $stmt = $conn->prepare("SELECT file_path, talent_id FROM resources WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resource_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();

    if ($resource) {
        // If linked to a talent, update the talent
        if (!empty($resource['talent_id'])) {
            $stmt = $conn->prepare("UPDATE talents SET media_path = 'deleted', is_downloadable = 0 WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $resource['talent_id'], $user_id);
            $stmt->execute();
        }

        // Delete file from server
        if (file_exists($resource['file_path'])) {
            unlink($resource['file_path']);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM resources WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $resource_id, $user_id);
        $stmt->execute();
        return true;
    }
    return false;
}
?>