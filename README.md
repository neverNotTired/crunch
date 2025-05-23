# crunch
Image optimisation on the fly ✈️

## Prerequisites
Spin up a new server (droplet, AWS, etc.) with the following:
- Ubuntu 20.04

## Installation
1. SSH into your server
Pull down this repo:
```bash
git clone git@github.com:neverNotTired/crunch.git
cd crunch
```
2. This script should handle the rest:
```bash
chmod +x setup-thumbor.sh
./setup-thumbor.sh
```
*If not try running as super user: `sudo ./setup-thumbor.sh`*
3. Once the script has finished, you should be able to access the server via your browser at `http://<your-server-ip>:8888` (you'll need to set up a domain name and SSL cert for production use).
