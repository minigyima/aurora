#!/bin/bash
source /mercury/index.sh

npm
pnpm install @soketi/soketi

pnpm run --if-present build

$PHP_PATH artisan storage:link
