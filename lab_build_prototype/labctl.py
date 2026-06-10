#!/usr/bin/env python3
import argparse
import os
import random
import string
import subprocess
import sys
import urllib.request


def generate_password(username: str) -> str:
    digits = "".join(random.choices(string.digits, k=4))
    return f"{username}@{digits}"


def detect_ip() -> str:
    try:
        ip = urllib.request.urlopen("https://ifconfig.me", timeout=5).read().decode().strip()
        if ip:
            return ip
    except Exception:
        pass
    try:
        import socket
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception:
        pass
    return "localhost"


def create_lab(username: str) -> tuple:
    username = username.lower().strip()
    password = generate_password(username)
    lab_dir = username
    os.makedirs(lab_dir, exist_ok=True)

    dockerfile = f"""FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \\
    openssh-server sudo curl wget nano ca-certificates \\
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://code-server.dev/install.sh | sh

RUN useradd -m -s /bin/bash {username} && \\
    echo "{username}:{password}" | chpasswd && \\
    usermod -aG sudo {username}

RUN echo "root:{password}" | chpasswd

RUN mkdir /var/run/sshd && \\
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \\
    sed -i 's/PasswordAuthentication no/PasswordAuthentication yes/' /etc/ssh/sshd_config

COPY startup.sh /startup.sh
RUN chmod +x /startup.sh

EXPOSE 22 8080

CMD ["/startup.sh"]
"""

    with open(os.path.join(lab_dir, "Dockerfile"), "w", newline="\n") as f:
        f.write(dockerfile)

    startup = f"""#!/bin/bash

echo "{username}:{password}" | chpasswd
echo "root:{password}" | chpasswd

mkdir -p /home/{username}
chown {username}:{username} /home/{username}

mkdir -p /home/{username}/.config/code-server
cat > /home/{username}/.config/code-server/config.yaml << EOF
bind-addr: 0.0.0.0:8080
auth: password
password: {password}
EOF

chown -R {username}:{username} /home/{username}

/usr/sbin/sshd

su - {username} -c "code-server" &

tail -f /dev/null
"""

    with open(os.path.join(lab_dir, "startup.sh"), "w", newline="\n") as f:
        f.write(startup)

    compose = f"""version: "3.8"

services:
  lab-{username}:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: lab-{username}
    ports:
      - "8080:8080"
      - "2222:22"
    volumes:
      - ./home:/home/{username}
    restart: unless-stopped
"""

    with open(os.path.join(lab_dir, "docker-compose.yml"), "w", newline="\n") as f:
        f.write(compose)

    os.makedirs(os.path.join(lab_dir, "home"), exist_ok=True)

    return username, password, lab_dir


def write_credentials(lab_dir: str, username: str, password: str, host_ip: str):
    creds = f"""Username: {username}
Password: {password}

SSH Access:
  ssh {username}@{host_ip} -p 2222
  ssh root@{host_ip} -p 2222

Web VS Code:
  http://{host_ip}:8080
"""
    with open(os.path.join(lab_dir, "credentials.txt"), "w", newline="\n") as f:
        f.write(creds)

    print(f"[+] Credentials: {os.path.join(lab_dir, 'credentials.txt')}")


def cmd_create(args):
    username, password, lab_dir = create_lab(args.username)
    print(f"[+] Lab config created for user: {username}")
    print(f"[+] Password:   {password}")
    print(f"[+] Lab folder: {os.path.abspath(lab_dir)}/")
    print(f"[+] To build:   cd {lab_dir} && docker-compose build")
    print(f"[+] To deploy:  cd {lab_dir} && docker-compose up -d")


def cmd_build(args):
    username, password, lab_dir = create_lab(args.username)
    print(f"[+] Building image for {username}...")
    result = subprocess.run(
        ["docker-compose", "build"],
        cwd=lab_dir, capture_output=True, text=True
    )
    print(result.stdout)
    if result.returncode != 0:
        print(f"[-] Build failed:\n{result.stderr}")
        sys.exit(1)
    print(f"[+] Image built. To deploy: cd {lab_dir} && docker-compose up -d")


def cmd_deploy(args):
    username, password, lab_dir = create_lab(args.username)
    print(f"[+] Building and deploying {username}...")
    result = subprocess.run(
        ["docker-compose", "up", "-d", "--build"],
        cwd=lab_dir, capture_output=True, text=True
    )
    print(result.stdout)
    if result.returncode != 0:
        print(f"[-] Deploy failed:\n{result.stderr}")
        sys.exit(1)
    host_ip = detect_ip()
    write_credentials(lab_dir, username, password, host_ip)
    print(f"[+] Lab deployed for user: {username}")
    print(f"[+] Password: {password}")
    print(f"[+] Host IP:  {host_ip}")
    print(f"[+] Folder:   {os.path.abspath(lab_dir)}/")
    print()
    print(f"  SSH:       ssh {username}@{host_ip} -p 2222")
    print(f"  VS Code:   http://{host_ip}:8080")


def main():
    parser = argparse.ArgumentParser(description="Lab control tool — create and deploy labs")
    sub = parser.add_subparsers(dest="command", help="Commands")

    p_create = sub.add_parser("create", help="Create lab config files only")
    p_create.add_argument("--username", required=True)

    p_build = sub.add_parser("build", help="Create config files + build Docker image")
    p_build.add_argument("--username", required=True)

    p_deploy = sub.add_parser("deploy", help="Create config files + build + start container + detect IP")
    p_deploy.add_argument("--username", required=True)

    args = parser.parse_args()
    if args.command == "create":
        cmd_create(args)
    elif args.command == "build":
        cmd_build(args)
    elif args.command == "deploy":
        cmd_deploy(args)
    else:
        parser.print_help()


if __name__ == "__main__":
    main()
