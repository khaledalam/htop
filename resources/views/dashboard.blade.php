<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel HTop Realtime</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: monospace;
            padding: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        thead {
            position: sticky;
            top: 0;
            background: #222;
        }
        th, td {
            padding: 8px;
            border-bottom: 1px solid #333;
        }
        tr:hover {
            background: #1a1a1a;
        }
    </style>
</head>
<body>
<h1>🔁 Laravel HTop Live Dashboard</h1>
<table>
    <thead>
    <tr>
        <th>Method</th>
        <th>Path</th>
        <th>Status</th>
        <th>Duration</th>
        <th>Time</th>
    </tr>
    </thead>
    <tbody id="htop-requests"></tbody>
</table>

<script>
    const ws = new WebSocket(`ws://${location.hostname}:8080/app/laravel?protocol=7&client=js&version=1.0.0&flash=false`);

    ws.onopen = () => {
        console.log('✅ Connected to WebSocket server');
        ws.send(JSON.stringify({
            event: "pusher:subscribe",
            data: {
                channel: "htop"
            }
        }));
    };

    ws.onmessage = (message) => {
        const parsed = JSON.parse(message.data);

        if (parsed.event === 'NewRequestEvent') {
            const r = parsed.data;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${r.method}</td>
                <td>${r.path}</td>
                <td>${r.status}</td>
                <td>${r.duration}</td>
                <td>${r.timestamp}</td>
            `;

            const table = document.getElementById('htop-requests');
            table.insertBefore(row, table.firstChild); // recent first

            if (table.children.length > 50) {
                table.removeChild(table.lastChild);
            }
        }
    };

    ws.onerror = (err) => {
        console.error('WebSocket error:', err);
    };
</script>

</body>
</html>
