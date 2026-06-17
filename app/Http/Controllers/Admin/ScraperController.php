<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScraperJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScraperController extends Controller
{
    public function concertIndex()
    {
        return Inertia::render('Admin/Concert/Scraper');
    }

    public function artistIndex()
    {
        return Inertia::render('Admin/Artist/Scraper');
    }

    public function highlightIndex()
    {
        return Inertia::render('Admin/Highlight/Scraper'); 
    }

    public function run(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string|in:concert,artist',
            'target_website' => 'nullable|string|in:allticket,theconcert,ticketier,highlight',
            'artist_source' => 'nullable|string|in:api,file',
            'artist_file' => 'nullable|file|mimes:json',
        ]);

        $filePath = null;
        if ($request->hasFile('artist_file')) {
            $file = $request->file('artist_file');

            $destinationPath = storage_path('app/scraper_uploads');
            $fileName = $file->hashName();

            $file->move($destinationPath, $fileName);

            $filePath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;
        }

        $job = ScraperJob::create([
            'target_type' => $request->target_type,
            'target_website' => $request->target_website,
            'status' => 'running',
            'progress' => 0,
            'results' => []
        ]);

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $pythonPath = $isWindows
            ? base_path('scraper' . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe')
            : base_path('scraper/.venv/bin/python');

        $scriptPath = base_path('scraper' . DIRECTORY_SEPARATOR . 'master_runner.py');
        $logPath = storage_path('logs' . DIRECTORY_SEPARATOR . 'scraper_debug.log');

        $websiteArg = $request->target_website ?: "none";
        $fileArg = $filePath ?: "none";
        $args = "{$request->target_type} {$websiteArg} {$job->id} \"{$fileArg}\"";

        if ($isWindows) {
            $batPath = storage_path('logs' . DIRECTORY_SEPARATOR . 'run_scraper.bat');
            
            $batContent = "chcp 65001 > NUL\n"; 
            $batContent .= "set PYTHONIOENCODING=utf-8\n"; 
            $batContent .= "\"{$pythonPath}\" \"{$scriptPath}\" {$args} > \"{$logPath}\" 2>&1\n";
            
            file_put_contents($batPath, $batContent);

            $cmd = "start /B \"\" \"{$batPath}\" > NUL 2>&1";
            pclose(popen($cmd, "r"));
        } else {
            $cmd = "PYTHONIOENCODING=utf-8 \"{$pythonPath}\" \"{$scriptPath}\" {$args} > \"{$logPath}\" 2>&1 &";
            exec($cmd);
        }

        return response()->json(['job_id' => $job->id]);
    }

    public function status(ScraperJob $job)
    {
        return response()->json($job);
    }

    public function updateStatus(Request $request, ScraperJob $job)
    {
        $data = $request->validate([
            'status' => 'nullable|string',
            'progress' => 'nullable|integer',
            'new_result' => 'nullable|string',
            'error_message' => 'nullable|string'
        ]);

        if (isset($data['status'])) $job->status = $data['status'];
        if (isset($data['progress'])) $job->progress = $data['progress'];
        if (isset($data['error_message'])) $job->error_message = $data['error_message'];

        if (isset($data['new_result'])) {
            $results = $job->results ?? [];
            $results[] = $data['new_result'];
            $job->results = $results;
        }

        $job->save();
        return response()->json(['message' => 'updated']);
    }

    public function cancel($id)
    {
        \Illuminate\Support\Facades\DB::table('scraper_jobs')->where('id', $id)->update([
            'status' => 'failed',
            'error_message' => 'Cancelled'
        ]);

        $killSwitchPath = storage_path("logs/cancel_{$id}.txt");
        file_put_contents($killSwitchPath, "STOP_PROCESS");

        return response()->json(['status' => 'success']);
    }
}
