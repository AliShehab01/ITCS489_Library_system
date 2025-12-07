<?php
// Simple test page to check if API is working
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <title>API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        pre {
            background: #f5f5f5;
            padding: 15px;
            overflow: auto;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <h1>Catalog API Test</h1>

    <h2>1. Direct PHP Test</h2>
    <?php
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';
    $dbname = 'library_system';

    echo "<p>Connecting to database...</p>";

    $conn = @new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_errno) {
        echo "<p class='error'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p class='success'>Connected successfully!</p>";

        // Check tables
        $tables = $conn->query("SHOW TABLES");
        echo "<p>Tables in database:</p><ul>";
        while ($row = $tables->fetch_array()) {
            echo "<li>" . htmlspecialchars($row[0]) . "</li>";
        }
        echo "</ul>";

        // Check books
        $books = $conn->query("SELECT COUNT(*) as cnt FROM books");
        if ($books) {
            $count = $books->fetch_assoc()['cnt'];
            echo "<p>Books in catalog: <strong>{$count}</strong></p>";

            if ($count > 0) {
                $sample = $conn->query("SELECT id, title, author, quantity, status FROM books LIMIT 5");
                echo "<p>Sample books:</p><pre>";
                while ($row = $sample->fetch_assoc()) {
                    print_r($row);
                }
                echo "</pre>";
            }
        } else {
            echo "<p class='error'>Could not query books table</p>";
        }

        $conn->close();
    }
    ?>

    <h2>2. API URL Test</h2>
    <p>API URL: <code>CatalogSearch_Browsingbackend.php</code></p>
    <button onclick="testApi()">Test API</button>
    <pre id="apiResult">Click button to test...</pre>

    <script>
        async function testApi() {
            const resultEl = document.getElementById('apiResult');
            resultEl.textContent = 'Loading...';

            try {
                const res = await fetch('./CatalogSearch_Browsingbackend.php');
                const text = await res.text();

                resultEl.textContent = 'Status: ' + res.status + '\n\nResponse:\n' + text;

                // Try to parse as JSON
                try {
                    const json = JSON.parse(text);
                    resultEl.textContent += '\n\nParsed JSON:\n' + JSON.stringify(json, null, 2);
                } catch (e) {
                    resultEl.textContent += '\n\nNot valid JSON: ' + e.message;
                }
            } catch (err) {
                resultEl.textContent = 'Fetch error: ' + err.message;
            }
        }
    </script>

    <h2>3. Go to Catalog</h2>
    <p><a href="CatalogSearch_Browsing-EN.php">Open Catalog Page</a></p>
</body>

</html>