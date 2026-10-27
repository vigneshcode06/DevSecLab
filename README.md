⚠️ **Note:** This repository contains **prototype-level code** and is not intended for production use. The complete, production-ready implementation is maintained in a private GitLab repository for security reasons 


# 🔐 DevSecLabs — Docker-Based Virtual Lab Environment

Welcome to **DecSecLab** — a fully containerized virtual lab platform designed to provide isolated, on-demand development and testing environments. Built using Docker, it enables users to launch dedicated lab instances with secure SSH and VS Code access.

The platform integrates essential services such as MySQL, MongoDB, Adminer, and more, allowing users to simulate real-world development, DevOps workflows, and security testing environments within a controlled setup.

> 🚀 Designed for hands-on learning, backend development, DevOps practice, and cybersecurity labs.


## 🚀 Features

- ✅ **SSH & VS Code Server Access**
- 📦 Docker-based isolated lab instances
- 💻 Real-time system stats (CPU, Memory, I/O)
- 🔁 Start/Stop lab containers from web UI
- 🔒 Private mini SSH servers for each user
- 🧩 CTF Missions
- 📚 Web-based terminal interface with logs
- 🎯 Easily Port-Forward services to localhost

---

## 🧪 Free Labs Available

You can access the following **free labs**:

- 🧰 Essentials Lab  
  `Status: Instance Down`

---

## 💎 Premium Labs (Beta Access)

Below are premium labs (currently inactive):

- 📦 MinIO S3  
- ☕ Java Dev Server  
- 🛠 MySQL WebBench  
- 🐧 Ubuntu Jammy LTS  
- ⚔ Kali Linux  
- ⚙ Buildroot Lab  
- 🐳 Docker Lab  
- 📉 gcc3 Exploit Lab  
- 📺 Nginx RTMP Lab  
- 🔄 Node-RED Lab  
- 📡 RTOS Lab for Zephyr  

---

## 🧩 Built-in Services

The lab includes a suite of integrated services, accessible through **port forwarding** or **VS Code remote**:

| Service | Hostname | Port | Description |
|--------|----------|------|-------------|
| **MySQL** | `mysql.Vlab` | 3306 | World's most popular open source database |
| **MariaDB** | `mariadb.Vlab` | 3306 | MySQL-compatible fork |
| **PostgreSQL** | `postgresql.Vlab` | 5432 | Advanced open-source SQL DB |
| **Adminer** | `adminer.Vlab` | 8080 | Lightweight DB management UI |
| **MongoDB** | `mongodb.Vlab` | 27017 | Document-based NoSQL DB |
| **RabbitMQ** | `rabbitmq.Vlab` | 15672 | Open-source message broker |
| **Redis** | `redis.Vlab` | 6379 | In-memory data store |
| **RedisInsight** | `redisinsight.Vlab` | 8001 | Redis admin GUI |
| **Memcached** | `memcached.Vlab` | 11211 | Memory-based caching system |
| **MinIO** | `minios3.Vlab` | 9000/9001 | S3-compatible object storage |

> 🔧 Use `socat` or native port forwarding to access these in your VS Code or terminal setup.

---

## 🧠 Access Method

### 🔐 SSH Access
```bash
ssh user@<your-lab-host>

💻 VS Code Remote SSH
Open VS Code

Install Remote - SSH extension

Connect to your lab:

kotlin
Copy
Edit
user@<your-lab-host>
🌐 Web Interface
Monitor system stats (CPU, RAM, I/O)

View and control logs from containers

Start/Stop labs with a single click

Access service URLs via port forwarding

📡 Port Forwarding Guide
To use services locally:

bash
Copy
Edit
ssh -L <local_port>:<service_host>:<service_port> user@<your-lab-host>
Example:

bash
Copy
Edit
ssh -L  user@lab.server.com
Now access localhost:3306 for MySQL from your system.

```

🎮 CTF Missions
Includes multiple CTF-style hacking challenges (under missions/):

Reverse engineering

Privilege escalation

Web exploits

Real-world security scenarios

🔒 Coming Soon
📊 Real-time monitoring dashboard (Grafana)

🔐 Per-user container sandboxing

🧰 Lab customizer UI

📜 Persistent storage options


🤝 Contributing
Pull requests are welcome! If you'd like to add new services, improve the UI, or suggest missions, feel free to fork and open a PR.

🧑‍💻 Author
Built with 💙 by Vignesh



---
