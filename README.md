PEAR krisp::
====

***krisp*** pear package는 IPv4 주소에 대한 정보를 확인 하거나 또는 Network 계산을 위한
API를 제공 합니다.

krisp package 는 [mod_krisp](https://github.com/OOPS-ORG-PHP/mod_krisp) extension 을
사용하기 힘든 환경이나 또는 mod_krisp 대신 사용할 수 있는 pure php pear code 입니다.
사용법은 소스안의 test.php 를 참고하고, 이 파일에 함수 proto type 에 대한 설명이 주석으로
제공이 됩니다.

이 버전은 [libkrisp](https://github.com/Joungkyun/libkrisp) 3.0.0 이상의 database scheme이
필요하며, 이전 버전의 database 를 사용하려고 한다면 pear_krisp 1.x 버전을 사용 하십시오.


## Requirements:

* [libkrisp](https://github.com/Joungkyun/libkrisp) 3.0.0 이상 버전의 database shcme
* ***[IPCALC](https://github.com/OOPS-ORG-PHP/IPCALC)*** pear package (http://pear.oops.org/)  
  ***[IPCALC](https://github.com/OOPS-ORG-PHP/IPCALC)*** pear package는 ***pear.oops.org***에서
  제공 합니다. http://pear.oops.org/ 를 참조하여 설치 하십시오. 간단하게 다음 명령으로 설치가 가능합니다.

```bash
  [root@host ~]$ pear channel-discover pear.oops.org
  [root@host ~]$ pear install oops/IPCALC
```

## Usages:

소스 코드의 test.php를 참조 하거나, [pear_KRISP Reference](http://pear.oops.org/docs/krisp/KRISP.html) page를 참조 하십시오.
