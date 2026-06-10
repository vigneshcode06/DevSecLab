#!/usr/bin/env python3
import argparse
import os
import subprocess
import sys
import urllib.request


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


def update_credentials(lab_dir: str, username: str, host_ip: str):
    creds_path = os.path.join(lab_dir, "credentials.txt")
    if not os.path.exists(creds_path):
        print(f"[-] credentials.txt not found in {lab_dir}")
        return
    with open(creds_path, "r") as f:
        lines = f.readlines()
    new_lines = []
    for line in lines:
        if line.startswith("ssh ") or line.startswith("  http"):
            continue
        if line.strip().startswith("SSH Access:") or line.strip().startswith("Web VS Code:"):
            continue
        new_lines.append(line)
    password = None
    for line in lines:
        if line.startswith("Password:"):
            password = line.split(":", 1)[1].strip()
            break
    new_lines.append(f"\nSSH Access:\n")
    new_lines.append(f"  ssh {username}@{host_ip} -p 2222\n")
    new_lines.append(f"  ssh root@{host_ip} -p 2222\n")
    new_lines.append(f"\nWeb VS Code:\n")
    new_lines.append(f"  http://{host_ip}:8080\n")
    with open(creds_path, "w", newline="\n") as f:
        f.writelines(new_lines)
    print(f"[+] Credentials updated with IP: {host_ip}")


def compose_cmd(lab_dir: str, *args):
    result = subprocess.run(
        ["docker-compose"] + list(args),
        cwd=lab_dir, capture_output=True, text=True
    )
    print(result.stdout, end="")
    if result.returncode != 0:
        print(f"[-] Command failed:\n{result.stderr}", end="")
        sys.exit(1)
    return result


def main():
    parser = argparse.ArgumentParser(description="Deploy and manage labs")
    parser.add_argument("--username", required=True, help="Username of the lab")
    parser.add_argument("action", choices=["start", "stop", "restart"], help="Action to perform")

    args = parser.parse_args()
    username = args.username.lower().strip()
    lab_dir = username

    if not os.path.isdir(lab_dir):
        print(f"[-] Lab folder '{lab_dir}' not found. Run 'labctl.py create --username {username}' first.")
        sys.exit(1)

    if args.action == "start":
        compose_cmd(lab_dir, "up", "-d")
        host_ip = detect_ip()
        update_credentials(lab_dir, username, host_ip)
        print(f"[+] Lab '{username}' started")
        print(f"    SSH:       ssh {username}@{host_ip} -p 2222")
        print(f"    VS Code:   http://{host_ip}:8080")

    elif args.action == "stop":
        compose_cmd(lab_dir, "down")
        print(f"[+] Lab '{username}' stopped")

    elif args.action == "restart":
        compose_cmd(lab_dir, "restart")
        host_ip = detect_ip()
        update_credentials(lab_dir, username, host_ip)
        print(f"[+] Lab '{username}' restarted")
        print(f"    SSH:       ssh {username}@{host_ip} -p 2222")
        print(f"    VS Code:   http://{host_ip}:8080")


if __name__ == "__main__":
    main()
