<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ChunkUploadController extends Controller
{
    /**
     * Handle a chunked file upload.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'resumableFilename' => 'required|string',
            'resumableIdentifier' => 'required|string',
            'resumableChunkNumber' => 'required|integer',
            'resumableTotalChunks' => 'required|integer',
        ]);

        $file = $request->file('file');
        $identifier = $request->input('resumableIdentifier');
        $chunkNumber = (int)$request->input('resumableChunkNumber');
        
        $tempDir = 'staging/' . $identifier;
        
        // Anti-Race Condition Lock for chunks
        $lock = Cache::lock("upload_chunk_{$identifier}_{$chunkNumber}", 10);
        
        if ($lock->get()) {
            try {
                $file->storeAs($tempDir, $chunkNumber . '.part');
                
                // If it's the last chunk, we might want to return the token.
                // The actual merge happens in Ticket creation inside DB::transaction()
                
                return response()->json(['success' => true, 'temp_token' => $identifier]);
            } finally {
                $lock->release();
            }
        }
        
        return response()->json(['error' => 'Concurrent upload error'], 429);
    }
}
