
FROM ubuntu:20.04
RUN apt-get update && apt-get install -y openssh-server sudo
RUN useradd -ms /bin/bash admin
RUN echo 'admin:admin@911_labs' | chpasswd
RUN adduser admin sudo
CMD ["/usr/sbin/sshd","-D"]
