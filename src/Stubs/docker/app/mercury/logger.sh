#!/bin/bash

# Logger vars
Color_Off='\033[0m'
Red='\033[1;31m'
Green='\033[0;32m'
Yellow='\033[0;33m'
Blue='\033[0;34m'
Purple='\033[1;35m'

log_header() {
	DATETIME="$(date '+%Y-%m-%d %H:%M:%S')"
	HEADER="$DATETIME [$1] @ Mercury / Bootstrap:"
	echo $HEADER
}

log_info() {
	HEADER="$(log_header 'info')"
	printf "${Blue}${HEADER} ${1}\n${Color_Off}"
}

log_success() {
	HEADER="$(log_header 'success')"
	printf "${Green}${HEADER} ${1}\n${Color_Off}"
}

log_warning() {
	HEADER="$(log_header 'warn')"
	printf "${Yellow}${HEADER} ${1}\n${Color_off}"
}

log_trace() {
	HEADER="$(log_header 'trace')"
	printf "${Purple}${HEADER} ${1}\n${Color_Off}"
}

log_danger() {
	HEADER="$(log_header 'err')"
	printf "${Red}${HEADER} ${1}\n${Color_Off}"
}
