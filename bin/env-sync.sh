#!/usr/bin/env bash
# Sync .env with .env.example: reorder keys, add missing ones, preserve existing values.
# .env.example is the source of truth for key set, order, comments and section breaks.
# Orphan keys (present in .env but missing from .env.example) are preserved at the bottom.

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$PROJECT_ROOT/.env"
EXAMPLE="$PROJECT_ROOT/.env.example"

[ -f "$EXAMPLE" ] || { echo "✗ $EXAMPLE not found" >&2; exit 1; }

if [ ! -f "$ENV_FILE" ]; then
    cp "$EXAMPLE" "$ENV_FILE"
    echo "✓ created $ENV_FILE from $EXAMPLE"
    exit 0
fi

TMP="$(mktemp)"
BACKUP="$ENV_FILE.bak"
trap 'rm -f "$TMP"' EXIT

awk -v env_file="$ENV_FILE" '
BEGIN {
    # Pass 1: load current .env values (order-preserving via array of keys)
    while ((getline line < env_file) > 0) {
        if (match(line, /^[A-Z][A-Z0-9_]*=/)) {
            eq = index(line, "=")
            key = substr(line, 1, eq - 1)
            val = substr(line, eq + 1)
            env_value[key] = val
            env_seen[key] = 1
        }
    }
    close(env_file)
}
# Pass 2: walk .env.example
{
    if (match($0, /^[A-Z][A-Z0-9_]*=/)) {
        eq = index($0, "=")
        key = substr($0, 1, eq - 1)
        seen_in_example[key] = 1
        if (key in env_value) {
            print key "=" env_value[key]
        } else {
            print $0
            added[++added_n] = key
        }
    } else {
        print $0
    }
}
END {
    # Orphans: keys in .env but not in .env.example
    has_orphan = 0
    for (k in env_seen) {
        if (!(k in seen_in_example)) {
            if (!has_orphan) {
                print ""
                print "# --- Local-only keys (not in .env.example) ---"
                has_orphan = 1
            }
            print k "=" env_value[k]
            orphan[++orphan_n] = k
        }
    }
    # Diagnostics to stderr
    if (added_n > 0) {
        printf "added %d key(s) from .env.example:\n", added_n > "/dev/stderr"
        for (i = 1; i <= added_n; i++) printf "  + %s\n", added[i] > "/dev/stderr"
    }
    if (orphan_n > 0) {
        printf "kept %d local-only key(s) at bottom:\n", orphan_n > "/dev/stderr"
        for (i = 1; i <= orphan_n; i++) printf "  ~ %s\n", orphan[i] > "/dev/stderr"
    }
    if (added_n == 0 && orphan_n == 0) {
        print ".env already in sync with .env.example" > "/dev/stderr"
    }
}
' "$EXAMPLE" > "$TMP"

if cmp -s "$ENV_FILE" "$TMP"; then
    echo "✓ no changes"
    exit 0
fi

cp "$ENV_FILE" "$BACKUP"
mv "$TMP" "$ENV_FILE"
chmod 600 "$ENV_FILE"
echo "✓ .env updated (backup at .env.bak)"
