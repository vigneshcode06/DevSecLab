import socket
import threading

# Settings
LISTEN_HOST = '0.0.0.0'    # Accept connections from all devices in your network
LISTEN_PORT = 8080         # Port to listen on
TARGET_HOST = '127.0.0.1'  # Docker web service host (usually localhost)
TARGET_PORT = 8000         # Docker web service port

def handle_client(client_socket):
    # Connect to the target
    target_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    target_socket.connect((TARGET_HOST, TARGET_PORT))

    # Start forwarding data
    def forward(src, dst):
        while True:
            try:
                data = src.recv(4096)
                if len(data) == 0:
                    break
                dst.send(data)
            except:
                break

    # Start threads for bi-directional communication
    threading.Thread(target=forward, args=(client_socket, target_socket)).start()
    threading.Thread(target=forward, args=(target_socket, client_socket)).start()

def start_forwarder():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.bind((LISTEN_HOST, LISTEN_PORT))
    server.listen(5)
    print(f"[+] Listening on {LISTEN_HOST}:{LISTEN_PORT} and forwarding to {TARGET_HOST}:{TARGET_PORT}")

    while True:
        client_sock, addr = server.accept()
        print(f"[+] Accepted connection from {addr[0]}:{addr[1]}")
        client_thread = threading.Thread(target=handle_client, args=(client_sock,))
        client_thread.start()

if __name__ == "__main__":
    start_forwarder()
