<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

class TestRunnerController extends Controller
{
    public function index(): View
    {
        return view('dev.test-runner');
    }

    public function run(Request $request): Response
    {
        $filter = $request->input('filter');

        $cmd = ['php', 'artisan', 'test', '--no-coverage', '--colors=never'];

        if ($filter) {
            $cmd[] = '--filter';
            $cmd[] = $filter;
        }

        $process = new Process($cmd, base_path());
        $process->setTimeout(300);
        $process->run();

        $output = $process->getOutput() . $process->getErrorOutput();
        $exitCode = $process->getExitCode();

        return response($output, 200, ['Content-Type' => 'text/plain'])
            ->header('X-Exit-Code', $exitCode);
    }
}
