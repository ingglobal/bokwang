CentOS WEB 사이트의 data 폴더만 백업
원격 저장 서버 : ssh ing@222.232.202.298 -p22

1) root로 로그인 한 후, root폴더로 이동
# cd /root/

2) SHELL 디렉토리가 없으면 생성하고, SHELL폴더로 이동
# mkdir SHELL
# cd SHELL 

3) BACKUP 디렉토리가 없으면 생성하고, BACKUP 폴더로 이동
# mkdir BACKUP
# cd BACKUP 

4) web_backup.sh 파일을 생성한다
# vi web_backup.sh 
-------------------------------
#!/bin/bash

## 변수설정
HOST="${/usr/bin/hostname}"
LOG="/tmp/backup.log"
PUSH
DATE
# 백업할 디렉토리 / 파일을 지정
BAK_LIST
# 백업 디렉토리
BAK_PATH
# 백업 파일명
BAK_FILE


## 스토리지에 마운트

## ---- 로그기록 시작

## 백업

## ---- 로그기록 끝

## 스토리지에 언마운트

## 텔레그램으로 백업 로그를 전송 

-------------------------------
