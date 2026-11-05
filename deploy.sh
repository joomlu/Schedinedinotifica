#!/usr/bin/env bash
# Reviewed deployment entry point; no application code runs in this shell.
set -euo pipefail
SCRIPT_DIRECTORY=${BASH_SOURCE[0]%/*}
if [[ "$SCRIPT_DIRECTORY" == "${BASH_SOURCE[0]}" ]]; then SCRIPT_DIRECTORY=.; fi
ROOT=$(cd -- "$SCRIPT_DIRECTORY" && pwd -P) || exit 1
command -v python3 >/dev/null || { printf 'ERROR: python3 is required.\n' >&2; exit 1; }
exec python3 "$ROOT/scripts/deployment/deploy.py" "$@"
