<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostUploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string|max:120',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1|max:2000',
            'filename' => 'required|string|max:255',
            'mime_type' => 'required|string|max:120',
            'chunk' => 'required|file|max:12288',
        ]);

        $uploadId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $request->input('upload_id'));
        $chunkIndex = (int) $request->input('chunk_index');

        if (!$uploadId) {
            return response()->json(['message' => 'Upload ID khong hop le.'], 422);
        }

        $chunkDir = 'post_chunks/' . $uploadId;
        $request->file('chunk')->storeAs($chunkDir, 'chunk_' . $chunkIndex . '.part', 'local');

        return response()->json([
            'ok' => true,
            'chunk_index' => $chunkIndex,
        ]);
    }

    public function completeUpload(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string|max:120',
            'total_chunks' => 'required|integer|min:1|max:2000',
            'filename' => 'required|string|max:255',
            'mime_type' => 'required|string|max:120',
        ]);

        $uploadId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $request->input('upload_id'));
        $totalChunks = (int) $request->input('total_chunks');
        $filename = (string) $request->input('filename');
        $mimeType = (string) $request->input('mime_type');

        if (!$uploadId) {
            return response()->json(['message' => 'Upload ID khong hop le.'], 422);
        }

        $chunkDir = 'post_chunks/' . $uploadId;

        $missingChunks = [];
        for ($i = 0; $i < $totalChunks; $i++) {
            if (!Storage::disk('local')->exists($chunkDir . '/chunk_' . $i . '.part')) {
                $missingChunks[] = $i;
            }
        }

        if (!empty($missingChunks)) {
            return response()->json([
                'message' => 'Thieu chunk du lieu, vui long thu tai lai.',
                'missing_chunk' => $missingChunks[0],
                'missing_chunks' => $missingChunks,
            ], 422);
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!$extension) {
            $extension = Str::startsWith($mimeType, 'video/') ? 'mp4' : 'jpg';
        }

        $finalRelativePath = 'posts/' . Str::uuid()->toString() . '.' . $extension;
        $finalAbsolutePath = storage_path('app/public/' . $finalRelativePath);
        $finalDir = dirname($finalAbsolutePath);

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $outHandle = fopen($finalAbsolutePath, 'wb');

        if ($outHandle === false) {
            return response()->json(['message' => 'Khong the gop tep video.'], 500);
        }

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkAbsolutePath = storage_path('app/' . $chunkDir . '/chunk_' . $i . '.part');
            $inHandle = fopen($chunkAbsolutePath, 'rb');

            if ($inHandle === false) {
                fclose($outHandle);
                return response()->json(['message' => 'Khong the doc chunk du lieu.'], 500);
            }

            stream_copy_to_stream($inHandle, $outHandle);
            fclose($inHandle);
        }

        fclose($outHandle);
        Storage::disk('local')->deleteDirectory($chunkDir);

        return response()->json([
            'ok' => true,
            'media_path' => $finalRelativePath,
            'media_type' => Str::startsWith($mimeType, 'video/') ? 'video' : 'image',
        ]);
    }
}
