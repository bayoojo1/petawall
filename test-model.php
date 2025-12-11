<!DOCTYPE html>
<html>
<head>
    <title>Threat Modeling Test</title>
</head>
<body>
    <button onclick="testThreatModeling()">Test Threat Modeling</button>
    <div id="result"></div>

    <script>
        async function testThreatModeling() {
            const testData = {
                name: 'Test System',
                type: 'web_application',
                components: [
                    { name: 'Web Server', type: 'web_server', sensitivity: 'medium' },
                    { name: 'Database', type: 'database', sensitivity: 'high' }
                ],
                data_flows: [
                    { source: 'Web Server', destination: 'Database', protocol: 'HTTPS' }
                ],
                methodologies: ['stride'],
                frameworks: ['owasp']
            };

            try {
                const response = await fetch('test_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        tool: 'threat_modeling',
                        system_data: JSON.stringify(testData)
                    })
                });

                const result = await response.json();
                document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
            } catch (error) {
                document.getElementById('result').innerHTML = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>