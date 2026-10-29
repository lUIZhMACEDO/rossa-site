#!/usr/bin/env bash
set -u

status=0
names=()
marks=()

run() {
    local name="$1"
    shift
    echo ""
    echo "==> ${name}"
    names+=("${name}")
    if "$@"; then
        marks+=("✓")
    else
        marks+=("✗")
        status=1
    fi
}

run "audit" composer audit --no-dev
run "lint:check" vendor/bin/pint --test
run "analyse" vendor/bin/phpstan analyse --memory-limit=2G
run "security" semgrep scan --config p/default --config p/php --error
run "test" php artisan test

if [[ "${status}" -eq 0 ]]; then
    run "build" npm run build
else
    echo ""
    echo "Skipping npm run build because a check failed."
fi

echo ""
echo "Summary"
for i in "${!names[@]}"; do
    printf "  %s  %s\n" "${marks[$i]}" "${names[$i]}"
done

echo ""
if [[ "${status}" -eq 0 ]]; then
    echo "All checks passed."
else
    echo "One or more checks failed."
fi

exit "${status}"
