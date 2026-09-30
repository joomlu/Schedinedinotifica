#!/usr/bin/env bash
# Compatibility entry point. The old automatic-migration flow is retired.
set -euo pipefail
SCRIPT_DIRECTORY=${BASH_SOURCE[0]%/*}
if [[ "$SCRIPT_DIRECTORY" == "${BASH_SOURCE[0]}" ]]; then SCRIPT_DIRECTORY=.; fi
ROOT=$(cd -- "$SCRIPT_DIRECTORY/.." && pwd -P) || exit 1
exec bash "$ROOT/deploy.sh" "$@"
