const express = require('express');
const { exec } = require('child_process');
const path = require('path');
const app = express();
const port = 3000;

app.use(express.json());

// Serve the HTML file from the new path
app.get('/', (req, res) => {
    res.sendFile(path.join('C:/Users/Admin/Music/code/project lab/docker-ssh-vscode/frontend', 'ubuntu.html')); // Update the path
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

app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});
