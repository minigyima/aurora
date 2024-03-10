#!/bin/bash
source /mercury/index.sh

pnpm update
pnpm install chokidar
pnpm install @soketi/soketi

pnpm run --if-present build

$PHP_PATH artisan storage:link
