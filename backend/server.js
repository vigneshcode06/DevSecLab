const express = require('express');
const { exec } = require('child_process');
const path = require('path');
const os = require('os'); // For load average

const app = express();
const port = 3000;

app.use(express.json());

// Serve the HTML file
app.get('/', (req, res) => {
    res.sendFile(path.join('C:/Users/Admin/Music/code/project lab/docker-ssh-vscode/frontend', 'ubuntu.html'));
});

// Handle the POST request to execute commands
app.post('/execute-command', (req, res) => {
    const command = req.body.command;

    exec(command, (error, stdout, stderr) => {
        if (error) {
            return res.status(500).json({ output: `Error: ${error.message}` });
        }
        if (stderr) {
            return res.status(500).json({ output: `stderr: ${stderr}` });
        }
        res.json({ output: stdout });
    });
});

// 🧠 Load average route
app.get('/loadavg', (req, res) => {
    const load = os.loadavg();
    res.json({
        one: load[0].toFixed(2),
        five: load[1].toFixed(2),
        fifteen: load[2].toFixed(2)
    });
});

app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});





