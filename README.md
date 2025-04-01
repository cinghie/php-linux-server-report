# Linux Server Report
Generate and send a monthly Linux Server Report

## Install Tools

```
sudo apt-get install pandoc
```

```
sudo apt-get install texlive-latex-base
```

## Create script and set permission

```
cd /usr/local/bin
```

```
nano generate_report.sh
```

```
chmod +x generate_report.sh
```

## Set Script

```
#!/bin/bash

# Directory e file temporanei
OUTPUT_DIR="/tmp/report"
mkdir -p "$OUTPUT_DIR"
REPORT_FILE="$OUTPUT_DIR/report.md"
PDF_FILE="$OUTPUT_DIR/report.pdf"

# Inizio report
{
  echo "# Report del Sistema"
  echo "Generato il: $(date)"
  echo ""

  # Informazioni Hardware
  echo "## Informazioni Hardware"
  echo '```'
  # Il comando lshw potrebbe richiedere privilegi elevati
  if command -v lshw &> /dev/null; then
    sudo lshw -short
  else
    echo "lshw non installato"
  fi
  echo '```'
  echo ""

  # Informazioni Software
  echo "## Informazioni Software"
  echo '```'
  if [ -f /etc/os-release ]; then
    cat /etc/os-release
  else
    echo "File /etc/os-release non trovato"
  fi
  echo '```'
  echo ""

  # Informazioni di Sicurezza
  echo "## Informazioni di Sicurezza"
  echo '```'
  if command -v ufw &> /dev/null; then
    sudo ufw status verbose
  else
    echo "ufw non installato o configurato"
  fi
  echo '```'
  echo ""

  # Log del Server (ultime 100 righe del syslog)
  echo "## Log del Server"
  echo '```'
  if [ -f /var/log/syslog ]; then
    sudo tail -n 100 /var/log/syslog
  elif [ -f /var/log/messages ]; then
    sudo tail -n 100 /var/log/messages
  else
    echo "Nessun log di sistema trovato"
  fi
  echo '```'
} > "$REPORT_FILE"

# Converte il report in PDF usando pandoc
if command -v pandoc &> /dev/null; then
  pandoc "$REPORT_FILE" -o "$PDF_FILE"
  echo "Report generato in: $PDF_FILE"
else
  echo "Pandoc non è installato. Installa pandoc per generare il PDF."
fi

```

## Run Scripts

```
sudo ./generate_report.sh
```
