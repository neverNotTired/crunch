#!/bin/bash

set -e

echo "Setting up Thumbor..."

echo -e "For more information, visit:\nhttps://github.com/thumbor/thumbor?tab=readme-ov-file"

echo "..."

# function to install Docker
install_docker() {
    echo "🐳 Docker not found. Installing Docker..."

    sudo apt update
    sudo apt install -y apt-transport-https ca-certificates curl software-properties-common gnupg lsb-release

    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io
}

# Check if Docker is already installed
if ! command -v docker &> /dev/null; then
    install_docker
else
    echo "Docker is already installed."
fi

# Check/add user perms
if ! groups $USER | grep -q '\bdocker\b'; then
    echo "⚠️  You are not in the 'docker' group. Run:"
    echo "   sudo usermod -aG docker \$USER && newgrp docker"
    echo "   Or run this script with sudo."
    exit 1
fi

# Check if Docker is running
sudo systemctl start docker
sudo systemctl enable docker

# Set up a Thumbor container
echo "Setting up a Thumbor container..."

# Remove any existing Thumbor container
if [ "$(docker ps -aq -f name=thumbor)" ]; then
    echo "🧹 Removing existing Thumbor container..."
    docker rm -f thumbor
fi

# Run Thumbor container
echo "🚀 Starting Thumbor container..."
docker run -d --name thumbor \
  -p 80:8000 \
  -e AUTO_WEBP=True \
  -e ALLOW_UNSAFE_URL=True \
  apsl/thumbor

# Health check
echo "Testing Thumbor..."
sleep 3
curl -I http://localhost:8888/unsafe/1200x578/filters:format(webp)/https://cremorne.pebble.design/wp-content/uploads/2025/05/cremorne-family-accommodation-one-bedroom-apartment-military-road-bedside-details-844x944.jpg || echo "⚠️ Thumbor test failed. Check Docker logs."

echo "✅ Thumbor is running. Access it at: http://<your-server-ip>/"
