<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['cropped_image']) && $_FILES['cropped_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['cropped_image']['tmp_name'];
        $fileName = uniqid('profile_') . '.png';
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $fileName;

        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        // Move the uploaded file to the destination path
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Load the uploaded image
            $croppedImage = imagecreatefrompng($dest_path);
            if (!$croppedImage) {
                echo json_encode(['error' => 'Failed to create cropped image resource.']);
                exit;
            }

            // Get dimensions of the uploaded image
            $croppedWidth = imagesx($croppedImage);
            $croppedHeight = imagesy($croppedImage);

            // Make the image square (cropping from center)
            $size = min($croppedWidth, $croppedHeight);
            $croppedSquareImage = imagecreatetruecolor($size, $size);
            imagesavealpha($croppedSquareImage, true);
            $transparent = imagecolorallocatealpha($croppedSquareImage, 255, 255, 255, 127);  // Transparent background
            imagefill($croppedSquareImage, 0, 0, $transparent);

            // Center crop the image into the square
            $srcX = ($croppedWidth - $size) / 2;
            $srcY = ($croppedHeight - $size) / 2;
            imagecopyresampled($croppedSquareImage, $croppedImage, 0, 0, $srcX, $srcY, $size, $size, $size, $size);

            // Apply circular cropping using a mask
            $mask = imagecreatetruecolor($size, $size);
            imagesavealpha($mask, true);
            $transparent = imagecolorallocatealpha($mask, 255, 255, 255, 127);  // Ensure transparency for the mask
            imagefill($mask, 0, 0, $transparent);
            $black = imagecolorallocate($mask, 0, 0, 0);
            imagefilledellipse($mask, $size / 2, $size / 2, $size, $size, $black);
            imagecolortransparent($mask, $black);  // Make the black part transparent

            // Merge the circular mask with the cropped image
            imagecopymerge($croppedSquareImage, $mask, 0, 0, 0, 0, $size, $size, 100);

            // Load the frame image
            $frameImagePath = './images/rounded_frame.png';
            if (!file_exists($frameImagePath)) {
                echo json_encode(['error' => 'Frame image not found.']);
                exit;
            }
            $frameImage = imagecreatefrompng($frameImagePath);
            if (!$frameImage) {
                echo json_encode(['error' => 'Failed to create frame image resource.']);
                exit;
            }

            // Get dimensions of the frame
            $frameWidth = imagesx($frameImage);
            $frameHeight = imagesy($frameImage);

            // **Determine the size of the inner circle** of the frame
            // Assuming the inner circle's diameter should be slightly smaller to avoid overlap
            $innerCircleDiameter = min($frameWidth, $frameHeight) * 0.70;  // Adjust this to fit well within the frame circle

            // Resize the circular cropped image to match the inner circle diameter
            $resizedCroppedImage = imagecreatetruecolor($innerCircleDiameter, $innerCircleDiameter);
            imagesavealpha($resizedCroppedImage, true);
            imagefill($resizedCroppedImage, 0, 0, $transparent);  // Ensure transparency is maintained
            imagecopyresampled($resizedCroppedImage, $croppedSquareImage, 0, 0, 0, 0, $innerCircleDiameter, $innerCircleDiameter, $size, $size);

            // Create a final image with the same size as the frame
            $finalImage = imagecreatetruecolor($frameWidth, $frameHeight);
            imagesavealpha($finalImage, true);
            imagefill($finalImage, 0, 0, $transparent);

            // Center the resized cropped circular image within the inner circle of the frame
            $destX = ($frameWidth - $innerCircleDiameter) / 2;
            $destY = ($frameHeight - $innerCircleDiameter) / 2;
            imagecopy($finalImage, $resizedCroppedImage, $destX, $destY, 0, 0, $innerCircleDiameter, $innerCircleDiameter);

            // Overlay the frame on top of the final image
            imagecopy($finalImage, $frameImage, 0, 0, 0, 0, $frameWidth, $frameHeight);

            // Save the final combined image
            $outputPath = $uploadFileDir . 'rounded_framed_' . $fileName;
            imagepng($finalImage, $outputPath);

            // Cleanup resources
            imagedestroy($croppedImage);
            imagedestroy($croppedSquareImage);
            imagedestroy($mask);
            imagedestroy($resizedCroppedImage);
            imagedestroy($frameImage);
            imagedestroy($finalImage);

            // Return the path of the final image
            echo json_encode(['imagePath' => $outputPath]);
        } else {
            echo json_encode(['error' => 'There was an error moving the uploaded file.']);
        }
    } else {
        echo json_encode(['error' => 'No image uploaded or an error occurred.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}