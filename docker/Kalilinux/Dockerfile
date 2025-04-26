FROM kalilinux/kali-rolling

ENV DEBIAN_FRONTEND=noninteractive

# Create user 'kali' and set password
RUN apt-get update && \
    apt-get install -y openssh-server curl sudo && \
    useradd -m -s /bin/bash kali && \
    echo 'kali:admin123' | chpasswd && \
    echo 'kali ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

# Install and configure SSH
RUN mkdir -p /var/run/sshd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    echo 'root:admin123' | chpasswd

# Install Code Server
RUN curl -fsSL https://code-server.dev/install.sh | sh

# Setup Code Server for user 'kali'
RUN mkdir -p /home/kali/.config/code-server && \
    echo 'bind-addr: 0.0.0.0:8080' > /home/kali/.config/code-server/config.yaml && \
    echo 'auth: password' >> /home/kali/.config/code-server/config.yaml && \
    echo 'password: vscode123' >> /home/kali/.config/code-server/config.yaml && \
    echo 'cert: false' >> /home/kali/.config/code-server/config.yaml && \
    chown -R kali:kali /home/kali/.config

# Expose SSH and Code Server ports
EXPOSE 22 8080

# Start SSH and Code Server
CMD service ssh start && sudo -u kali code-server
