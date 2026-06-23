<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Runner — {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0d1117; color: #e6edf3; font-family: 'Consolas', 'Monaco', monospace; font-size: 13px; padding: 20px; }
        h1 { font-size: 16px; color: #58a6ff; margin-bottom: 16px; font-weight: 600; }
        .controls { display: flex; gap: 10px; margin-bottom: 16px; align-items: center; flex-wrap: wrap; }
        input[type=text] {
            background: #161b22; border: 1px solid #30363d; color: #e6edf3;
            padding: 6px 12px; border-radius: 6px; font-size: 13px; font-family: monospace;
            width: 280px; outline: none;
        }
        input[type=text]:focus { border-color: #58a6ff; }
        button {
            background: #238636; color: #fff; border: none; padding: 7px 16px;
            border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;
        }
        button:hover { background: #2ea043; }
        button:disabled { background: #333; color: #666; cursor: not-allowed; }
        button.stop { background: #b91c1c; }
        button.stop:hover { background: #dc2626; }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        }
        .badge.running { background: #1d4ed8; color: #bfdbfe; }
        .badge.passed  { background: #166534; color: #bbf7d0; }
        .badge.failed  { background: #7f1d1d; color: #fecaca; }
        #output {
            background: #0d1117; border: 1px solid #30363d; border-radius: 8px;
            padding: 14px; min-height: 400px; max-height: 70vh; overflow-y: auto;
            white-space: pre-wrap; word-break: break-word; line-height: 1.55;
            font-family: 'Consolas', 'Monaco', monospace; font-size: 12.5px;
        }
        .line-ok   { color: #3fb950; }
        .line-fail { color: #f85149; }
        .line-warn { color: #e3b341; }
        .line-info { color: #79c0ff; }
        .line-dim  { color: #8b949e; }
    </style>
</head>
<body>
    <h1>⚗ Test Runner — Local Dev Only</h1>

    <div class="controls">
        <input type="text" id="filter" placeholder="--filter TestClass hoặc method_name" title="Filter">
        <button id="run-btn" onclick="runTests()">▶ Run Tests</button>
        <span id="status-badge"></span>
        <span id="timer" style="color:#8b949e; font-size:12px;"></span>
    </div>

    <div id="output"><span style="color:#8b949e">Nhấn "Run Tests" để bắt đầu...</span></div>

    <script>
    let startTime, timerInterval;

    function colorize(line) {
        if (/PASS|✓|OK \(/.test(line))          return `<span class="line-ok">${esc(line)}</span>`;
        if (/FAIL|FAILED|Error|Exception/.test(line)) return `<span class="line-fail">${esc(line)}</span>`;
        if (/Warning|Deprecated/.test(line))     return `<span class="line-warn">${esc(line)}</span>`;
        if (/Tests:|Assertions:|Duration/.test(line)) return `<span class="line-info">${esc(line)}</span>`;
        if (/^\s*$/.test(line))                  return '';
        if (/^#\d+|^at /.test(line))             return `<span class="line-dim">${esc(line)}</span>`;
        return esc(line);
    }

    function esc(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    async function runTests() {
        const btn    = document.getElementById('run-btn');
        const output = document.getElementById('output');
        const badge  = document.getElementById('status-badge');
        const timer  = document.getElementById('timer');
        const filter = document.getElementById('filter').value.trim();

        btn.disabled = true;
        output.innerHTML = '';
        badge.innerHTML = '<span class="badge running">Running…</span>';
        startTime = Date.now();
        timerInterval = setInterval(() => {
            timer.textContent = ((Date.now() - startTime) / 1000).toFixed(1) + 's';
        }, 100);

        try {
            const res = await fetch('{{ route("dev.test-runner.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ filter }),
            });

            const text = await res.text();
            const exitCode = parseInt(res.headers.get('X-Exit-Code') ?? '1');

            const html = text.split('\n').map(colorize).filter(Boolean).join('\n');
            output.innerHTML = html;
            output.scrollTop = output.scrollHeight;

            badge.innerHTML = exitCode === 0
                ? '<span class="badge passed">✓ Passed</span>'
                : '<span class="badge failed">✗ Failed</span>';
        } catch (e) {
            output.textContent = 'Error: ' + e.message;
            badge.innerHTML = '<span class="badge failed">Error</span>';
        } finally {
            clearInterval(timerInterval);
            btn.disabled = false;
        }
    }

    document.getElementById('filter').addEventListener('keydown', e => {
        if (e.key === 'Enter') runTests();
    });
    </script>
</body>
</html>
