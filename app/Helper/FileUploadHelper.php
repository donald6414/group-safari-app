<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileUploadHelper
{
	/**
	 * Upload and save file (image or PDF) with proper folder organization
	 *
	 * @param UploadedFile $file - The uploaded file
	 * @param string $uploadPath - Base path where files should be stored (e.g., "uploads")
	 * @param string $prefix - Prefix for folder organization (e.g., "payment-receipts")
	 * @return string - Returns the storage path (e.g., "storage/uploads/payment-receipts/images/unique_filename.jpg")
	 */
	public static function uploadAndSaveFile(UploadedFile $file, string $uploadPath, string $prefix): string
	{
		// Clean and normalize paths
		$uploadPath = trim($uploadPath, '/');
		$prefix = trim($prefix, '/');

		// Get file MIME type and extension
		$mimeType = $file->getMimeType();
		$originalExtension = $file->getClientOriginalExtension();

		// Determine if file is an image or PDF
		$isImage = strpos($mimeType, 'image/') === 0;
		$isPdf = $mimeType === 'application/pdf';

		if (!$isImage && !$isPdf) {
			throw new \InvalidArgumentException('File must be an image (JPEG, PNG, GIF) or PDF');
		}

		// Determine folder type (images or pdfs)
		$folderType = $isImage ? 'images' : 'pdfs';

		// Build the full storage path: uploadPath/prefix/folderType/
		$storagePath = $uploadPath . '/' . $prefix . '/' . $folderType;

		// Generate unique filename: rand() + uniqid() + extension
		$uniqueId = rand(100000, 999999) . '_' . uniqid();
		$fileName = $uniqueId . '.' . $originalExtension;

		// Full file path for storage
		$fullFilePath = $storagePath . '/' . $fileName;

		// Store the file using Laravel Storage
		Storage::disk('public')->put($fullFilePath, file_get_contents($file->getRealPath()));

		// Return the storage path that can be used in the application
		return 'storage/' . $fullFilePath;
	}
}